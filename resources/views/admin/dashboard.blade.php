@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/plugins/chart.js/Chart.min.css') }}">
<style>
.stat-card {
    border-radius: 12px;
    border: none;
    transition: transform .15s ease, box-shadow .15s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.10) !important;
}
.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.stat-value {
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1.1;
    color: #1a1f36;
}
.stat-label {
    font-size: .78rem;
    font-weight: 500;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: #8898aa;
}
.trend-up   { color: #2dce89; font-size: .8rem; }
.trend-down { color: #f5365c; font-size: .8rem; }
.section-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #8898aa;
    margin-bottom: .75rem;
}
.alert-row { border-left: 4px solid #fb6340; }
.card { border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.card-header { background: transparent; border-bottom: 1px solid #f0f2f5; padding: 1rem 1.25rem; }
</style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title mb-0">Dashboard</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item active">
            {{ now()->format('l, F j Y') }}
        </li>
    </ul>
</div>
@endpush

@section('content')

{{-- ── Stat Cards ─────────────────────────────────────────────────────────── --}}
<div class="row">

    {{-- Today's Revenue --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon mr-3" style="background:#e8f5e9;">
                    <i class="fas fa-coins" style="color:#2dce89;"></i>
                </div>
                <div>
                    <div class="stat-value">
                        {{ settings('app_currency', '$') }} {{ number_format($today_sales, 2) }}
                    </div>
                    <div class="stat-label">Today's Revenue</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Revenue --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon mr-3" style="background:#e8eaf6;">
                    <i class="fas fa-chart-line" style="color:#4361ee;"></i>
                </div>
                <div>
                    <div class="stat-value">
                        {{ settings('app_currency', '$') }} {{ number_format($monthly_revenue, 2) }}
                    </div>
                    <div class="stat-label">This Month</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Expired Medicines --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon mr-3" style="background:#fce4ec;">
                    <i class="fas fa-calendar-times" style="color:#f72585;"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $total_expired }}</div>
                    <div class="stat-label">Expired Medicines</div>
                    @if($total_expired > 0)
                    <a href="{{ route('expired') }}" class="trend-down">
                        <i class="fas fa-exclamation-circle"></i> View all
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon mr-3" style="background:#fff3e0;">
                    <i class="fas fa-box-open" style="color:#fb6340;"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $low_stock_count }}</div>
                    <div class="stat-label">Low Stock Items</div>
                    @if($low_stock_count > 0)
                    <a href="{{ route('outstock') }}" class="trend-down">
                        <i class="fas fa-exclamation-circle"></i> View all
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Charts + Recent Sales ──────────────────────────────────────────────── --}}
<div class="row">

    {{-- 7-Day Revenue Chart --}}
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 font-weight-bold">Revenue — Last 7 Days</h6>
                <span class="badge badge-success">
                    {{ settings('app_currency', '$') }} {{ number_format(array_sum($weekly_revenue), 2) }} total
                </span>
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Distribution Chart --}}
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 font-weight-bold">Overview</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                {!! $pieChart->render() !!}
            </div>
        </div>
    </div>

</div>

{{-- ── Quick Stats Row ────────────────────────────────────────────────────── --}}
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-center p-3">
            <div class="section-title">Categories</div>
            <div class="stat-value">{{ $total_categories }}</div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-center p-3">
            <div class="section-title">System Users</div>
            <div class="stat-value">{{ $total_users }}</div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-center p-3">
            <div class="section-title">Quick Actions</div>
            <div class="d-flex justify-content-center flex-wrap" style="gap:.5rem;">
                @can('create-purchase')
                <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus mr-1"></i>Purchase
                </a>
                @endcan
                @can('create-sale')
                <a href="{{ route('sales.create') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-cash-register mr-1"></i>Sale
                </a>
                @endcan
                @can('view-reports')
                <a href="{{ route('sales.report') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-alt mr-1"></i>Report
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Sales Table ─────────────────────────────────────────────────── --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 font-weight-bold">Recent Sales</h6>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="dashboard-sales-table" class="datatable table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Medicine</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-js')
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script>
$(document).ready(function () {

    // Recent sales DataTable (no pagination controls, just the latest 10)
    $('#dashboard-sales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('sales.index') }}",
        pageLength: 10,
        dom: 'rt',
        columns: [
            { data: 'product',     name: 'product' },
            { data: 'quantity',    name: 'quantity' },
            { data: 'total_price', name: 'total_price' },
            { data: 'date',        name: 'date' },
        ]
    });

    // 7-day revenue bar chart
    var ctx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($weekly_labels) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($weekly_revenue) !!},
                backgroundColor: 'rgba(67, 97, 238, 0.15)',
                borderColor: '#4361ee',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: {
                        callback: function(v) {
                            return '{{ settings("app_currency", "$") }}' + v.toLocaleString();
                        }
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
