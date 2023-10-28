$(document).ready(function () {

    var tabla = $("#tabla").dataTable();
    var tabla = $("#tabla").dataTable();
    tabla.on('search.dt', function () {
        var sum = tabla.api().column(4, { search: 'applied' }).data().sum();
        $('#total').text('$'+$.number(sum, 2, '.', ','));
    });
    cargartabla();

    function cargartablavacia() {
        $("#tabla").dataTable().fnDestroy();
        $('#tabla').dataTable({
            columnDefs: [
                {
                    target: 0,
                    visible: false,
                    searchable: false,
                },
                { width: 20, targets: [2, 3, 4] },


            ],
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


    $('#select-almacen').on('change', (event) => {
        cargartabla();
    });


    function cargartabla() {
        $('#select-almacen').attr('disabled', 'disabled');
        $("#tabla").dataTable().fnDestroy();

        var idalmacen = $("#select-almacen :selected").val();



        tabla = $('#tabla').dataTable({

            responsive: true,
            // dom: 'lBftip',
            //  dom: 'lBrtip<"bottom"f>',
            //  dom: '<"top"Bf>rt<"bottom"lp><"clear">',
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'Todos']
            ],
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ],
            ajax: {
                url: "/productos/get",
                type: "GET",
                data: { id: idalmacen },
                contentType: "application/json",
                dataSrc: 'productos',
                timeout: 5000,

                error: function (request, status, err) {
                    var msg = 'Servidor no encontrado!!</div>'
                    if (status == "timeout") {
                        var msg = "La petición demoró más de lo permitido";
                    }
                    $('.dataTables_empty').html('<label class="text-danger">' + msg + '</label>');

                    //cargartablavacia();
                    $('#select-almacen').removeAttr('disabled');
                }
            },
            columns: [
             
                { data: 'descripcion' },
                { data: 'existencias' },
                { data: 'unidad_medida' },
                { data: "precio", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                { data: "valor", render: $.fn.dataTable.render.number(',', '.', 2, '$') }
            ],
            columnDefs: [
              
                { width: 20, targets: [1,2, 3, 4] ,"className": "text-center",},


            ],
            order: [[ 0, 'asc' ], ],

            
            initComplete: function () {
                $('#select-almacen').removeAttr('disabled');
                var sum = tabla.api().column(4, { search: 'applied' }).data().sum();
                $('#total').text('$'+$.number(sum, 2, '.', ','));
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