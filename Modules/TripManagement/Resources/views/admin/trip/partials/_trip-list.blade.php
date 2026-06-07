<div class="table-responsive mt-3">
    <table class="table table-borderless align-middle table-hover">
        <thead class="table-light align-middle text-capitalize text-nowrap">
            <tr>
                <th class="text-center sl">{{translate('SL')}}</th>
                <th class="text-center trip-id">{{translate('trip_ID')}}</th>
                <th class="text-center date">{{translate('date')}}</th>
                <th class="text-center customer-name">{{translate('customer')}}</th>
                <th class="text-center driver">{{translate('driver')}}</th>
                <th class="text-center driver">{{translate('trip_type')}}</th>
                <th class="text-center trip-cost">{{translate('trip_cost')}} ({{getSession('currency_symbol')}})</th>
                <th class="text-center additional-fee text-capitalize">
                    {{translate('additional_fee')}} ({{getSession('currency_symbol')}})
                </th>
                <th class="text-center text-capitalize total-trip-cost">
                    {{translate('total_trip')}} <br /> {{translate('cost')}} ({{getSession('currency_symbol')}})
                </th>
                <th class="text-center trip-status">{{translate('trip_payment_status')}}</th>
                <th class="text-center trip-status">{{translate('trip_status')}}</th>
                <th class="text-center action text-center">{{translate('action')}}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trips as $key => $trip)
            <tr>
                <td class="text-center sl">{{$trips->firstItem() + $key}}</td>
            <td class="text-center trip-id">
    <a href="{{ route('admin.trip.show', ['type' => $type, 'id' => $trip->id, 'page' => 'summary']) }}">
        {{ $trip->ref_id }}
    </a>
</td>
                <td class="text-center text-nowrap date">
                    <div dir="ltr">
                        {{date('d F Y', strtotime($trip->created_at))}},
                        <br /> {{date('h:i a', strtotime($trip->created_at))}}
                    </div>
                </td>
                <td class="text-center customer-name">
                    <a target="_blank" @if($trip->customer)
                        href="{{route('admin.customer.show', [$trip->customer?->id])}}"
                        @endif>
                        {{ $trip->customer?->id ? $trip->customer?->first_name. ' ' . $trip->customer?->last_name : translate('no_customer_assigned') }}
                        @if($trip?->safetyAlerts)
                        @foreach($trip->safetyAlerts as $alert)
                        @if($alert?->sentBy->id == $trip->customer?->id)
                        <img src="{{ dynamicAsset('public/assets/admin-module/img/safety-alert-shield-icon-red.png')}}" alt="safety alert shield icon" width="20" height="20">
                        @break
                        @endif
                        @endforeach
                        @endif
                    </a>
                </td>
                <td class="text-center text-capitalize driver">
                    <a target="_blank" @if($trip->driver)
                        href="{{route('admin.driver.show', [$trip->driver?->id])}}"
                        @endif>
                        {{ $trip->driver?->id ? $trip->driver?->first_name. ' ' . $trip->driver?->last_name : translate('no_driver_assigned') }}
                        @if($trip?->safetyAlerts)
                        @foreach($trip->safetyAlerts as $alert)
                        @if($alert?->sentBy->id == $trip->driver?->id)
                        <img src="{{ dynamicAsset('public/assets/admin-module/img/safety-alert-shield-icon-red.png')}}" alt="safety alert shield icon" width="20" height="20">
                        @break
                        @endif
                        @endforeach
                        @endif
                    </a>
                </td>
                <td class="text-center trip-type">
                    <span class="badge badge-primary">{{ translate($trip->type)}}</span>
                    @if($trip->ride_request_type == SCHEDULED)
                    <span class="text-info fw-semibold d-block mt-1">{{ translate(SCHEDULED) }}</span>
                    @endif
                </td>

                {{-- Trip Cost --}}
                <td class="text-center trip-cost">    {{ getCurrencyFormat($trip->current_status == 'completed' ? ($trip->actual_fare ?? 0) : ($trip->estimated_fare ?? 0)) }}
</td>

                {{-- Additional Fee: Cancellation Fee only --}}
                <td class="text-center text-capitalize additional-fee">
    <div>{{translate('cancellation_fee')}}: {{getCurrencyFormat($trip->fee?->cancellation_fee ?? 0)}}</div>
                </td>

                {{-- Total Trip Cost --}}
                <td class="text-center total-trip-cost">{{ getCurrencyFormat($trip->paid_fare) }}</td>

                {{-- Payment Status --}}
                <td class="text-center trip-status">
                    <span class="badge badge-{{ $trip->payment_status == PAID ? 'primary' : 'warning' }}">
                        {{translate($trip->payment_status)}}
                    </span>
                </td>

                {{-- Trip Status --}}
                <td class="text-center trip-status">
                    <span class="badge badge-{{ ($trip->current_status == COMPLETED || $trip->current_status == RETURNED) ? 'primary' : 'warning' }}">
                        {{translate($trip->current_status)}}
                    </span>
                </td>

             {{-- Actions --}}
        <td class="text-center action">
    <div class="d-flex justify-content-center gap-2 align-items-center">
        @can('trip_log')
            <a href="{{route('admin.trip.show', ['id' => $trip->id, 'type' => Request::get('type'), 'page' => 'log'])}}"
               class="btn btn-outline-info btn-action">
                <i class="bi bi-clock-fill"></i>
            </a>
        @endcan
        @can('trip_view')
            <a href="{{route('admin.trip.show', ['type' => $type, 'id' => $trip->id, 'page' => 'summary'])}}"
               class="btn btn-outline-info btn-action">
                <i class="bi bi-eye-fill"></i>
            </a>
        @endcan
        @can('trip_delete')
            <form action="{{ route('admin.trip.delete', $trip->id) }}"
                  method="POST"
                  onsubmit="return confirm('{{translate('are_you_sure_you_want_to_delete_this_trip')}}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-action"
                        title="{{ translate('delete') }}">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </form>
        @endcan
    </div>
</td>
            </tr>
            @empty
            <tr>
                <td colspan="12">
                    <div class="d-flex flex-column justify-content-center align-items-center gap-2 py-3">
                        <img src="{{ dynamicAsset('public/assets/admin-module/img/empty-icons/no-data-found.svg') }}" alt="" width="100">
                        <p class="text-center">{{translate('no_data_available')}}</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-bottom d-flex flex-column flex-sm-row justify-content-sm-between align-items-center gap-2 pt-2">
    <p class="mb-0"></p>
    {{$trips->links()}}
</div>
