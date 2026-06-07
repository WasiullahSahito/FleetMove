<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class BookNowController extends Controller
{
    public function index(string $status = 'all')
    {
        $query = DB::table('trip_requests as tr')
            ->leftJoin('trip_request_coordinates as trc', 'trc.trip_request_id', '=', 'tr.id')
            ->leftJoin('trip_request_fees as trf',        'trf.trip_request_id', '=', 'tr.id')
            ->leftJoin('users as customer', 'customer.id', '=', 'tr.customer_id')
            ->leftJoin('users as driver',   'driver.id',   '=', 'tr.driver_id')
            ->leftJoin('zones', 'zones.id', '=', 'tr.zone_id')
            ->leftJoin('vehicle_categories as vc', 'vc.id', '=', 'tr.vehicle_category_id')
            ->whereIn('tr.ride_request_type', ['book_now', 'scheduled']) // FIXED: was ->where() with array
            ->whereNull('tr.deleted_at')
            ->select([
                'tr.id',
                'tr.ref_id',
                'tr.current_status',
                'tr.scheduled_at',
                'tr.estimated_fare',
                'tr.actual_fare',
                'tr.estimated_distance',
                'tr.payment_method',
                'tr.payment_status',
                'tr.note',
                'tr.created_at',
                'trc.pickup_address',
                'trc.destination_address',
                'trf.vat_tax',
                'trf.admin_commission',
                'trf.cancellation_fee as fee_cancellation',
                'trf.delay_fee',
                'trf.idle_fee',
                'customer.first_name as customer_first_name',
                'customer.last_name  as customer_last_name',
                'customer.phone      as customer_phone',
                'driver.first_name   as driver_first_name',
                'driver.last_name    as driver_last_name',
                'driver.phone        as driver_phone',
                'zones.name          as zone_name',
                'vc.name             as vehicle_category_name',
            ]);

        if ($status !== 'all') {
            $query->where('tr.current_status', $status);
        }

        $bookings = $query->latest('tr.created_at')->paginate(15);

        foreach ($bookings->items() as $booking) {
            if (empty($booking->driver_first_name) && $booking->current_status === 'pending') {
                $tempDrivers = DB::table('temp_trip_notifications')
                    ->where('trip_request_id', $booking->id)
                    ->pluck('user_id');

                // REPLACE WITH:
                if ($tempDrivers->count() >= 1) {
                    if ($tempDrivers->count() === 1) {
                        $preAssignedDriver = DB::table('users')->where('id', $tempDrivers->first())->first();
                        if ($preAssignedDriver) {
                            $booking->driver_first_name = $preAssignedDriver->first_name;
                            $booking->driver_last_name  = $preAssignedDriver->last_name;
                            $booking->driver_phone      = $preAssignedDriver->phone;
                        }
                    } else {
                        $booking->driver_first_name = $tempDrivers->count() . ' drivers';
                        $booking->driver_last_name  = 'notified';
                        $booking->driver_phone      = '';
                    }
                }
            }
        }

        $counts = DB::table('trip_requests')
            ->where('ride_request_type', 'book_now')
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as `all`,
                SUM(CASE WHEN current_status='pending'   THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN current_status='confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN current_status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN current_status='cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->first();

        if (!$counts || is_null($counts->all)) {
            $counts = (object)['all' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        }

        return view('adminmodule::admin.book-now.index', compact('bookings', 'status', 'counts'));
    }

    public function create()
    {
        $customers = DB::table('users')
            ->where('user_type', 'customer')->where('is_active', 1)->whereNull('deleted_at')
            ->get(['id', 'first_name', 'last_name', 'phone']);

        $drivers = DB::table('users')
            ->where('user_type', 'driver')->where('is_active', 1)->whereNull('deleted_at')
            ->get(['id', 'first_name', 'last_name', 'phone'])
            ->values()
            ->map(function ($driver, $index) {
                $driver->serial = $index + 1;
                return $driver;
            });

        $zones = DB::table('zones')
            ->whereNull('deleted_at')
            ->get(['id', 'name']);

        $vehicleCategories = DB::table('vehicle_categories')
            ->where('is_active', 1)->whereNull('deleted_at')
            ->orderByRaw("FIELD(name, '4 Seater', '6 Seater', '8 Seater', 'Wheelchair')")
            ->get(['id', 'name', 'type']);

        $mapApiKey = 'AIzaSyCKiFS2h2oGRmQHKLCNJtf-8Z6QAFlGAns';
        $mapSetting = DB::table('business_settings')->where('key_name', 'map_api_key_server')->first();
        if ($mapSetting) {
            $decoded = json_decode($mapSetting->value, true);
            $mapApiKey = is_array($decoded) ? ($decoded['map_api_key_server'] ?? $mapSetting->value) : $mapSetting->value;
        }

        return view(
            'adminmodule::admin.book-now.create',
            compact('customers', 'drivers', 'zones', 'vehicleCategories', 'mapApiKey')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'         => 'required|exists:users,id',
            'zone_id'             => 'required|exists:zones,id',
            'vehicle_category_id' => 'required|exists:vehicle_categories,id',
            'pickup_address'      => 'required|string|max:255',
            'pickup_lat'          => 'required|numeric',
            'pickup_lng'          => 'required|numeric',
            'destination_address' => 'required|string|max:255',
            'destination_lat'     => 'required|numeric',
            'destination_lng'     => 'required|numeric',
            'scheduled_at'        => 'required',
            'driver_id'           => 'nullable|exists:users,id',
            'payment_method'      => 'nullable|string|max:50',
            'note'                => 'nullable|string|max:500',
        ]);

        // --- Distance & Polyline ---
        $origin      = [$request->pickup_lat, $request->pickup_lng];
        $destination = [$request->destination_lat, $request->destination_lng];
        $distance        = 0;
        $encodedPolyline = null;

        if (function_exists('getRoutes')) {
            $routeData = getRoutes($origin, $destination);
            if (!isset($routeData['error'])) {
                if (isset($routeData[1]['distance'])) {
                    $distance        = (float) $routeData[1]['distance'];
                    $encodedPolyline = $routeData[1]['encoded_polyline'];
                } elseif (isset($routeData[0]['distance'])) {
                    $distance        = (float) $routeData[0]['distance'];
                    $encodedPolyline = $routeData[0]['encoded_polyline'];
                }
            }
        }

        if ($distance == 0) {
            $distance = $this->calculateDistance(
                $request->pickup_lat,
                $request->pickup_lng,
                $request->destination_lat,
                $request->destination_lng
            );
        }

        // --- Fare ---
        $fare = DB::table('trip_fares')
            ->where('zone_id', $request->zone_id)
            ->where('vehicle_category_id', $request->vehicle_category_id)
            ->first();

        if ($fare) {
            $baseFare      = $fare->base_fare;
            $baseFarePerKm = $fare->base_fare_per_km;
        } else {
            $defaultFare = DB::table('zone_wise_default_trip_fares')
                ->where('zone_id', $request->zone_id)
                ->first();

            if (!$defaultFare) {
                Toastr::error('No fare configured for this zone/category. Please set up fares first.');
                return back()->withInput();
            }
            $baseFare      = $defaultFare->base_fare;
            $baseFarePerKm = $defaultFare->base_fare_per_km;
        }

        $subTotal   = $baseFare + ($distance * $baseFarePerKm);
        $vatRow     = DB::table('business_settings')->where('key_name', 'vat_percent')->first();
        $vatPercent = $vatRow ? (float) json_decode($vatRow->value) : 0;
        $vatAmount  = ($subTotal * $vatPercent) / 100;
        $totalFare  = $subTotal + $vatAmount;

        // --- IDs & timestamps ---
        $tripId = (string) Str::uuid();
        $refId  = 'BN-' . strtoupper(Str::random(8));
        $now    = now();

        // --- Parse scheduled_at safely ---
        try {
            $scheduledAt = Carbon::parse($request->scheduled_at)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            $scheduledAt = $request->scheduled_at;
        }

        // --- Insert everything in a transaction ---
        DB::beginTransaction();
        try {
            // 1. trip_requests
            DB::table('trip_requests')->insert([
                'id'                   => $tripId,
                'ref_id'               => $refId,
                'customer_id'          => $request->customer_id,
                'driver_id'            => null,
                'vehicle_category_id'  => $request->vehicle_category_id,
                'vehicle_id'           => null,
                'zone_id'              => $request->zone_id,
                'area_id'              => null,
                'estimated_fare'       => $totalFare,
                'actual_fare'          => $totalFare,
                'estimated_distance'   => $distance,
                'actual_distance'      => $distance,
                'encoded_polyline'     => $encodedPolyline,
                'paid_fare'            => 0,
                'return_fee'           => 0,
                'cancellation_fee'     => 0,
                'extra_fare_fee'       => 0,
                'extra_fare_amount'    => 0,
                'surge_percentage'     => 0,
                'due_amount'           => 0,
                'payment_method'       => $request->payment_method ?? 'cash',
                'payment_status'       => 'unpaid',
                'note'                 => $request->note,
                'otp'                  => (string) rand(1000, 9999),
                'type'                 => 'ride_request',
                'ride_request_type'    => 'book_now',
                'scheduled_at'         => $scheduledAt,
                'current_status'       => 'pending',
                'is_notification_sent' => 0,
                'checked'              => 0,
                'rise_request_count'   => 0,
                'tips'                 => 0,
                'is_paused'            => 0,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            // 2. trip_request_fees
            DB::table('trip_request_fees')->insert([
                'trip_request_id'  => $tripId,
                'cancellation_fee' => 0,
                'return_fee'       => 0,
                'cancelled_by'     => null,
                'waiting_fee'      => 0,
                'waited_by'        => null,
                'idle_fee'         => 0,
                'delay_fee'        => 0,
                'delayed_by'       => null,
                'vat_tax'          => $vatAmount,
                'tips'             => 0,
                'admin_commission' => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            // 3. trip_request_coordinates
            DB::statement("
                INSERT INTO trip_request_coordinates
                    (trip_request_id, pickup_address, pickup_coordinates,
                     destination_address, destination_coordinates,
                     customer_request_coordinates, start_coordinates, drop_coordinates,
                     created_at, updated_at)
                VALUES (?, ?, POINT(?, ?), ?, POINT(?, ?), POINT(?, ?), POINT(?, ?), POINT(?, ?), ?, ?)
            ", [
                $tripId,
                $request->pickup_address,
                $request->pickup_lng,
                $request->pickup_lat,
                $request->destination_address,
                $request->destination_lng,
                $request->destination_lat,
                $request->pickup_lng,
                $request->pickup_lat,        // customer_request
                $request->pickup_lng,
                $request->pickup_lat,        // start
                $request->destination_lng,
                $request->destination_lat,   // drop
                $now,
                $now,
            ]);

            // 4. trip_status
            DB::table('trip_status')->insert([
                'trip_request_id' => $tripId,
                'customer_id'     => $request->customer_id,
                'driver_id'       => null,
                'pending'         => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // 5. trip_request_times
            DB::table('trip_request_times')->insert([
                'trip_request_id' => $tripId,
                'estimated_time'  => 0,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Full error logged so you can see exactly which insert failed
            Log::error('BookNow store error: ' . $e->getMessage()
                . ' | File: ' . $e->getFile()
                . ' | Line: ' . $e->getLine());
            Toastr::error('Booking failed: ' . $e->getMessage());
            return back()->withInput();
        }

        // 6. Notify drivers (outside transaction — failure won't roll back the booking)
        $this->notifyDrivers($tripId, $request->zone_id, $request->driver_id);

        // 7. Redirect to Trip Management → /admin/trip/list/all
        Toastr::success('Booking #' . $refId . ' created successfully!');
        return redirect()->route('admin.trip.index', 'all');
    }

    public function details(string $id)
    {
        $booking = DB::table('trip_requests as tr')
            ->leftJoin('trip_request_coordinates as trc', 'trc.trip_request_id', '=', 'tr.id')
            ->leftJoin('trip_request_fees as trf',        'trf.trip_request_id', '=', 'tr.id')
            ->leftJoin('trip_status as ts',               'ts.trip_request_id',  '=', 'tr.id')
            ->leftJoin('users as customer', 'customer.id', '=', 'tr.customer_id')
            ->leftJoin('users as driver',   'driver.id',   '=', 'tr.driver_id')
            ->leftJoin('zones',             'zones.id',    '=', 'tr.zone_id')
            ->leftJoin('vehicle_categories as vc', 'vc.id', '=', 'tr.vehicle_category_id')
            ->where('tr.id', $id)
            ->where('tr.ride_request_type', 'book_now')
            ->whereNull('tr.deleted_at')
            ->select([
                'tr.*',
                'trc.pickup_address',
                'trc.destination_address',
                'trf.vat_tax',
                'trf.admin_commission',
                'trf.cancellation_fee as fee_cancellation',
                'trf.return_fee as fee_return',
                'trf.delay_fee',
                'trf.idle_fee',
                'ts.pending    as status_pending',
                'ts.accepted   as status_accepted',
                'ts.ongoing    as status_ongoing',
                'ts.completed  as status_completed',
                'ts.cancelled  as status_cancelled',
                'customer.first_name    as customer_first_name',
                'customer.last_name     as customer_last_name',
                'customer.phone         as customer_phone',
                'customer.profile_image as customer_image',
                'driver.first_name      as driver_first_name',
                'driver.last_name       as driver_last_name',
                'driver.phone           as driver_phone',
                'driver.profile_image   as driver_image',
                'zones.name             as zone_name',
                'vc.name                as vehicle_category_name',
            ])
            ->first();

        abort_if(!$booking, 404);

        if ($booking->current_status === 'pending' && empty($booking->driver_first_name)) {
            $tempDrivers = DB::table('temp_trip_notifications')
                ->where('trip_request_id', $booking->id)
                ->pluck('user_id');

            // REPLACE WITH:
            if ($tempDrivers->count() >= 1) {
                $preAssignedDriver = DB::table('users')->where('id', $tempDrivers->first())->first();
                if ($preAssignedDriver) {
                    $booking->driver_first_name = $preAssignedDriver->first_name;
                    $booking->driver_last_name  = $preAssignedDriver->last_name;
                    $booking->driver_phone      = $preAssignedDriver->phone;
                    $booking->driver_image      = $preAssignedDriver->profile_image;
                }
            }
        }

        $drivers = DB::table('users')
            ->where('user_type', 'driver')->where('is_active', 1)->whereNull('deleted_at')
            ->get(['id', 'first_name', 'last_name', 'phone']);

        return view('adminmodule::admin.book-now.details', compact('booking', 'drivers'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'current_status' => 'required|in:pending,confirmed,completed,cancelled',
            'driver_id'      => 'nullable|exists:users,id',
        ]);

        $now    = now();
        $status = $request->current_status;

        DB::table('trip_requests')->where('id', $id)->update([
            'current_status' => $status,
            'driver_id'      => $request->driver_id,
            'updated_at'     => $now,
        ]);

        $statusColumn = match ($status) {
            'confirmed' => 'accepted',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default     => null,
        };

        if ($statusColumn) {
            DB::table('trip_status')
                ->where('trip_request_id', $id)
                ->update([
                    $statusColumn => $now,
                    'driver_id'   => $request->driver_id,
                    'updated_at'  => $now,
                ]);
        }

        if ($status === 'confirmed' && $request->driver_id) {
            $trip = DB::table('trip_requests')->where('id', $id)->first();
            $this->notifyDrivers($id, $trip->zone_id ?? null, $request->driver_id);
        }

        Toastr::success('Booking Updated Successfully!');
        return back();
    }

    public function destroy(string $id)
    {
        DB::table('trip_requests')
            ->where('id', $id)
            ->where('ride_request_type', 'book_now')
            ->update(['deleted_at' => now()]);

        Toastr::success('Booking Deleted');
        return redirect()->route('admin.book-now.index', 'all');
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $theta = $lon1 - $lon2;
        $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2))
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist  = acos(max(-1.0, min(1.0, $dist)));
        $miles = rad2deg($dist) * 60 * 1.1515;
        return round($miles * 1.609344, 2);
    }

    private function notifyDrivers(string $tripId, ?string $zoneId, ?string $assignedDriverId = null): void
    {
        try {
            if ($assignedDriverId) {
                $drivers = DB::table('users')
                    ->where('id', $assignedDriverId)
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->get(['id', 'fcm_token']);
            } else {
                $drivers = DB::table('users as u')
                    ->join('driver_details as dd', 'dd.user_id', '=', 'u.id')
                    ->leftJoin('user_last_locations as ull', function ($join) {
                        $join->on('ull.user_id', '=', 'u.id')
                            ->where('ull.type', 'driver');
                    })
                    ->where('u.user_type', 'driver')
                    ->where('u.is_active', 1)
                    ->whereNull('u.deleted_at')
                    ->whereIn('dd.is_online', ['1', 'true'])
                    ->where('dd.availability_status', 'available')
                    ->where(function ($q) use ($zoneId) {
                        $q->where('ull.zone_id', $zoneId)
                            ->orWhereNull('ull.zone_id');
                    })
                    ->select('u.id', 'u.fcm_token')
                    ->distinct()
                    ->get();
            }

            if ($drivers->isEmpty()) {
                Log::warning("BookNow FCM: No drivers found for trip {$tripId}.");
                return;
            }

            foreach ($drivers as $driver) {
                $alreadyQueued = DB::table('temp_trip_notifications')
                    ->where('trip_request_id', $tripId)
                    ->where('user_id', $driver->id)
                    ->exists();

                if (!$alreadyQueued) {
                    DB::table('temp_trip_notifications')->insert([
                        'trip_request_id' => $tripId,
                        'user_id'         => $driver->id,
                    ]);
                }

                if ($driver->fcm_token && function_exists('sendDeviceNotification')) {
                    sendDeviceNotification(
                        $driver->fcm_token,
                        'New ride request',
                        'You have a new ride request.',
                        1,
                        null,
                        $tripId,
                        'ride_request',
                        'trip',
                        'new_ride_request',
                        $driver->id
                    );
                }
            }

            DB::table('trip_requests')
                ->where('id', $tripId)
                ->update(['is_notification_sent' => 1, 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Log::error("BookNow FCM Exception: " . $e->getMessage()
                . " in " . $e->getFile() . " on line " . $e->getLine());
        }
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name'  => 'required|string|max:191',
            'phone'      => 'required|string|unique:users,phone|max:20',
        ]);

        $phone = $request->phone;
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        $user = \Modules\UserManagement\Entities\User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'full_name'  => trim($request->first_name . ' ' . $request->last_name),
            'phone'      => $phone,
            'password'   => Hash::make('12345678'),
            'user_type'  => 'customer',
            'is_active'  => 1,
        ]);

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'   => $user->id,
                'text' => $user->full_name . ' (' . $user->phone . ')',
            ],
        ]);
    }

    public function getDriverVehicleCategory(string $id)
    {
        $vehicle = DB::table('vehicles as v')
            ->join('vehicle_categories as vc', 'vc.id', '=', 'v.category_id')
            ->where('v.driver_id', $id)
            ->whereNull('v.deleted_at')
            ->select(
                'vc.id   as category_id',
                'vc.name as category_name',
                'vc.type as category_type',
                'v.id    as vehicle_id',
            )
            ->first();

        if (!$vehicle) {
            return response()->json([
                'found'   => false,
                'message' => 'No vehicle assigned to this driver.',
            ]);
        }

        return response()->json([
            'found'         => true,
            'category_id'   => $vehicle->category_id,
            'category_name' => $vehicle->category_name,
            'category_type' => $vehicle->category_type,
            'vehicle_id'    => $vehicle->vehicle_id,
        ]);
    }

    public function getZoneFleet(string $id)
    {
        $drivers = DB::table('users as u')
            ->leftJoin('vehicles as v', 'v.driver_id', '=', 'u.id')
            ->leftJoin('vehicle_categories as vc', 'vc.id', '=', 'v.category_id')
            ->where('u.user_type', 'driver')
            ->where('u.zone_id', $id)
            ->where('u.is_active', 1)
            ->whereNull('u.deleted_at')
            ->whereNull('v.deleted_at')
            ->select(
                'u.id',
                'u.first_name',
                'u.last_name',
                'u.phone',
                'u.latitude',
                'u.longitude',
                'vc.name as vehicle_category',
                'v.model as vehicle_model'
            )
            ->distinct()
            ->get();

        $customers = DB::table('users')
            ->where('user_type', 'customer')
            ->where('zone_id', $id)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->select('id', 'first_name', 'last_name', 'phone', 'email', 'latitude', 'longitude')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'drivers'   => $drivers,
                'customers' => $customers,
            ],
        ]);
    }
    public function estimateFare(Request $request)
    {
        $zoneId     = $request->zone_id;
        $categoryId = $request->vehicle_category_id;
        $distanceKm = (float) $request->distance_km;

        // Try specific zone+category fare first
        $fare = DB::table('trip_fares')
            ->where('zone_id', $zoneId)
            ->where('vehicle_category_id', $categoryId)
            ->first();

        if ($fare) {
            $baseFare      = $fare->base_fare;
            $baseFarePerKm = $fare->base_fare_per_km;
        } else {
            // Fall back to zone default
            $defaultFare = DB::table('zone_wise_default_trip_fares')
                ->where('zone_id', $zoneId)
                ->first();

            if (!$defaultFare) {
                return response()->json([
                    'success' => false,
                    'message' => translate('No fare configured for this zone/category'),
                ]);
            }
            $baseFare      = $defaultFare->base_fare;
            $baseFarePerKm = $defaultFare->base_fare_per_km;
        }

        $subTotal   = $baseFare + ($distanceKm * $baseFarePerKm);
        $vatRow     = DB::table('business_settings')->where('key_name', 'vat_percent')->first();
        $vatPercent = $vatRow ? (float) json_decode($vatRow->value) : 0;
        $vatAmount  = ($subTotal * $vatPercent) / 100;
        $totalFare  = $subTotal + $vatAmount;

        // Get currency symbol
        $currencyRow = DB::table('business_settings')->where('key_name', 'currency_symbol')->first();
        $currency    = $currencyRow ? json_decode($currencyRow->value) : '';

        return response()->json([
            'success'        => true,
            'estimated_fare' => round($totalFare, 2),
            'currency'       => $currency,
        ]);
    }
}
