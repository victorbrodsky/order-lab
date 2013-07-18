$(document).ready(function() {
    $('.combobox').combobox();
    $("#optional").hide();

    $('#show').on('click', function(event) {        
       jQuery('#optional').toggle('show');
       $("#show").hide();
    });
      
});





