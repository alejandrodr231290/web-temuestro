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
      pageLength: 10,
      //dom: 'Bfrtip',
      // dom: 'lBftip',
      dom: 'r',
      ajax: {
        url: '/conexiones/get',
        data: { 'unidad': idunidad },
        method: 'POST',
        dataSrc: 'conexiones',
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
        { data: 'tipo' },
        { data: 'sistema' },
        { data: 'host' },
        { data: 'instancia' },
        { data: 'db' },
        { data: 'usuario' },
        { data: 'contrasna' },
        { data: 'almacenes' },

      ],
      columnDefs: [  //columna id
        {
          target: 0,
          visible: false,
          searchable: false,
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
      edthost: {
        required: true,
      },
      edtinstancia: {
        required: false,
      },
      edtbd: {
        required: true,

      },
      edtusuario: {
        required: true,

      },
      edtcontrasena: {
        required: true,

      },

    },
    messages: {
      edthost: {

      },
      edtbd: {

      },

      edtusuario: {

      },

      edtcontrasena: {

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
            url: "/conexiones/get",
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

              $("#modalLabel").text('Editar Conexión de ' + response.tipo + '' + '');

              $('#edtsistema').val(response.sistema);
              $('#edthost').val(response.host);
              $('#edtinstancia').val(response.instancia);
              $('#edtbd').val(response.db);
              $('#edtusuario').val(response.usuario);
              $('#edtcontrasena').val(response.contrasna);


              var almacenes = response.almacenes
              $("#edtalmacenes").empty();
              var len = almacenes.length;
              for (var i = 0; i < len; i++) {
                $('#edtalmacenes').append($("<option>", {
                  value: almacenes[i].id,
                  text: almacenes[i].nombre,
                }));
                if (almacenes[i].seleccionado) {
                  $('#edtalmacenes option[value="' + almacenes[i].id + '"]').prop("selected", true).trigger("change");

                }
              }


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

      var sistema = $('#edtsistema').val();
      var host = $('#edthost').val();
      var instancia = $('#edtinstancia').val();
      var bd = $('#edtbd').val();
      var usuario = $('#edtusuario').val();
      var contrasena = $('#edtcontrasena').val();
      //  var contrasena = $('#edtalmacenes').val();

      var almacenes = [];

      $("#edtalmacenes option").each(function () {
        var almacen = [];
        almacen.push($(this).attr('value'), $(this).text(), $(this).is(':selected'));
        almacenes.push(almacen);
        //  alert('opcion ' + $(this).text() + ' valor ' + $(this).attr('value') + '   ' + $(this).is(':selected'));
      });


      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/conexiones/edt",
            data: { id: id, sistema: sistema, host: host, instancia: instancia, bd: bd, usuario: usuario, contrasena: contrasena, almacenes: JSON.stringify(almacenes) },
            // contentType: 'application/json; charset=utf-8',
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




  $('#checkconexion').click(function () {


    var host = $('#edthost').val();
    var instancia = $('#edtinstancia').val();
    var bd = $('#edtbd').val();
    var usuario = $('#edtusuario').val();
    var contrasena = $('#edtcontrasena').val();

    $.confirm({
      title: false,
      content: function () {
        var self = this;
        return $.ajax({
          url: "/conexiones/check",
          data: { host: host, instancia: instancia, bd: bd, usuario: usuario, contrasena: contrasena },
          dataType: 'json',
          method: 'POST',
          timeout: 5000,
        }).done(function (response) {
          //cieroo
          self.close();
          if (response.code == 200) {  //todook, elimino la linea en la tabla
            //recargo
            $.alert({
              title: 'Conexión',
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
        }).fail(function (request, status, err) {

          if (status == "timeout") {
            var msg = "La petición demoró más de lo permitido";
          } else {
            if (request.mensaje != null) {
              var msg = request.mensaje;
            } else {
              var msg = 'Servidor no encontrado!!';

            }


          }
          self.close();
          $.alert({
            title: 'Error',
            type: 'red',
            animateFromElement: false,
            content: msg,
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


  });


  $('#checkalmacenes').click(function () {


    var host = $('#edthost').val();
    var instancia = $('#edtinstancia').val();
    var bd = $('#edtbd').val();
    var usuario = $('#edtusuario').val();
    var contrasena = $('#edtcontrasena').val();
    $('#edtalmacenes').attr('disabled', 'disabled');
    $.confirm({
      title: false,
      content: function () {
        var self = this;
        return $.ajax({
          url: "/conexiones/check",
          data: { host: host, instancia: instancia, bd: bd, usuario: usuario, contrasena: contrasena, almacenes: true },
          dataType: 'json',
          method: 'POST',
          timeout: 3000,
        }).done(function (response) {
          //cieroo
          self.close();
          $('#edtalmacenes').removeAttr('disabled');
          if (response.code == 200) {

            var almacenes = response.almacenes
            $("#edtalmacenes").empty();
            var len = almacenes.length;
            for (var i = 0; i < len; i++) {
              $('#edtalmacenes').append($("<option>", {
                value: almacenes[i].id,
                text: almacenes[i].nombre
              }));
            }

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
        }).fail(function (request, status, err) {
          var msg = 'Servidor no encontrado!!';
          if (status == "timeout") {
            var msg = "La petición demoró más de lo permitido";
          }
          $('#edtalmacenes').removeAttr('disabled');
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


  });


});
