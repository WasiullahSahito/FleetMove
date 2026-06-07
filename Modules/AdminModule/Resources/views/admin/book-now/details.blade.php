@extends('adminmodule::layouts.master')

@section('title', translate('Booking Details') . ' — ' . $booking->ref_id)

@section('content')
<div class="main-content">
    <div class="container-fluid">

        {{-- Back + Header --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('admin.book-now.index', 'all') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="page-title mb-0">{{ translate('Booking Details') }} — {{ $booking->ref_id }}</h2>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @php
        $tripCost = (float) ($booking->actual_fare ?: $booking->estimated_fare);
        $cancelFee = (float) ($booking->cancellation_fee ?? 0);
        $extraFare = (float) ($booking->extra_fare_fee ?? 0);
        $returnFee = (float) ($booking->return_fee ?? 0);
        $tips = (float) ($booking->tips ?? 0);
        $totalAdditional = $cancelFee + $extraFare + $returnFee;
        $totalTripCost = $tripCost + $totalAdditional;

        $currency = config('addon_setting.currency_symbol', '€');
        @endphp

        <div class="row g-4">

            {{-- ── Left Column ── --}}
            <div class="col-lg-8">

                {{-- Booking Overview --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ translate('Booking Info') }}</h5>
                        @php
                        $statusClass = match($booking->current_status) {
                        'confirmed' => 'badge-info',
                        'completed' => 'badge-success',
                        'cancelled' => 'badge-danger',
                        default => 'badge-warning',
                        };
                        @endphp
                        <span class="badge {{ $statusClass }} text-capitalize fs-6">
                            {{ translate($booking->current_status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Ref ID') }}</label>
                                <p class="fw-semibold mb-0">{{ $booking->ref_id }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Scheduled At') }}</label>
                                <p class="fw-semibold mb-0">
                                    {{ $booking->scheduled_at
                                        ? \Carbon\Carbon::parse($booking->scheduled_at)->format('d M Y, h:i A')
                                        : '—' }}
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Pickup') }}</label>
                                <p class="mb-0"><i class="bi bi-geo-alt-fill text-success me-1"></i>{{ $booking->pickup_address }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Destination') }}</label>
                                <p class="mb-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $booking->destination_address }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Zone') }}</label>
                                <p class="mb-0">{{ $booking->zone_name ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Vehicle Category') }}</label>
                                <p class="mb-0">{{ $booking->vehicle_category_name ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Payment Method') }}</label>
                                <p class="mb-0 text-capitalize">{{ $booking->payment_method ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">{{ translate('Payment Status') }}</label>
                                <p class="mb-0">
                                    <span class="badge {{ $booking->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                        {{ translate($booking->payment_status ?? 'unpaid') }}
                                    </span>
                                </p>
                            </div>
                            @if($booking->note)
                            <div class="col-12">
                                <label class="text-muted small">{{ translate('Note') }}</label>
                                <p class="mb-0">{{ $booking->note }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Fare Breakdown --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('Fare Breakdown') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td>{{ translate('Trip Cost') }}</td>
                                    <td class="text-end fw-semibold">{{ $currency }} {{ number_format($tripCost, 2) }}</td>
                                </tr>

                                {{-- Additional fees --}}
                                <tr class="table-light">
                                    <td colspan="2" class="fw-semibold small text-muted">{{ translate('Additional Fees') }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">{{ translate('Cancellation Fee') }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format($cancelFee, 2) }}</td>
                                </tr>
                                @if($extraFare > 0)
                                <tr>
                                    <td class="ps-4">{{ translate('Extra Fare') }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format($extraFare, 2) }}</td>
                                </tr>
                                @endif
                                @if($returnFee > 0)
                                <tr>
                                    <td class="ps-4">{{ translate('Return Fee') }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format($returnFee, 2) }}</td>
                                </tr>
                                @endif
                                @if($tips > 0)
                                <tr>
                                    <td class="ps-4">{{ translate('Tips') }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format($tips, 2) }}</td>
                                </tr>
                                @endif

                                {{-- Totals --}}
                                <tr class="table-primary fw-bold">
                                    <td>{{ translate('Total Trip Cost') }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format($totalTripCost, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ translate('Paid Fare') }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format((float)($booking->paid_fare ?? 0), 2) }}</td>
                                </tr>
                                @if(($booking->due_amount ?? 0) > 0)
                                <tr class="text-danger">
                                    <td>{{ translate('Due Amount') }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format((float)$booking->due_amount, 2) }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Status Timeline --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('Status Timeline') }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach([
                        'Pending' => $booking->status_pending,
                        'Accepted' => $booking->status_accepted,
                        'Ongoing' => $booking->status_ongoing,
                        'Completed' => $booking->status_completed,
                        'Cancelled' => $booking->status_cancelled,
                        ] as $label => $ts)
                        @if($ts)
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-secondary text-capitalize" style="min-width:80px">{{ translate($label) }}</span>
                            <span class="text-muted small">{{ \Carbon\Carbon::parse($ts)->format('d M Y, h:i A') }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

            </div>{{-- /col-lg-8 --}}

            {{-- ── Right Column ── --}}
            <div class="col-lg-4">

                {{-- Customer Card --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('Customer') }}</h5>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        @if($booking->customer_image)
                        <img src="{{ asset('storage/'.$booking->customer_image) }}" class="rounded-circle" width="50" height="50" alt="">
                        @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width:50px;height:50px;font-size:20px;">
                            <i class="bi bi-person"></i>
                        </div>
                        @endif
                        <div>
                            <div class="fw-semibold">
                                {{ trim($booking->customer_first_name . ' ' . $booking->customer_last_name) ?: '—' }}
                            </div>
                            <div class="text-muted small">{{ $booking->customer_phone ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Driver Card --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('Driver') }}</h5>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        @if($booking->driver_image)
                        <img src="{{ asset('storage/'.$booking->driver_image) }}" class="rounded-circle" width="50" height="50" alt="">
                        @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width:50px;height:50px;font-size:20px;">
                            <i class="bi bi-person"></i>
                        </div>
                        @endif
                        <div>
                            @if($booking->driver_first_name)
                            <div class="fw-semibold">
                                {{ trim($booking->driver_first_name . ' ' . $booking->driver_last_name) }}
                            </div>
                            <div class="text-muted small">{{ $booking->driver_phone }}</div>
                            @else
                            <span class="text-muted">{{ translate('No Driver Assigned') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Update Status / Assign Driver --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('Update Booking') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.book-now.update', $booking->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">{{ translate('Status') }}</label>
                                <select name="current_status" class="form-select">
                                    @foreach(['pending','confirmed','completed','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $booking->current_status === $s ? 'selected' : '' }}>
                                        {{ translate(ucfirst($s)) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('current_status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ translate('Assign Driver') }}</label>
                                <select name="driver_id" class="form-select">
                                    <option value="">— {{ translate('Unassigned') }} —</option>
                                    @foreach($drivers as $d)
                                    <option value="{{ $d->id }}" {{ $booking->driver_id === $d->id ? 'selected' : '' }}>
                                        {{ $d->first_name }} {{ $d->last_name }} ({{ $d->phone }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                {{ translate('Update Booking') }}
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Delete --}}
                <form action="{{ route('admin.book-now.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> {{ translate('Delete Booking') }}
                    </button>
                </form>

            </div>{{-- /col-lg-4 --}}
        </div>{{-- /row --}}

    </div>
</div>
@endsection
