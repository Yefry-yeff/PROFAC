listaParametroComision();


function listaParametroComision() {
  $('#tbl_comisiones_empleado').DataTable({
    destroy: true,
    order: [0, 'desc'],
    language: { "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json" },
    pageLength: 5,
    responsive: true,
    deferRender: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excel',
                title: 'mis_comisiones'
            }
        ],
    ajax: {
      url: "/listar/empleado/comision",
      dataSrc: 'data',
      error: function () {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la tabla.' });
      }
    },
    columns: [
        { data: 'user_id' },
        { data: 'nombre_empleado' },
        { data: 'anio' },
        { data: 'mes_letra' },
        { data: 'comision_acumulada' },
        { data: 'cantidad_facturas' },
        { data: 'ultima_actualizacion' }
    ]
  });
}
