/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function cleanValidationAlert() {
    if( cicle == "new" || cicle == "amend" || cicle == "edit" ) {
        $('.validationerror-added').each(function() {
            $(this).remove();
        });
        //$('#validationerror').html('')
        dataquality_message1.length = 0;
        dataquality_message2.length = 0;
    }
}