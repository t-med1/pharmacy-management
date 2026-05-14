<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst(AppSettings::get('app_name', 'PharmaManager')) }} — {{ ucfirst($title ?? 'Dashboard') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ !empty(AppSettings::get('favicon')) ? asset('storage/'.AppSettings::get('favicon')) : asset('assets/img/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
    <!-- Feather Icons -->
    <link rel="stylesheet" href="{{ asset('assets/css/feathericon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/icons.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <!-- Snackbar -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/snackbar/snackbar.min.css') }}">
    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Custom overrides -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Page-specific CSS -->
    @stack('page-css')
</head>
<body>

<div class="main-wrapper">

    @include('admin.includes.header')
    @include('admin.includes.sidebar')

    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    @stack('page-header')
                </div>
            </div>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <x-alerts.danger :error="$error" />
                @endforeach
            @endif

            @yield('content')

            <x-modals.add-sale />
        </div>
    </div>

</div>

<!-- jQuery -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<!-- SlimScroll -->
<script src="{{ asset('assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- Snackbar -->
<script src="{{ asset('assets/plugins/snackbar/snackbar.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<!-- App JS -->
<script src="{{ asset('assets/js/script.js') }}"></script>

<script>
$(document).ready(function () {

    // Global delete handler — uses class .deletebtn (not id, which must be unique)
    $('body').on('click', '.deletebtn', function () {
        var route = $(this).data('route');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e7515a',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Delete',
            cancelButtonText: 'Cancel',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: route,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Record has been removed.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        if ($.fn.DataTable.isDataTable('.datatable')) {
                            $('.datatable').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                    }
                });
            }
        });
    });

    @if(Session::has('message'))
    var type = "{{ Session::get('alert-type', 'info') }}";
    var colors = {
        info:    '#2196f3',
        warning: '#e2a03f',
        success: '#4caf50',
        danger:  '#e7515a',
    };
    Snackbar.show({
        text: "{{ addslashes(Session::get('message')) }}",
        pos: 'top-right',
        actionTextColor: '#fff',
        backgroundColor: colors[type] || colors.info,
        duration: 4000,
    });
    @endif

});
</script>

@stack('page-js')
</body>
</html>
