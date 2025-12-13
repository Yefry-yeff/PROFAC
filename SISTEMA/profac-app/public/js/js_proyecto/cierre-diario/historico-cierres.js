
tblHistorico();
function tblHistorico(){
   $("#tbl_bitacoracierre").dataTable().fnDestroy();
   $('#tbl_bitacoracierre').DataTable({
       "paging": true,
       "language": {
           "url": "//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"
       },
       pageLength: 10,
       responsive: true,
       dom: '<"html5buttons"B>lTfgitp',
       buttons: [

           {
               extend: 'excel',
               title: 'Facuracion_dia',
               className:'btn btn-success'
           }
       ],
       "ajax": "/cargar/historico",
       "columns": [

           {
               data: 'id'
           },
           {
               data: 'fechaCierre'
           },
           {
               data: 'user_cierre_id'
           },
           {
               data: 'comentario'
           },
           {
               data: 'estado_cierre',
               render: function (data, type, row) {


                   if(data === 1){
                       return "<span class='badge badge-primary'>CERRADO</span>";
                   }


               }
           },

           {
               data: 'totalContado'
           },
           {
               data: 'totalCredito'
           },
           {
               data: 'totalAnulado'
           },
           {
               data: 'created_at'
           },
           {
               data: 'acciones'
           }
       ],initComplete: function () {
           var r = $('#tbl_bitacoracierre tfoot tr');
           r.find('th').each(function(){
             $(this).css('padding', 8);
           });
           $('#tbl_bitacoracierre thead').append(r);
           $('#search_0').css('text-align', 'center');
           this.api()
               .columns()
               .every(function () {
                   let column = this;
                   let title = column.footer().textContent;

                   // Create input element
                   let input = document.createElement('input');
                   input.placeholder = title;
                   column.footer().replaceChildren(input);

                   // Event listener for user input
                   input.addEventListener('keyup', () => {
                       if (column.search() !== this.value) {
                           column.search(input.value).draw();
                       }
                   });
               });




       }


   });
}
