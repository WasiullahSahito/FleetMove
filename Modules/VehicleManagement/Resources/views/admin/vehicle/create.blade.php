@section('title', translate('add_New_Vehicle'))

@extends('adminmodule::layouts.master')

@push('css_or_js')
@endpush

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3 justify-content-between mb-4">
                <h2 class="fs-22 text-capitalize">{{ translate('add_New_Vehicle') }}</h2>
            </div>
            <form id="vehicle_form" action="{{ route('admin.vehicle.store') }}" enctype="multipart/form-data"
                  method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-primary text-uppercase mb-4">{{ translate('vehicle_information') }}</h5>

                                <div class="row align-items-end">

                                    {{-- Vehicle Brand --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4 text-capitalize">
                                            <label for="brand_id" class="mb-2">
                                                {{ translate('vehicle_brand') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <div class="flex-grow-1">
                                                    <select class="js-select-ajax cmn_focus" name="brand_id" id="brand_id"
                                                            data-placeholder="{{ translate('select_brand') }}"
                                                            onchange="ajax_models('{{ url('/') }}/admin/vehicle/attribute-setup/model/ajax-models/'+this.value)"
                                                            required tabindex="1">
                                                    </select>
                                                </div>
                                                <button type="button"
                                                        class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center"
                                                        style="width:36px;height:36px;flex-shrink:0;margin-top:2px;"
                                                        data-bs-toggle="modal" data-bs-target="#addBrandModal"
                                                        title="{{ translate('add_new_brand') }}">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Vehicle Model --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4" id="model-selector">
                                            <label for="model_id" class="mb-2">
                                                {{ translate('vehicle_model') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <div class="flex-grow-1">
                                                    <select class="js-select-ajax cmn_focus theme-input-style w-100 form-control"
                                                            name="model_id" id="model_id"
                                                            data-placeholder="{{ translate('please_select_vehicle_model') }}"
                                                            required tabindex="2">
                                                    </select>
                                                </div>
                                                <button type="button"
                                                        class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center"
                                                        style="width:36px;height:36px;flex-shrink:0;margin-top:2px;"
                                                        data-bs-toggle="modal" data-bs-target="#addModelModal"
                                                        title="{{ translate('add_new_model') }}">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Vehicle Category --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4 text-capitalize">
                                            <label for="vehicle_category" class="mb-2">
                                                {{ translate('vehicle_category') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select id="vehicle_category" class="js-select-ajax cmn_focus" name="category_id"
                                                    data-placeholder="{{ translate('select_vehicle_category') }}"
                                                    required tabindex="3">
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Licence Plate Number --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4">
                                            <label for="licence_plate_number" class="mb-2">
                                                {{ translate('licence_plate_number') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" value="{{ old('licence_plate_number') }}"
                                                   id="licence_plate_number" class="form-control"
                                                   name="licence_plate_number"
                                                   placeholder="Ex: DB-3212" required tabindex="4">
                                        </div>
                                    </div>

                                    {{-- Licence Expire Date --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4">
                                            <label for="licence_expire_date" class="mb-2">
                                                {{ translate('licence_expire_date') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" id="licence_expire_date" name="licence_expire_date"
                                                   class="form-control" required tabindex="5">
                                        </div>
                                    </div>

                                    {{-- VIN Number --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4">
                                            <label for="vin_number" class="mb-2">
                                                {{ strtoupper(translate('vin')) }} {{ translate('number') }}
                                            </label>
                                            <input type="text" value="{{ old('vin_number') }}" id="vin_number"
                                                   class="form-control" name="vin_number"
                                                   placeholder="Ex: 1HGBH41JXMN109186" tabindex="6">
                                        </div>
                                    </div>

                                    {{-- Transmission --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4">
                                            <label for="transmission" class="mb-2">{{ translate('transmission') }}</label>
                                            <input type="text" value="{{ old('transmission') }}" id="transmission"
                                                   class="form-control" name="transmission" placeholder="Ex: AMT" tabindex="7">
                                        </div>
                                    </div>

                                    {{-- Parcel Weight Capacity --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4">
                                            <label for="parcel_weight_capacity" class="mb-2">
                                                {{ translate('parcel_weight_capacity') }}
                                                ({{ businessConfig(key: 'parcel_weight_unit')?->value ?? 'kg' }})
                                            </label>
                                            <input type="number" maxlength="99999999"
                                                   value="{{ old('parcel_weight_capacity') }}"
                                                   id="parcel_weight_capacity"
                                                   class="form-control" name="parcel_weight_capacity"
                                                   placeholder="Ex: 10" tabindex="8">
                                        </div>
                                    </div>

                                    {{-- Fuel Type --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4 text-capitalize">
                                            <label for="fuel_type" class="mb-2">
                                                {{ translate('fuel_type') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="js-select cmn_focus" id="fuel_type" name="fuel_type" required tabindex="9">
                                                <option value="" selected disabled>{{ translate('select_fuel_type') }}</option>
                                                @foreach(FUEL_TYPES as $key => $value)
                                                    <option value="{{ $key }}">{{ translate($key) }}</option>
                                                @endforeach
                                                <option value="ev">{{ translate('EV') }}</option>
                                                <option value="hybrid">{{ translate('Hybrid') }}</option>
                                                <option value="plug_in_hybrid">{{ translate('Plug-in Hybrid') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Ownership --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4 text-capitalize">
                                            <label for="ownership" class="mb-2">
                                                {{ translate('ownership') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="js-select required cmn_focus" id="ownership" name="ownership" required tabindex="10">
                                                <option value="" selected disabled>{{ translate('select_owner') }}</option>
                                                <option value="admin">{{ translate('admin') }}</option>
                                                <option value="driver">{{ translate('driver') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Driver --}}
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="mb-4 text-capitalize">
                                            <label for="driver" class="mb-2">
                                                {{ translate('driver') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select required class="js-select-driver required cmn_focus" id="driver"
                                                    name="driver_id" tabindex="11">
                                                <option value="" selected disabled>{{ translate('select_driver') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Upload Documents --}}
                    <div class="col-12">
                        <div class="card mt-3">
                            <div class="card-body">
                                <h5 class="mb-4 text-capitalize">{{ translate('upload_documents') }}</h5>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex flex-wrap gap-3" id="selected-files-container1"></div>
                                    <div id="input-data"></div>
                                    <div class="upload-file cmn_focus rounded-10 file__input" id="file__input">
                                        <input type="file" class="upload-file__input2" multiple="multiple"
                                               accept="{{ IMAGE_ACCEPTED_EXTENSIONS }}, {{ FILE_ACCEPTED_EXTENSIONS }}"
                                               tabindex="12">
                                        <div class="upload-file__img2">
                                            <div class="upload-box rounded media gap-4 align-items-center p-4 px-lg-5">
                                                <i class="bi bi-cloud-arrow-up-fill fs-20"></i>
                                                <div class="media-body">
                                                    <p class="text-muted mb-2 fs-12">{{ translate('upload') }}</p>
                                                    <h6 class="fs-12 text-capitalize">{{ translate('file_or_image') }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button class="btn btn-primary cmn_focus" type="submit" tabindex="13">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- End Main Content -->


    {{-- ========================================================
         ADD BRAND MODAL
    ========================================================= --}}
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="addBrandModalLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ translate('add_new_brand') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- Left Column: Brand Name + Short Description --}}
                        <div class="col-md-8">
                            {{-- Brand Name --}}
                            <div class="mb-3">
                                <label for="new_brand_name" class="form-label fw-semibold">
                                    {{ translate('brand_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="new_brand_name" class="form-control"
                                       placeholder="Ex: Brand" maxlength="100">
                                <div id="brand_name_error" class="text-danger small mt-1 d-none"></div>
                            </div>

                            {{-- Short Description --}}
                            <div class="mb-3">
                                <label for="new_brand_description" class="form-label fw-semibold">
                                    {{ translate('short_description') }}
                                </label>
                                <textarea id="new_brand_description" class="form-control" rows="5"
                                          placeholder="Ex: Description" maxlength="800"></textarea>
                                <div class="d-flex justify-content-end">
                                    <small id="brand_desc_count" class="text-muted">0/800</small>
                                </div>
                                <div id="brand_desc_error" class="text-danger small mt-1 d-none"></div>
                            </div>
                        </div>

                        {{-- Right Column: Brand Logo --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('brand_logo') }}</label>
                            <div class="border rounded text-center p-3" style="min-height:160px;cursor:pointer;"
                                 onclick="document.getElementById('new_brand_image').click()">
                                <div id="brand_upload_placeholder">
                                    <i class="bi bi-file-earmark fs-30 text-muted"></i>
                                    <p class="text-primary small mb-0 mt-2">{{ translate('click_to_upload') }}</p>
                                    <p class="text-muted small mb-0">{{ translate('or_drag_and_drop') }}</p>
                                    <p class="text-muted" style="font-size:11px;margin-top:8px;">
                                        File Format - .png, Image Size - Maximum 2MB
                                    </p>
                                </div>
                                <div id="brand_image_preview" class="d-none">
                                    <img id="brand_preview_img" src="#" alt="preview"
                                         style="max-height:120px;max-width:100%;border-radius:8px;">
                                </div>
                            </div>
                            <input type="file" id="new_brand_image" class="d-none"
                                   accept=".png,.jpg,.jpeg,.webp">
                            <div id="brand_image_error" class="text-danger small mt-1 d-none"></div>
                        </div>
                    </div>

                    {{-- General error --}}
                    <div id="brand_general_error" class="alert alert-danger d-none py-2 mt-2"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ translate('cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="saveBrandBtn">
                        <span id="brandBtnText">{{ translate('submit') }}</span>
                        <span id="brandBtnSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                            {{ translate('saving') }}...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>


    {{-- ========================================================
         ADD MODEL MODAL
    ========================================================= --}}
    <div class="modal fade" id="addModelModal" tabindex="-1" aria-labelledby="addModelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="addModelModalLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ translate('add_new_model') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- Left/Main Column --}}
                        <div class="col-md-8">
                            <div class="row">
                                {{-- Model Name --}}
                                <div class="col-sm-6 mb-3">
                                    <label for="new_model_name" class="form-label fw-semibold">
                                        {{ translate('model_name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="new_model_name" class="form-control"
                                           placeholder="Ex: Model" maxlength="100">
                                    <div id="model_name_error" class="text-danger small mt-1 d-none"></div>
                                </div>

                                {{-- Brand Name --}}
                                <div class="col-sm-6 mb-3">
                                    <label for="model_brand_id" class="form-label fw-semibold">
                                        {{ translate('brand_name') }} <span class="text-danger">*</span>
                                    </label>
                                    <select id="model_brand_id" class="form-control">
                                        <option value="" disabled selected>{{ translate('select_brand') }}</option>
                                    </select>
                                    <div id="model_brand_error" class="text-danger small mt-1 d-none"></div>
                                </div>

                                {{-- Seat Capacity --}}
                                <div class="col-sm-6 mb-3">
                                    <label for="new_model_seat_capacity" class="form-label fw-semibold">
                                        {{ translate('seat_capacity') }}
                                    </label>
                                    <input type="number" min="0" id="new_model_seat_capacity" class="form-control"
                                           placeholder="Ex: Seat Capacity">
                                </div>

                                {{-- Maximum Weight --}}
                                <div class="col-sm-6 mb-3">
                                    <label for="new_model_max_weight" class="form-label fw-semibold">
                                        {{ translate('maximum_weight') }} (Kg)
                                    </label>
                                    <input type="number" min="0" id="new_model_max_weight" class="form-control"
                                           placeholder="Ex: Maximum Weight">
                                </div>

                                {{-- Hatch Bag Capacity --}}
                                <div class="col-sm-6 mb-3">
                                    <label for="new_model_hatch_bag" class="form-label fw-semibold">
                                        {{ translate('hatch_bag_capacity') }}
                                    </label>
                                    <input type="number" min="0" id="new_model_hatch_bag" class="form-control"
                                           placeholder="Ex: Hatch Bag Capacity">
                                </div>

                                {{-- Engine --}}
                                <div class="col-sm-6 mb-3">
                                    <label for="new_model_engine" class="form-label fw-semibold">
                                        {{ translate('engine') }}
                                    </label>
                                    <input type="text" id="new_model_engine" class="form-control"
                                           placeholder="Ex: Engine" maxlength="100">
                                </div>
                            </div>

                            {{-- Short Description --}}
                            <div class="mb-3">
                                <label for="new_model_description" class="form-label fw-semibold">
                                    {{ translate('short_description') }}
                                </label>
                                <textarea id="new_model_description" class="form-control" rows="4"
                                          placeholder="Ex: Description" maxlength="800"></textarea>
                                <div class="d-flex justify-content-end">
                                    <small id="model_desc_count" class="text-muted">0/800</small>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Model Image --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('model_image') }}</label>
                            <div class="border rounded text-center p-3" style="min-height:160px;cursor:pointer;"
                                 onclick="document.getElementById('new_model_image').click()">
                                <div id="model_upload_placeholder">
                                    <i class="bi bi-file-earmark fs-30 text-muted"></i>
                                    <p class="text-primary small mb-0 mt-2">{{ translate('click_to_upload') }}</p>
                                    <p class="text-muted small mb-0">{{ translate('or_drag_and_drop') }}</p>
                                    <p class="text-muted" style="font-size:11px;margin-top:8px;">
                                        File Format - .png, Image Size - Maximum 2MB
                                    </p>
                                </div>
                                <div id="model_image_preview" class="d-none">
                                    <img id="model_preview_img" src="#" alt="preview"
                                         style="max-height:120px;max-width:100%;border-radius:8px;">
                                </div>
                            </div>
                            <input type="file" id="new_model_image" class="d-none"
                                   accept=".png,.jpg,.jpeg,.webp">
                            <div id="model_image_error" class="text-danger small mt-1 d-none"></div>
                        </div>
                    </div>

                    {{-- General error --}}
                    <div id="model_general_error" class="alert alert-danger d-none py-2 mt-2"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ translate('cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="saveModelBtn">
                        <span id="modelBtnText">
                            <i class="bi bi-check-lg me-1"></i>{{ translate('save_model') }}
                        </span>
                        <span id="modelBtnSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                            {{ translate('saving') }}...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection


@push('script')
    <script src="{{ dynamicAsset('public/assets/admin-module/js/vehicle-management/vehicle/create.js') }}"></script>
    <script>
        "use strict";

        /* -------------------------------------------------------
           Existing: load models when brand changes
        ------------------------------------------------------- */
        function ajax_models(route) {
            $.get({
                url: route,
                dataType: 'json',
                data: {},
                success: function (response) {
                    $('#model-selector').html(response.template);
                }
            });
        }

        /* -------------------------------------------------------
           Existing: Brand select2 (ajax)
        ------------------------------------------------------- */
        $('#brand_id').select2({
            ajax: {
                url: '{{ route('admin.vehicle.attribute-setup.brand.all-brands', parameters: ['status' => 'active']) }}',
                data: function (params) {
                    return { q: params.term, page: params.page };
                },
                processResults: function (data) {
                    return { results: data };
                },
                __port: function (params, success, failure) {
                    var $r = $.ajax(params);
                    $r.then(success);
                    $r.fail(failure);
                    return $r;
                }
            }
        });

        /* -------------------------------------------------------
           Existing: Vehicle category select2 (ajax)
        ------------------------------------------------------- */
        $('#vehicle_category').select2({
            ajax: {
                url: '{{ route('admin.vehicle.attribute-setup.category.all-categories', parameters: ['status' => 'active']) }}',
                data: function (params) {
                    return { q: params.term, page: params.page };
                },
                processResults: function (data) {
                    return { results: data };
                },
                __port: function (params, success, failure) {
                    let $r = $.ajax(params);
                    $r.then(success);
                    $r.fail(failure);
                    return $r;
                }
            }
        });

        /* -------------------------------------------------------
           Existing: Driver select2 (ajax)
        ------------------------------------------------------- */
        let all_driver = 0;
        $('.js-select-driver').select2({
            ajax: {
                url: '{{ route('admin.driver.get-all-ajax-vehicle') }}',
                data: function (params) {
                    return { search: params.term, all_driver: all_driver, page: params.page };
                },
                processResults: function (data) {
                    return { results: data };
                },
                __port: function (params, success, failure) {
                    var $r = $.ajax(params);
                    $r.then(success);
                    $r.fail(failure);
                    return $r;
                }
            }
        });

        /* -------------------------------------------------------
           Existing: Form submit validation
        ------------------------------------------------------- */
        $('#vehicle_form').on('submit', function (event) {
            if ($('#model_id').val() === null) {
                toastr.error('{{ translate('fill_up_vehicle_model') }}');
                event.preventDefault();
            }
            if ($('#fuel_type').val() === null) {
                toastr.error('{{ translate('fill_up_fuel_type') }}');
                event.preventDefault();
            }
            if ($('#ownership').val() === null) {
                toastr.error('{{ translate('fill_up_ownership') }}');
                event.preventDefault();
            }
            if ($('#driver').val() === null) {
                toastr.error('{{ translate('fill_up_driver') }}');
                event.preventDefault();
            }
        });


        /* =======================================================
           NEW: Brand image preview + character counter
        ======================================================= */
        $('#new_brand_image').on('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#brand_preview_img').attr('src', e.target.result);
                    $('#brand_image_preview').removeClass('d-none');
                    $('#brand_upload_placeholder').addClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                $('#brand_image_preview').addClass('d-none');
                $('#brand_upload_placeholder').removeClass('d-none');
            }
        });

        $('#new_brand_description').on('input', function () {
            $('#brand_desc_count').text($(this).val().length + '/800');
        });

        /* -------------------------------------------------------
           NEW: Reset brand modal when it closes
        ------------------------------------------------------- */
        $('#addBrandModal').on('hidden.bs.modal', function () {
            $('#new_brand_name').val('');
            $('#new_brand_description').val('');
            $('#brand_desc_count').text('0/800');
            $('#new_brand_image').val('');
            $('#brand_preview_img').attr('src', '#');
            $('#brand_image_preview').addClass('d-none');
            $('#brand_upload_placeholder').removeClass('d-none');
            $('#brand_name_error').addClass('d-none').text('');
            $('#brand_general_error').addClass('d-none').text('');
        });

        /* -------------------------------------------------------
           NEW: Save Brand
        ------------------------------------------------------- */
        $('#saveBrandBtn').on('click', function () {
            const name        = $('#new_brand_name').val().trim();
            const description = $('#new_brand_description').val().trim();
            const image       = $('#new_brand_image')[0].files[0];

            // Reset errors
            $('#brand_name_error').addClass('d-none').text('');
            $('#brand_general_error').addClass('d-none').text('');

            // Validate
            if (!name) {
                $('#brand_name_error')
                    .text('{{ translate('brand_name_is_required') }}')
                    .removeClass('d-none');
                return;
            }

            // Build FormData
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('name', name);
            if (description) formData.append('description', description);
            if (image) formData.append('image', image);

            // Show spinner
            $('#brandBtnText').addClass('d-none');
            $('#brandBtnSpinner').removeClass('d-none');
            $('#saveBrandBtn').prop('disabled', true);

            $.ajax({
                url: '{{ route('admin.vehicle.attribute-setup.brand.store') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 200 || response.status === 'success') {
                        toastr.success('{{ translate('brand_added_successfully') }}');

                        // Auto-select the new brand in the main form dropdown
                        const newOption = new Option(
                            response.brand.name,
                            response.brand.id,
                            true,
                            true
                        );
                        $('#brand_id').append(newOption).trigger('change');

                        // Trigger model reload for the new brand
                        ajax_models(
                            '{{ url('/') }}/admin/vehicle/attribute-setup/model/ajax-models/' + response.brand.id
                        );

                        $('#addBrandModal').modal('hide');
                    } else {
                        $('#brand_general_error')
                            .text(response.message ?? '{{ translate('something_went_wrong') }}')
                            .removeClass('d-none');
                    }
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) {
                            $('#brand_name_error').text(errors.name[0]).removeClass('d-none');
                        }
                        const otherErrors = Object.entries(errors)
                            .filter(([k]) => k !== 'name')
                            .map(([, v]) => v[0])
                            .join(' ');
                        if (otherErrors) {
                            $('#brand_general_error').text(otherErrors).removeClass('d-none');
                        }
                    } else {
                        $('#brand_general_error')
                            .text(xhr.responseJSON?.message ?? '{{ translate('something_went_wrong') }}')
                            .removeClass('d-none');
                    }
                },
                complete: function () {
                    $('#brandBtnText').removeClass('d-none');
                    $('#brandBtnSpinner').addClass('d-none');
                    $('#saveBrandBtn').prop('disabled', false);
                }
            });
        });


        /* =======================================================
           NEW: Model image preview + character counter
        ======================================================= */
        $('#new_model_image').on('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#model_preview_img').attr('src', e.target.result);
                    $('#model_image_preview').removeClass('d-none');
                    $('#model_upload_placeholder').addClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                $('#model_image_preview').addClass('d-none');
                $('#model_upload_placeholder').removeClass('d-none');
            }
        });

        $('#new_model_description').on('input', function () {
            $('#model_desc_count').text($(this).val().length + '/800');
        });

        /* =======================================================
           NEW: Load brands into Model modal dropdown when it opens
        ======================================================= */
        $('#addModelModal').on('show.bs.modal', function () {
            const $select = $('#model_brand_id');
            $select.find('option:not(:first)').remove();
            $select.prop('disabled', true);

            $.get(
                '{{ route('admin.vehicle.attribute-setup.brand.all-brands', ['status' => 'active']) }}',
                function (data) {
                    $.each(data, function (i, brand) {
                        $select.append(
                            $('<option>', { value: brand.id, text: brand.text })
                        );
                    });
                }
            ).always(function () {
                $select.prop('disabled', false);
            });
        });

        /* -------------------------------------------------------
           NEW: Reset model modal when it closes
        ------------------------------------------------------- */
        $('#addModelModal').on('hidden.bs.modal', function () {
            $('#new_model_name').val('');
            $('#new_model_description').val('');
            $('#model_desc_count').text('0/800');
            $('#new_model_seat_capacity').val('');
            $('#new_model_max_weight').val('');
            $('#new_model_hatch_bag').val('');
            $('#new_model_engine').val('');
            $('#new_model_image').val('');
            $('#model_preview_img').attr('src', '#');
            $('#model_image_preview').addClass('d-none');
            $('#model_upload_placeholder').removeClass('d-none');
            $('#model_brand_id').val('').find('option:not(:first)').remove();
            $('#model_brand_error').addClass('d-none').text('');
            $('#model_name_error').addClass('d-none').text('');
            $('#model_general_error').addClass('d-none').text('');
        });

        /* -------------------------------------------------------
           NEW: Save Model
        ------------------------------------------------------- */
        $('#saveModelBtn').on('click', function () {
            const brandId      = $('#model_brand_id').val();
            const modelName    = $('#new_model_name').val().trim();
            const seatCapacity = $('#new_model_seat_capacity').val();
            const maxWeight    = $('#new_model_max_weight').val();
            const hatchBag     = $('#new_model_hatch_bag').val();
            const engine       = $('#new_model_engine').val().trim();
            const description  = $('#new_model_description').val().trim();
            const image        = $('#new_model_image')[0].files[0];

            // Reset errors
            $('#model_brand_error').addClass('d-none').text('');
            $('#model_name_error').addClass('d-none').text('');
            $('#model_general_error').addClass('d-none').text('');

            // Validate required fields
            let valid = true;
            if (!brandId) {
                $('#model_brand_error')
                    .text('{{ translate('please_select_a_brand') }}')
                    .removeClass('d-none');
                valid = false;
            }
            if (!modelName) {
                $('#model_name_error')
                    .text('{{ translate('model_name_is_required') }}')
                    .removeClass('d-none');
                valid = false;
            }
            if (!valid) return;

            // Show spinner
            $('#modelBtnText').addClass('d-none');
            $('#modelBtnSpinner').removeClass('d-none');
            $('#saveModelBtn').prop('disabled', true);

            // Build FormData to support image upload
            const modelFormData = new FormData();
            modelFormData.append('_token',        '{{ csrf_token() }}');
            modelFormData.append('name',          modelName);
            modelFormData.append('brand_id',      brandId);
            if (seatCapacity) modelFormData.append('seat_capacity',      seatCapacity);
            if (maxWeight)    modelFormData.append('maximum_weight',     maxWeight);
            if (hatchBag)     modelFormData.append('hatch_bag_capacity', hatchBag);
            if (engine)       modelFormData.append('engine',             engine);
            if (description)  modelFormData.append('description',        description);
            if (image)        modelFormData.append('image',              image);

            $.ajax({
                url: '{{ route('admin.vehicle.attribute-setup.model.store') }}',
                method: 'POST',
                data: modelFormData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 200 || response.status === 'success') {
                        toastr.success('{{ translate('model_added_successfully') }}');

                        // If the selected brand in the main form matches, reload models
                        const currentBrand = $('#brand_id').val();
                        if (currentBrand && currentBrand == brandId) {
                            ajax_models(
                                '{{ url('/') }}/admin/vehicle/attribute-setup/model/ajax-models/' + brandId
                            );

                            // Auto-select the new model after dropdown reloads
                            setTimeout(function () {
                                const newOption = new Option(
                                    response.model.name,
                                    response.model.id,
                                    true,
                                    true
                                );
                                $('#model_id').append(newOption).trigger('change');
                            }, 700);
                        }

                        $('#addModelModal').modal('hide');
                    } else {
                        $('#model_general_error')
                            .text(response.message ?? '{{ translate('something_went_wrong') }}')
                            .removeClass('d-none');
                    }
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.brand_id) {
                            $('#model_brand_error').text(errors.brand_id[0]).removeClass('d-none');
                        }
                        if (errors.name) {
                            $('#model_name_error').text(errors.name[0]).removeClass('d-none');
                        }
                        const otherErrors = Object.entries(errors)
                            .filter(([k]) => !['brand_id', 'name'].includes(k))
                            .map(([, v]) => v[0])
                            .join(' ');
                        if (otherErrors) {
                            $('#model_general_error').text(otherErrors).removeClass('d-none');
                        }
                    } else {
                        $('#model_general_error')
                            .text(xhr.responseJSON?.message ?? '{{ translate('something_went_wrong') }}')
                            .removeClass('d-none');
                    }
                },
                complete: function () {
                    $('#modelBtnText').removeClass('d-none');
                    $('#modelBtnSpinner').addClass('d-none');
                    $('#saveModelBtn').prop('disabled', false);
                }
            });
        });

    </script>
@endpush