
$(document).ready(function () {

  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });

  $(".alert").delay(4000).slideUp(250, function () {
    $(this).alert('close');
  });

});

$('#cambiarcontrasena').click(function () {

  var iduser = $('#cambiarcontrasena').attr('iduser');
  $.confirm({
    title: 'Cambiar Contraseña',
    content: '<div class="form-group">' +
      '<label class="control-label">Contraseña Nueva</label><input autofocus="" type="text" id="input-newpassword" placeholder="Contraseña para la autenticación" class="form-control">' +
      '<label class="control-label">Repetir Contraseña</label><input type="text" id="input-renewpassword" placeholder="Contraseña para la autenticación" class="form-control">' +
      '</div>'
    ,
    buttons: {
      salir: {
        text: 'Cancelar',
      },
      cambiar: {
        text: 'Aceptar',
        btnClass: 'btn-orange',
        action: function () {
          var newpassword = this.$content.find('input#input-newpassword');
          var renewpassword = this.$content.find('input#input-renewpassword');

          // var errorText = this.$content.find('.text-danger');
          if (!newpassword.val().trim() || !renewpassword.val().trim()) {  //chequeo vacio
            $.alert({
              title: 'Error',
              content: "Rellene los campos",
             type: 'red',
                        animateFromElement: false,
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


            return false;
          } else if (
            newpassword.val().length < 8 | newpassword.val().length > 20 ||   //chequeo len
            renewpassword.val().length < 8 || renewpassword.val().length > 20) //chequeo len
          { //chequeo len 
            $.alert({
              title: 'Error',
              content: "Use de 8 a 20 caracteres",
             type: 'red',
                        animateFromElement: false,
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
            return false;
          } else
            if (newpassword.val() != renewpassword.val()) {
              $.alert({
                title: 'Error',
                content: "Las contraseñas no coinciden",
               type: 'red',
                        animateFromElement: false,
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
              return false;
            } else {

              $.confirm({
                title: false,
                content: function () {
                  var self = this;
                  return $.ajax({
                    url: "/configuracion/usuarios/resetpassword",
                    data: { iduser: iduser, newpassword: newpassword.val() }, //anterior true o false para saber si chequeo el pass anterior
                    dataType: 'json',
                    method: 'get'
                  }).done(function (response) {
                    //cieroo
                    self.close();
                    if (response.code == 200) {  //todook, elimino la linea en la tabla
                      //mustra y carga form edit
                      $.alert({
                        title: response.mensaje,
                        type: 'green',
                        //type: 'orange',  
                        content: '',  //muesro mensaje de error
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
                        animateFromElement: false,  //type: 'orange', 
                        content: '<div>' + response.mensaje + '</div>',  //muesro mensaje de error
                        icon: 'bi bi-info-circle',
                        animation: 'scale',
                                                buttons: {
                          okay: {
                            text: 'Aceptar',
                            btnClass: 'btn-red'
                          }
                        }
                      });
                    }
                  }).fail(function () {
                    self.close();  //cierro
                    $.alert({
                      title: 'Error',
                     type: 'red',
                        animateFromElement: false,
                      //type: 'orange',  
                      content: '<div>Servidor no encontrado!!</div>',
                      icon: 'bi bi-info-circle',
                      animation: 'scale',
                                            buttons: {
                        okay: {
                          text: 'Aceptar',
                          btnClass: 'btn-red'
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
              return true;
            }
        }
      },
    }

  });



});


