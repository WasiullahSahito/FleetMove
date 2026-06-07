<x-master-layout :assets="$assets ?? []">

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');

:root {
    --accent:       #4788ff;
    --accent-light: #e8f0ff;
    --pin-start:    #22c55e;
    --pin-end:      #ef4444;
    --radius:       14px;
    --shadow:       0 4px 24px rgba(0,0,0,.08);
    --shadow-lg:    0 8px 40px rgba(71,136,255,.15);
}

/* ── card ── */
.dispatch-wrap { font-family: 'DM Sans', sans-serif; }
.dispatch-card { border-radius: var(--radius) !important; box-shadow: var(--shadow) !important; border: none !important; overflow: hidden; }
.dispatch-card .card-header {
    background: linear-gradient(135deg,#1a2340,#2b3a6e) !important;
    border: none !important; padding: 18px 24px !important;
}
.dispatch-card .card-header h4 { color:#fff !important; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:1.15rem; margin:0; }

/* ── map ── */
#dispatch-map-wrap { position:relative; border-radius:12px; overflow:hidden; box-shadow:var(--shadow-lg); }
#dispatch-map     { width:100%; height:420px; display:block; }

#map-mode-pill {
    position:absolute; top:14px; left:50%; transform:translateX(-50%);
    background:rgba(255,255,255,.97); border-radius:999px;
    box-shadow:0 3px 16px rgba(0,0,0,.18); display:flex; gap:4px;
    padding:5px 6px; z-index:10; backdrop-filter:blur(6px);
}
.pill-btn {
    border:none; background:transparent; border-radius:999px;
    padding:6px 18px; font-family:'DM Sans',sans-serif; font-size:13px;
    font-weight:600; cursor:pointer; color:#64748b; transition:all .2s;
    display:flex; align-items:center; gap:6px; white-space:nowrap;
}
.pill-btn.active-start { background:var(--pin-start); color:#fff; box-shadow:0 2px 10px rgba(34,197,94,.35); }
.pill-btn.active-end   { background:var(--pin-end);   color:#fff; box-shadow:0 2px 10px rgba(239,68,68,.35); }
.pill-dot { width:9px; height:9px; border-radius:50%; display:inline-block; }
.dot-start { background:var(--pin-start); } .dot-end { background:var(--pin-end); }

#map-instruction {
    position:absolute; bottom:14px; left:50%; transform:translateX(-50%);
    background:rgba(26,35,64,.88); color:#e2e8f0; font-size:12.5px;
    font-family:'DM Sans',sans-serif; padding:8px 20px; border-radius:999px;
    white-space:nowrap; pointer-events:none; z-index:10; backdrop-filter:blur(4px);
}

/* ── address summary cards ── */
.addr-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin:14px 0 4px; }
.addr-card {
    border-radius:10px; padding:11px 14px; display:flex; align-items:flex-start;
    gap:10px; border:1.5px solid #e5e7eb; background:#fafafa; transition:border-color .2s;
    cursor:pointer; min-height:56px;
}
.addr-card.has-val { border-color:#c3d5ff; background:var(--accent-light); }
.addr-card.is-start.has-val { border-color:#bbf7d0; background:#f0fdf4; }
.addr-card.is-end.has-val   { border-color:#fecdd3; background:#fff1f2; }
.addr-icon { flex-shrink:0; width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:16px; margin-top:1px; }
.addr-icon.s { background:#dcfce7; } .addr-icon.e { background:#ffe4e6; }
.addr-lbl { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; line-height:1; margin-bottom:4px; }
.addr-val { font-size:13px; color:#1e293b; line-height:1.35; font-weight:500; word-break:break-word; }
.addr-val.ph { color:#94a3b8; font-weight:400; font-style:italic; }
.coord-badge { font-size:11px; color:#64748b; background:#f1f5f9; border-radius:6px; padding:2px 8px; margin-top:3px; display:inline-block; }

/* ── section title ── */
.sec-title { font-family:'Space Grotesk',sans-serif; font-size:.75rem; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:#94a3b8; margin:20px 0 12px; }

/* ── form inputs ── */
.dispatch-wrap .form-control,
.dispatch-wrap .select2-container--default .select2-selection--single {
    border-radius:9px !important; border:1.5px solid #e5e7eb !important;
    font-family:'DM Sans',sans-serif !important; font-size:13.5px !important;
    height:42px !important; transition:border-color .2s,box-shadow .2s !important;
}
.dispatch-wrap .form-control:focus { border-color:var(--accent) !important; box-shadow:0 0 0 3px rgba(71,136,255,.12) !important; }
.dispatch-wrap label.form-control-label { font-size:12.5px !important; font-weight:600 !important; color:#475569 !important; }

/* pac-container fix — keep Google autocomplete above everything */
.pac-container { z-index: 99999 !important; border-radius: 10px !important; box-shadow: 0 8px 30px rgba(0,0,0,.12) !important; font-family:'DM Sans',sans-serif !important; }

/* ── drop rows ── */
.drop-hdr { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
.drop-hdr h5 { margin:0; font-family:'Space Grotesk',sans-serif; font-weight:600; font-size:.95rem; color:#1e293b; }
.btn-add-drop { border:1.5px dashed var(--accent); color:var(--accent); background:var(--accent-light); border-radius:8px; font-size:12.5px; font-weight:600; padding:5px 14px; cursor:pointer; transition:all .2s; font-family:'DM Sans',sans-serif; }
.btn-add-drop:hover { background:var(--accent); color:#fff; }
.drop-item { display:flex; align-items:center; gap:8px; margin-bottom:8px; }
.drop-bullet { flex-shrink:0; width:22px; height:22px; border-radius:50%; background:#e0e7ff; color:var(--accent); font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; }
.remove-drop-btn { flex-shrink:0; background:none; border:none; color:#ef4444; cursor:pointer; padding:4px 6px; border-radius:6px; transition:background .15s; font-size:15px; }
.remove-drop-btn:hover { background:#fee2e2; }

/* ── save btn ── */
.btn-dispatch-save {
    background:linear-gradient(135deg,#4788ff,#2563eb) !important; color:#fff !important;
    border:none !important; border-radius:10px !important;
    font-family:'Space Grotesk',sans-serif !important; font-weight:600 !important;
    font-size:14px !important; padding:10px 32px !important;
    box-shadow:0 4px 16px rgba(71,136,255,.35) !important; transition:transform .15s !important;
}
.btn-dispatch-save:hover { transform:translateY(-1px) !important; }

.refresh-icon { color:var(--accent); cursor:pointer; font-size:15px; transition:transform .4s; }
.refresh-icon:hover { transform:rotate(180deg); }

/* ── alert toast ── */
#no-driver-alert { display:none; border-radius:10px; font-size:13px; }
</style>

<div class="dispatch-wrap">
    <?php $id = $id ?? null; ?>
    @if(isset($id))
        {!! Form::model($data, ['route'=>['dispatch.update',$id],'method'=>'patch','data-toggle'=>'validator']) !!}
    @else
        {!! Form::open(['route'=>['dispatch.store'],'method'=>'post','data-toggle'=>'validator']) !!}
    @endif

    {{-- Hidden coords --}}
    <input type="hidden" id="start_latitude"  name="start_latitude">
    <input type="hidden" id="start_longitude" name="start_longitude">
    <input type="hidden" id="end_latitude"    name="end_latitude">
    <input type="hidden" id="end_longitude"   name="end_longitude">
    <input type="hidden" id="pick_lat"        name="pick_lat">
    <input type="hidden" id="pick_lng"        name="pick_lng">
    <input type="hidden" id="drop_lat"        name="drop_lat">
    <input type="hidden" id="drop_lng"        name="drop_lng">

    <div class="row">
        <div class="col-lg-12 mt-3">
            <div class="card dispatch-card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">🚖 {{ $pageTitle }}</h4>
                    <?php echo $button ?? ''; ?>
                </div>

                <div class="card-body p-4">

                    {{-- ═══ MAP ═══ --}}
                    <p class="sec-title">📍 Set Pickup &amp; Drop-off on Map</p>

                    <div id="dispatch-map-wrap">
                        <div id="dispatch-map"></div>
                        <div id="map-mode-pill">
                            <button type="button" class="pill-btn active-start" id="btn-set-start">
                                <span class="pill-dot dot-start"></span> Set Pickup
                            </button>
                            <button type="button" class="pill-btn" id="btn-set-end">
                                <span class="pill-dot dot-end"></span> Set Drop-off
                            </button>
                        </div>
                        <div id="map-instruction">Click on the map to set <strong>Pickup</strong> location</div>
                    </div>

                    <div class="addr-row">
                        <div class="addr-card is-start" id="card-start" onclick="activateMode('start')">
                            <div class="addr-icon s">🟢</div>
                            <div style="flex:1;min-width:0">
                                <div class="addr-lbl">Pickup</div>
                                <div class="addr-val ph" id="disp-start">Click map or type in field below</div>
                                <span class="coord-badge" id="coord-start" style="display:none"></span>
                            </div>
                        </div>
                        <div class="addr-card is-end" id="card-end" onclick="activateMode('end')">
                            <div class="addr-icon e">🔴</div>
                            <div style="flex:1;min-width:0">
                                <div class="addr-lbl">Drop-off</div>
                                <div class="addr-val ph" id="disp-end">Click map or type in field below</div>
                                <span class="coord-badge" id="coord-end" style="display:none"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ BOOKING DETAILS ═══ --}}
                    <p class="sec-title">📋 Booking Details</p>

                    <div class="row">
                        {{-- Rider --}}
                        <div class="form-group col-md-4">
                            {{ Form::label('rider_id',__('message.rider').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                            {{ Form::select('rider_id',
                                isset($id)?[optional($data->rider)->id=>optional($data->rider)->display_name]:[],
                                old('rider_id'),
                                ['data-ajax--url'=>route('ajax-list',['type'=>'rider']),'class'=>'form-control select2js','data-placeholder'=>__('message.select_field',['name'=>__('message.rider')]),'required'])
                            }}
                        </div>

                        {{-- Start Address — Google Places Autocomplete --}}
                        <div class="form-group col-md-4">
                            {{ Form::label('start_address',__('message.start_address').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                            <input type="text" id="start_address" name="start_address"
                                   class="form-control"
                                   placeholder="{{ __('message.start_address') }}"
                                   autocomplete="off" required
                                   value="{{ old('start_address', isset($id) ? optional($data)->start_address : '') }}">
                        </div>

                        {{-- End Address — Google Places Autocomplete --}}
                        <div class="form-group col-md-4">
                            {{ Form::label('end_address',__('message.end_address').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                            <input type="text" id="end_address" name="end_address"
                                   class="form-control"
                                   placeholder="{{ __('message.end_address') }}"
                                   autocomplete="off" required
                                   value="{{ old('end_address', isset($id) ? optional($data)->end_address : '') }}">
                        </div>

                        {{-- Service --}}
                        <div class="form-group col-md-4">
                            {{ Form::label('service_id',__('message.service').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                            <a class="float-right refresh-icon serviceList" href="javascript:void(0)" title="Refresh"><i class="ri-refresh-line"></i></a>
                            {{ Form::select('service_id',
                                isset($id)?[optional($data->service)->id=>optional($data->service)->display_name]:[],
                                old('service_id'),
                                ['class'=>'select2js form-group service','id'=>'service_id','required','data-placeholder'=>__('message.select_name',['select'=>__('message.service')])])
                            }}
                        </div>

                        {{-- Driver --}}
                        <div class="form-group col-md-4">
                            {{ Form::label('driver_id',__('message.driver'),['class'=>'form-control-label'],false) }}
                            <a class="float-right refresh-icon driverList" href="javascript:void(0)" title="Refresh"><i class="ri-refresh-line"></i></a>
                            {{ Form::select('driver_id',
                                isset($id)?[optional($data->driver)->id=>optional($data->driver)->display_name]:[],
                                old('driver_id'),
                                ['class'=>'select2js form-group driver','id'=>'driver_id','data-placeholder'=>__('message.select_name',['select'=>__('message.driver')])])
                            }}
                        </div>

                        {{-- Schedule --}}
                        <div class="form-group col-md-4">
                            {{ Form::label('schedule_datetime',__('message.schedule_datetime'),['class'=>'form-control-label']) }}
                            {{ Form::text('schedule_datetime',old('schedule_datetime'),['placeholder'=>__('message.schedule_datetime'),'class'=>'form-control datetimepicker']) }}
                        </div>
                    </div>

                    {{-- Driver not available notice --}}
                    <div class="alert alert-warning" id="no-driver-alert">
                        ⚠️ <strong>No drivers found for this service.</strong>
                        Make sure at least one driver has <strong>service_id</strong> matching the selected service,
                        <strong>status = active</strong>, and is registered in the system.
                        You can save the ride without a driver — it will be auto-assigned later.
                    </div>

                    <hr class="my-3">

                    {{-- ═══ DROP ADDRESSES ═══ --}}
                    <div class="drop-hdr">
                        <h5>{{ __('message.drop_address') }}</h5>
                        <button type="button" id="add_button" class="btn-add-drop">+ {{ __('message.add') }}</button>
                    </div>

                    <div id="dropAddress" class="clone-master">
                        <div class="drop-item clone-item" id="row_0" row="0">
                            <div class="drop-bullet">1</div>
                            <input type="text" name="search_drop_location[]" value=""
                                   class="form-control drop_location" id="search_drop_location_0" row="0"
                                   placeholder="{{ __('message.drop_address') }} 1" autocomplete="off">
                            <input type="hidden" name="drop_location[]" value=""
                                   class="hidden_drop_location" id="drop_location_0" row="0">
                            <button type="button" class="remove-drop-btn removebtn" row="0" id="remove_0">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="mt-4 mb-3">
                    <div class="text-right">
                        <button type="submit" class="btn btn-dispatch-save">{{ __('message.save') }}</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</div>

@section('bottom_script')

{{-- Google Maps JS with Places library --}}
<script>
window.__mapsReady = false;
function initDispatchMap() { window.__mapsReady = true; _setupMap(); }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places&callback=initDispatchMap" async defer></script>

<script>
$(function () {

    /* ═══════════════════════════════════════════════════════════
       STATE
    ═══════════════════════════════════════════════════════════ */
    var map, startMarker, endMarker, directionsRenderer;
    var mapMode = 'start';
    var state   = { start:{lat:null,lng:null,address:''}, end:{lat:null,lng:null,address:''} };

    /* ═══════════════════════════════════════════════════════════
       MAP SETUP
    ═══════════════════════════════════════════════════════════ */
    window._setupMap = function () {
        // Default center = middle of Hyderabad region polygon
        var center = { lat: 25.380, lng: 68.365 };

        map = new google.maps.Map(document.getElementById('dispatch-map'), {
            center: center, zoom: 13,
            mapTypeControl: false, streetViewControl: false,
            zoomControlOptions:      { position: google.maps.ControlPosition.RIGHT_BOTTOM },
            fullscreenControlOptions:{ position: google.maps.ControlPosition.RIGHT_BOTTOM },
            styles: [
                { featureType:'poi.business', stylers:[{visibility:'off'}] },
                { featureType:'transit', elementType:'labels.icon', stylers:[{visibility:'off'}] }
            ]
        });

        directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions:{ strokeColor:'#4788ff', strokeWeight:4, strokeOpacity:.75 }
        });
        directionsRenderer.setMap(map);

        map.addListener('click', function(e){
            handleMapClick(e.latLng.lat(), e.latLng.lng());
        });

        // Bind Google Places Autocomplete to the two text inputs
        bindGoogleAutocomplete('start_address', 'start');
        bindGoogleAutocomplete('end_address',   'end');

        // Bind drop-row autocompletes
        bindDropRowAutocomplete(document.getElementById('search_drop_location_0'), 0);

        @if(isset($id))
        var sLat='{{ optional($data)->start_latitude }}', sLng='{{ optional($data)->start_longitude }}';
        var eLat='{{ optional($data)->end_latitude }}',   eLng='{{ optional($data)->end_longitude }}';
        if(sLat && sLng) setLocation('start',parseFloat(sLat),parseFloat(sLng),'{{ optional($data)->start_address }}',false);
        if(eLat && eLng) setLocation('end',  parseFloat(eLat),parseFloat(eLng),'{{ optional($data)->end_address }}',  false);
        @endif
    };
    if(window.__mapsReady) _setupMap();

    /* ═══════════════════════════════════════════════════════════
       GOOGLE PLACES AUTOCOMPLETE on text inputs
       This uses the native Google widget — no custom AJAX needed
       for start/end address. Type any address and Google suggests.
    ═══════════════════════════════════════════════════════════ */
    function bindGoogleAutocomplete(inputId, type) {
        var input = document.getElementById(inputId);
        if (!input || !window.google) return;

        var autocomplete = new google.maps.places.Autocomplete(input, {
            // No country restriction — works everywhere
            fields: ['formatted_address', 'geometry', 'name']
        });

        // Prevent form submit on Enter inside autocomplete
        input.addEventListener('keydown', function(e){
            if(e.key === 'Enter') e.preventDefault();
        });

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;

            var lat  = place.geometry.location.lat();
            var lng  = place.geometry.location.lng();
            var addr = place.formatted_address || place.name || input.value;

            setLocation(type, lat, lng, addr, true);
        });
    }

    /* Drop rows use the same Google Places widget */
    function bindDropRowAutocomplete(inputEl, row) {
        if (!inputEl || !window.google) return;
        var ac = new google.maps.places.Autocomplete(inputEl, {
            fields: ['formatted_address', 'geometry', 'name']
        });
        inputEl.addEventListener('keydown', function(e){ if(e.key==='Enter') e.preventDefault(); });
        ac.addListener('place_changed', function(){
            var place = ac.getPlace();
            if (!place.geometry) return;
            var lat  = place.geometry.location.lat();
            var lng  = place.geometry.location.lng();
            var addr = place.formatted_address || inputEl.value;
            inputEl.value = addr;
            document.getElementById('drop_location_' + row).value = JSON.stringify({
                address: addr, latitude: lat, longitude: lng
            });
        });
    }

    /* ═══════════════════════════════════════════════════════════
       MAP MODE
    ═══════════════════════════════════════════════════════════ */
    window.activateMode = function(mode){
        mapMode = mode;
        $('#btn-set-start').removeClass('active-start active-end');
        $('#btn-set-end').removeClass('active-start active-end');
        if(mode==='start'){
            $('#btn-set-start').addClass('active-start');
            $('#map-instruction').html('Click on the map to set <strong>Pickup</strong> location');
        } else {
            $('#btn-set-end').addClass('active-end');
            $('#map-instruction').html('Click on the map to set <strong>Drop-off</strong> location');
        }
    };
    $('#btn-set-start').on('click', function(){ activateMode('start'); });
    $('#btn-set-end').on('click',   function(){ activateMode('end');   });

    /* ═══════════════════════════════════════════════════════════
       MAP CLICK → reverse geocode
    ═══════════════════════════════════════════════════════════ */
    function handleMapClick(lat, lng){
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location:{lat:lat,lng:lng} }, function(results, status){
            var addr = (status==='OK' && results[0]) ? results[0].formatted_address : lat.toFixed(6)+', '+lng.toFixed(6);
            setLocation(mapMode, lat, lng, addr, true);
            if(mapMode==='start' && !state.end.lat) activateMode('end');
        });
    }

    /* ═══════════════════════════════════════════════════════════
       SET LOCATION  (shared by map + autocomplete)
    ═══════════════════════════════════════════════════════════ */
    function setLocation(type, lat, lng, address, panMap){
        state[type] = {lat:lat, lng:lng, address:address};

        if(type==='start'){
            $('#start_latitude,#pick_lat').val(lat);
            $('#start_longitude,#pick_lng').val(lng);
            $('#start_address').val(address);
            placeMarker('start', lat, lng);
            updateCard('start', address, lat, lng);
            loadServices(lat, lng);
        } else {
            $('#end_latitude,#drop_lat').val(lat);
            $('#end_longitude,#drop_lng').val(lng);
            $('#end_address').val(address);
            placeMarker('end', lat, lng);
            updateCard('end', address, lat, lng);
        }
        if(panMap && map) map.panTo({lat:lat,lng:lng});
        if(state.start.lat && state.end.lat) drawRoute();
    }

    /* ═══════════════════════════════════════════════════════════
       MARKERS
    ═══════════════════════════════════════════════════════════ */
    function placeMarker(type, lat, lng){
        if(!map) return;
        var pos = {lat:lat, lng:lng};
        var iconUrl = (type==='start')
            ? 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
            : 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
        var size = new google.maps.Size(38,38);

        if(type==='start'){
            if(startMarker) startMarker.setMap(null);
            startMarker = new google.maps.Marker({
                position:pos, map:map, title:'Pickup',
                icon:{url:iconUrl, scaledSize:size},
                animation:google.maps.Animation.DROP, zIndex:10
            });
            startMarker.addListener('click', function(){ activateMode('start'); });
        } else {
            if(endMarker) endMarker.setMap(null);
            endMarker = new google.maps.Marker({
                position:pos, map:map, title:'Drop-off',
                icon:{url:iconUrl, scaledSize:size},
                animation:google.maps.Animation.DROP, zIndex:9
            });
            endMarker.addListener('click', function(){ activateMode('end'); });
        }
    }

    /* ═══════════════════════════════════════════════════════════
       ROUTE
    ═══════════════════════════════════════════════════════════ */
    function drawRoute(){
        if(!map || !state.start.lat || !state.end.lat) return;
        new google.maps.DirectionsService().route({
            origin:      {lat:state.start.lat, lng:state.start.lng},
            destination: {lat:state.end.lat,   lng:state.end.lng},
            travelMode:  google.maps.TravelMode.DRIVING
        }, function(result, status){
            if(status==='OK'){
                directionsRenderer.setDirections(result);
                var b = new google.maps.LatLngBounds();
                b.extend({lat:state.start.lat,lng:state.start.lng});
                b.extend({lat:state.end.lat,  lng:state.end.lng});
                map.fitBounds(b, {top:60,bottom:60,left:40,right:40});
            }
        });
    }

    /* ═══════════════════════════════════════════════════════════
       ADDRESS CARDS
    ═══════════════════════════════════════════════════════════ */
    function updateCard(type, address, lat, lng){
        $('#disp-'+type).text(address).removeClass('ph');
        $('#card-'+type).addClass('has-val');
        $('#coord-'+type).text(lat.toFixed(5)+', '+lng.toFixed(5)).show();
    }

    /* ═══════════════════════════════════════════════════════════
       SERVICE LOADER
       Calls service_for_ride with lat/lng for spatial region filter.
       Also provides a FALLBACK that loads ALL active services if the
       coordinate is outside the region polygon (useful for testing).
    ═══════════════════════════════════════════════════════════ */
    function loadServices(lat, lng){
        var url = "{{ route('ajax-list',['type'=>'service_for_ride']) }}"
                + "&latitude=" + lat + "&longitude=" + lng;
        url = url.replaceAll('amp;','');

        $.ajax({
            url: url,
            success: function(result){
                var items = result.results || [];

                // Fallback: if spatial query returns nothing, load all active services
                if(items.length === 0){
                    loadAllServices(lat, lng);
                    return;
                }

                renderServiceSelect(items);
            },
            error: function(){ loadAllServices(lat, lng); }
        });
    }

    /* Fallback — load all services (ignores region boundary) */
    function loadAllServices(lat, lng){
        var url = "{{ route('ajax-list',['type'=>'service']) }}";
        $.ajax({
            url: url,
            success: function(result){
                var items = result.results || [];
                renderServiceSelect(items);
            }
        });
    }

    function renderServiceSelect(items){
        if($('.service').data('select2')) $('.service').select2('destroy');
        $('.service').html('').select2({
            width:'100%',
            placeholder:"{{ __('message.select_name',['select'=>__('message.service')]) }}",
            data: items
        });
        if(items.length === 1){
            $('.service').val(items[0].id).trigger('change');
        } else {
            $('.service').val(null).trigger('change');
        }
    }

    /* ═══════════════════════════════════════════════════════════
       DRIVER LOADER
       The driver query requires:
         1. Driver is_online = 1
         2. Driver is_available = 1
         3. Driver has latitude & longitude set (from mobile app)
         4. Driver is within DISTANCE_RADIUS (default 50 km)
         5. Driver has service_id matching selected service

       Two-step loading:
         Step 1 → GPS-located nearby drivers (driver_for_ride)
         Step 2 → If empty, fallback to ALL active drivers for
                  this service regardless of GPS / online status
                  (admin dispatch needs to assign any driver)
    ═══════════════════════════════════════════════════════════ */
    function loadDrivers(serviceId){
        var lat = $('#start_latitude').val() || '25.3960';
        var lng = $('#start_longitude').val() || '68.3578';
        var url = "{{ route('ajax-list',['type'=>'driver_for_ride']) }}"
                + "&service_id=" + serviceId
                + "&latitude="   + lat
                + "&longitude="  + lng;
        url = url.replaceAll('amp;','');

        $.ajax({
            url: url,
            success: function(result){
                var items = result.results || [];

                if (items.length === 0) {
                    // ── FALLBACK: load ALL active drivers for this service ──
                    loadAllDriversForService(serviceId);
                } else {
                    renderDriverSelect(items, false);
                }
            },
            error: function(){
                loadAllDriversForService(serviceId);
            }
        });
    }

    /* Fallback: all active drivers with this service_id, no GPS/online filter */
    function loadAllDriversForService(serviceId){
        var url = "{{ route('ajax-list',['type'=>'driver']) }}"
                + "&service_id=" + serviceId
                + "&status=active";
        url = url.replaceAll('amp;','');

        $.ajax({
            url: url,
            success: function(result){
                var items = result.results || [];
                renderDriverSelect(items, items.length === 0);
            },
            error: function(){
                renderDriverSelect([], true);
            }
        });
    }

    function renderDriverSelect(items, showWarning){
        if($('.driver').data('select2')) $('.driver').select2('destroy');
        $('.driver').html('').select2({
            width:'100%',
            placeholder:"{{ __('message.select_name',['select'=>__('message.driver')]) }}",
            data: items
        });
        $('.driver').val(null).trigger('change');

        if(showWarning){
            $('#no-driver-alert').fadeIn(200);
        } else {
            $('#no-driver-alert').hide();
        }
    }

    /* ═══════════════════════════════════════════════════════════
       EVENTS
    ═══════════════════════════════════════════════════════════ */
    $(document).on('change', '.service', function(){
        var sid = $(this).val();
        if(sid) loadDrivers(sid); else $('.driver').html('');
    });

    $('.serviceList').on('click', function(){
        var lat=$('#start_latitude').val(), lng=$('#start_longitude').val();
        if(lat && lng) loadServices(lat, lng);
        else loadAllServices();
    });

    $('.driverList').on('click', function(){
        var sid=$('#service_id').val();
        if(sid) loadDrivers(sid); else alert('Please select a Service first.');
    });

    /* ═══════════════════════════════════════════════════════════
       DROP ADDRESS ROWS
    ═══════════════════════════════════════════════════════════ */
    function resetDropNumbers(){
        $('#dropAddress .clone-item').each(function(i){
            $(this).find('.drop-bullet').text(i+1);
            $(this).find('input.drop_location').attr('placeholder',"{{ __('message.drop_address') }} "+(i+1));
        });
    }

    $('#add_button').click(function(){
        var last     = parseInt($(".clone-master .clone-item:last").attr('row')) || 0;
        var newCount = last + 1;
        var clone    = $(".clone-master .clone-item:first").clone();

        clone.attr('id','row_'+newCount).attr('row',newCount);
        clone.find('input.drop_location')
             .attr('id','search_drop_location_'+newCount).attr('row',newCount).val('');
        clone.find('input.hidden_drop_location')
             .attr('id','drop_location_'+newCount).attr('row',newCount).val('');
        clone.find('.removebtn').attr('id','remove_'+newCount).attr('row',newCount);
        $(".clone-master").append(clone);

        // Bind Google Places to new row (after DOM insert)
        setTimeout(function(){
            bindDropRowAutocomplete(document.getElementById('search_drop_location_'+newCount), newCount);
        }, 100);
        resetDropNumbers();
    });

    $(document).on('click','.removebtn',function(){
        var row = $(this).attr('row');
        if($('#dropAddress .clone-item').length===1){ $('#add_button').trigger('click'); }
        if(!confirm("{{ __('message.delete_msg') }}")) return false;
        $('#row_'+row).remove();
        resetDropNumbers();
    });

}); // end $(function)
</script>
@endsection

</x-master-layout>