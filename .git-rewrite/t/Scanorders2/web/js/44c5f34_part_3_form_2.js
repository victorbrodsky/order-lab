
$(document).ready(function() {
    $('.combobox').combobox();
    $("#orderinfo").hide();
    $("#optional_button").hide(); 
    //$("#priority_option").hide();
    $('#priority_option').collapse({
        toggle: false
    })

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
//    $('#modelPlainLogin').modal('show');
    
    //datepicker
    $('.datepicker').datepicker();
    
    //priority options   
    $('#oleg_orderformbundle_orderinfotype_priority').change(function(e) {
        e.preventDefault();  
        $('#priority_option').collapse('toggle');
//        var val = $("#oleg_orderformbundle_orderinfotype_priority option:selected").text();
        //alert(val);       
//        if( val == "Stat" ) {
//            //alert("val=Stat!");                  
//            $('#priority_option').collapse('toggle');
//        }

    });
    
    
});
