@extends('adminmodule::layouts.master')

@section('title', translate('Manual_Booking'))

@push('css_or_js')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $mapApiKey }}&libraries=places"></script>
<script src="{{ dynamicAsset('public/assets/admin-module/js/maps/markerclusterer.js') }}"></script>
<style>
    /* ══════════════════════════════════════════
           ROOT LAYOUT — two-column, full height
        ══════════════════════════════════════════ */
    .bn-layout {
        display: grid;
        grid-template-columns: 460px 1fr;
        height: calc(100vh - 62px);
        overflow: hidden;
    }

    /* LEFT — scrollable form */
    .bn-form-panel {
        overflow-y: auto;
        background: #fff;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
    }
    .bn-form-panel::-webkit-scrollbar { width: 4px; }
    .bn-form-panel::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    .bn-form-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #fff;
        border-bottom: 1px solid #f3f4f6;
        padding: 14px 24px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .bn-form-body { padding: 20px 24px 28px; flex: 1; }

    /* small map inside form */
    .bn-form-map {
        width: 100%;
        height: 220px;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 12px;
        border: 1px solid #e5e7eb;
    }

    /* RIGHT — fleet map panel */
    .bn-map-panel { display: flex; flex-direction: column; overflow: hidden; background: #f8fafc; }

    /* FLEET MAP */
    .bn-fleet-header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 10px 16px 0; flex-shrink: 0; }
    .bn-fleet-top { display: flex; align-items: center; justify-content: space-between; padding-bottom: 6px; }
    .bn-fleet-title { font-size: 14px; font-weight: 700; color: #111827; }
    .bn-fleet-sub { font-size: 11.5px; color: #9ca3af; }
    .bn-fleet-tabs { display: flex; align-items: center; gap: 0; }
    .bn-fleet-tabs a {
        padding: 8px 14px; font-size: 13px; font-weight: 500; color: #6b7280;
        text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px;
        transition: all .15s; white-space: nowrap;
    }
    .bn-fleet-tabs a.active, .bn-fleet-tabs a:hover { color: #0f766e; border-bottom-color: #0f766e; }
    .bn-fleet-tabs .sep { color: #d1d5db; padding: 0 4px; font-size: 12px; line-height: 34px; }

    .bn-zone-select {
        font-size: 12.5px; border: 1px solid #e5e7eb; border-radius: 6px;
        padding: 4px 8px; color: #374151; outline: none; background: #f9fafb; min-width: 120px;
    }

    .bn-fleet-body { display: flex; flex: 1; min-height: 0; overflow: hidden; }

    .bn-user-list { width: 260px; flex-shrink: 0; overflow-y: auto; border-right: 1px solid #e5e7eb; background: #fff; }
    .bn-user-list::-webkit-scrollbar { width: 4px; }
    .bn-user-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

    .bn-list-inner { padding: 12px; }
    .bn-list-inner h6 { font-size: 13px; font-weight: 700; margin-bottom: 10px; color: #111; }

    .bn-search-wrap { display: flex; gap: 6px; margin-bottom: 12px; }
    .bn-search-wrap input {
        flex: 1; font-size: 12.5px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px 10px; outline: none;
    }
    .bn-search-wrap input:focus { border-color: #0f766e; }
    .bn-search-wrap button {
        background: #0f766e; color: #fff; border: none; border-radius: 6px;
        padding: 6px 12px; font-size: 12px; cursor: pointer;
    }

    #bn-zone-list .zone-list { list-style: none; padding: 0; margin: 0; }
    #bn-user-details { display: none; padding: 12px; }

    .bn-map-area { flex: 1; min-width: 0; position: relative; }
    #bn-fleet-map { width: 100%; height: 100%; }

    .bn-map-search {
        position: absolute; top: 10px; left: 50%; transform: translateX(-50%);
        z-index: 5; width: calc(100% - 80px); max-width: 500px;
    }
    .bn-map-search input {
        width: 100%; padding: 9px 14px; border: none; border-radius: 7px;
        font-size: 13px; box-shadow: 0 2px 10px rgba(0,0,0,.18); outline: none;
    }

    /* FORM STYLES */
    .f-section {
        font-size: .68rem; font-weight: 700; letter-spacing: .1em;
        color: #9ca3af; text-transform: uppercase; margin: 18px 0 10px;
    }
    .f-section:first-child { margin-top: 0; }
    .form-label { font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .form-control, .form-select { font-size: 13px; border-color: #e5e7eb; border-radius: 7px; padding: 7px 11px; }
    .form-control:focus, .form-select:focus {
        border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15,118,110,.1);
    }

    .addr-wrap { position: relative; }
    .addr-dot {
        position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
        width: 8px; height: 8px; border-radius: 50%; z-index: 1;
    }
    .addr-dot.pickup { background: #e53935; }
    .addr-dot.dest { background: #1a73e8; }
    .addr-wrap input { padding-left: 28px !important; }

    /* ══════════════════════════════════════════
       FARE ESTIMATE CARD
    ══════════════════════════════════════════ */
    #fare-estimate-card {
        display: none;
        margin-top: 10px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #99f6e4;
        background: linear-gradient(135deg, #f0fdf9 0%, #ecfdf5 100%);
    }
    .fare-card-header {
        padding: 8px 14px;
        background: #0f766e;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .fare-card-header span {
        font-size: 12px; font-weight: 700; color: #fff;
        text-transform: uppercase; letter-spacing: .06em;
    }
    .fare-card-body {
        display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0;
    }
    .fare-stat {
        padding: 10px 14px; border-right: 1px solid #ccfbf1; text-align: center;
    }
    .fare-stat:last-child { border-right: none; }
    .fare-stat-label {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: #6b7280; margin-bottom: 3px;
    }
    .fare-stat-value {
        font-size: 15px; font-weight: 800; color: #0f766e; line-height: 1;
    }
    .fare-stat-value.loading {
        font-size: 11px; color: #9ca3af; font-weight: 400;
        display: flex; align-items: center; justify-content: center; gap: 4px;
    }
    .fare-card-note {
        padding: 5px 14px 8px; font-size: 10.5px; color: #6b7280;
        text-align: center; border-top: 1px solid #d1fae5;
    }

    /* Old route info bar — hidden */
    #route-info-bar { display: none !important; }

    /* SEARCHABLE SELECT */
    .ss-wrap { position: relative; }
    .ss-wrap .ss-dropdown {
        position: absolute; top: calc(100% + 3px); left: 0; right: 0;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
        max-height: 190px; overflow-y: auto; z-index: 1060; display: none;
        box-shadow: 0 6px 20px rgba(0,0,0,.1);
    }
    .ss-wrap .ss-dropdown.open { display: block; }
    .ss-wrap .dd-item {
        padding: 7px 12px; cursor: pointer; font-size: 12.5px;
        border-bottom: 1px solid #f3f4f6; transition: background .1s;
    }
    .ss-wrap .dd-item:last-child { border-bottom: none; }
    .ss-wrap .dd-item:hover { background: #f0fdf4; color: #0f766e; }
    .ss-wrap .dd-empty { padding: 9px 12px; color: #9ca3af; font-size: 12px; text-align: center; }

    .sel-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #f0fdf4; color: #065f46; border: 1px solid #a7f3d0;
        border-radius: 20px; padding: 2px 9px; font-size: 12px; margin-top: 4px;
    }
    .sel-badge .clr { cursor: pointer; color: #9ca3af; font-weight: bold; }
    .sel-badge .clr:hover { color: #e53935; }

    .vh-box { display: flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 6px; font-size: 12px; margin-top: 5px; }
    .vh-box.success { background: #d1fae5; color: #064e3b; border: 1px solid #6ee7b7; }
    .vh-box.warning { background: #fef9c3; color: #713f12; border: 1px solid #fde047; }
    .vh-box.error   { background: #fee2e2; color: #7f1d1d; border: 1px solid #fca5a5; }
    .vh-box.loading { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }

    .cat-auto-sel { border-color: #0f766e !important; box-shadow: 0 0 0 3px rgba(15,118,110,.15) !important; }

    #dt-picker { position: absolute; z-index: 9999; min-width: 290px; border-radius: 10px !important; }

    .cust-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
    .cust-row .form-label { margin-bottom: 0; }

    .bn-submit {
        padding: 16px 24px; border-top: 1px solid #f3f4f6;
        display: flex; gap: 10px; justify-content: flex-end;
        background: #fff; flex-shrink: 0;
    }

    /* ══════════════════════════════════════════
       NEAREST DRIVER CARDS
    ══════════════════════════════════════════ */
    .bn-nearest-empty {
        text-align: center; padding: 24px 12px;
        color: #9ca3af; font-size: 12px; line-height: 1.6;
    }
    .bn-nearest-empty svg { display: block; margin: 0 auto 8px; opacity: .4; }

    .bn-driver-card {
        border: 1px solid #e5e7eb; border-radius: 8px;
        padding: 10px 12px; margin-bottom: 8px; background: #fff;
        cursor: pointer; transition: border-color .15s, box-shadow .15s; position: relative;
    }
    .bn-driver-card:hover { border-color: #0f766e; box-shadow: 0 2px 8px rgba(15,118,110,.12); }
    .bn-driver-card.selected { border-color: #0f766e; background: #f0fdf9; }

    .bn-driver-card-top {
        display: flex; align-items: center;
        justify-content: space-between; margin-bottom: 4px;
    }
    .bn-driver-card-name { font-size: 13px; font-weight: 700; color: #111827; }
    .bn-driver-card-serial {
        font-size: 10px; font-weight: 700;
        background: #f0fdf4; color: #065f46; border: 1px solid #a7f3d0;
        border-radius: 4px; padding: 1px 6px;
    }
    .bn-driver-card-dist {
        display: inline-flex; align-items: center; gap: 3px;
        border-radius: 20px; padding: 2px 8px;
        font-size: 11px; font-weight: 700;
    }
    .bn-driver-card-dist svg { flex-shrink: 0; }
    .bn-driver-card-meta { font-size: 11px; color: #9ca3af; }

    .bn-nearest-hint {
        font-size: 11px; color: #6b7280;
        background: #f9fafb; border: 1px dashed #e5e7eb;
        border-radius: 6px; padding: 8px 10px;
        margin-bottom: 12px; text-align: center; line-height: 1.5;
    }
    .bn-nearest-hint.active-hint {
        background: #f0fdf4; border-color: #6ee7b7; color: #065f46;
    }
    .bn-nearest-loading {
        text-align: center; padding: 20px 12px; color: #9ca3af; font-size: 12px;
    }

    /* Nearest tab pulse dot */
    #bn-nearest-tab { position: relative; }
    #bn-nearest-tab .pulse-dot {
        display: inline-block; width: 6px; height: 6px;
        background: #22c55e; border-radius: 50%; margin-left: 4px;
        vertical-align: middle; animation: pulse-green 1.5s infinite;
    }
    @keyframes pulse-green {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: .5; transform: scale(1.4); }
    }

    @media (max-width: 1100px) {
        .bn-layout { grid-template-columns: 1fr; height: auto; }
        .bn-map-panel { height: 520px; }
        .bn-user-list { width: 220px; }
    }
</style>
@endpush

@section('content')
<div class="bn-layout">

    {{-- LEFT — Booking Form --}}
    <div class="bn-form-panel">

        <div class="bn-form-header">
            <div>
                <div style="font-size:15px;font-weight:700;">{{ translate('Create Manual Booking') }}</div>
                <div style="font-size:11.5px;color:#9ca3af;">{{ translate('Fill in the booking details') }}</div>
            </div>
            <a href="{{ route('admin.book-now.index', 'all') }}" class="btn btn-outline-secondary btn-sm" style="font-size:12px;">
                &larr; {{ translate('Back') }}
            </a>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 mb-0" role="alert" style="font-size:12.5px;">
            <strong>{{ translate('Please fix the following errors:') }}</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="bn-form-body">
            <form action="{{ route('admin.book-now.store') }}" method="POST" id="booking-form">
                @csrf

                <div class="f-section">{{ translate('Booking Parties') }}</div>

                <div class="row g-2">

                    {{-- Customer --}}
                    <div class="col-12">
                        <div class="cust-row">
                            <label class="form-label">{{ translate('Customer') }} <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11.5px;" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                + {{ translate('New Customer') }}
                            </button>
                        </div>
                        <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}" required>
                        <div class="ss-wrap" id="wrap-customer">
                            <input type="text" class="form-control ss-input" id="search-customer" placeholder="{{ translate('Search customer...') }}" autocomplete="off">
                            <div class="ss-dropdown" id="dd-customer">
                                @foreach($customers as $c)
                                <div class="dd-item" data-value="{{ $c->id }}" data-label="{{ $c->first_name }} {{ $c->last_name }} ({{ ltrim($c->phone,'+') }})">
                                    {{ $c->first_name }} {{ $c->last_name }}
                                    <small class="text-muted ms-1">{{ ltrim($c->phone,'+') }}</small>
                                </div>
                                @endforeach
                            </div>
                            <div id="badge-customer" class="sel-badge d-none">
                                <span id="badge-customer-text"></span>
                                <span class="clr" data-target="customer">&times;</span>
                            </div>
                        </div>
                    </div>

                    {{-- Driver --}}
                    <div class="col-12">
                        <label class="form-label">
                            {{ translate('Driver') }}
                            <span class="text-muted fw-normal" style="font-size:11px;">({{ translate('Optional') }})</span>
                        </label>
                        <input type="hidden" name="driver_id" id="driver_id" value="{{ old('driver_id') }}">
                        <div class="ss-wrap" id="wrap-driver">
                            <input type="text" class="form-control ss-input" id="search-driver" placeholder="{{ translate('Search driver...') }}" autocomplete="off">
                            <div class="ss-dropdown" id="dd-driver">
                                @foreach($drivers as $d)
                                <div class="dd-item"
                                     data-value="{{ $d->id }}"
                                     data-label="#{{ $d->serial }} {{ $d->first_name }} {{ $d->last_name }} ({{ ltrim($d->phone,'+') }})">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="background:#f0fdf4;color:#065f46;border:1px solid #a7f3d0;
                                                     border-radius:4px;padding:1px 6px;font-size:11px;font-weight:700;
                                                     min-width:28px;text-align:center;">#{{ $d->serial }}</span>
                                        <div>
                                            <div style="font-size:13px;">{{ $d->first_name }} {{ $d->last_name }}</div>
                                            <div style="font-size:11px;color:#9ca3af;">{{ ltrim($d->phone,'+') }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div id="badge-driver" class="sel-badge d-none">
                                <span id="badge-driver-text"></span>
                                <span class="clr" data-target="driver">&times;</span>
                            </div>
                        </div>
                        <div id="driver-vehicle-hint" class="vh-box d-none"></div>
                    </div>

                    {{-- Zone --}}
                    <div class="col-6">
                        <label class="form-label">{{ translate('Zone') }} <span class="text-danger">*</span></label>
                        <input type="hidden" name="zone_id" id="zone_id" value="{{ old('zone_id') }}" required>
                        <div class="ss-wrap" id="wrap-zone">
                            <input type="text" class="form-control ss-input" id="search-zone" placeholder="{{ translate('Search zone...') }}" autocomplete="off">
                            <div class="ss-dropdown" id="dd-zone">
                                @foreach($zones as $z)
                                <div class="dd-item" data-value="{{ $z->id }}" data-label="{{ $z->name }}">{{ $z->name }}</div>
                                @endforeach
                            </div>
                            <div id="badge-zone" class="sel-badge d-none">
                                <span id="badge-zone-text"></span>
                                <span class="clr" data-target="zone">&times;</span>
                            </div>
                        </div>
                    </div>

                    {{-- Vehicle Category --}}
                    <div class="col-6">
                        <label class="form-label">
                            {{ translate('Vehicle Category') }} <span class="text-danger">*</span>
                            <span id="category-auto-badge" class="badge bg-success ms-1 d-none" style="font-size:9px;vertical-align:middle;">Auto</span>
                        </label>
                        <input type="hidden" name="vehicle_category_id" id="vehicle_category_id" value="{{ old('vehicle_category_id') }}" required>
                        <div class="ss-wrap" id="wrap-vehicle_category">
                            <input type="text" class="form-control ss-input" id="search-vehicle_category" placeholder="{{ translate('Search category...') }}" autocomplete="off">
                            <div class="ss-dropdown" id="dd-vehicle_category">
                                @foreach($vehicleCategories as $vc)
                                <div class="dd-item" data-value="{{ $vc->id }}" data-label="{{ $vc->name }} ({{ $vc->type }})">
                                    {{ $vc->name }} <small class="text-muted">({{ $vc->type }})</small>
                                </div>
                                @endforeach
                            </div>
                            <div id="badge-vehicle_category" class="sel-badge d-none">
                                <span id="badge-vehicle_category-text"></span>
                                <span class="clr" data-target="vehicle_category">&times;</span>
                            </div>
                        </div>
                    </div>

                </div>

                <hr style="margin:16px 0;border-color:#f3f4f6;">

                <div class="f-section">{{ translate('Trip Locations') }}</div>

                <div class="mb-2">
                    <label class="form-label">{{ translate('Pickup Address') }} <span class="text-danger">*</span></label>
                    <div class="addr-wrap">
                        <span class="addr-dot pickup"></span>
                        <input type="text" id="pickup_address" name="pickup_address" class="form-control" required
                               value="{{ old('pickup_address') }}" placeholder="{{ translate('Search pickup location...') }}">
                    </div>
                    <input type="hidden" id="pickup_lat" name="pickup_lat" value="{{ old('pickup_lat') }}">
                    <input type="hidden" id="pickup_lng" name="pickup_lng" value="{{ old('pickup_lng') }}">
                </div>

                <div class="mb-3">
                    <div id="bn-form-map" class="bn-form-map"></div>
                </div>

                <div class="mb-1">
                    <label class="form-label">{{ translate('Dropoff Address') }} <span class="text-danger">*</span></label>
                    <div class="addr-wrap">
                        <span class="addr-dot dest"></span>
                        <input type="text" id="destination_address" name="destination_address" class="form-control" required
                               value="{{ old('destination_address') }}" placeholder="{{ translate('Search destination...') }}">
                    </div>
                    <input type="hidden" id="destination_lat" name="destination_lat" value="{{ old('destination_lat') }}">
                    <input type="hidden" id="destination_lng" name="destination_lng" value="{{ old('destination_lng') }}">
                </div>

                {{-- Hidden route-info-bar (kept for JS compatibility) --}}
                <div id="route-info-bar">
                    <span><strong>Distance:</strong> <span id="route-distance">—</span></span>
                    <span><strong>Est. Time:</strong> <span id="route-duration">—</span></span>
                </div>

                {{-- ══ FARE ESTIMATE CARD ══ --}}
                <div id="fare-estimate-card">
                    <div class="fare-card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="#fff" viewBox="0 0 16 16">
                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 12.5v-9zm1.5-.5a.5.5 0 0 0-.5.5v1h14v-1a.5.5 0 0 0-.5-.5h-13zm13 4h-14v5.5a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5V7z"/>
                        </svg>
                        <span>{{ translate('Trip Estimate') }}</span>
                    </div>
                    <div class="fare-card-body">
                        <div class="fare-stat">
                            <div class="fare-stat-label">{{ translate('Distance') }}</div>
                            <div class="fare-stat-value" id="fe-distance">—</div>
                        </div>
                        <div class="fare-stat">
                            <div class="fare-stat-label">{{ translate('Est. Time') }}</div>
                            <div class="fare-stat-value" id="fe-duration">—</div>
                        </div>
                        <div class="fare-stat">
                            <div class="fare-stat-label">{{ translate('Est. Fare') }}</div>
                            <div class="fare-stat-value" id="fe-fare">—</div>
                        </div>
                    </div>
                    <div class="fare-card-note" id="fe-note">
                        {{ translate('Select zone & category above to see estimated fare') }}
                    </div>
                </div>

                <hr style="margin:16px 0;border-color:#f3f4f6;">

                <div class="f-section">{{ translate('Schedule & Payment') }}</div>

                <div class="row g-2">

                    {{-- Schedule time --}}
                    <div class="col-12" style="position:relative;">
                        <label class="form-label">{{ translate('Schedule Time') }} <span class="text-danger">*</span></label>
                        <input type="hidden" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}">
                        <input type="text" id="scheduled_at_display" class="form-control" readonly required
                               placeholder="{{ translate('Select date & time') }}"
                               value="{{ old('scheduled_at') ? \Carbon\Carbon::parse(old('scheduled_at'))->format('m/d/Y H:i') : '' }}"
                               style="background:#fff;cursor:pointer;">
                        <div id="dt-picker" class="card shadow border mt-1 p-3 d-none">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">{{ translate('Date') }}</label>
                                <input type="date" id="dt-date" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">{{ translate('Time') }}</label>
                                <div class="d-flex align-items-center gap-2">
                                    <select id="dt-hour" class="form-select form-select-sm" style="width:84px;">
                                        @for($h=0;$h<=23;$h++)<option value="{{ $h }}">{{ str_pad($h,2,'0',STR_PAD_LEFT) }}</option>@endfor
                                    </select>
                                    <span class="fw-bold">:</span>
                                    <select id="dt-minute" class="form-select form-select-sm" style="width:84px;">
                                        @for($m=0;$m<60;$m+=5)<option value="{{ $m }}">{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>@endfor
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="dt-cancel">{{ translate('Cancel') }}</button>
                                <button type="button" class="btn btn-sm btn-primary" id="dt-apply">{{ translate('Apply') }}</button>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="col-12">
                        <label class="form-label">{{ translate('Payment Method') }}</label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value="cash"           {{ old('payment_method','cash')==='cash'           ?'selected':'' }}>{{ translate('Cash') }}</option>
                            <option value="digital_payment"{{ old('payment_method')==='digital_payment'       ?'selected':'' }}>{{ translate('Digital Payment') }}</option>
                            <option value="wallet"         {{ old('payment_method')==='wallet'                ?'selected':'' }}>{{ translate('Wallet') }}</option>
                        </select>
                    </div>

                    {{-- Note --}}
                    <div class="col-12">
                        <label class="form-label">
                            {{ translate('Note') }}
                            <span class="text-muted fw-normal" style="font-size:11px;">({{ translate('Optional') }})</span>
                        </label>
                        <textarea name="note" id="note" rows="2" class="form-control"
                                  placeholder="{{ translate('Any special instructions...') }}">{{ old('note') }}</textarea>
                    </div>

                </div>

            </form>
        </div>

        <div class="bn-submit">
            <a href="{{ route('admin.book-now.index', 'all') }}" class="btn btn-outline-secondary px-4" style="font-size:13px;">
                {{ translate('Cancel') }}
            </a>
            <button type="submit" form="booking-form" class="btn btn-primary px-5"
                    style="font-size:13px;background:#0f766e;border-color:#0f766e;">
                {{ translate('Create Booking') }}
            </button>
        </div>

    </div>{{-- /bn-form-panel --}}


    {{-- RIGHT — Fleet Map --}}
    <div class="bn-map-panel">

        <div class="bn-fleet-header">
            <div class="bn-fleet-top">
                <div>
                    <div class="bn-fleet-title">{{ translate('Live Fleet View') }}</div>
                    <div class="bn-fleet-sub">{{ translate('Route preview updates as you select addresses') }}</div>
                </div>
                <select id="bn-zone-filter" class="bn-zone-select">
                    @foreach($zones as $z)
                    <option value="{{ $z->id }}">{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="bn-fleet-tabs">
                 <a href="#" data-type="nearest" id="bn-nearest-tab">
                    📍 {{ translate('Nearest') }} <span class="pulse-dot"></span>
                </a>
                <a href="#" class="active" data-type="{{ ALL_DRIVER }}">{{ translate('All Drivers') }}</a>
                <a href="#" data-type="{{ DRIVER_ON_TRIP }}">{{ translate('On-Trip') }}</a>
                <a href="#" data-type="{{ DRIVER_IDLE }}">{{ translate('Idle') }}</a>

                <span class="sep">|</span>
                <a href="#" data-type="{{ ALL_CUSTOMER }}">{{ translate('Customers') }}</a>
            </div>
        </div>

        <div class="bn-fleet-body">

            <div class="bn-user-list">
                <div class="bn-list-inner">
                    <div id="bn-zone-list">
                        <h6 id="bn-list-title">{{ translate('Driver List') }}</h6>
                        <div class="bn-search-wrap" id="bn-search-wrap">
                            <input type="text" id="bn-search-input" placeholder="{{ translate('Search driver...') }}">
                            <button id="bn-search-btn">{{ translate('Search') }}</button>
                        </div>
                        <ul class="zone-list" id="bn-user-list-ul">
                            <li class="py-3 text-center text-muted" style="font-size:12px;">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                {{ translate('Loading...') }}
                            </li>
                        </ul>
                    </div>
                    <div id="bn-user-details"></div>
                </div>
            </div>

            <div class="bn-map-area">
                <div class="bn-map-search">
                    <input type="text" id="bn-location-search" placeholder="{{ translate('Search for a location') }}">
                </div>
                <div id="bn-fleet-map"></div>
            </div>

        </div>

    </div>

</div>


{{-- ADD CUSTOMER MODAL --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('Add New Customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="customer-form-error" class="alert alert-danger d-none"></div>
                <div id="customer-form-success" class="alert alert-success d-none"></div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">{{ translate('First Name') }} <span class="text-danger">*</span></label>
                        <input type="text" id="new_first_name" class="form-control" placeholder="{{ translate('First Name') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">{{ translate('Last Name') }} <span class="text-danger">*</span></label>
                        <input type="text" id="new_last_name" class="form-control" placeholder="{{ translate('Last Name') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                        <input type="text" id="new_phone" class="form-control" placeholder="e.g. 923001234567">
                        <div class="form-text">{{ translate('Include country code without the + sign') }}</div>
                    </div>
                    <!--<div class="col-12">-->
                    <!--    <label class="form-label fw-semibold">-->
                    <!--        {{ translate('Email') }}-->
                    <!--        <span class="text-muted fw-normal">({{ translate('Optional') }})</span>-->
                    <!--    </label>-->
                    <!--    <input type="email" id="new_email" class="form-control" placeholder="email@example.com">-->
                    <!--</div>-->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-customer-btn">
                    <span id="save-customer-text">{{ translate('Save Customer') }}</span>
                    <span id="save-customer-spinner" class="spinner-border spinner-border-sm d-none ms-1"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="bn-userId">
@endsection


@push('script')
<script>
"use strict";

// ══════════════════════════════════════════════════════════════════
// FARE ESTIMATE ENGINE
// ══════════════════════════════════════════════════════════════════
const FareEngine = (function () {

    let _distanceKm  = 0;
    let _durationTxt = '—';
    let _distanceTxt = '—';
    let _zoneId      = '';
    let _categoryId  = '';
    let _fetchTimer  = null;

    const card    = document.getElementById('fare-estimate-card');
    const elDist  = document.getElementById('fe-distance');
    const elDur   = document.getElementById('fe-duration');
    const elFare  = document.getElementById('fe-fare');
    const elNote  = document.getElementById('fe-note');

    function _showLoading() {
        elFare.className = 'fare-stat-value loading';
        elFare.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:11px;height:11px;border-width:2px;"></span> {{ translate("Calculating...") }}';
        elNote.textContent = '';
    }

    function _showFare(amount, currency) {
        elFare.className = 'fare-stat-value';
        elFare.textContent = currency + ' ' + parseFloat(amount).toFixed(2);
        elNote.textContent = '{{ translate("Estimated fare — final amount may vary") }}';
    }

    function _showFareError(msg) {
        elFare.className = 'fare-stat-value';
        elFare.style.color = '#dc2626';
        elFare.style.fontSize = '11px';
        elFare.textContent = msg;
        elNote.textContent = '';
    }

    function _resetFare() {
        elFare.className = 'fare-stat-value';
        elFare.style.color = '';
        elFare.style.fontSize = '';
        elFare.textContent = '—';
        elNote.textContent = '{{ translate("Select zone & category above to see estimated fare") }}';
    }

    function _fetchFare() {
        if (!_zoneId || !_categoryId || _distanceKm <= 0) { _resetFare(); return; }
        _showLoading();
        fetch('{{ route("admin.book-now.estimate-fare") }}?' + new URLSearchParams({
            zone_id: _zoneId,
            vehicle_category_id: _categoryId,
            distance_km: _distanceKm
        }))
        .then(r => r.json())
        .then(function(res) {
            if (res.success) _showFare(res.estimated_fare, res.currency ?? '');
            else _showFareError(res.message ?? '{{ translate("No fare configured") }}');
        })
        .catch(function() { _showFareError('{{ translate("Could not fetch fare") }}'); });
    }

    function _tryFetch() { clearTimeout(_fetchTimer); _fetchTimer = setTimeout(_fetchFare, 300); }

    function setRoute(distKm, distTxt, durTxt) {
        _distanceKm  = distKm;
        _distanceTxt = distTxt || (distKm.toFixed(1) + ' km');
        _durationTxt = durTxt || '—';
        elDist.textContent = _distanceTxt;
        elDur.textContent  = _durationTxt;
        card.style.display = 'block';
        _tryFetch();
    }

    function setZone(id)     { _zoneId = id; _tryFetch(); }
    function setCategory(id) { _categoryId = id; _tryFetch(); }

    function reset() {
        _distanceKm = 0; _distanceTxt = '—'; _durationTxt = '—';
        elDist.textContent = '—'; elDur.textContent = '—';
        card.style.display = 'none'; _resetFare();
    }

    return { setRoute, setZone, setCategory, reset };
})();


// ══════════════════════════════════════════════════════════════════
// 1. FLEET MAP + NEAREST DRIVERS
// ══════════════════════════════════════════════════════════════════
$(document).ready(function() {

    let fleetMap, markerCluster = null, activeInfoWindow = null;
    let formMap = null, formDirectionsRenderer = null, formPickupMarker = null, formDestMarker = null;
    let fleetMarkers = [], currentlyOpenMarkerId = null;
    let isSingleView = false;
    let singleInterval, doubleInterval;

    let directionsService, directionsRenderer;
    let pickupLatLng = null, destLatLng = null;
    const geocoder = new google.maps.Geocoder();

    let currentType   = '{{ ALL_DRIVER }}';
    let currentZone   = $('#bn-zone-filter').val();
    let currentSearch = '';

    // ── Nearest mode state ────────────────────────────────────────
    let isNearestMode      = false;
    let nearestPickupLat   = null;
    let nearestPickupLng   = null;

    // ── Haversine distance calculator ─────────────────────────────
    function haversineKm(lat1, lng1, lat2, lng2) {
        const R    = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a    = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                     Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                     Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    // ── Render nearest drivers list ───────────────────────────────
    function renderNearestDrivers() {
        const listUl = document.getElementById('bn-user-list-ul');

        if (!nearestPickupLat || !nearestPickupLng) {
            listUl.innerHTML = `
                <div class="bn-nearest-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#9ca3af" viewBox="0 0 16 16">
                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                    Set a <strong>pickup address</strong> first<br>to see nearest drivers
                </div>`;
            return;
        }

        if (!fleetMarkers.length) {
            listUl.innerHTML = '<div class="bn-nearest-loading"><span class="spinner-border spinner-border-sm me-1"></span> Loading drivers...</div>';
            return;
        }

        // Compute distance for every marker
        const drivers = fleetMarkers.map(function(marker) {
            const pos    = marker.getPosition();
            const distKm = haversineKm(nearestPickupLat, nearestPickupLng, pos.lat(), pos.lng());
            return { marker, distKm, id: marker.id, title: marker.getTitle() || '' };
        });

        // Sort ascending by distance
        drivers.sort(function(a, b) { return a.distKm - b.distKm; });

        let html = `<div class="bn-nearest-hint active-hint">
            📍 Sorted by distance from pickup &nbsp;·&nbsp; updates every 15s
        </div>`;

        drivers.forEach(function(d, idx) {
            // Human-readable distance
            const km = d.distKm < 1
                ? (d.distKm * 1000).toFixed(0) + ' m'
                : d.distKm.toFixed(1) + ' km';

            // Serial from title e.g. "#2 Mike John ..."
            const serialMatch = d.title.match(/#(\d+)/);
            const serial      = serialMatch ? serialMatch[1] : (idx + 1);

            // Name — strip serial and phone
            const nameRaw  = d.title.replace(/#\d+\s*/, '').split('(')[0].trim();
            const phoneMat = d.title.match(/\(([^)]+)\)/);
            const phone    = phoneMat ? phoneMat[1] : '';

            // Colour-code distance badge
            let distColor = '#1d4ed8', distBg = '#eff6ff', distBorder = '#bfdbfe';
            if (d.distKm <= 2)      { distColor = '#065f46'; distBg = '#d1fae5'; distBorder = '#6ee7b7'; }
            else if (d.distKm <= 5) { distColor = '#92400e'; distBg = '#fef3c7'; distBorder = '#fcd34d'; }
            else                    { distColor = '#991b1b'; distBg = '#fee2e2'; distBorder = '#fca5a5'; }

            html += `
            <div class="bn-driver-card" data-marker-id="${d.id}">
                <div class="bn-driver-card-top">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="bn-driver-card-serial">#${serial}</span>
                        <span class="bn-driver-card-name">${nameRaw}</span>
                    </div>
                    <span class="bn-driver-card-dist"
                          style="background:${distBg};color:${distColor};border:1px solid ${distBorder};">
                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                        ${km}
                    </span>
                </div>
                <div class="bn-driver-card-meta">${phone}</div>
            </div>`;
        });

        listUl.innerHTML = html;

        // Click → pan map + open info window
        listUl.querySelectorAll('.bn-driver-card').forEach(function(card) {
            card.addEventListener('click', function() {
                listUl.querySelectorAll('.bn-driver-card').forEach(function(c) { c.classList.remove('selected'); });
                card.classList.add('selected');
                const markerId = card.dataset.markerId;
                const marker   = fleetMarkers.find(function(m) { return String(m.id) === String(markerId); });
                if (marker) {
                    fleetMap.panTo(marker.getPosition());
                    fleetMap.setZoom(16);
                    google.maps.event.trigger(marker, 'click');
                }
            });
        });
    }

    // ── Fetch all-driver markers for nearest mode ─────────────────
    function fetchNearestMarkers(callback) {
        $.get({
            url: '{{ route("admin.fleet-map-view-using-ajax") }}',
            data: { zone_id: currentZone, type: '{{ ALL_DRIVER }}', search: '' },
            dataType: 'json',
            success: function(response) {
                if (!response) return;
                updateMarkers(JSON.parse(response.markers));
                try { drawZoneFromPolygons(JSON.parse(response.polygons || '[]')); } catch(e) {}
                setTimeout(function() {
                    renderNearestDrivers();
                    if (typeof callback === 'function') callback();
                }, 200);
            }
        });
    }

    function initFleetMap() {
        fleetMap = new google.maps.Map(document.getElementById('bn-fleet-map'), {
            zoom: 2, center: { lat: 0, lng: 0 },
            fullscreenControl: true,
            mapTypeControlOptions: { position: google.maps.ControlPosition.BOTTOM_LEFT }
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: { strokeColor: '#0f766e', strokeWeight: 5, strokeOpacity: .9 }
        });
        directionsRenderer.setMap(fleetMap);

        try {
            formMap = new google.maps.Map(document.getElementById('bn-form-map'), {
                zoom: 13, center: { lat: 0, lng: 0 },
                fullscreenControl: false, mapTypeControl: false, streetViewControl: false
            });
            formDirectionsRenderer = new google.maps.DirectionsRenderer({
                suppressMarkers: true,
                polylineOptions: { strokeColor: '#0f766e', strokeWeight: 4, strokeOpacity: .9 }
            });
            formDirectionsRenderer.setMap(formMap);

            formPickupMarker = new google.maps.Marker({
                map: formMap, draggable: true, visible: false,
                label: { text: 'A', color: '#fff', fontWeight: 'bold', fontSize: '11px' },
                icon: { path: google.maps.SymbolPath.CIRCLE, scale: 9, fillColor: '#e53935', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 }
            });
            formDestMarker = new google.maps.Marker({
                map: formMap, draggable: true, visible: false,
                label: { text: 'B', color: '#fff', fontWeight: 'bold', fontSize: '11px' },
                icon: { path: google.maps.SymbolPath.CIRCLE, scale: 9, fillColor: '#0f766e', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 }
            });

            formPickupMarker.addListener('dragend', function() {
                const pos = formPickupMarker.getPosition();
                pickupLatLng = pos;
                nearestPickupLat = pos.lat(); nearestPickupLng = pos.lng();
                document.getElementById('pickup_lat').value = pos.lat();
                document.getElementById('pickup_lng').value = pos.lng();
                if (window.pickupMarker) { window.pickupMarker.setPosition(pos); window.pickupMarker.setVisible(true); }
                geocoder.geocode({ location: pos }, function(res, st) {
                    if (st === 'OK' && res[0]) document.getElementById('pickup_address').value = res[0].formatted_address;
                });
                tryDrawRoute();
            });
            formDestMarker.addListener('dragend', function() {
                const pos = formDestMarker.getPosition();
                destLatLng = pos;
                document.getElementById('destination_lat').value = pos.lat();
                document.getElementById('destination_lng').value = pos.lng();
                if (window.destMarker) { window.destMarker.setPosition(pos); window.destMarker.setVisible(true); }
                geocoder.geocode({ location: pos }, function(res, st) {
                    if (st === 'OK' && res[0]) document.getElementById('destination_address').value = res[0].formatted_address;
                });
                tryDrawRoute();
            });
        } catch (e) { console.warn('formMap init failed', e); }

        window.pickupMarker = new google.maps.Marker({
            map: fleetMap, draggable: true, visible: false,
            label: { text: 'A', color: '#fff', fontWeight: 'bold', fontSize: '11px' },
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 11, fillColor: '#e53935', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 }
        });
        window.destMarker = new google.maps.Marker({
            map: fleetMap, draggable: true, visible: false,
            label: { text: 'B', color: '#fff', fontWeight: 'bold', fontSize: '11px' },
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 11, fillColor: '#0f766e', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 }
        });

        window.pickupMarker.addListener('dragend', function() {
            const pos = window.pickupMarker.getPosition();
            pickupLatLng = pos;
            nearestPickupLat = pos.lat(); nearestPickupLng = pos.lng();
            document.getElementById('pickup_lat').value = pos.lat();
            document.getElementById('pickup_lng').value = pos.lng();
            geocoder.geocode({ location: pos }, function(res, st) {
                if (st === 'OK' && res[0]) document.getElementById('pickup_address').value = res[0].formatted_address;
            });
            tryDrawRoute();
        });
        window.destMarker.addListener('dragend', function() {
            const pos = window.destMarker.getPosition();
            destLatLng = pos;
            document.getElementById('destination_lat').value = pos.lat();
            document.getElementById('destination_lng').value = pos.lng();
            geocoder.geocode({ location: pos }, function(res, st) {
                if (st === 'OK' && res[0]) document.getElementById('destination_address').value = res[0].formatted_address;
            });
            tryDrawRoute();
        });

        const searchBox = new google.maps.places.SearchBox(document.getElementById('bn-location-search'));
        fleetMap.addListener('bounds_changed', function() { searchBox.setBounds(fleetMap.getBounds()); });
        searchBox.addListener('places_changed', function() {
            const places = searchBox.getPlaces();
            if (!places.length) return;
            const bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {
                if (!place.geometry) return;
                if (place.geometry.viewport) bounds.union(place.geometry.viewport);
                else bounds.extend(place.geometry.location);
            });
            fleetMap.fitBounds(bounds);
        });

        fetchListAndMarkers();
        doubleInterval = setInterval(fetchListAndMarkers, 15000);
    }

    let zonePolygon = null;

    function drawZoneFromPolygons(polys) {
        if (zonePolygon) { zonePolygon.setMap(null); zonePolygon = null; }
        try {
            const p = polys && polys.length ? polys[0] : [];
            if (!p || !p.length) return;
            const path = p.map(function(pt) { return { lat: parseFloat(pt.lat), lng: parseFloat(pt.lng) }; });
            zonePolygon = new google.maps.Polygon({
                paths: path, strokeColor: '#0f766e', strokeOpacity: 0.9, strokeWeight: 2,
                fillColor: '#0f766e', fillOpacity: 0.06
            });
            zonePolygon.setMap(fleetMap);
            const bounds = new google.maps.LatLngBounds();
            path.forEach(function(pt) { bounds.extend(pt); });
            fleetMap.fitBounds(bounds, { top: 60, left: 20, right: 20, bottom: 20 });
        } catch (e) { console.warn('drawZoneFromPolygons error', e); }
    }

    // ── Route drawing — feeds FareEngine + nearest ────────────────
    function tryDrawRoute() {
        if (!pickupLatLng || !destLatLng) {
            // Even if no dest yet, update nearest pickup coords + re-render
            if (pickupLatLng) {
                nearestPickupLat = pickupLatLng.lat();
                nearestPickupLng = pickupLatLng.lng();
                if (isNearestMode) renderNearestDrivers();
            }
            return;
        }

        // Update nearest coords from current pickup
        nearestPickupLat = pickupLatLng.lat();
        nearestPickupLng = pickupLatLng.lng();

        directionsService.route(
            { origin: pickupLatLng, destination: destLatLng, travelMode: google.maps.TravelMode.DRIVING },
            function(result, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(result);
                    if (formDirectionsRenderer) formDirectionsRenderer.setDirections(result);

                    const leg    = result.routes[0].legs[0];
                    const distKm = leg.distance.value / 1000;
                    const distTxt = leg.distance.text;
                    const durTxt  = leg.duration.text;

                    document.getElementById('route-distance').textContent = distTxt;
                    document.getElementById('route-duration').textContent = durTxt;
                    FareEngine.setRoute(distKm, distTxt, durTxt);

                    const bounds = new google.maps.LatLngBounds();
                    bounds.extend(pickupLatLng);
                    bounds.extend(destLatLng);
                    fleetMap.fitBounds(bounds, { top: 60, left: 20, right: 20, bottom: 20 });
                    try { if (formMap) formMap.fitBounds(bounds); } catch(e) {}
                }
            }
        );

        // Re-render nearest list instantly with new pickup position
        if (isNearestMode) renderNearestDrivers();
    }

    function setupAddressAC(inputId, latId, lngId, marker, assignFn) {
        const ac = new google.maps.places.Autocomplete(document.getElementById(inputId));
        ac.addListener('place_changed', function() {
            const place = ac.getPlace();
            if (!place.geometry) return;
            const loc = place.geometry.location;
            assignFn(loc);
            document.getElementById(latId).value = loc.lat();
            document.getElementById(lngId).value = loc.lng();
            marker.setPosition(loc);
            marker.setVisible(true);
            try {
                if (marker === window.pickupMarker && formPickupMarker) {
                    formPickupMarker.setPosition(loc); formPickupMarker.setVisible(true);
                    formMap.panTo(loc); formMap.setZoom(14);
                }
                if (marker === window.destMarker && formDestMarker) {
                    formDestMarker.setPosition(loc); formDestMarker.setVisible(true);
                    formMap.panTo(loc); formMap.setZoom(14);
                }
            } catch(e) {}
            fleetMap.panTo(loc); fleetMap.setZoom(14);
            tryDrawRoute();
        });
    }

    const infoWindow = new google.maps.InfoWindow();

    function openInfoWindow(marker, data) {
        const content = `<div class="map-clusters-custom-window">
            <a class="d-flex justify-content-between gap-1 align-items-center"
               href="${data.driver ?? data.customer}" target="_blank">
                <h6>${data.title}</h6>
                ${data.safetyAlertIcon ? `<img src="${data.safetyAlertIcon}" alt="" height="22" width="22">` : ''}
            </a>
            <a href="${data.trip ?? '#'}" target="_blank"><p>${data.subtitle || ''}</p></a>
        </div>`;
        infoWindow.setContent(content);
        infoWindow.setPosition(marker.getPosition());
        infoWindow.open(fleetMap, marker);
        currentlyOpenMarkerId = data.id;
        activeInfoWindow = infoWindow;
        singleViewZoom(data.position);
    }

    function updateMarkers(markerData) {
        const updated = new Map();
        const newM    = [];
        markerData.forEach(function(data) {
            const existing = fleetMarkers.find(function(m) { return m.id === data.id; });
            if (existing) {
                const oldPos = existing.getPosition();
                const newPos = new google.maps.LatLng(data.position.lat, data.position.lng);
                if (!oldPos.equals(newPos)) animateMarker(existing, { lat: oldPos.lat(), lng: oldPos.lng() }, data.position);
                if (existing.getIcon() !== data.icon) existing.setIcon(data.icon);
                if (currentlyOpenMarkerId === data.id) openInfoWindow(existing, data);
                updated.set(data.id, existing);
            } else {
                const m = new google.maps.Marker({ id: data.id, position: data.position, title: data.title, icon: data.icon });
                m.addListener('click', function() { openInfoWindow(m, data); });
                m.setMap(fleetMap);
                newM.push(m);
                updated.set(data.id, m);
            }
        });
        fleetMarkers.forEach(function(m) {
            if (!updated.has(m.id)) { if (markerCluster) markerCluster.removeMarker(m); m.setMap(null); }
        });
        fleetMarkers = Array.from(updated.values());
        if (markerCluster) markerCluster.addMarkers(newM);
        else markerCluster = new markerClusterer.MarkerClusterer({ map: fleetMap, markers: fleetMarkers });
    }

    function animateMarker(marker, startLL, endLL, duration) {
        duration = duration || 14980;
        const startTime = performance.now();
        function move(ts) {
            const p = Math.min((ts - startTime) / duration, 1);
            marker.setPosition(new google.maps.LatLng(
                startLL.lat + (endLL.lat - startLL.lat) * p,
                startLL.lng + (endLL.lng - startLL.lng) * p
            ));
            if (p < 1) requestAnimationFrame(move);
        }
        requestAnimationFrame(move);
    }

    function singleViewZoom(center) {
        if (fleetMap.getZoom() <= 19) { fleetMap.setCenter(center); fleetMap.setZoom(19); }
    }

    function fetchListAndMarkers() {
        const reqData = { zone_id: currentZone, type: currentType, search: currentSearch };
        $.get({
            url: '{{ route("admin.fleet-map-view-using-ajax") }}',
            data: reqData, dataType: 'json',
            success: function(response) {
                if (!response) return;
                updateMarkers(JSON.parse(response.markers));
                try { drawZoneFromPolygons(JSON.parse(response.polygons || '[]')); } catch(e) {}
                const listUrl = currentType === '{{ ALL_CUSTOMER }}'
                    ? '{{ route("admin.fleet-map-customer-list", ":type") }}'.replace(':type', currentType)
                    : '{{ route("admin.fleet-map-driver-list",   ":type") }}'.replace(':type', currentType);
                $.get({ url: listUrl, data: reqData, dataType: 'json', success: function(html) {
                    $('#bn-user-list-ul').html(html); bindListClicks();
                }});
            }
        });
    }

    function fetchSingleUser() {
        const id = $('#bn-userId').val();
        if (!id) return;
        const url = currentType === '{{ ALL_CUSTOMER }}'
            ? '{{ route("admin.fleet-map-view-single-customer", ":id") }}'.replace(':id', id)
            : '{{ route("admin.fleet-map-view-single-driver",   ":id") }}'.replace(':id', id);
        $.get({ url: url, data: { zone_id: currentZone }, dataType: 'json', success: function(response) {
            const markerData = JSON.parse(response.markers);
            updateMarkers(markerData);
            try { drawZoneFromPolygons(JSON.parse(response.polygons || '[]')); } catch(e) {}
            if (!isSingleView && markerData.length) {
                const first = markerData[0]; singleViewZoom(first.position); isSingleView = true;
                const m = fleetMarkers.find(function(mk) { return mk.id === first.id; });
                if (m) openInfoWindow(m, first);
            }
        }});
    }

    function loadUserDetails(id) {
        const url = currentType === '{{ ALL_CUSTOMER }}'
            ? '{{ route("admin.fleet-map-customer-details", ":id") }}'.replace(':id', id)
            : '{{ route("admin.fleet-map-driver-details",   ":id") }}'.replace(':id', id);
        $.get({ url: url, dataType: 'json', success: function(html) {
            $('#bn-zone-list').hide(); $('#bn-user-details').show().html(html);
            $('#bn-user-details').find('.customer-back-btn, [data-action="back"]').on('click', function(e) {
                e.preventDefault(); resetListView();
            });
        }});
    }

    function resetListView() {
        $('#bn-userId').val(''); $('#bn-user-details').hide().html(''); $('#bn-zone-list').show();
        isSingleView = false; clearInterval(singleInterval); singleInterval = null;
        fetchListAndMarkers();
        if (!doubleInterval) doubleInterval = setInterval(fetchListAndMarkers, 15000);
    }

    function bindListClicks() {
        $('#bn-user-list-ul').find('.user-details label, [data-id]').off('click').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id'); if (!id) return;
            $('#bn-userId').val(id); isSingleView = false;
            clearInterval(doubleInterval); doubleInterval = null;
            fetchSingleUser();
            if (singleInterval) clearInterval(singleInterval);
            singleInterval = setInterval(fetchSingleUser, 15000);
            loadUserDetails(id);
        });
    }

    // ── Tab click handler (includes Nearest tab) ──────────────────
    $('.bn-fleet-tabs a').on('click', function(e) {
        e.preventDefault();
        $('.bn-fleet-tabs a').removeClass('active');
        $(this).addClass('active');
        currentType = $(this).data('type');
        $('#bn-userId').val(''); isSingleView = false;
        clearInterval(singleInterval); singleInterval = null;
        clearInterval(doubleInterval); doubleInterval = null;
        $('#bn-user-details').hide().html(''); $('#bn-zone-list').show();

        if (currentType === 'nearest') {
            // ── NEAREST MODE ──
            isNearestMode = true;
            $('#bn-search-wrap').hide();
            $('#bn-list-title').text('{{ translate("Nearest Drivers") }}');

            fetchNearestMarkers(function() {
                // Start 15s auto-refresh
                doubleInterval = setInterval(function() {
                    fetchNearestMarkers();
                }, 15000);
            });

        } else {
            // ── NORMAL MODE ──
            isNearestMode = false;
            $('#bn-search-wrap').show();
            const isCustomer = currentType === '{{ ALL_CUSTOMER }}';
            $('#bn-list-title').text(isCustomer ? '{{ translate("Customer List") }}' : '{{ translate("Driver List") }}');
            $('#bn-search-input').attr('placeholder', isCustomer ? '{{ translate("Search customer...") }}' : '{{ translate("Search driver...") }}');
            fetchListAndMarkers();
            doubleInterval = setInterval(fetchListAndMarkers, 15000);
        }
    });

    $('#bn-zone-filter').on('change', function() {
        currentZone = $(this).val();
        clearInterval(doubleInterval);
        if (isNearestMode) {
            fetchNearestMarkers(function() {
                doubleInterval = setInterval(function() { fetchNearestMarkers(); }, 15000);
            });
        } else {
            fetchListAndMarkers();
            doubleInterval = setInterval(fetchListAndMarkers, 15000);
        }
    });

    $('#bn-search-btn').on('click', function() { currentSearch = $('#bn-search-input').val(); fetchListAndMarkers(); });
    $('#bn-search-input').on('keydown', function(e) { if (e.key === 'Enter') { currentSearch = $(this).val(); fetchListAndMarkers(); } });

    initFleetMap();
    setupAddressAC('pickup_address',      'pickup_lat',      'pickup_lng',      window.pickupMarker, function(l) { pickupLatLng = l; });
    setupAddressAC('destination_address', 'destination_lat', 'destination_lng', window.destMarker,   function(l) { destLatLng   = l; });
});


// ══════════════════════════════════════════════════════════════════
// 2. SEARCHABLE SELECT WIDGET
// ══════════════════════════════════════════════════════════════════
(function() {
    function init(key, onSelect, onClear) {
        const hiddenId = (key === 'vehicle_category') ? 'vehicle_category_id' : key + '_id';
        const hidden   = document.getElementById(hiddenId);
        const search   = document.getElementById('search-' + key);
        const dd       = document.getElementById('dd-' + key);
        const badge    = document.getElementById('badge-' + key);
        const badgeTxt = document.getElementById('badge-' + key + '-text');
        if (!hidden || !search || !dd) return;

        const items = Array.from(dd.querySelectorAll('.dd-item'));
        if (hidden.value) {
            const pre = items.find(function(i) { return i.dataset.value == hidden.value; });
            if (pre) apply(pre.dataset.value, pre.dataset.label, false);
        }

        search.addEventListener('input', function() {
            const raw = this.value.trim();
            const q   = raw.toLowerCase();
            let any   = false;

            items.forEach(function(i) {
                const label = i.dataset.label.toLowerCase();
                let m;

                if (raw === '') {
                    m = true;
                } else if (key === 'driver') {
                    if (/^\d+$/.test(raw)) {
                        // Plain number → exact serial match
                        const serialMatch = label.match(/^#(\d+)\s/);
                        const serial      = serialMatch ? serialMatch[1] : null;
                        m = serial === raw;
                    } else if (raw.startsWith('#')) {
                        // "#2" → strip and serial match
                        const numPart = raw.slice(1);
                        if (/^\d+$/.test(numPart)) {
                            const serialMatch = label.match(/^#(\d+)\s/);
                            const serial      = serialMatch ? serialMatch[1] : null;
                            m = serial === numPart;
                        } else {
                            m = label.includes(q);
                        }
                    } else {
                        m = label.includes(q);
                    }
                } else {
                    m = label.includes(q);
                }

                i.style.display = m ? '' : 'none';
                if (m) any = true;
            });

            let empty = dd.querySelector('.dd-empty');
            if (!any) {
                if (!empty) {
                    empty = document.createElement('div');
                    empty.className = 'dd-empty';
                    empty.textContent = 'No results';
                    dd.appendChild(empty);
                }
                empty.style.display = '';
            } else if (empty) {
                empty.style.display = 'none';
            }
            open();
        });

        search.addEventListener('focus', function() { items.forEach(function(i) { i.style.display = ''; }); open(); });
        dd.addEventListener('mousedown', function(e) {
            const item = e.target.closest('.dd-item'); if (!item) return;
            e.preventDefault(); apply(item.dataset.value, item.dataset.label, true);
        });
        document.addEventListener('click', function(e) {
            if (!search.contains(e.target) && !dd.contains(e.target)) { close(); if (!hidden.value) search.value = ''; }
        });
        if (badge) badge.querySelector('.clr').addEventListener('click', function() {
            hidden.value = ''; search.value = ''; badge.classList.add('d-none');
            items.forEach(function(i) { i.style.display = ''; });
            if (typeof onClear === 'function') onClear();
        });

        function apply(val, label, trigger) {
            hidden.value = val; search.value = ''; badgeTxt.textContent = label;
            badge.classList.remove('d-none'); close();
            if (trigger && typeof onSelect === 'function') onSelect(val, label);
        }
        function open()  { dd.classList.add('open'); }
        function close() { dd.classList.remove('open'); }
        window['_ssApply_' + key] = apply;
    }

    init('customer');

    init('zone',
        function(id) {
            try { $('#bn-zone-filter').val(id).trigger('change'); } catch(e) {}
            FareEngine.setZone(id);
        },
        function() {
            try {
                $('#bn-zone-filter').val('');
                if (typeof zonePolygon !== 'undefined' && zonePolygon) { zonePolygon.setMap(null); zonePolygon = null; }
            } catch(e) {}
            FareEngine.setZone('');
        }
    );

    init('vehicle_category',
        function(id) { FareEngine.setCategory(id); },
        function()   { FareEngine.setCategory(''); }
    );

    init('driver',
        function(id) { fetchDriverVehicleCategory(id, true); },
        function()   { document.getElementById('category-auto-badge').classList.add('d-none'); hideDriverHint(); }
    );

    document.addEventListener('DOMContentLoaded', function() {
        const pre = document.getElementById('driver_id').value;
        if (pre) fetchDriverVehicleCategory(pre, false);
    });
})();


// ══════════════════════════════════════════════════════════════════
// 3. DRIVER → VEHICLE CATEGORY AUTO-FETCH
// ══════════════════════════════════════════════════════════════════
function showDriverHint(type, html) {
    const el = document.getElementById('driver-vehicle-hint');
    el.className = 'vh-box ' + type; el.innerHTML = html; el.classList.remove('d-none');
}
function hideDriverHint() {
    const el = document.getElementById('driver-vehicle-hint');
    el.classList.add('d-none'); el.innerHTML = '';
}

function fetchDriverVehicleCategory(driverId, animate) {
    if (!driverId) { hideDriverHint(); return; }
    const badge = document.getElementById('category-auto-badge');
    showDriverHint('loading', '<span class="spinner-border spinner-border-sm me-1" style="width:12px;height:12px;border-width:2px;"></span>Fetching...');

    fetch('{{ route("admin.book-now.driver-vehicle-category", ":id") }}'.replace(':id', driverId))
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.found) {
            const label = res.category_name + ' (' + res.category_type + ')';
            if (typeof window._ssApply_vehicle_category === 'function')
                window._ssApply_vehicle_category(res.category_id, label, false);
            document.getElementById('vehicle_category_id').value = res.category_id;
            FareEngine.setCategory(res.category_id);
            badge.classList.remove('d-none');
            if (animate) {
                const si = document.getElementById('search-vehicle_category');
                if (si) { si.classList.add('cat-auto-sel'); setTimeout(function() { si.classList.remove('cat-auto-sel'); }, 2000); }
            }
            showDriverHint('success',
                '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Auto-selected: <strong>' + label + '</strong>'
            );
        } else {
            badge.classList.add('d-none');
            showDriverHint('warning', 'No vehicle assigned. Select manually.');
        }
    })
    .catch(function() { badge.classList.add('d-none'); showDriverHint('error', 'Could not fetch vehicle info.'); });
}


// ══════════════════════════════════════════════════════════════════
// 4. DATETIME PICKER
// ══════════════════════════════════════════════════════════════════
(function() {
    const display = document.getElementById('scheduled_at_display');
    const hidden  = document.getElementById('scheduled_at');
    const picker  = document.getElementById('dt-picker');
    const dtDate  = document.getElementById('dt-date');
    const dtHour  = document.getElementById('dt-hour');
    const dtMin   = document.getElementById('dt-minute');

    const now = new Date();
    dtDate.value = now.toISOString().slice(0, 10);
    let h = now.getHours(), m = Math.ceil(now.getMinutes() / 5) * 5;
    if (m === 60) { m = 0; h++; }
    if (h >= 24) h = 0;
    dtHour.value = h; dtMin.value = m;

    display.addEventListener('click', function(e) { e.stopPropagation(); picker.classList.toggle('d-none'); });
    document.addEventListener('click', function(e) { if (!picker.contains(e.target) && e.target !== display) picker.classList.add('d-none'); });
    document.getElementById('dt-cancel').addEventListener('click', function() { picker.classList.add('d-none'); });
    document.getElementById('dt-apply').addEventListener('click', function() {
        const d = dtDate.value; if (!d) return;
        const hh = String(parseInt(dtHour.value)).padStart(2, '0');
        const mm = String(parseInt(dtMin.value)).padStart(2, '0');
        const parts = d.split('-');
        display.value = parts[1] + '/' + parts[2] + '/' + parts[0] + ' ' + hh + ':' + mm;
        hidden.value  = d + ' ' + hh + ':' + mm;
        picker.classList.add('d-none');
    });
    @if(old('scheduled_at')) hidden.value = '{{ old('scheduled_at') }}'; @endif
})();


// ══════════════════════════════════════════════════════════════════
// 5. ADD CUSTOMER VIA AJAX
// ══════════════════════════════════════════════════════════════════
document.getElementById('save-customer-btn').addEventListener('click', function() {
    const btn        = this;
    const btnText    = document.getElementById('save-customer-text');
    const spinner    = document.getElementById('save-customer-spinner');
    const errorBox   = document.getElementById('customer-form-error');
    const successBox = document.getElementById('customer-form-success');

    errorBox.classList.add('d-none'); errorBox.innerHTML = '';
    successBox.classList.add('d-none');

    const firstName = document.getElementById('new_first_name').value.trim();
    const lastName  = document.getElementById('new_last_name').value.trim();
    const phone     = document.getElementById('new_phone').value.trim();
    const email     = document.getElementById('new_email').value.trim();

    if (!firstName || !lastName || !phone) {
        errorBox.textContent = '{{ translate("First name, last name and phone are required.") }}';
        errorBox.classList.remove('d-none'); return;
    }

    btn.disabled = true; btnText.textContent = '{{ translate("Saving...") }}'; spinner.classList.remove('d-none');

    fetch('{{ route("admin.book-now.store-customer") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ first_name: firstName, last_name: lastName, phone: phone, email: email || null })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            const ddEl = document.getElementById('dd-customer');
            const item = document.createElement('div');
            item.className = 'dd-item'; item.dataset.value = res.customer.id;
            item.dataset.label = res.customer.text; item.innerHTML = res.customer.text;
            ddEl.prepend(item);
            if (typeof window._ssApply_customer === 'function')
                window._ssApply_customer(res.customer.id, res.customer.text, false);
            document.getElementById('customer_id').value = res.customer.id;
            successBox.textContent = '{{ translate("Customer created successfully!") }}';
            successBox.classList.remove('d-none');
            setTimeout(function() {
                ['new_first_name','new_last_name','new_phone','new_email'].forEach(function(id) { document.getElementById(id).value = ''; });
                successBox.classList.add('d-none');
                bootstrap.Modal.getInstance(document.getElementById('addCustomerModal')).hide();
            }, 900);
        }
    })
    .catch(function() {
        errorBox.textContent = '{{ translate("Something went wrong. Please try again.") }}';
        errorBox.classList.remove('d-none');
    })
    .finally(function() {
        btn.disabled = false; btnText.textContent = '{{ translate("Save Customer") }}';
        spinner.classList.remove('d-none'); spinner.classList.add('d-none');
    });
});

document.getElementById('addCustomerModal').addEventListener('hidden.bs.modal', function() {
    ['customer-form-error','customer-form-success'].forEach(function(id) {
        const el = document.getElementById(id); el.classList.add('d-none'); el.innerHTML = '';
    });
    ['new_first_name','new_last_name','new_phone','new_email'].forEach(function(id) { document.getElementById(id).value = ''; });
});
</script>
@endpush
