$(document).ready(function () {
  $('#menu-toggler').on('click', function () {
    if ($('.menu-principal').hasClass('show')) {
      $('.menu-principal').removeClass('show');
    }
    else {
      $('.menu-principal').addClass('show');
    }
    if ($('.menu-backdrop').hasClass('show')) {
      $('.menu-backdrop').removeClass('show');
    }
    else {
      $('.menu-backdrop').addClass('show');
    }
  });
  $('.menu-backdrop').on('click', function () {
    if ($('.menu-principal').hasClass('show')) {
      $('.menu-principal').removeClass('show');
    }
    else {
      $('.menu-principal').addClass('show');
    }
    if ($('.menu-backdrop').hasClass('show')) {
      $('.menu-backdrop').removeClass('show');
    }
    else {
      $('.menu-backdrop').addClass('show');
    }
  });
 
});