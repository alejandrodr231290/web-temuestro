$(document).ready(function () {

    var date = new Date();
    var primerDia = new Date(date.getFullYear(), date.getMonth(), 1);
    var ultimoDia = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    var minimodia = new Date(date.getFullYear() - 2, 1, 1);

    const elem = document.getElementById('foo');
    const rangepicker = new DateRangePicker(elem, {
        buttonClass: 'btn',
        language: 'es',
        autohide: true,
        format: 'dd-mm-yyyy',
        clearBtn: false,
        todayHighlight: true,
        minDate: minimodia,
        maxDate: ultimoDia,
    });
    rangepicker.setDates(primerDia, ultimoDia);

    const desde = document.getElementById('desde');
    const hasta = document.getElementById('hasta');
    desde.addEventListener('changeDate', function (e) {
        cargartabla();
    });

    hasta.addEventListener('changeDate', function (e) {
        cargartabla();
    });
    var tabla = $("#tabla").dataTable();
    tabla.on('search.dt', function () {
        var sum = tabla.api().column(4, { search: 'applied' }).data().sum();
        $('#total').text($.number(sum, 2, '.', ','));
    });
    cargartabla();
    function cargartablavacia() {

        $('#total').text('-');

        $("#tabla").dataTable().fnDestroy();
        tabla = $('#tabla').dataTable({
            language: {
                "decimal": "",
                "emptyTable": "La tabla está vacía",
                "info": "Mostrando _START_ - _END_ de _TOTAL_ entradas",
                "infoEmpty": " 0 hasta 0 of 0 entradas",
                "infoFiltered": "(Flitrado de _MAX_ total)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrando  _MENU_  entradas",
                "loadingRecords": '<i class="fas fa-spinner fa-pulse"></i>',
                "processing": "",
                "search": "Buscar:",
                "zeroRecords": "La tabla está vacía",
                "paginate": {
                    "first": "Primera",
                    "last": "Última",
                    "next": ">>",
                    "previous": "<<"
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }

            },

        });

    }

    $('#select-unidad').on('change', (event) => {
        cargartabla();
    });


    function cargartabla() {
        $('#select-unidad').attr('disabled', 'disabled');
        $('#start').attr('disabled', 'disabled');
        $('#end').attr('disabled', 'disabled');
        $("#tabla").dataTable().fnDestroy();
        $('#total').text('-');
        var idunidad = $("#select-unidad :selected").val();
        var dates = rangepicker.getDates('yyyy-mm-dd');
        tabla = $('#tabla').dataTable({
            responsive: true,
            // dom: 'lBftip',
            //  dom: 'lBrtip<"bottom"f>',
            //  dom: '<"top"Bf>rt<"bottom"lp><"clear">',
            lengthMenu: [
                [10, 25, -1],
                [10, 25, 'Todos']
            ],
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ],
            ajax: {
                url: "/recepciones/get",
                type: "GET",
                data: { id: idunidad, desde: dates[0], hasta: dates[1] },
                contentType: "application/json",
                dataSrc: 'recepciones',
                timeout: 5000,
                error: function (request, status, err) {
                    var msg = 'Servidor no encontrado!!</div>'
                    if (status == "timeout") {
                        var msg = "La petición demoró más de lo permitido";
                    }
                    $('.dataTables_empty').html('<label class="text-danger">' + msg + '</label>');
                    // //cargartablavacia();
                    $('#select-unidad').removeAttr('disabled');
                    $('#start').removeAttr('disabled');
                    $('#end').removeAttr('disabled');
                    $('#total').text('-');
                }
            },
            columns: [
                { data: 'proveedor' },
                { data: 'destino' },
                { data: 'fecha' },
                { data: 'estado' },
                { data: "total", render: $.fn.dataTable.render.number(',', '.', 2, '$') }
            ],
            order: [[2, "desc"]],
            columnDefs: [
                { width: 20, targets: [2, 3, 4,] },
            ],
            initComplete: function () {
                $('#select-unidad').removeAttr('disabled');
                $('#start').removeAttr('disabled');
                $('#end').removeAttr('disabled');
                var sum = this.api().column(4).data().sum();
                $('#total').text($.number(sum, 2, '.', ','));

            },
            language: {
                "decimal": "",
                "emptyTable": "La tabla está vacía",
                "info": "Mostrando _START_ - _END_ de _TOTAL_ entradas",
                "infoEmpty": " 0 hasta 0 of 0 entradas",
                "infoFiltered": "(Flitrado de _MAX_ total)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrando  _MENU_  entradas",
                "loadingRecords": '<i class="fas fa-spinner fa-pulse"></i>',
                "processing": "",
                "search": "Buscar:",
                "zeroRecords": "La tabla está vacía",
                "paginate": {
                    "first": "Primera",
                    "last": "Última",
                    "next": ">>",
                    "previous": "<<"
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }

            },
        });
    };


});