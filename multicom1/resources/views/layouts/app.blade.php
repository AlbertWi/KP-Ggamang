<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Dashboard')</title>
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  {{-- Navbar dan Sidebar --}}
  @include('partials.navbar')
  @include('partials.sidebar')

  {{-- Konten --}}
  <div class="content-wrapper">
    <section class="content pt-3">
      <div class="container-fluid">

        {{-- Flash Message (atas konten) --}}
        @if(session('success'))
          <div class="alert alert-success" role="alert">
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger" role="alert">
            {{ session('error') }}
          </div>
        @endif

        @if(session('warning'))
          <div class="alert alert-warning" role="alert">
            {{ session('warning') }}
          </div>
        @endif

        @if(session('info'))
          <div class="alert alert-info" role="alert">
            {{ session('info') }}
          </div>
        @endif

        {{-- Konten utama --}}
        @yield('content')

      </div>
    </section>
  </div>

  {{-- Footer --}}
  @include('partials.footer')
</div>

{{-- Script --}}
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@stack('scripts')
</body>
</html>
