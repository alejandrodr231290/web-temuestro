$(document).ready(function () {
    var carga = 0;
    cargartablaventas();
    cargartablaservicios();
   
    $('#select-mes').on('change', (event) => {
        carga = 0;
        cargartablaventas();
        cargartablaservicios();

    });


    function cargartablaventas() {
        $('#select-mes').attr('disabled', 'disabled');
        var mes = $("#select-mes :selected").val();
        $('#tablaventas').dataTable({
            responsive: true,
            // dom: 'lBftip',
            dom: 'r',

            ajax: {
                url: "/panel/getventas",
                type: "GET",
                data: { mes: mes },
                contentType: "application/json",
                dataSrc: 'ventas',
                timeout: 10000,
                error: function (request, status, err) {
                    var msg = 'Servidor no encontrado!!</div>'
                    if (status == "timeout") {
                        var msg = "La petición demoró más de lo permitido";
                    }
                    $('.dataTables_empty').html('<label class="text-danger">' + msg + '</label>');
                }
            },

            columns: [

                { data: 'unidad' },
                { data: "plan", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
                { data: "real", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
                {
                    data: 'porciento',
                    render: function (data, type, row, meta) {

                        if (type === 'display') {
                            var p = data;
                            var bgsuccess = 'bg-primary';
                            w = p;
                            if (p >= 100) {
                                p = 100;
                                w = p;
                                var bgsuccess = 'bg-success';
                            }
                            var dat = $.number(data, 2, '.', ',');
                            if (p < 25) {
                                var dat = $.number(data, 0, '.', ',');

                            }

                            if (data == 0) {  //es cero
                                var bgsuccess = 'bg-danger';
                                p = 100;
                                w = p;
                                dat = 0;
                            }

                            return '<span class="badge badgeportada text-' + bgsuccess + '">' + dat + '%</span>' +

                                '<div class="progress progressportada">' +
                                '<div class="progress-bar ' + bgsuccess + '" role="progressbar" aria-label="Example with label" style="width: ' + w + '%;" aria-valuenow="' + p + '" aria-valuemin="0" aria-valuemax="100">' + dat + '%</div>'
                                + '</div>';
                        }
                        else {
                            return data;

                        }

                    }
                },
            ],

            columnDefs: [

                { width: "20%", targets: [0, 1, 2,] },
                { width: "40%", target: 3, className: 'dt-body-center' },
                {
                    targets: "_all",
                    orderable: false,
                },
            ],

            initComplete: function () {
                fincarga();

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

    function cargartablaservicios() {
        $('#select-mes').attr('disabled', 'disabled');
        var mes = $("#select-mes :selected").val();
        $('#tablaservicios').dataTable({
            //
            responsive: true,
            // dom: 'lBftip',
            dom: 'r',

            ajax: {
                url: "/panel/getservicios",
                type: "POST",
                contentType: "application/json",
                dataSrc: 'servicios',
                data: { mes: mes },
                timeout: 10000,
                error: function (request, status, err) {
                    var msg = 'Servidor no encontrado!!</div>'
                    if (status == "timeout") {
                        var msg = "La petición demoró más de lo permitido";
                    }
                    $('.dataTables_empty').html('<label class="text-danger">' + msg + '</label>');

                }
            },

            columns: [

                { data: 'unidad' },
                { data: "plan", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
                { data: "real", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
                {
                    data: 'porciento',
                    render: function (data, type, row, meta) {

                        if (type === 'display') {
                            var p = data;
                            var bgsuccess = 'bg-primary';
                            w = p;
                            if (p >= 100) {
                                p = 100;
                                w = p;
                                var bgsuccess = 'bg-success';
                            }
                            var dat = $.number(data, 2, '.', ',');
                            if (p < 25) {
                                var dat = $.number(data, 0, '.', ',');

                            }

                            if (data == 0) {  //es cero
                                var bgsuccess = 'bg-danger';
                                p = 100;
                                w = p;
                                dat = 0;
                            }

                            return '<span class="badge badgeportada text-' + bgsuccess + '">' + dat + '%</span>' +

                                '<div class="progress progressportada">' +
                                '<div class="progress-bar ' + bgsuccess + '" role="progressbar" aria-label="Example with label" style="width: ' + w + '%;" aria-valuenow="' + p + '" aria-valuemin="0" aria-valuemax="100">' + dat + '%</div>'
                                + '</div>';
                        }
                        else {
                            return data;

                        }

                    }
                },
            ],
            paging: false,
            columnDefs: [

                { width: "20%", targets: [0, 1, 2,] },
                { width: "40%", target: 3, className: 'dt-body-center' },
                {
                    targets: "_all",
                    orderable: false,
                },
            ],

            initComplete: function () {
                fincarga();

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
    function fincarga() {
        carga++;
        if (carga == 2) {
            $('#select-mes').removeAttr('disabled');
        }
    }


});