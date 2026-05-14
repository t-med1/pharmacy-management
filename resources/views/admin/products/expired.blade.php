@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Expired Medicines</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
        <li class="breadcrumb-item active">Expired</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="{{ route('purchases.create') }}" class="btn btn-success float-right mt-2">
        <i class="fas fa-plus mr-1"></i> Add Stock
    </a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                    Medicines Past Expiry Date
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="expired-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Category</th>
                                <th>Cost Price</th>
                                <th>Quantity</th>
                                <th>Expiry Date</th>
                                <th class="action-btn">Action</th>
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
<script>
$(document).ready(function () {
    $('#expired-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('expired') }}",
        columns: [
            { data: 'product',     name: 'product' },
            { data: 'category',    name: 'category' },
            { data: 'cost_price',  name: 'cost_price' },
            { data: 'quantity',    name: 'quantity' },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'action',      name: 'action', orderable: false, searchable: false },
        ]
    });
});
</script>
@endpush
