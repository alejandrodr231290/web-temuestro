$(document).ready(function () {

  $('#buttoneditar').prop('disabled', true);
  //mostrar tabla
  var tabla = $("#tabla").dataTable();
  cargartabla();
  $('#select-unidad').on('change', (event) => {
    cargartabla();
  });


  function cargartabla() {
    var idunidad = $("#select-unidad :selected").val();
    $('#select-unidad').attr('disabled', 'disabled');

    $("#tabla").dataTable().fnDestroy(); //destruyo para si habia algo
    tabla = $('#tabla').DataTable({  //tiene q ser con mayuscula


      responsive: true,
      pageLength: 12,
      //dom: 'Bfrtip',
      // dom: 'lBftip',
      dom: 'r',
      ajax: {
        url: '/unidades/get',
        data: { 'id': idunidad },
        method: 'POST',
        dataSrc: 'planes',
        timeout: 5000,
        error: function (request, status, err) {
          var msg = 'Servidor no encontrado!!</div>'
          if (status == "timeout") {
            var msg = "La petición demoró más de lo permitido";
          }
          $('.dataTables_empty').html('<label class="text-danger">' + msg + '</label>');

          $.alert({
            title: 'Error',
            type: 'red',
            animateFromElement: false,
            animation: 'zoom',
            content: msg,
            icon: 'bi bi-info-circle',
            autoClose: 'cancelAction|5000',
            buttons: {
              cancelAction: {
                text: 'Aceptar',
                btnClass: 'btn-red',
                action: function () {

                }
              }
            }
          });
        }
      },
      columns: [
        { data: 'id' },
        { data: 'mes' },
        { data: 'servicio' },
        { data: 'venta' },
        { data: 'total' },
      ],
      columnDefs: [  //columna id
        {
          target: 0,
          visible: false,
          searchable: false,
        },
        {
          target: 2,
          render: $.fn.dataTable.render.number(',', '.', 0, '$', '')
        },
        {
          target: 3,
          render: $.fn.dataTable.render.number(',', '.', 0, '$', '')
        },
        {
          target: 4,
          render: $.fn.dataTable.render.number(',', '.', 0, '$', '')
        },
        {
          targets: "_all",
          orderable: false,
        },
      ],

      initComplete: function () {
        $('#select-unidad').removeAttr('disabled');
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

  $('#tabla tbody').on('click', 'tr', function () {   //para seleccionar un elemento ,de uno e uno
    var vacia = $(this).find(".dataTables_empty").length == 1;  //si tabla esta vacia en busqueda
    if (!vacia) {  //si tiene elementos
      if ($(this).hasClass('selected')) {
        $(this).removeClass('selected');
        $('#buttoneditar').prop('disabled', true);
      } else {
        tabla.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        $('#buttoneditar').prop('disabled', false);
      }
    }

  });

  //edtformulario
  $('#edtformulario').validate({
    rules: {
      edtservicio: {
        required: true,
        pattern: /^[0-9]+$/,
        maxlength: 9,
      },
      edtventa: {
        required: true,
        pattern: /^[0-9]+$/,
        maxlength: 9,
      },

    },
    messages: {
      edtservicio: {

        pattern: 'Formato inválido',
      },
      edtventa: {

        pattern: 'Formato inválido',
      },

    },
    errorElement: "em",
    errorPlacement: function (error, element) {
      // agredo `invalid-feedback` class del error 
      error.addClass("invalid-feedback mb3"); //espacio margin buton(abajo) mb3
      error.insertAfter(element);

    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    }

  });

  //mostrar modal editar ,cargar antes usr por id
  $('#buttoneditar').click(function () {  //cuando seleccion el editbutton 
    if (tabla.rows('.selected').count() == 1) {  //si hay algo seleccionado
      var rowindex = tabla.row('.selected').index();
      var id = tabla.cell(rowindex, 0).data();  //elemento de coumna id q no se muestra
      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/planes/get",
            data: { id: id },
            dataType: 'json',
            method: 'POST'
          }).done(function (response) {
            //cieroo
            self.close();
            if (response.code == 200) {  //
              //mustra y carga form edit
              $("#edtmodal").modal("show");
              //reset form
              $("#edtformulario")[0].reset();  //rese values form
              $("#edtformulario").find(".form-control").removeClass("is-valid is-invalid");

              $('#edtventa').val(response.venta);
              $("#edtservicio").val(response.servicio);
              $("#modalLabel").text('Editar Plan de ' + response.mes + '' + '');
              //chequeo form
              $('#edtformulario').valid();
            } else {
              $.alert({
                title: 'Error',
                type: 'red',
                animateFromElement: false,
                content: '<div>' + response.mensaje + '</div>',  //muesro mensaje de error
                icon: 'bi bi-info-circle',
                animation: 'scale',
                autoClose: 'cancelAction|5000',
                buttons: {
                  cancelAction: {
                    text: 'Aceptar',
                    btnClass: 'btn-red'
                  }
                }
              });
            }
          }).fail(function () {
            self.close();
            $.alert({
              title: 'Error',
              type: 'red',
              animateFromElement: false,
              content: '<div>Servidor no encontrado!!</div>',
              icon: 'bi bi-info-circle',
              animation: 'scale',
              autoClose: 'cancelAction|5000',
              buttons: {
                cancelAction: {
                  text: 'Aceptar',
                  btnClass: 'btn-red',
                  action: function () {

                  }
                }
              }
            });
          });
        }, onAction: function () { },
        buttons: {
          text: 'Aceptar',
          icon: 'bi bi-info-circle',
          btnClass: 'btn-blue',
          aceptar: function () {
          }
        }
      });

    }
  });
  //boton para agregar en addmodal
  $('#edtbuttonform').click(function () {
    if ($('#edtformulario').valid()) {  //si todook form
      $("#edtmodal").modal("hide");
      var rowindex = tabla.row('.selected').index();
      var id = tabla.cell(rowindex, 0).data();  //elemento de coumna id q no se muestra
      var venta = $('#edtventa').val();
      var servicio = $('#edtservicio').val();

      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/planes/edt",
            data: { venta: venta, servicio: servicio, id: id },
            dataType: 'json',
            method: 'POST'
          }).done(function (response) {
            //cieroo
            self.close();
            if (response.code == 200) {  //todook, elimino la linea en la tabla
              //recargo
              cargartabla();
              $.alert({
                title: 'Editar',
                type: 'green', //orange //blue
                content: response.mensaje,  //muesro mensaje 
                icon: 'bi bi-info-circle',
                animation: 'scale',
                autoClose: 'cancelAction|3000',
                buttons: {
                  cancelAction: {
                    text: 'Aceptar',
                    btnClass: 'btn-green',
                    action: function () {

                    }
                  }
                }
              });
            } else {
              $.alert({
                title: 'Error',
                type: 'red',
                animateFromElement: false, //orange //blue
                content: '<div>' + response.mensaje + '</div>',  //muesro mensaje de error
                icon: 'bi bi-info-circle',
                animation: 'scale',
                autoClose: 'cancelAction|3000',
                buttons: {
                  cancelAction: {
                    text: 'Aceptar',
                    btnClass: 'btn-red',
                    action: function () {
                      $("#edtmodal").modal("show"); //cuando hay error mantengo el model
                    }
                  }
                }

              });
            }
          }).fail(function () {
            self.close();
            $.alert({
              title: 'Error',
              type: 'red',
              animateFromElement: false,
              content: '<div>Servidor no encontrado!!</div>',
              icon: 'bi bi-info-circle',
              animation: 'scale',
              autoClose: 'cancelAction|5000',
              buttons: {
                cancelAction: {
                  text: 'Aceptar',
                  btnClass: 'btn-red',
                  action: function () {
                    $("#edtmodal").modal("show"); //cuando hay error mantengo el model
                  }
                }
              }
            });
          });
        },
        onAction: function () { },
        buttons: {
          text: 'Aceptar',
          icon: 'bi bi-info-circle',
          btnClass: 'btn-blue',
          aceptar: function () {

          }
        }
      });
    }

  });






});
