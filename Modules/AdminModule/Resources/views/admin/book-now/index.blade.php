@extends('adminmodule::layouts.master')

@section('title', translate('Book Now List'))

@section('content')
<div class="main-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="page-title-wrap mb-3">
            <h2 class="page-title">{{ translate('Book Now List') }}</h2>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach (['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
            <a href="{{ route('admin.book-now.index', $key) }}" class="btn {{ $status === $key ? 'btn-primary' : 'btn-outline-primary' }} btn-sm d-flex align-items-center gap-1">
                {{ translate($label) }}
                <span class="badge {{ $status === $key ? 'bg-white text-primary' : 'bg-primary' }} ms-1">
                    {{ $counts->$key ?? 0 }}
                </span>
            </a>
            @endforeach

            <a href="{{ route('admin.book-now.create') }}" class="btn btn-success btn-sm ms-auto">
                <i class="bi bi-plus-circle me-1"></i> {{ translate('Create Booking') }}
            </a>
        </div>

        {{-- Success / Error Alerts --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Bookings Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>{{ translate('Ref ID') }}</th>
                                <th>{{ translate('Date') }}</th>
                                <th>{{ translate('Customer') }}</th>
                                <th>{{ translate('Driver') }}</th>
                                <th>{{ translate('Trip Type') }}</th>
                                <th>{{ translate('Trip Cost') }}</th>
                                <th>{{ translate('Additional Fee') }}</th>
                                <th>{{ translate('Total Trip Cost') }}</th>
                                <th>{{ translate('Trip Payment Status') }}</th>
                                <th>{{ translate('Trip Status') }}</th>
                                <th>{{ translate('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $index => $b)
                            @php
                            $tripCost = (float) ($b->actual_fare ?: $b->estimated_fare);
                            $cancelFee = (float) ($b->cancellation_fee ?? 0);
                            $extraFare = (float) ($b->extra_fare_fee ?? 0);
                            $returnFee = (float) ($b->return_fee ?? 0);
                            $tips = (float) ($b->tips ?? 0);
                            $totalAdditional= $cancelFee + $extraFare + $returnFee;
                            $totalTripCost = $tripCost + $totalAdditional;

                            $currency = config('addon_setting.currency_symbol', '€');
                            @endphp
                            <tr>
                                <td>{{ $bookings->firstItem() + $index }}</td>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $b->ref_id }}</span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($b->created_at)->format('d M Y, h:i a') }}
                                </td>
                                <td>
                                    <div>{{ trim($b->customer_first_name . ' ' . $b->customer_last_name) ?: '—' }}</div>
                                    @if($b->customer_phone)
                                    <small class="text-muted">{{ $b->customer_phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($b->driver_first_name)
                                    <div>{{ trim($b->driver_first_name . ' ' . $b->driver_last_name) }}</div>
                                    <small class="text-muted">{{ $b->driver_phone }}</small>
                                    @else
                                    <span class="text-muted">{{ translate('No Driver Assigned') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-primary fw-semibold">{{ translate('Book now') }}</span>
                                </td>

                                {{-- Trip Cost --}}
                                <td>{{ $currency }} {{ number_format($tripCost, 2) }}</td>

                                {{-- Additional Fee (Cancellation Fee only) --}}
                                <td>
                                    <div class="small">
                                        <div>{{ translate('Cancellation Fee') }}: {{ $currency }} {{ number_format($cancelFee, 2) }}</div>
                                        @if($tips > 0)
                                        <div>{{ translate('Tips') }}: {{ $currency }} {{ number_format($tips, 2) }}</div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Total Trip Cost --}}
                                <td class="fw-semibold">{{ $currency }} {{ number_format($totalTripCost, 2) }}</td>

                                {{-- Payment Status --}}
                                <td>
                                    @php
                                    $payClass = match($b->payment_status ?? 'unpaid') {
                                    'paid' => 'badge-success',
                                    'partial' => 'badge-warning',
                                    default => 'badge-warning',
                                    };
                                    @endphp
                                    <span class="badge {{ $payClass }} text-capitalize">
                                        {{ translate($b->payment_status ?? 'Unpaid') }}
                                    </span>
                                </td>

                                {{-- Trip Status --}}
                                <td>
                                    @php
                                    $statusClass = match($b->current_status) {
                                    'confirmed' => 'badge-info',
                                    'completed' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    default => 'badge-warning',
                                    };
                                    @endphp
                                    <span class="badge {{ $statusClass }} text-capitalize">
                                        {{ translate($b->current_status) }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.book-now.details', $b->id) }}" class="btn btn-outline-primary btn-sm" title="{{ translate('View Details') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <form action="{{ route('admin.book-now.destroy', $b->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this booking?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ translate('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    {{ translate('No bookings found') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($bookings->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $bookings->appends(['status' => $status])->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
