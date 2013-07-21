$(document).ready(function() {
    $('.combobox').combobox();
    $("#optional").hide();

    $('#show').on('click', function(event) {        
       jQuery('#optional').toggle('show');
       $("#show").hide();
    });
     
    //TODO: fix it
//    $('.navbar li a').on('click', function() {
//        $(this).parent().parent().find('.active').removeClass('active');
//        $(this).parent().addClass('active').css('font-weight', 'bold');
//    });
//   
    //Note: index 5 can be changed according to url structure
    var index = window.location.pathname.split('/')[5];
    //alert(index);
    $('ul.li').removeClass('active');
    $('li.' + index).addClass('active');
          
});






