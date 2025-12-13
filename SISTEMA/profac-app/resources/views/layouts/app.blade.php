<!DOCTYPE html>
<html lang="es" >

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'D. VALENCIA') }}</title>
    <link rel="icon" type="image/x-icon" href="/img/valencia-fondo-transparente.png">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @livewireStyles
    @stack('styles')<!--Por esta ranura se cargan los estilos de las paginas individuales-->
    <!-- Styles -->

    <!-- ApexChart -->

    


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
  

    <!--font awesome-->
    <link href="{{ asset('font-awesome/css/font-awesome.css') }}" rel="stylesheet">

    <!-- Toastr style -->
    <link href="{{ asset('css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

    <!-- Gritter -->
    <link href="{{ asset('js/plugins/gritter/jquery.gritter.css') }}" rel="stylesheet">
    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">


    <!-- Fonts -->
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap"> -->

    <!--Data-parsley-validate-->
    <link href="{{ asset('css/plugins/parsley/parsley.css') }}" rel="stylesheet">
    
    <!--Datatable-->
    <link href="{{ asset('css/plugins/dataTables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <!--select2-->
    <link href="{{ asset('css/plugins/select2/select2.min.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>



</head>

<body>
    {{-- <x-jet-banner /> --}}



    <!--Menu lateral-->
    
   
        @livewire('navigation-menu')
  
  

    



        <div id="wrapper" class="" >

            <!-- Page Content -->
            <div id="page-wrapper" class="gray-bg" style="margin-top:65px">

                <div class="wrapper wrapper-content animated fadeInRight">

                    <main  >{{-- <img src="{{ asset('img/LOGO_VALENCIA.jpg') }}" alt=""> --}}
                        {{ $slot }}

                    </main>

                </div>

            </div>


        </div>




    <!-- Mainly scripts -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    {{-- <script src="{{ asset('js/jquery-3.1.1.min.js') }}"></script> version original de la plantilla --}}
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>
    <script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
    <script src="{{ asset('js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>


    <!-- Flot -->
    <script src="{{ asset('js/plugins/flot/jquery.flot.js') }}"></script>
    <script src="{{ asset('js/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flot/jquery.flot.spline.js') }}"></script>
    <script src="{{ asset('js/plugins/flot/jquery.flot.resize.js') }}"></script>
    <script src="{{ asset('js/plugins/flot/jquery.flot.pie.js') }}"></script>

    <!-- Peity -->
    <script src="{{ asset('js/plugins/peity/jquery.peity.min.js') }}"></script>
    <script src="{{ asset('js/demo/peity-demo.js') }}"></script>

    <!-- Custom and plugin javascript -->
    <script src="{{ asset('js/inspinia.js') }}"></script>
    <script src="{{ asset('js/plugins/pace/pace.min.js') }}"></script>

    <!-- jQuery UI -->
    <script src="{{ asset('js/plugins/jquery-ui/jquery-ui.min.js') }}"></script>

    <!-- GITTER -->
    <script src="{{ asset('js/plugins/gritter/jquery.gritter.min.js') }}"></script>

    <!-- Sparkline -->
    <script src="{{ asset('js/plugins/sparkline/jquery.sparkline.min.js') }}"></script>

    <!-- Sparkline demo data  -->
    <script src="{{ asset('js/demo/sparkline-demo.js') }}"></script>

    <!-- ChartJS-->
    <script src="{{ asset('js/plugins/chartJs/Chart.min.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ asset('js/plugins/toastr/toastr.min.js') }}"></script>


    <!--Data-parsley-validate-->

    <script src="{{ asset('js/data_parsley/parsley.js') }}"></script>
    <script src="{{ asset('js/data_parsley/i18n/es.js') }}"></script>

    <!-- Datatable JS -->

    <script src="{{ asset('js/plugins/dataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/dataTables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/plugins/dataTables/sum.js') }}"></script>

    <!--select2-->
    <script src="{{ asset('js/plugins/select2/select2.full.min.js') }}"></script>


    <!--ApexChart-->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>

    </script>




    @stack('scripts')
    @stack('modals')
    @livewireScripts








</body>

</html>

