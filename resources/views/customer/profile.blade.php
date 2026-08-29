@extends('template.app')

@section('title', 'Customer Profile')

@section('content')
<div class="col-md-12">
    <div class="px-md-2 mx-auto mb-5">
        <div class="row">
            <!-- Customer Details Card -->
            <div class="col-md-4 col-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="user-avatar-section">
                            <div class=" d-flex align-items-center flex-column">
                                <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div class="user-info text-center">
                                    <h4 class="mb-2">{{ $customer->name }}</h4>
                                    <span class="badge bg-label-secondary">Customer</span>
                                </div>
                            </div>
                        </div>
                        <h5 class="pb-2 border-bottom mb-4 mt-4">Details</h5>
                        <div class="info-container">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <span class="fw-bold me-2">WhatsApp/Phone:</span>
                                    <span>{{ $customer->phone }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold me-2">Email:</span>
                                    <span>{{ $customer->email ?: '-' }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold me-2">Gender:</span>
                                    <span class="text-capitalize">{{ $customer->gender ?: '-' }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold me-2">Birth Date:</span>
                                    <span>{{ $customer->birth_date ? date('d M Y', strtotime($customer->birth_date)) : '-' }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold me-2">Address:</span>
                                    <span>{{ $customer->address ?: '-' }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold me-2">Total Booking:</span>
                                    <span class="badge bg-primary">{{ $total_visits }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold me-2">Last Booking:</span>
                                    <span>{{ $last_visit_at }}</span>
                                </li>
                            </ul>
                        </div>
                        
                        @if($customer->notes)
                        <h5 class="pb-2 border-bottom mb-3 mt-4">Notes</h5>
                        <p class="text-muted">{{ $customer->notes }}</p>
                        @endif
                        
                        <div class="d-flex justify-content-center pt-3">
                            <a href="{{ route('customer.index') }}" class="btn btn-label-secondary">Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Booking History Card -->
            <div class="col-md-8 col-12 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Riwayat Booking</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Treatment</th>
                                        <th>Staff</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookings as $booking)
                                    <tr>
                                        <td>{{ date('d M Y', strtotime($booking->booking_date)) }}</td>
                                        <td>{{ $booking->treatment_name ?? 'Unknown Treatment' }}</td>
                                        <td>{{ $booking->staff_name ?? 'Any Staff' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = 'bg-label-primary';
                                                switch($booking->status) {
                                                    case 'completed': $badgeClass = 'bg-label-success'; break;
                                                    case 'cancelled': $badgeClass = 'bg-label-danger'; break;
                                                    case 'pending': $badgeClass = 'bg-label-warning'; break;
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }} text-capitalize">{{ str_replace('_', ' ', $booking->status) }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat booking.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
