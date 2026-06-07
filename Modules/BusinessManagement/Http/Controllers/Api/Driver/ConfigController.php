<?php

namespace Modules\BusinessManagement\Http\Controllers\Api\Driver;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\BusinessManagement\Service\Interfaces\BusinessSettingServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\CancellationReasonServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\ParcelCancellationReasonServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\QuestionAnswerServiceInterface;
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
    protected $questionAnswerService;
    protected $safetyAlertReasonService;

    protected $safetyPrecautionService;

    public function __construct(BusinessSettingServiceInterface    $businessSettingService, SettingServiceInterface $settingService,
                                CancellationReasonServiceInterface $cancellationReasonService, ParcelCancellationReasonServiceInterface $parcelCancellationReasonService,
                                ZoneServiceInterface               $zoneService, UserLastLocationServiceInterface $userLastLocationService,
                                TripRequestServiceInterface        $tripRequestService, QuestionAnswerServiceInterface $questionAnswerService,
                                SafetyAlertReasonServiceInterface  $safetyAlertReasonService, SafetyPrecautionServiceInterface $safetyPrecautionService)
    {
        $this->businessSettingService = $businessSettingService;
        $this->settingService = $settingService;
        $this->cancellationReasonService = $cancellationReasonService;
        $this->parcelCancellationReasonService = $parcelCancellationReasonService;
        $this->zoneService = $zoneService;
        $this->userLastLocationService = $userLastLocationService;
        $this->tripRequestService = $tripRequestService;
        $this->questionAnswerService = $questionAnswerService;
        $this->safetyAlertReasonService = $safetyAlertReasonService;
        $this->safetyPrecautionService = $safetyPrecautionService;
    }

    public function configuration()
    {
        // Removed offset: 1 to ensure no settings are skipped
        $info = $this->businessSettingService->getAll(limit: 999);

        $getSetting = fn($key) => $info->firstWhere('key_name', $key);

        $loyaltyPointsSetting = $getSetting('loyalty_points');
        $loyaltyPoints = (is_array($loyaltyPointsSetting?->value) && $loyaltyPointsSetting?->settings_type == 'driver_settings')
            ? $loyaltyPointsSetting->value
            : [];

        $appVersions = $this->businessSettingService->getBy(criteria: ['settings_type' => APP_VERSION]);
        $smsConfigData = $this->settingService->getBy(criteria: ['settings_type' => SMS_CONFIG]);
        $smsConfiguration = $smsConfigData->where('live_values.status', 1)->isNotEmpty() ? 1 : 0;

        // Face Verification Logic
        $faceVerifApi = $getSetting('face_verification_api')?->value;
        $driverIdVerifStatus = $getSetting('driver_identity_verification_status')?->value;
        $driverVerifyIdentity = (bool)($faceVerifApi['status'] ?? 0) && (bool)($driverIdVerifStatus ?? 0);

        $initFaceVerif = $getSetting('initiate_face_verification')?->value ?? [];
        $chooseVerificationWhenToTrigger = ($driverVerifyIdentity && in_array('at_intervals', (array)$initFaceVerif))
            ? $getSetting('choose_verification_when_to_trigger')?->value : null;

        $isActiveGateway = $this->settingService->getBy(criteria: ['settings_type' => SMS_CONFIG])->where('is_active', 1)->isNotEmpty();
        $isFirebaseOtpEnabled = (bool) $this->businessSettingService->findOneBy(criteria: ['key_name' => 'firebase_otp_verification_status' , 'settings_type' => FIREBASE_OTP])?->value;

        $fbLogin = $getSetting('facebook_login')?->value;
        $googleLogin = $getSetting('google_login')?->value;
        $parcelLimit = $getSetting('maximum_parcel_request_accept_limit')?->value;

        $androidVer = $appVersions->firstWhere('key_name', 'driver_app_version_control_for_android')?->value;
        $iosVer = $appVersions->firstWhere('key_name', 'driver_app_version_control_for_ios')?->value;

        $configs = [
            'is_demo' => (bool)env('APP_MODE') != 'live',
            'maintenance_mode' => checkMaintenanceMode(),
            'required_pin_to_start_trip' => (bool)($getSetting('required_pin_to_start_trip')?->value ?? false),
            'add_intermediate_points' => (bool)($getSetting('add_intermediate_points')?->value ?? false),
            'business_name' => $getSetting('business_name')?->value ?? null,
            'logo' => $getSetting('header_logo')?->value ?? null,
            'bid_on_fare' => (bool)($getSetting('bid_on_fare')?->value ?? 0),
            'driver_completion_radius' => $getSetting('driver_completion_radius')?->value ?? 10,
            'country_code' => $getSetting('country_code')?->value ?? null,
            'business_address' => $getSetting('business_address')?->value ?? null,
            'business_contact_phone' => $getSetting('business_contact_phone')?->value ?? null,
            'business_contact_email' => $getSetting('business_contact_email')?->value ?? null,
            'business_support_phone' => $getSetting('business_support_phone')?->value ?? null,
            'business_support_email' => $getSetting('business_support_email')?->value ?? null,
            'conversion_status' => (bool)($loyaltyPoints['status'] ?? false),
            'conversion_rate' => (double)($loyaltyPoints['points'] ?? 0),
            'base_url' => url('/') . '/api/v1/',
            'websocket_url' => $getSetting('websocket_url')?->value ?? null,
            'websocket_port' => (string)($getSetting('websocket_port')?->value ?? 6001),
            'websocket_key' => env('PUSHER_APP_KEY'),
            'websocket_scheme' => env('PUSHER_SCHEME'),
            'review_status' => (bool)($getSetting(DRIVER_REVIEW)?->value ?? false),
            'level_status' => (bool)($getSetting(DRIVER_LEVEL)?->value ?? false),
            'image_base_url' => [
                'profile_image_customer' => dynamicStorage('storage/app/public/customer/profile'),
                'profile_image_admin' => dynamicStorage('storage/app/public/employee/profile'),
                'banner' => dynamicStorage('storage/app/public/promotion/banner'),
                'vehicle_category' => dynamicStorage('storage/app/public/vehicle/category'),
                'vehicle_model' => dynamicStorage('storage/app/public/vehicle/model'),
                'vehicle_brand' => dynamicStorage('storage/app/public/vehicle/brand'),
                'profile_image' => dynamicStorage('storage/app/public/driver/profile'),
                'identity_image' => dynamicStorage('storage/app/public/driver/identity'),
                'documents' => dynamicStorage('storage/app/public/driver/document'),
                'pages' => dynamicStorage('storage/app/public/business/pages'),
                'conversation' => dynamicStorage('storage/app/public/conversation'),
                'parcel' => dynamicStorage('storage/app/public/parcel/category'),
            ],
            'otp_resend_time' => (int)($getSetting('otp_resend_time')?->value ?? 60),
            'currency_decimal_point' => $getSetting('currency_decimal_point')?->value ?? 2,
            'currency_code' => $getSetting('currency_code')?->value ?? 'USD',
            'currency_symbol' => $getSetting('currency_symbol')?->value ?? '$',
            'currency_symbol_position' => $getSetting('currency_symbol_position')?->value ?? 'left',
            'about_us' => $getSetting('about_us')?->value ?? null,
            'privacy_policy' => $getSetting('privacy_policy')?->value ?? null,
            'terms_and_conditions' => $getSetting('terms_and_conditions')?->value ?? null,
            'legal' => $getSetting('legal')?->value,
            'refund_policy' => $getSetting('refund_policy')?->value,
            'verification' => (bool)($getSetting('driver_verification')?->value ?? 0),
            'sms_verification' => (bool)($getSetting('sms_verification')?->value ?? 0),
            'email_verification' => (bool)($getSetting('email_verification')?->value ?? 0),
            'facebook_login' => (bool)($fbLogin['status'] ?? 0),
            'google_login' => (bool)($googleLogin['status'] ?? 0),
            'self_registration' => (bool)($getSetting('driver_self_registration')?->value ?? 0),
            // 'referral_earning_status' => (bool)(referralEarningSetting('referral_earning_status', DRIVER)?->value ?? false),
            'referral_earning_status' => false,

            'app_minimum_version_for_android' => (double)($androidVer['minimum_app_version'] ?? 0),
            'app_url_for_android' => $androidVer['app_url'] ?? null,
            'app_minimum_version_for_ios' => (double)($iosVer['minimum_app_version'] ?? 0),
            'app_url_for_ios' => $iosVer['app_url'] ?? null,
            'firebase_otp_verification' => (bool)($getSetting('firebase_otp_verification_status')?->value == 1),
            'sms_gateway' => (bool)$smsConfiguration,
            'chatting_setup_status' => (bool)($getSetting('chatting_setup_status')?->value == 1),
            'maximum_parcel_request_accept_limit_status_for_driver' => (bool)($parcelLimit['status'] ?? 0),
            'maximum_parcel_request_accept_limit_for_driver' => (int)($parcelLimit['limit'] ?? 0),
            // 'safety_feature_status' => (bool)($getSetting('safety_feature_status')?->value == 1),
            'safety_feature_status' => false, // To hide safety feature from driver app until it's fully ready
            'otp_confirmation_for_trip' => (bool)($getSetting('driver_otp_confirmation_for_trip')?->value == 1),
            'fuel_types' => defined('FUEL_TYPES') ? array_keys(FUEL_TYPES) : [],
            'cash_in_hand_setup_status' => (bool)($getSetting('cash_in_hand_setup_status')?->value ?? false),
            'cash_in_hand_max_amount_to_hold_cash' =>  $getSetting('max_amount_to_hold_cash')?->value ?? 0,
            'cash_in_hand_min_amount_to_pay' =>  $getSetting('min_amount_to_pay')?->value ?? 0,
            'verify_driver_identity' => $driverVerifyIdentity,
            'is_otp_enabled' => $isActiveGateway || $isFirebaseOtpEnabled,
            'schedule_trip_status' => (bool)($getSetting('schedule_trip_status')?->value == 1),
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

    public function cancellationReasonList()
    {
        $ongoingRide = $this->cancellationReasonService->getBy(criteria: ['cancellation_type' => 'ongoing_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $acceptedRide = $this->cancellationReasonService->getBy(criteria: ['cancellation_type' => 'accepted_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $data = [
            'ongoing_ride' => $ongoingRide,
            'accepted_ride' => $acceptedRide,
        ];
        return response(responseFormatter(DEFAULT_200, $data));
    }

    public function parcelCancellationReasonList()
    {
        $ongoingRide = $this->parcelCancellationReasonService->getBy(criteria: ['cancellation_type' => 'ongoing_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $acceptedRide = $this->parcelCancellationReasonService->getBy(criteria: ['cancellation_type' => 'accepted_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $data = [
            'ongoing_ride' => $ongoingRide,
            'accepted_ride' => $acceptedRide,
        ];
        return response(responseFormatter(DEFAULT_200, $data));
    }


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

    /**
     * Summary of placeApiAutocomplete
     * @param Request $request
     * @return JsonResponse
     */
    public function placeApiAutocomplete(Request $request): JsonResponse
    {
        // Validate the incoming request
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
        $response = Http::get(MAP_API_BASE_URI . '/geocode/json?latlng=' . $request->lat . ',' . $request->lng . '&key=' . $mapApiKey);
        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

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
            auth()->user()->lastLocations->latitude,
            auth()->user()->lastLocations->longitude,
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

        $drivingMode = auth()->user()->vehicleCategory->category->type == 'motor_bike' ? 'TWO_WHEELER' : 'DRIVE';

        $getRoutes = getRoutes(
            originCoordinates: $pickupCoordinates,
            destinationCoordinates: $destinationCoordinates,
            intermediateCoordinates: $intermediateCoordinates,
        ); //["DRIVE", "TWO_WHEELER"]

        $result = [];
        foreach ($getRoutes as $route) {
            if ($route['drive_mode'] == $drivingMode) {
                if ($trip->current_status == 'completed' || $trip->current_status == 'cancelled') {
                    $result['is_dropped'] = true;
                } else {
                    $result['is_dropped'] = false;
                }
                if ($trip->current_status === PENDING || $trip->current_status === ACCEPTED) {
                    $result['is_picked'] = false;
                } else {
                    $result['is_picked'] = true;
                }
                // --- FIX: Only set to true if actually ongoing ---
                if ($trip->current_status === 'ongoing') {
                    $result['is_picked'] = true;
                } else {
                    $result['is_picked'] = false;
                }
                return [array_merge($result, $route)];
            }
        }

    }

    public function predefinedQuestionAnswerList(): JsonResponse
    {
        $predefinedQAs = $this->questionAnswerService->getBy(criteria: ['is_active' => true], orderBy: ['created_at' => 'desc']);

        return response()->json(responseFormatter(DEFAULT_200, $predefinedQAs), 200);
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
            'reason_for_whom' => DRIVER
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
            ['for_whom', 'like', '%' . DRIVER . '%'],
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
}
