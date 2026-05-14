@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Activity Log</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Activity Log</li>
    </ul>
</div>
@can('view-settings')
<div class="col-sm-5 col text-right">
    <form method="POST" action="{{ route('activity.destroy') }}" onsubmit="return confirm('Clear all activity logs?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger mt-2">
            <i class="fas fa-trash mr-1"></i> Clear Log
        </button>
    </form>
</div>
@endcan
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-history mr-2 text-primary"></i>All User Activity
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="activity-table" class="datatable table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>When</th>
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
    $('#activity-table').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: "{{ route('activity.index') }}",
        columns: [
            { data: 'DT_RowIndex',  name: 'id', orderable: false, searchable: false, width: '50px' },
            { data: 'user',         name: 'user' },
            { data: 'action',       name: 'action', width: '110px' },
            { data: 'description',  name: 'description' },
            { data: 'ip_address',   name: 'ip_address', width: '120px' },
            { data: 'created_at',   name: 'created_at', width: '130px' },
        ]
    });
});
</script>
@endpush
