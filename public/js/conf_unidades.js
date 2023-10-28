$(document).ready(function () {

  var tabla = $("#tabla").dataTable();
  cargartabla();
  $('#buttoneliminar').prop('disabled', true);
  $('#buttoneditar').prop('disabled', true);
  function cargartabla() {
    $("#tabla").dataTable().fnDestroy(); //destruyo para si habia algo
    tabla = $('#tabla').DataTable({  //tiene q ser con mayuscula


      //dom: 'Bfrtip',
      //  dom: 'lBftip',
      dom: 'r',
      responsive: true,
      ajax: {
        url: "/unidades/get",
        type: "POST",
        contentType: "application/json",
        dataSrc: 'unidades',
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
        { data: 'nombre' },
        { data: 'codigo' },
        { data: 'margencomercial' },

      ],
      columnDefs: [  //columna id
        {
          target: 0,
          visible: false,
          searchable: false,
        },
        {
          target: 3,
          render: $.fn.dataTable.render.number(',', '.', 0, '', ' %'),
        },

      ],
      initComplete: function () {
        //tabla.buttons().container().appendTo('#example_wrapper .col-md-6:eq(0)');
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

  tabla.on('search.dt', function () {
    tabla.$('tr.selected').removeClass('selected');  //para q deseleccione cuando busco
    $('#buttoneliminar').prop('disabled', true);
    $('#buttonver').prop('disabled', true);
    $('#buttoneditar').prop('disabled', true);
    $('#buttonresetpass').prop('disabled', true);
  });
  $('#tabla tbody').on('click', 'tr', function () {   //para seleccionar un elemento ,de uno e uno
    var vacia = $(this).find(".dataTables_empty").length == 1;  //si tabla esta vacia en busqueda
    if (!vacia) {  //si tiene elementos
      if ($(this).hasClass('selected')) {
        $(this).removeClass('selected');
        $('#buttoneliminar').prop('disabled', true);
        $('#buttoneditar').prop('disabled', true);


      } else {
        tabla.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        $('#buttoneliminar').prop('disabled', false);
        $('#buttoneditar').prop('disabled', false);

      }
    }

  });

  $('#addformulario').validate({
    rules: {
      addnombre: {
        required: true,
        pattern: /^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/,
        minlength: 3,
        maxlength: 20,
      },
      addcodigo: {
        required: true,
        minlength: 3,
        maxlength: 5,
      },
      addmargencomercial: {
        required: true,
        pattern: /^[0-9]+$/,
        maxlength: 2,
      },
    },
    messages: {
      addnombre: {
        pattern: 'Formato inválido',
        minlength: 'Use de 3 a 20 caracteres',
        maxlength: 'Use de 3 a 20 caracteres',
      },
      edtcodigo: {
        minlength: 'Use de 3 a 5 caracteres',
        maxlength: 'Use de 3 a 5 caracteres',
      },
      addmargencomercial: {
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

  $('#edtformulario').validate({
    rules: {
      edtnombre: {
        required: true,
        pattern: /^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/,
        minlength: 3,
        maxlength: 20,
      },
      edtcodigo: {
        required: true,
        minlength: 3,
        maxlength: 5,
      },
      edtmargencomercial: {
        pattern: /^[0-9]+$/,
        maxlength: 2,

      },
    },
    messages: {
      edtnombre: {
        pattern: 'Formato inválido',
        minlength: 'Use de 3 a 20 caracteres',
        maxlength: 'Use de 3 a 20 caracteres',
      },
      edtcodigo: {
        minlength: 'Use de 3 a 5 caracteres',
        maxlength: 'Use de 3 a 5 caracteres',
      },
      edtmargencomercial: {
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

  //acciones
  $('#buttonnuevo').click(function () {
    $("#addmodal").modal("show");
    //reset form
    $("#addformulario")[0].reset();  //rese values form
    $("#addformulario").find(".form-control").removeClass("is-valid is-invalid");  //busco en todos los form control y elimino las clases is-valid is-invalid
  });


  //boton para agregar en addmodal
  $('#addbuttonform').click(function () {
    if ($('#addformulario').valid()) {
      $("#addmodal").modal("hide");
      var nombre = $('#addnombre').val();
      var margencomercial = $('#addmargencomercial').val();
      var codigo = $('#addcodigo').val();

      var addnext = $('#addnext').prop('checked');

      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/unidades/add",
            data: { nombre: nombre, margencomercial: margencomercial, codigo: codigo },
            dataType: 'json',
            method: 'POST'
          }).done(function (response) {
            //cieroo
            self.close();
            if (response.code == 200) {
              //recargo
              cargartabla();
              $.alert({
                title: 'Agregar',
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
                      if (addnext) {  //si hay next mustro y reseteo
                        $("#addmodal").modal("show");
                        //reset form
                        $("#addformulario")[0].reset();  //rese values form
                        $("#addformulario").find(".form-control").removeClass("is-valid is-invalid");
                      }
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
                      $("#addmodal").modal("show"); //cuando hay error mantengo el model
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
                    $("#addmodal").modal("show"); //cuando hay error mantengo el model
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

  //boton para eliminar
  $('#buttoneliminar').click(function () {  //cuando seleccion el buttoneliminar 
    //alert(tabla.rows('.selected').count());
    if (tabla.rows('.selected').count() == 1) {  //si hay algo seleccionado
      var rowindex = tabla.row('.selected').index();
      var id = tabla.cell(rowindex, 0).data();  //elemento de coumna id q no se muestra
      $.confirm({
        title: 'Eliminar Unidad',
        content: 'No se podrá deshacer los cambios, ¿desea continuar?.',
        type: 'orange',
        icon: 'bi bi-question-circle',
        closeIcon: true,
        animation: 'scale',
        opacity: 0.5,
        buttons: {
          confirm: {
            text: 'Aceptar',
            escapeKey: 'cancelar',
            btnClass: 'btn-orange',
            action: function () {
              $.confirm({
                title: false,
                content: function () {
                  var self = this;
                  return $.ajax({
                    url: "/unidades/del",
                    data: { id: id },
                    dataType: 'json',
                    method: 'POST'
                  }).done(function (response) {
                    //cieroo
                    self.close();
                    if (response.code == 200) {  //todook, elimino la linea en la tabla
                      tabla.row('.selected').remove().draw(false);  //draw false para no pierdala paginacion esto quita la fila
                      //muestro ok
                      $.alert({
                        title: 'Eliminar',
                        type: 'green', //orange //blue
                        content: response.mensaje,
                        icon: 'bi bi-info-circle',
                        animation: 'scale',
                        autoClose: 'cancelAction|5000',
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
          },
          cancel: {
            text: 'Cancelar',
            action: function () {
            },
          }
        }
      });
    }

  });



  $('#buttoneditar').click(function () {  //cuando seleccion el editbutton 
    if (tabla.rows('.selected').count() == 1) {  //si hay algo seleccionado
      var rowindex = tabla.row('.selected').index();
      var id = tabla.cell(rowindex, 0).data();  //elemento de coumna id q no se muestra
      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/unidades/get",
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
              $('#edtnombre').val(response.nombre);
              $('#edtcodigo').val(response.codigo);

              $('#edtmargencomercial').val(response.margencomercial);



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


  $('#edtbuttonform').click(function () {
    if ($('#edtformulario').valid()) {  //si todook form
      $("#edtmodal").modal("hide");
      var rowindex = tabla.row('.selected').index();
      var id = tabla.cell(rowindex, 0).data();  //elemento de coumna id q no se muestra
      var nombre = $('#edtnombre').val();
      var codigo = $('#edtcodigo').val();
      var margencomercial = $('#edtmargencomercial').val();
      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/unidades/edt",
            data: { nombre: nombre, id: id, margencomercial: margencomercial, codigo: codigo },
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