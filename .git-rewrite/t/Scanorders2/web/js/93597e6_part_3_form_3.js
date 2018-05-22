
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
//    var index = window.location.pathname.split('/')[1];
//    //alert('index=('+index+')');
//    console.log("full="+window.location.pathname+", index="+index);
//    $('ul.li').removeClass('active');
//    if( index ) {
//        $('li.' + index).addClass('active');
//    } else {
//        $('li.' + 'new').addClass('active');
//    }
    setNavBar();
        
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
    
    //tab
    $('#optional_param_tab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })

    
//    $('#deleteButton').click(function (e) {
//        bootbox.confirm("Are you sure?", function(result) {
//            Example.show("Confirm result: "+result);
//        }); 
//    }
    
});

function setNavBar() {

    var index_arr = window.location.pathname.split('/');
    var index = index_arr.indexOf("order");
    if( index_arr.indexOf("app_dev.php") != -1 ) {
        index = index + 4;
    }
    console.info("full="+window.location.pathname+", index="+index + " name="+index_arr[index+1]);
    $('ul.li').removeClass('active');

    switch( index_arr[index+1] )
    {
        case "multy":
            id = 1;
            break;
        case "edu":
            id = 2;
            break;
        case "res":
            id = 3;
            break;
        case "index":
            id = 4;
            break;
        case "login":
            id = 5;
            break;
        default:
            id = 0;
    }

    $('#'+id).addClass('active');
}
