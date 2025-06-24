<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Multicom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('adminlte/dist/css/adminlte.min.css') }}" rel="stylesheet">
</head>
<body class="hold-transition login-page">
    @yield('content')

    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
