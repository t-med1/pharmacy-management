@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
<style>
.report-summary-card { border-left: 4px solid #4361ee; }
.report-summary-card .card-body { padding: 1rem 1.25rem; }
</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Sales Reports</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Sales Reports</li>
    </ul>
</div>
<div class="col-sm-5 col text-right">
    <a href="#generate_report" data-toggle="modal" class="btn btn-primary mt-2">
        <i class="fas fa-filter mr-1"></i> Generate Report
    </a>
</div>
@endpush

@section('content')

@isset($sales)

{{-- Summary cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card report-summary-card shadow-sm">
            <div class="card-body">
                <div class="text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">Total Sales</div>
                <div style="font-size:1.8rem;font-weight:700;color:#1a1f36;">{{ $sales->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card report-summary-card shadow-sm" style="border-left-color:#2dce89;">
            <div class="card-body">
                <div class="text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">Total Revenue</div>
                <div style="font-size:1.8rem;font-weight:700;color:#1a1f36;">
                    {{ settings('app_currency', '$') }} {{ number_format($sales->sum('total_price'), 2) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card report-summary-card shadow-sm" style="border-left-color:#fb6340;">
            <div class="card-body">
                <div class="text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">Total Units Sold</div>
                <div style="font-size:1.8rem;font-weight:700;color:#1a1f36;">{{ $sales->sum('quantity') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-list mr-2 text-primary"></i>
                    Report Results
                    @if(request('from_date') && request('to_date'))
                        <small class="text-muted font-weight-normal">
                            — {{ \Carbon\Carbon::parse(request('from_date'))->format('d M Y') }}
                            to {{ \Carbon\Carbon::parse(request('to_date'))->format('d M Y') }}
                        </small>
                    @endif
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sales-report-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Medicine Name</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sales as $i => $sale)
                                @if(!empty($sale->product->purchase))
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        @if(!empty($sale->product->purchase->image))
                                            <span class="avatar avatar-sm mr-2">
                                                <img class="avatar-img rounded" src="{{ asset('storage/purchases/'.$sale->product->purchase->image) }}" alt="img">
                                            </span>
                                        @endif
                                        {{ $sale->product->purchase->product }}
                                    </td>
                                    <td>{{ $sale->quantity }}</td>
                                    <td>{{ settings('app_currency','$') }} {{ number_format($sale->total_price, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d M, Y') }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="2" class="text-right">Totals:</td>
                                <td>{{ $sales->sum('quantity') }}</td>
                                <td>{{ settings('app_currency','$') }} {{ number_format($sales->sum('total_price'), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endisset

@if(!isset($sales))
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No report generated yet</h5>
                <p class="text-muted mb-4">Click "Generate Report" and select a date range to view sales data.</p>
                <a href="#generate_report" data-toggle="modal" class="btn btn-primary">
                    <i class="fas fa-filter mr-1"></i> Generate Report
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Generate Modal --}}
<div class="modal fade" id="generate_report" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:1px solid #e9ecef;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-filter mr-2 text-primary"></i>Generate Sales Report
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('sales.report') }}">
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>From Date <span class="text-danger">*</span></label>
                                <input type="date" name="from_date" class="form-control" required
                                       value="{{ old('from_date') }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>To Date <span class="text-danger">*</span></label>
                                <input type="date" name="to_date" class="form-control" required
                                       value="{{ old('to_date') }}">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Generate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-js')
@isset($sales)
<script>
$(document).ready(function () {
    $('#sales-report-table').DataTable({
        dom: '<"d-flex align-items-center justify-content-between mb-3"<"d-flex align-items-center"lB><"d-flex"f>>rtip',
        buttons: [
            {
                extend: 'collection',
                text: '<i class="fas fa-download mr-1"></i> Export',
                className: 'btn btn-sm btn-outline-primary mr-2',
                buttons: [
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                        title: 'Sales Report',
                        exportOptions: { columns: ':not(.action-btn)' }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                        title: 'Sales Report',
                        exportOptions: { columns: ':not(.action-btn)' }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                        exportOptions: { columns: ':not(.action-btn)' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Print',
                        exportOptions: { columns: ':not(.action-btn)' }
                    },
                ]
            }
        ],
        pageLength: 25,
        order: [[4, 'desc']],
    });
});
</script>
@endisset
@endpush
