<div>
    @push('styles')
        <link href="{{ asset('css/plugins/switchery/switchery.css') }}" rel="stylesheet">

        <style>
            .apexcharts-canvas: {
                background: '#6C4034'
            }
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Configuración</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a>Parametros de venta</a>
                        </li>


                    </ol>
                </div>
                <div>
                    <button onclick="actualizarDatos()" class="btn btn-primary mt-3"><i class="fa-solid fa-arrow-rotate-right"></i> Actualizar datos</button>
                </div>

            </div>


        </div>
    </div>

    @if(Auth::user()->id==3 || Auth::user()->id==2 )
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Metodo Switch <i class="fa-solid fa-laptop-file"></i></h3>
                    </div>
                    <div class="ibox-content ">
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6 d-flex align-items-center">
                                <h4>Definir estado:</h4>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6 d-flex align-items-center">
                                <div>
                                    <input id="btn_check" name="btn_check" type="checkbox" class="js-switch"
                                        onchange="estadoBoton()"  />
                                    <p id="estadoBoton">Encendido</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Datos de venta <i class="fa-solid fa-cart-shopping"></i></h3>
                    </div>
                    <div class="ibox-content ">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 ">
                                <div id="chartCompras" class="apexcharts-canvas" style="min-height: 365px;">
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 mt-4">
                                <div id="ventasMesAnterior" class="apexcharts-canvas" style="min-height: 365px;">
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 mt-4">
                                <div id="ventasMesActual" class="apexcharts-canvas" style="min-height: 365px;">
                                </div>
                            </div>

                        </div>
                        <div class="d-flex justify-content-between">
                            <h4>Cantidad otorgada: <span id="1"></span></h4>
                            <h4>Cantidad D/C: <span id="2"></span></h4>
                            <h4>Cantidad N/D: <span id="3"></span></h4>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
        <script src="{{ asset('js/plugins/switchery/switchery.js') }}"></script>
        <script>


            $( function(){
                convertirSwitechs();
                getData();

                obtenerDatosCompra();
                graficoVentaMesActaul();
                graficoVentaMesAnterior();

            });

            function actualizarDatos(){

                this.obtenerDatosCompra();
                this.graficoVentaMesActaul();
                this.graficoVentaMesAnterior();
            }


            //['#FEBC3B','#26E7A6']
            function getData(){
                axios.get('/configuracion/datos')
                .then( response =>{
                    let datos = response.data.datos;


                    if(datos.st == 1){
                        let element=document.getElementById('btn_check');
                        element.click()
                    }

                let clickCheckbox = document.querySelector('.js-switch')
                let text = document.getElementById('estadoBoton');

                if (clickCheckbox.checked) {
                    text.innerHTML = 'Encendido'
                } else {
                    text.innerHTML = 'Apagado'
                }



                })
                .catch(err=>{
                    console.log(err);
                })
            }

            function obtenerDatosCompra(){

                axios.get('/configuracion/datos/compra')
                .then( response=>{

                   let datosCompra = response.data.arrayTotales;
                   let categoriasCompra = response.data.arrayMes;
                   let datos = response.data.datosCai;

                    let colors = ['#FEBC3B', '#26E7A6'];
                let options = {
                    series: [{
                        name: 'Compra en Lps',
                        data: datosCompra
                    }],
                    chart: {
                        background: '#F3F3F3',
                        height: 350,
                        type: 'bar',
                        events: {
                            click: function(chart, w, e) {
                                // console.log(chart, w, e)
                            }
                        }
                    },
                    colors: colors,
                    plotOptions: {
                        bar: {
                            columnWidth: '25%',
                            borderRadius: 10,

                            distributed: true,
                            dataLabels: {
                                position: 'top', // top, center, bottom
                            },
                        }
                    },

                    legend: {
                        show: false
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            let valor = new Intl.NumberFormat('es-HN').format(val)
                            return valor + " Lps";
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },
                    xaxis: {
                        categories: categoriasCompra,
                        labels: {
                            style: {
                                colors: colors,
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        axisBorder: {
                            show: true
                        },
                        axisTicks: {
                            show: true,
                        },

                        labels: {
                            show: true,
                            formatter: function(val) {

                                return val + " Lps";
                            }
                        }

                    },
                    title: {
                        text: 'Comparación de compras',
                        floating: false,

                        align: 'center',
                        margin:10,
                        style: {
                            color: '#444'
                        }
                    }
                };


                var chart = new ApexCharts(document.querySelector("#chartCompras"), options);
                chart.render();

                document.getElementById('1').innerHTML=datos.cantidad_otorgada;
                document.getElementById('2').innerHTML=datos.numero_actual;
                document.getElementById('3').innerHTML=datos.serie;

                })
                .catch( err=>{
                    console.log(err.response.data)
                })
            }



            function graficoVentaMesAnterior() {

                axios.get('/datos/mes/anterior')
                .then(response=>{

                    let datosMesAnterior = response.data.arrayTotales;
                    let categoriasMesAnterior = ['D/C', 'N/D'];
                    let titulo = 'Total de ventas en el mes de '+response.data.nombreMes;

                    let colors = ['#26A0FC', '#FF6178'];
                let options = {
                    series: [{
                        name: 'Compra en Lps',
                        data: datosMesAnterior,
                    }],
                    chart: {
                        background: '#F3F3F3',
                        height: 350,
                        type: 'bar',
                        events: {
                            click: function(chart, w, e) {
                                // console.log(chart, w, e)
                            }
                        }
                    },
                    colors: colors,
                    plotOptions: {
                        bar: {
                            columnWidth: '30%',
                            distributed: true,
                            borderRadius: 10,
                            dataLabels: {
                                position: 'top', // top, center, bottom
                            },
                        }
                    },

                    legend: {
                        show: false
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            let valor = new Intl.NumberFormat('es-HN').format(val)
                            return valor + " Lps";
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },
                    xaxis: {
                        categories: categoriasMesAnterior,
                        labels: {
                            style: {
                                colors: colors,
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        axisBorder: {
                            show: true
                        },
                        axisTicks: {
                            show: true,
                        },

                        labels: {
                            show: true,
                            formatter: function(val) {
                                return val + " Lps";
                            }
                        }

                    },
                    title: {
                        text:titulo ,
                        floating: true,

                        align: 'center',
                        style: {
                            color: '#444'
                        }
                    }
                };


                var chart = new ApexCharts(document.querySelector("#ventasMesAnterior"), options);
                chart.render();
                })
                .catch( err=>{
                    console.log(err.response.data)
                })


            }


            function graficoVentaMesActaul() {

                axios.get('/datos/mes/actual')
                .then(response=>{

                    let datosMesActual = response.data.arrayTotales;
                    let mesNombre = 'Total de ventas en el mes de '+response.data.nombreMes;
                    let categoriasMesActual = ['D/C', 'N/D'];

                    let colors = ['#26A0FC', '#FF6178'];
                let options = {
                    series: [{
                        name: 'Compra en Lps',
                        data: datosMesActual,
                    }],
                    chart: {
                        background: '#F3F3F3',
                        height: 350,
                        type: 'bar',
                        events: {
                            click: function(chart, w, e) {
                                // console.log(chart, w, e)
                            }
                        }
                    },
                    colors: colors,
                    plotOptions: {
                        bar: {
                            columnWidth: '30%',
                            borderRadius: 10,
                            distributed: true,
                            dataLabels: {
                                position: 'top', // top, center, bottom
                            },
                        }
                    },

                    legend: {
                        show: false
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            let valor = new Intl.NumberFormat('es-HN').format(val)
                            return valor + " Lps";
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },
                    xaxis: {
                        categories: categoriasMesActual,
                        labels: {
                            style: {
                                colors: colors,
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        axisBorder: {
                            show: true
                        },
                        axisTicks: {
                            show: true,
                        },

                        labels: {
                            show: true,
                            formatter: function(val) {
                                return val + " Lps";
                            }
                        }

                    },
                    title: {
                        text: mesNombre,
                        floating: true,

                        align: 'center',
                        style: {
                            color: '#444'
                        }
                    }
                };


                var chart = new ApexCharts(document.querySelector("#ventasMesActual"), options);
                chart.render();


                })
                .catch( err=>{
                    console.log(err.response.data);
                })


            }




            function convertirSwitechs() {

                //     var elem = document.querySelector('.js-switch');
                // var switchery = new Switchery(elem, { color: '#1AB394',size: 'small' });

                var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));

                elems.forEach(function(html) {
                    var switchery = new Switchery(html, {
                        color: '#1AB394',
                        size: 'default'
                    });
                });
            }

            function estadoBoton() {

                let clickCheckbox = document.querySelector('.js-switch')
                let estado = 0;

                if (clickCheckbox.checked) {
                 estado=1;
                } else {
                 estado =0;
                }

                document.querySelector('.js-switch').addEventListener('click', function() {
                switchery.disable();
                });

                axios.get('/editar/configuracion/'+estado)
                .then( response=>{
                    document.querySelector('.js-switch').addEventListener('click', function() {
                    switchery.enable();
                    });
                })
                .catch(err=>{
                    console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: "Ha ocurrido un error"
                        })
                })



            }
        </script>
    @endpush
</div>
<?php
    date_default_timezone_set('America/Tegucigalpa');
    $act_fecha=date("Y-m-d");
    $act_hora=date("H:i:s");
    $mes=date("m");
    $year=date("Y");
    $datetim=$act_fecha." ".$act_hora;
?>
<script>
    function mostrarHora() {
        var fecha = new Date(); // Obtener la fecha y hora actual
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();

        // A単adir un 0 delante si los minutos o segundos son menores a 10
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;

        // Mostrar la hora actual en el elemento con el id "reloj"
        document.getElementById("reloj").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    // Actualizar el reloj cada segundo
    setInterval(mostrarHora, 1000);
</script>
<div class="float-right">
    <?php echo "$act_fecha";  ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year";  ?>
</div>
<p id="reloj"></p>
