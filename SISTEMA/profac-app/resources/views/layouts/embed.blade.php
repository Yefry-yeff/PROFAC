<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'D. VALENCIA') }}</title>
    <link rel="icon" type="image/x-icon" href="/img/valencia-fondo-transparente.png">

    @livewireStyles
    @stack('styles')

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
    <link href="{{ asset('font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/parsley/parsley.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/dataTables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/select2/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        /* ── Embed mode: stripped layout, form only ── */
        html, body {
            background: #f4f5f7 !important;
            overflow-x: hidden;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 13px;
        }
        /* Hide page header / breadcrumb — already shown in parent modal header */
        .row.wrapper.border-bottom.white-bg.page-heading { display: none !important; }
        /* Hide Registrar Pedido / Modificar Pedido ibox titles in embed */
        .pedido-main-title,
        .editar-main-title { display: none !important; }
        /* Round top corners of ibox-content since title is hidden */
        .pedido-main-title + .ibox-content,
        .editar-main-title + .ibox-content { border-radius: 4px 4px 0 0 !important; }
        /* Remove all extra wrapper spacing */
        .wrapper.wrapper-content {
            padding: 8px 14px 14px !important;
            margin: 0 !important;
            animation: none !important;
        }
        /* Compact ibox */
        .ibox { margin-top: 4px !important; margin-bottom: 0 !important; }
        .ibox-title { padding: 10px 16px !important; }
        .ibox-content { padding: 16px !important; }
        /* Tighten form controls */
        .form-group { margin-bottom: 10px !important; }
        .form-control { font-size: 13px !important; }
        label { font-size: 12px !important; margin-bottom: 3px !important; }
        /* Remove page-level outer padding that inspinia adds */
        #wrapper, #page-wrapper { margin: 0 !important; padding: 0 !important; }
        /* Ensure save/action buttons keep their gradient color in embed */
        .of-save-btn {
            background: linear-gradient(135deg,#f39c12,#e67e22) !important;
            color: #fff !important;
            border: none !important;
        }
    </style>
</head>
<body>
    <main style="padding: 0;">
        {{ $slot }}
    </main>

    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>
    <script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>

    <script src="{{ asset('js/plugins/dataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/dataTables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/select2.full.min.js') }}"></script>

    <script src="{{ asset('js/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('js/data_parsley/parsley.js') }}"></script>
    <script src="{{ asset('js/data_parsley/i18n/es.js') }}"></script>

    <script src="{{ asset('js/inspinia.js') }}"></script>

    @stack('scripts')
    @livewireScripts
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
