<?php

namespace Modules\BusinessManagement\Http\Controllers\Api\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\BusinessManagement\Http\Requests\UserLocationStore;
use Modules\BusinessManagement\Service\Interfaces\BusinessSettingServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\CancellationReasonServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\ParcelCancellationReasonServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\ParcelRefundReasonServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\SafetyAlertReasonServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\SafetyPrecautionServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\SettingServiceInterface;
use Modules\TripManagement\Service\Interfaces\TripRequestServiceInterface;
use Modules\UserManagement\Service\Interfaces\UserLastLocationServiceInterface;
use Modules\ZoneManagement\Service\Interfaces\ZoneServiceInterface;

class ConfigController extends Controller
{
    protected $businessSettingService;
    protected $settingService;
    protected $cancellationReasonService;
    protected $parcelCancellationReasonService;
    protected $zoneService;
    protected $userLastLocationService;
    protected $tripRequestService;
    protected $parcelRefundReasonService;
    protected $safetyAlertReasonService;
    protected $safetyPrecautionService;

    public function __construct(BusinessSettingServiceInterface          $businessSettingService, SettingServiceInterface $settingService,
                                CancellationReasonServiceInterface       $cancellationReasonService, ZoneServiceInterface $zoneService,
                                UserLastLocationServiceInterface         $userLastLocationService, TripRequestServiceInterface $tripRequestService,
                                ParcelCancellationReasonServiceInterface $parcelCancellationReasonService, ParcelRefundReasonServiceInterface $parcelRefundReasonService,
                                SafetyAlertReasonServiceInterface        $safetyAlertReasonService, SafetyPrecautionServiceInterface $safetyPrecautionService)
    {
        $this->businessSettingService = $businessSettingService;
        $this->settingService = $settingService;
        $this->cancellationReasonService = $cancellationReasonService;
        $this->parcelCancellationReasonService = $parcelCancellationReasonService;
        $this->zoneService = $zoneService;
        $this->userLastLocationService = $userLastLocationService;
        $this->tripRequestService = $tripRequestService;
        $this->parcelRefundReasonService = $parcelRefundReasonService;
        $this->safetyAlertReasonService = $safetyAlertReasonService;
        $this->safetyPrecautionService = $safetyPrecautionService;
    }

    public function configuration()
    {
        // Change offset to 0 or remove it to ensure we don't skip the first setting
        $info = $this->businessSettingService->getAll(limit: 999);

        $loyaltyPointsSetting = $info->where('key_name', 'loyalty_points')->firstWhere('settings_type', 'customer_settings');
        $loyaltyPoints = is_array($loyaltyPointsSetting?->value) ? $loyaltyPointsSetting->value : [];

        $martExternalSetting = false;
        if (checkSelfExternalConfiguration()) {
            try {
                $martBaseUrl = externalConfig('mart_base_url')?->value;
                $systemSelfToken = externalConfig('system_self_token')?->value;
                $martToken = externalConfig('mart_token')?->value;
                if($martBaseUrl && $systemSelfToken && $martToken) {
                    $response = Http::get($martBaseUrl . '/api/v1/configurations/get-external', [
                        'mart_token' => $martToken,
                        'drivemond_base_url' => url('/'),
                        'drivemond_token' => $systemSelfToken,
                    ]);
                    if ($response->successful()) {
                        $martResponse = $response->json();
                        $martExternalSetting = $martResponse['status'] ?? false;
                    }
                }
            } catch (\Exception $exception) {}
        }

        $appVersions = $this->businessSettingService->getBy(criteria: ['settings_type' => APP_VERSION]);
        $smsConfig = $this->settingService->getBy(criteria: ['settings_type' => SMS_CONFIG]);
        $smsConfiguration = $smsConfig->where('live_values.status', 1)->isNotEmpty() ? 1 : 0;

        $zoneExtraFare = $this->zoneService->getBy(criteria: ['is_active' => 1, 'extra_fare_status' => 1])
            ->map(fn($q) => [
                'status' => $q->extra_fare_status,
                'zone_id' => $q->id,
                'reason' => $q->extra_fare_reason,
            ]);

        // Safely fetch settings with defaults
        $getSetting = fn($key) => $info->firstWhere('key_name', $key);

        $fbLogin = $getSetting('facebook_login')?->value;
        $googleLogin = $getSetting('google_login')?->value;
        $customerWallet = $getSetting('customer_wallet')?->value;

        $androidVer = $appVersions->firstWhere('key_name', 'customer_app_version_control_for_android')?->value;
        $iosVer = $appVersions->firstWhere('key_name', 'customer_app_version_control_for_ios')?->value;

        $configs = [
            'is_demo' => env('APP_MODE') != 'live',
            'maintenance_mode' => checkMaintenanceMode(),
            'required_pin_to_start_trip' => (bool)($getSetting('required_pin_to_start_trip')?->value ?? false),
            'add_intermediate_points' => (bool)($getSetting('add_intermediate_points')?->value ?? false),
            'business_name' => (string)($getSetting('business_name')?->value ?? ""),
            'logo' => $getSetting('header_logo')?->value ?? null,
            'bid_on_fare' => (bool)($getSetting('bid_on_fare')?->value ?? 0),
            'country_code' => (string)($getSetting('country_code')?->value ?? ""),
            'business_address' => (string)($getSetting('business_address')?->value ?? ""),
            'business_contact_phone' => (string)($getSetting('business_contact_phone')?->value ?? ""),
            'business_contact_email' => (string)($getSetting('business_contact_email')?->value ?? ""),
            'business_support_phone' => (string)($getSetting('business_support_phone')?->value ?? ""),
            'business_support_email' => (string)($getSetting('business_support_email')?->value ?? ""),
            'conversion_status' => (bool)($loyaltyPoints['status'] ?? false),
            'conversion_rate' => (double)($loyaltyPoints['points'] ?? 0),
            'websocket_url' => $getSetting('websocket_url')?->value ?? null,
            'websocket_port' => (string)($getSetting('websocket_port')?->value ?? 6001),
            'websocket_key' => env('PUSHER_APP_KEY'),
            'websocket_scheme' => env('PUSHER_SCHEME'),
            'base_url' => url('/') . '/api/v1/',
            'review_status' => (bool)($getSetting(CUSTOMER_REVIEW)?->value ?? false),
            'level_status' => (bool)($getSetting(CUSTOMER_LEVEL)?->value ?? false),
            'search_radius' => $getSetting('search_radius')?->value ?? 10000,
            'popular_tips' => $this->tripRequestService->getPopularTips()?->tips ?? 5,
            'driver_completion_radius' => $getSetting('driver_completion_radius')?->value ?? 1000,
            'image_base_url' => [
                'profile_image_driver' => dynamicStorage('storage/app/public/driver/profile'),
                'profile_image_admin' => dynamicStorage('storage/app/public/employee/profile'),
                'banner' => dynamicStorage('storage/app/public/promotion/banner'),
                'vehicle_category' => dynamicStorage('storage/app/public/vehicle/category'),
                'vehicle_model' => dynamicStorage('storage/app/public/vehicle/model'),
                'vehicle_brand' => dynamicStorage('storage/app/public/vehicle/brand'),
                'profile_image' => dynamicStorage('storage/app/public/customer/profile'),
                'identity_image' => dynamicStorage('storage/app/public/customer/identity'),
                'documents' => dynamicStorage('storage/app/public/customer/document'),
                'level' => dynamicStorage('storage/app/public/customer/level'),
                'pages' => dynamicStorage('storage/app/public/business/pages'),
                'conversation' => dynamicStorage('storage/app/public/conversation'),
                'parcel' => dynamicStorage('storage/app/public/parcel/category'),
                'payment_method' => dynamicStorage('storage/app/public/payment_modules/gateway_image')
            ],
            'currency_decimal_point' => $getSetting('currency_decimal_point')?->value ?? 2,
            'trip_request_active_time' => (int)($getSetting('trip_request_active_time')?->value ?? 10),
            'currency_code' => $getSetting('currency_code')?->value ?? 'USD',
            'currency_symbol' => $getSetting('currency_symbol')?->value ?? '$',
            'currency_symbol_position' => $getSetting('currency_symbol_position')?->value ?? 'left',
            'about_us' => $getSetting('about_us')?->value,
            'privacy_policy' => $getSetting('privacy_policy')?->value,
            'refund_policy' => $getSetting('refund_policy')?->value,
            'terms_and_conditions' => $getSetting('terms_and_conditions')?->value,
            'legal' => $getSetting('legal')?->value,
            'verification' => (bool)($getSetting('customer_verification')?->value ?? 0),
            'sms_verification' => (bool)($getSetting('sms_verification')?->value ?? 0),
            'email_verification' => (bool)($getSetting('email_verification')?->value ?? 0),
            'facebook_login' => (bool)($fbLogin['status'] ?? 0),
            'google_login' => (bool)($googleLogin['status'] ?? 0),
            'otp_resend_time' => (int)($getSetting('otp_resend_time')?->value ?? 60),
            // 'vat_tax' => (double)(get_cache('vat_percent') ?? 0),
            'vat_tax' => null, // Ensures app doesn't render 0%
            'payment_gateways' => collect($this->getPaymentMethods()),
            // 'referral_earning_status' => (bool)(referralEarningSetting('referral_earning_status', CUSTOMER)?->value ?? false),
            'referral_earning_status' => false,
            'coupon_status' => false, // Added to ensure discounts are hidden
            'external_system' => $martExternalSetting,
            'mart_business_name' => $martExternalSetting ? (externalConfig('mart_business_name')?->value ?? "6amMart") : "",
            'mart_app_url_android' => $martExternalSetting ? (externalConfig('mart_app_url_android')?->value ?? "") : "",
            'mart_app_minimum_version_android' => $martExternalSetting ? (externalConfig('mart_app_minimum_version_android')?->value ?? null) : null,
            'mart_app_url_ios' => $martExternalSetting ? (externalConfig('mart_app_url_ios')?->value ?? "") : "",
            'mart_app_minimum_version_ios' => $martExternalSetting ? (externalConfig('mart_app_minimum_version_ios')?->value ?? null) : null,
            'app_minimum_version_for_android' => (double)($androidVer['minimum_app_version'] ?? 0),
            'app_url_for_android' => $androidVer['app_url'] ?? null,
            'app_minimum_version_for_ios' => (double)($iosVer['minimum_app_version'] ?? 0),
            'app_url_for_ios' => $iosVer['app_url'] ?? null,
            'parcel_refund_status' => (bool)($getSetting('parcel_refund_status')?->value ?? false),
            'parcel_refund_validity' => (int)($getSetting('parcel_refund_validity')?->value ?? 0),
            'parcel_refund_validity_type' => (string)($getSetting('parcel_refund_validity_type')?->value ?? 'day'),
            'firebase_otp_verification' => (bool)($getSetting('firebase_otp_verification_status')?->value == 1),
            'sms_gateway' => (bool)$smsConfiguration,
            'zone_extra_fare' => $zoneExtraFare,
            'maximum_parcel_weight_status' => (bool)($getSetting('max_parcel_weight_status')?->value == 1),
            'maximum_parcel_weight_capacity' => (double)($getSetting('max_parcel_weight')?->value ?? 0),
            'parcel_weight_unit' => businessConfig(key: 'parcel_weight_unit', settingsType: PARCEL_SETTINGS)?->value ?? 'kg',
            // 'safety_feature_status' => (bool)($getSetting('safety_feature_status')?->value == 1),
            'safety_feature_status' => false, // To hide safety feature from customer app until it's fully ready

            'wallet_add_fund_status' => (bool)($customerWallet['add_fund_status'] ?? false),
            'wallet_minimum_deposit_limit' => (double)($customerWallet['min_deposit_limit'] ?? 1),
            'post_max_size' => ini_get('post_max_size'),
            // --- ADD THIS MISSING CODE BACK IN ---
            'safety_feature_minimum_trip_delay_time' => $getSetting('safety_feature_status')?->value == 1 ? convertTimeToSecond(
                $getSetting('for_trip_delay')?->value['minimum_delay_time'] ?? null,
                $getSetting('for_trip_delay')?->value['time_format'] ?? null
            ) : null,
            'safety_feature_minimum_trip_delay_time_type' => $getSetting('safety_feature_status')?->value == 1 ? ($getSetting('for_trip_delay')?->value['time_format'] ?? null) : null,
            'after_trip_completed_safety_feature_active_status' => (bool)($getSetting('safety_feature_status')?->value == 1) && (bool)($getSetting('after_trip_complete')?->value['safety_feature_active_status'] ?? false),
            'after_trip_completed_safety_feature_set_time' => ($getSetting('after_trip_complete')?->value['safety_feature_active_status'] ?? 0) == 1 ? convertTimeToSecond(
                $getSetting('after_trip_complete')?->value['set_time'] ?? null,
                $getSetting('after_trip_complete_time_format')?->value ?? null
            ) : null,
            'after_trip_completed_safety_feature_set_time_type' => ($getSetting('after_trip_complete')?->value['safety_feature_active_status'] ?? 0) == 1 ? ($getSetting('after_trip_complete_time_format')?->value ?? null) : null,

            'safety_feature_emergency_govt_number' => $getSetting('emergency_number_for_call_status')?->value == 1 ? $getSetting('emergency_govt_number_for_call')?->value : null,
            'otp_confirmation_for_trip' => (bool)($getSetting('driver_otp_confirmation_for_trip')?->value == 1),

            // --> SCHEDULE TRIP SETTINGS <--
            'schedule_trip_status' => (bool)($getSetting('schedule_trip_status')?->value == 1),
            'minimum_schedule_book_time' => $getSetting('schedule_trip_status')?->value ? convertTimeToSecond(
                (int)($getSetting('minimum_schedule_book_time')?->value ?? 0),
                (string)($getSetting('minimum_schedule_book_time_type')?->value ?? 'minute')
            ) : null,
            'minimum_schedule_book_time_type' => $getSetting('minimum_schedule_book_time_type')?->value ?? 'minute',
            'advance_schedule_book_time' => $getSetting('schedule_trip_status')?->value ? convertTimeToSecond(
                (int)($getSetting('advance_schedule_book_time')?->value ?? 0),
                (string)($getSetting('advance_schedule_book_time_type')?->value ?? 'minute')
            ) : null,
            'advance_schedule_book_time_type' => $getSetting('advance_schedule_book_time_type')?->value ?? 'minute',

            'do_not_charge_customer_return_fee' => (bool)(businessConfig('do_not_charge_customer_return_fee', PARCEL_SETTINGS)?->value ?? true),
            'upload_max_image_size' => maxUploadSize('image'),
            'upload_max_file_size' => maxUploadSize('file'),

        ];

        return response()->json($configs);
    }
    public function getPaymentMethods()
    {
        $methods = $this->settingService->getBy(criteria: ['settings_type' => PAYMENT_CONFIG]);
        $data = [];
        foreach ($methods as $method) {
            $additionalData = json_decode($method->additional_data, true);
            if ($method?->is_active == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additionalData['gateway_title'],
                    'gateway_image' => $additionalData['gateway_image']
                ];
            }
        }
        return collect($data);
    }

    public function pages($page_name)
    {
        $validated = in_array($page_name, ['about_us', 'privacy_and_policy', 'terms_and_conditions', 'legal']);

        if (!$validated) {
            return response()->json(responseFormatter(DEFAULT_400), 400);
        }

        $data = businessConfig(key: $page_name, settingsType: PAGES_SETTINGS);
        return response(responseFormatter(DEFAULT_200, [$data]));

    }

    public function placeApiAutocomplete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }

        $mapApiKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
        $url = 'https://places.googleapis.com/v1/places:autocomplete';
        $data = [
            'input' => $request->input('search_text'),
            // Optionally, you can add more parameters here
            // 'components' => 'country:IN', // Example: Restrict results to a specific country
        ];

        // API Headers
        $headers = [
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $mapApiKey,
            'X-Goog-FieldMask' => '*'
        ];

        // Send POST request
        $response = Http::withHeaders($headers)->post($url, $data);
        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

    public function placeApiDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'placeid' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }
        $mapApiKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
        // API Headers
        $headers = [
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $mapApiKey,
            'X-Goog-FieldMask' => '*'
        ];
        $response = Http::withHeaders($headers)->get('https://places.googleapis.com/v1/places/' . $request['placeid']);

        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }


    public function distanceApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
            'mode' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }

        $mapApiKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
        $response = Http::get(MAP_API_BASE_URI . '/distancematrix/json?origins=' . $request['origin_lat'] . ',' . $request['origin_lng'] . '&destinations=' . $request['destination_lat'] . ',' . $request['destination_lng'] . '&travelmode=' . $request['mode'] . '&key=' . $mapApiKey);

        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

    #
    public function getRoutes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_request_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(constant: DEFAULT_400, errors: errorProcessor($validator)), 403);
        }
        $trip = $this->tripRequestService->findOne(id: $request->trip_request_id, relations: ['coordinate', 'vehicleCategory']);
        if (!$trip) {
            return response()->json(responseFormatter(constant: TRIP_REQUEST_404, errors: errorProcessor($validator)), 403);
        }

        $pickupCoordinates = [
            $trip->driver?->lastLocations->latitude,
            $trip->driver?->lastLocations->longitude,
        ];

        $intermediateCoordinates = [];
        if ($trip->current_status == ONGOING) {
            $destinationCoordinates = [
                $trip->coordinate->destination_coordinates->latitude,
                $trip->coordinate->destination_coordinates->longitude,
            ];
            $intermediateCoordinates = $trip->coordinate->intermediate_coordinates ? json_decode($trip->coordinate->intermediate_coordinates, true) : [];
        } else {
            $destinationCoordinates = [
                $trip->coordinate->pickup_coordinates->latitude,
                $trip->coordinate->pickup_coordinates->longitude,
            ];
        }

        return getRoutes(
            originCoordinates: $pickupCoordinates,
            destinationCoordinates: $destinationCoordinates,
            intermediateCoordinates: $intermediateCoordinates,
        ); //["DRIVE", "TWO_WHEELER"]

        $result = [];
        foreach ($getRoutes as $route) {
            if ($route['drive_mode'] == $drivingMode) {
                $result['is_picked'] = $trip->current_status == ONGOING;
                return [array_merge($result, $route)];
            }
        }

    }

    #
    public function geocodeApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }
        $mapApiKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
//        $response = Http::get(MAP_API_BASE_URI . '/geocode/json?latlng=' . $request->lat . ',' . $request->lng . '&key=' . $mapApiKey . '&components=country:IN');

        $response = Http::get(MAP_API_BASE_URI . '/geocode/json?latlng=' . $request->lat . ',' . $request->lng . '&key=' . $mapApiKey);
        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

    #
    public function userLastLocation(UserLocationStore $request)
    {

        if (empty($request->header('zoneId'))) {

            return response()->json(responseFormatter(ZONE_404), 200);
        }

        $zone_id = $request->header('zoneId');
        $user = auth('api')->user();
        $request->merge([
            'user_id' => $user->id,
            'type' => $user->user_type,
            'zone_id' => $zone_id,
        ]);
        $userLastLocation = $this->userLastLocationService->findOneBy(criteria: ['user_id' => $user->id]);
        if ($userLastLocation) {
            return $this->userLastLocationService->update(id: $userLastLocation->id, data: $request->all());
        }
        return $this->userLastLocationService->create(data: $request->all());
    }

    #
    public function getZone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }

        $point = new Point($request->lat, $request->lng);
        $zone = $this->zoneService->getByPoints($point)->where('is_active', 1)->first();
        if ($zone) {
            return response()->json(responseFormatter(DEFAULT_200, $zone), 200);
        }

        return response()->json(responseFormatter(ZONE_RESOURCE_404), 403);
    }

    #
    public function cancellationReasonList()
    {
        $ongoingRide = $this->cancellationReasonService->getBy(criteria: ['cancellation_type' => 'ongoing_ride', 'user_type' => 'customer', 'is_active' => 1])->pluck('title')->toArray();
        $acceptedRide = $this->cancellationReasonService->getBy(criteria: ['cancellation_type' => 'accepted_ride', 'user_type' => 'customer', 'is_active' => 1])->pluck('title')->toArray();
        $data = [
            'ongoing_ride' => $ongoingRide,
            'accepted_ride' => $acceptedRide,
        ];
        return response(responseFormatter(DEFAULT_200, $data));
    }

    public function parcelCancellationReasonList()
    {
        $ongoingRide = $this->parcelCancellationReasonService->getBy(criteria: ['cancellation_type' => 'ongoing_ride', 'user_type' => 'customer', 'is_active' => 1])->pluck('title')->toArray();
        $acceptedRide = $this->parcelCancellationReasonService->getBy(criteria: ['cancellation_type' => 'accepted_ride', 'user_type' => 'customer', 'is_active' => 1])->pluck('title')->toArray();
        $data = [
            'ongoing_ride' => $ongoingRide,
            'accepted_ride' => $acceptedRide,
        ];
        return response(responseFormatter(DEFAULT_200, $data));
    }

    public function parcelRefundReasonList()
    {
        $parcelRefundReasonList = $this->parcelRefundReasonService->getBy(criteria: ['is_active' => 1])->pluck('title')->toArray();
        return response(responseFormatter(DEFAULT_200, $parcelRefundReasonList));
    }

    public function otherEmergencyContactList(): JsonResponse
    {
        $criteria = [
            'settings_type' => SAFETY_FEATURE_SETTINGS,
            'key_name' => 'emergency_other_numbers_for_call'
        ];
        $emergencyOtherNumberList = businessConfig(key: 'emergency_number_for_call_status', settingsType: 'safety_feature_settings')?->value == 1 ? $this->businessSettingService->findOneBy(criteria: $criteria)?->value : null;
        return response()->json(responseFormatter(constant: DEFAULT_200, content: $emergencyOtherNumberList));
    }

    public function safetyAlertReasonList(): JsonResponse
    {
        $criteria = [
            'is_active' => 1,
            'reason_for_whom' => CUSTOMER
        ];
        $safetyAlertReasons = businessConfig(key: 'safety_alert_reasons_status', settingsType: 'safety_feature_settings')?->value == 1
            ? $this->safetyAlertReasonService->getBy(criteria: $criteria)->pluck('reason')->map(function ($reason) {
                return ['reason' => $reason];
            })
            : null;
        return response()->json(responseFormatter(constant: DEFAULT_200, content: $safetyAlertReasons));
    }

    public function safetyPrecautionList(): JsonResponse
    {
        $criteria = [
            'is_active' => 1,
            ['for_whom', 'like', '%' . CUSTOMER . '%'],
        ];
        $safetyPrecautions = $this->safetyPrecautionService->getBy(criteria: $criteria);
        $responseData = $safetyPrecautions->map(function ($item) {
            return [
                'title' => $item['title'],
                'description' => $item['description'],
            ];
        });
        return response()->json(responseFormatter(constant: DEFAULT_200, content: $responseData));
    }

    public function calculateDistance(Request $request)
    {
        $trip = $this->tripRequestService->findOneBy(criteria: [
            'id' => $request->trip_request_id,
        ]);
        $destinationCoordinates = json_decode($trip?->coordinate, true);

        $data = [];
        $data['from_longitude'] = (float)$request->driver_last_longitude;
        $data['from_latitude'] = (float)$request->driver_last_latitude;
        $data['to_longitude'] = (float)$destinationCoordinates['destination_coordinates']['coordinates'][0];
        $data['to_latitude'] = (float)$destinationCoordinates['destination_coordinates']['coordinates'][1];
        $distanceToReach = number_format(distanceCalculator($data) * 1.3, 2);

        return response()->json(['distance' => $distanceToReach], 200);
    }


}
