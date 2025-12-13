
var idFactura = document.getElementById('idFactura').value;
$( document ).ready(function() {

       $('#tbl_productos_factura').DataTable({
           "order": [0, 'desc'],
           "language": {
               "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
           },
           pageLength: 10,
           responsive: true,
           dom: '<"html5buttons"B>lTfgitp',
           buttons: [{
                   extend: 'copy'
               },
               {
                   extend: 'csv'
               },
               {
                   extend: 'excel',
                   title: 'ExampleFile'
               },
               {
                   extend: 'pdf',
                   title: 'ExampleFile'
               },

               {
                   extend: 'print',
                   customize: function(win) {
                       $(win.document.body).addClass('white-bg');
                       $(win.document.body).css('font-size', '10px');

                       $(win.document.body).find('table')
                           .addClass('compact')
                           .css('font-size', 'inherit');
                   }
               }
           ],
           "ajax": "/desglose/productos/"+idFactura,
           "columns": [
               {
                   data: 'idFactura'
               },
               {
                   data: 'numero_factura'
               },
               {
                   data: 'idProducto'
               },
               {
                   data: 'producto'
               },
               {
                   data: 'precio_base'
               },
               {
                   data: 'ultimo_costo_compra'
               },
               {
                   data: 'unidad_venta'
               },
               {
                   data: 'cantidad'
               },
               {
                   data: 'precio_unidad'
               },
               {
                   data: 'gananciaUnidad'
               },
               {
                   data: 'gananciatotal'
               },
               {
                   data: 'total'
               },
               {
                   data: 'sub_total'
               },
               {
                   data: 'isv'
               },
               {
                   data: 'seccion_id'
               },
               {
                   data: 'seccion'
               },
               {
                   data: 'bodega'
               },
               {
                   data: 'estadoComisionado'
               }

           ]


       });
});

   $(document).on('submit', '#comisionForm', function(event) {
       event.preventDefault();
       guardarComision();
   });

   function guardarComision() {

           $('#modal_comision_crear').modal('hide');
           $('#modalSpinnerLoading').modal('show');

           var data = new FormData($('#comisionForm').get(0));
           console.log(data);
           axios.post("/comision/guardar", data)
               .then(response => {



                   document.getElementById("comisionForm").reset();

                  // $('#tbl_techos_guardados').DataTable().ajax.reload();

                  $('#modalSpinnerLoading').modal('hide');
                   Swal.fire({
                       icon: 'success',
                       title: 'Exito!',
                       text: "Asignado y guardado con Éxito."
                   });


                   window.location.href = "/comisiones/historico";

               })
               .catch(err => {
                   let data = err.response.data;
                   $('#modalSpinnerLoading').modal('hide');

                   document.getElementById("comisionForm").reset();
                   Swal.fire({
                       icon: data.icon,
                       title: data.title,
                       text: data.text
                   })
                   console.error(err);

               });
   }

