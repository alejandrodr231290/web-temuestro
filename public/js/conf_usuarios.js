$(document).ready(function () {

  //mostrar tabla
  var tabla = $("#tabla").dataTable();
  cargartabla();
  $('#select-unidad').on('change', (event) => {
    cargartabla();
  });
  function cargartabla() {
    var unidad = $("#select-unidad :selected").val();
    $('#select-unidad').attr('disabled', 'disabled');

    $("#tabla").dataTable().fnDestroy(); //destruyo para si habia algo
    tabla = $('#tabla').DataTable({  //tiene q ser con mayuscula
      responsive: true,
      ajax: {
        url: '/usuarios/get',
        data: { 'unidad': unidad },
        method: 'POST',
        dataSrc: 'usuarios',
        timeout: 5000,
        error: function (request, status, err) {
          var msg = 'Servidor no encontrado!!</div>'
          if (status == "timeout") {
            var msg = "La peticiÃ³n demorÃ³ mÃ¡s de lo permitido";
          }
          $('.dataTables_empty').html('<label class="text-danger">' + msg + '</label>');
    


        }
      },
      columns: [
        { data: 'id' },
        { data: 'username' },
        { data: 'nombre' },
        { data: 'apellidos' },
        { data: "rol" },
        { data: "unidad" },

      ],
      columnDefs: [  //columna id
        {
          target: 0,
          visible: false,
          searchable: false,
        },
      ],
      initComplete: function () {
        $('#select-unidad').removeAttr('disabled');
      },

      language: {
        "decimal": "",
        "emptyTable": "La tabla estÃ¡ vacÃ­a",
        "info": "Mostrando _START_ - _END_ de _TOTAL_ entradas",
        "infoEmpty": " 0 hasta 0 of 0 entradas",
        "infoFiltered": "(Flitrado de _MAX_ total)",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Mostrando  _MENU_  entradas",
        "loadingRecords": '<i class="fas fa-spinner fa-pulse"></i>',
        "processing": "",
        "search": "Buscar:",
        "zeroRecords": "La tabla estÃ¡ vacÃ­a",
        "paginate": {
          "first": "Primera",
          "last": "Ãšltima",
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
        $('#buttonver').prop('disabled', true);
        $('#buttoneditar').prop('disabled', true);
        $('#buttonresetpass').prop('disabled', true);

      } else {
        tabla.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        $('#buttoneliminar').prop('disabled', false);
        $('#buttonver').prop('disabled', false);
        $('#buttoneditar').prop('disabled', false);
        $('#buttonresetpass').prop('disabled', false);

      }
    }

  });

  //addformulario/*
  $('#addformulario').validate({
    ignore: [],
    rules: {
      addnombre: {
        required: true,
        pattern: /^[a-zA-Z0-9Ã±Ã‘Ã¼ÃœÃ±Ã¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“Ãš ]+$/,
        minlength: 3,
        maxlength: 20,
      },
      addapellidos: {
        required: true,
        pattern: /^[a-zA-Z0-9Ã±Ã‘Ã¼ÃœÃ±Ã¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“Ãš ]+$/,
        minlength: 3,
        maxlength: 20,
      },
      addunidad: {
        required: true,
      },
      addrol: {
        required: true,
      },
      addusuario: {
        required: true,
        pattern: /[a-zÃ±]+$/,
      },
      addpassword: {
        required: true,
        minlength: 8,
        maxlength: 20,

      },
      addconfirm_password: {
        required: true,
        minlength: 8,
        maxlength: 20,
        equalTo: "#addpassword"
      },

    },
    messages: {
      addnombre: {
        pattern: 'Formato invÃ¡lido',
        minlength: 'Use de 3 a 20 caracteres',
        maxlength: 'Use de 3 a 20 caracteres',

      },
      addapellidos: {
        pattern: 'Formato invÃ¡lido',
        minlength: 'Use de 3 a 20 caracteres',
        maxlength: 'Use de 3 a 20 caracteres',
      },
      addusuario: {

        pattern: 'Formato invÃ¡lido',
      },
      addpassword: {
        minlength: 'Use al menos 8 caracteres',
        maxlength: 'Use hasta 20 caracteres',
      },
      addconfirm_password: {
        minlength: 'Use al menos 8 caracteres',
        maxlength: 'Use hasta 20 caracteres',
        equalTo: 'Las contraseÃ±as no coinciden'
      }
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
  //edtformulario
  $('#edtformulario').validate({
    rules: {
      edtnombre: {
        required: true,
        pattern: /^[a-zA-Z0-9Ã±Ã‘Ã¼ÃœÃ±Ã¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“Ãš ]+$/,
        minlength: 3,
        maxlength: 20,
      },
      edtapellidos: {
        required: true,
        pattern: /^[a-zA-ZÃ±Ã‘ ]+$/,
        minlength: 3,
        maxlength: 20,
      },
      edtusuario: {
        required: true,
        pattern: /[a-zÃ±]+$/,
      },
    },
    messages: {
      edtnombre: {

        pattern: 'Formato invÃ¡lido',
        minlength: 'Use de 3 a 20 caracteres',
        maxlength: 'Use de 3 a 20 caracteres',

      },
      edtapellidos: {

        pattern: 'Formato invÃ¡lido',
        minlength: 'Use de 3 a 20 caracteres',
        maxlength: 'Use de 3 a 20 caracteres',
      },
      edtusuario: {

        pattern: 'Formato invÃ¡lido',
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

  //passformulario/*
  $('#passformulario').validate({
    rules: {

      passpassword: {
        required: true,
        minlength: 8,
        maxlength: 20,

      },
      passconfirm_password: {
        required: true,
        minlength: 8,
        maxlength: 20,
        equalTo: "#passpassword"
      },

    },
    messages: {
      passpassword: {

        minlength: 'Use al menos 8 caracteres',
        maxlength: 'Use hasta 20 caracteres',
      },
      passconfirm_password: {

        minlength: 'Use al menos 8 caracteres',
        maxlength: 'Use hasta 20 caracteres',
        equalTo: 'Las contraseÃ±as no coinciden'
      }
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
    if ($('#addformulario').valid()) {  //si todook form
      $("#addmodal").modal("hide");
      //alert('add');
      var nombre = $('#addnombre').val();
      var apellidos = $('#addapellidos').val();

      var rol = $("#addrol :selected").val();
      var unidad = $("#addunidad :selected").val();
      var usuario = $('#addusuario').val();
      var password = $('#addpassword').val();
      var confirm_password = $('#addconfirm_password').val();
      var addnext = $('#addnext').prop('checked'); //saer si esta selcted o no
      // alert(' '+nombre+' '+apellidos+' '+usuario+' '+password);
      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/usuarios/add",
            data: { nombre: nombre, apellidos: apellidos, rol: rol, usuario: usuario, password: password, unidad: unidad },
            dataType: 'json',
            method: 'POST'
          }).done(function (response) {
            //cieroo
            self.close();
            if (response.code == 200) {  //todook, elimino la linea en la tabla
              //recargo
              cargartabla();
              $.alert({
                title: 'Agregar Usuario',
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
        title: 'Eliminar Usuario',
        content: 'No se podrÃ¡n deshacer los cambios, Â¿desea continuar?.',
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
                    url: "/usuarios/del",
                    data: { id: id },
                    method: 'POST'
                  }).done(function (response) {
                    //cieroo
                    self.close();
                    if (response.code == 200) {  //todook, elimino la linea en la tabla
                      tabla.row('.selected').remove().draw(false);  //draw false para no pierdala paginacion esto quita la fila
                      //muestro ok
                      $.alert({
                        title: 'Eliminar Usuario',
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
            url: "/usuarios/get",
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

              $('#edtunidad').val(response.unidad);
              $("#edtnombre").val(response.nombre);
              $("#edtapellidos").val(response.apellidos);

              $("#edtrol").val(response.rol);
              $('#edtusuario').val(response.usuario);
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
      var nombre = $('#edtnombre').val();
      var apellidos = $('#edtapellidos').val();
      var rol = $("#edtrol :selected").val();
      var usuario = $('#edtusuario').val();
      var unidad = $('#edtunidad').val();

      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/usuarios/edt",
            data: { nombre: nombre, apellidos: apellidos, rol: rol, usuario: usuario, unidad: unidad },
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



  $('#buttonresetpass').click(function () {
    $("#passmodal").modal("show");
    //reset form
    $("#passformulario")[0].reset();  //rese values form
    $("#passformulario").find(".form-control").removeClass("is-valid is-invalid");  //busco en todos los form control y elimino las clases is-valid is-invalid
  });


  //boton para resetear password
  $('#passbuttonform').click(function () {
    if ($('#passformulario').valid()) {  //si todook form
      $("#passmodal").modal("hide");
      var rowindex = tabla.row('.selected').index();
      var id = tabla.cell(rowindex, 0).data();  //elemento de coumna id q no se muestra
      var password = $('#passpassword').val();

      $.confirm({
        title: false,
        content: function () {
          var self = this;
          return $.ajax({
            url: "/usuarios/resetpass",
            data: { id: id, password: password },
            dataType: 'json',
            method: 'POST'
          }).done(function (response) {
            //cieroo
            self.close();
            if (response.code == 200) {  //todook, elimino la linea en la tabla
              //recargo

              $.alert({
                title: 'ContraseÃ±a',
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
                      $("#passmodal").modal("show"); //cuando hay error mantengo el model
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
                    $("#passmodal").modal("show"); //cuando hay error mantengo el model
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
