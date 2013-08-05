
$(document).ready(function() {
    $('.combobox').combobox();
    $("#orderinfo").hide();
    $("#optional_button").hide();   

    $('#next_button').on('click', function(event) {        
       $("#next_button").hide();
       $("#optional_button").show();
    });
        
    //TODO: fix it
//    $('.navbar li a').on('click', function() {
//        $(this).parent().parent().find('.active').removeClass('active');
//        $(this).parent().addClass('active').css('font-weight', 'bold');
//    });
//   
    //Note: index 5 can be changed according to url structure
    var index = window.location.pathname.split('/')[6];
    //alert(index);
    $('ul.li').removeClass('active');
    $('li.' + index).addClass('active');
        
//    load login form in main page
    $('#modelPlainLogin').modal('show');
//    $('#modelPlainLogin').modal();
    
});
