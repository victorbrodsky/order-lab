/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var urlBase = $("#baseurl").val();
var urlCheck = "http://"+urlBase+"/check/";

var keys = new Array("mrn", "accession", "partname", "blockname");
var arrayFieldShow = new Array("clinicalHistory","age","diffDisident"); //,"disident"); //display as array fields "sex"
var selectStr = 'input[type=file],input.form-control,div.patientsexclass,div.diseaseType,div.select2-container,[class^="ajax-combobox-"],[class^="combobox"],textarea,select';  //div.select2-container, select.combobox, div.horizontal_type

var orderformtype = $("#orderformtype").val();

var dataquality_message1 = new Array();
var dataquality_message2 = new Array();

//var _autogenAcc = 8;
//var _autogenMrn = 13;

//add disident to a single form array field
$(document).ready(function() {

    if( orderformtype == "single") {
        arrayFieldShow.push("disident")
    }

    $("#save_order_onidletimeout_btn").click(function() {
        $(this).attr("clicked", "true");
    });

    //validation on form submit
    $("#scanorderform").on("submit", function () {
        return validateForm();
    });

    addKeyListener();

});

//  0         1              2           3   4  5  6   7
//oleg_orderformbundle_orderinfotype_patient_0_mrn_0_field
var fieldIndex = 3;     //get 'key'
var holderIndex = 5;    //get 'patient'
//console.log("urlCheck="+urlCheck);

//needed by a single slide form
var asseccionKeyGlobal = "";
var asseccionKeytypeGlobal = "";
var partKeyGlobal = "";
var blockKeyGlobal = "";
var mrnKeyGlobal = "";
var mrnKeytypeGlobal = "";

//remove errors from inputs
function addKeyListener() {
    //remove has-error class from mrn and accession inputs
    $('.accessionaccession').find('.keyfield').parent().keypress(function() {
        $(this).removeClass('has-error');
    });
    $('.patientmrn').find('.keyfield').parent().keypress(function() {
        //console.log("remove has-error on keypress");
        $(this).removeClass('has-error');
    });

    $('.ajax-combobox-partname').on("change", function(e) {
        //console.log("remove maskerror-added on change");
        $(this).siblings('.maskerror-added').remove();
    });
    $('.ajax-combobox-blockname').on("change", function(e) {
        //console.log("remove maskerror-added on change");
        $(this).siblings('.maskerror-added').remove();
    });
}

//object contains: input value, type, parent (btn element), name, fieldname
function btnObject( btn ){

    if( !btn ) {
        return null;
    }

    //printF(btn,"btn object:");

    this.btn = btn;
    this.element = null;
    this.key = "";
    this.type = null;
    this.typename = null;
    this.parentbtn = null;
    this.name = null;
    this.fieldname = null;
    this.remove = false;

    var inputEl = btn.closest('.row').find('input.keyfield');
    this.element = inputEl;
    if( inputEl.attr('class').indexOf("ajax-combobox") != -1 ) {    //select2
       //var selectEl = btn.closest('.row').find('div.keyfield');
       //console.log('ajax-combobox OK: testval='+selectEl.select2("val"));
       if( inputEl.select2("val") ) {
           //console.log('select2 data OK');
           this.key = inputEl.select2('data').text;
       } else {
           //console.log('select2 data not OK ?');
       }
    } else {
       this.key = inputEl.val();
    }
   
    //get type
    var typeEl = inputEl.prev();
    if( typeEl.hasClass('combobox') ) {    //type exists
        this.type = typeEl.select2('val');
        this.typename = typeEl.select2('data').text;
    }
    
    //get name
    var idsArr = inputEl.attr('id').split("_");
    this.name = idsArr[idsArr.length-holderIndex];       //i.e. "patient"
    this.fieldname = idsArr[idsArr.length-fieldIndex];   //i.e. "mrn"
    
    //get parent
    if( this.name == 'part' || this.name == 'block' ) {      
        this.parentbtn = getParentBtn(btn);             
    } 
    
    //if remove
    if( btn.hasClass('removebtn') ) {
        this.remove = true;
    }
    
    console.log(this);
    console.log('finished: this.name='+this.name+', this.key='+this.key+', this.type='+this.type);
}

function getParentBtn(btn) {
    
    var parentBtn = null;
    
    if( orderformtype == "single" ) {
        if( name == 'part' ) {
            parentBtn = $('#accessionbtn');
        } 
        if( name == 'block' ) {
            parentBtn = $('#partbtn');
        }    
    } else {
        var parentEl1 = btn.closest('.panel');      
        //console.log(parentEl1);
        var parentEl2 =  parentEl1.siblings().first();    //.find('#check_btn');
        //console.log(parentEl2);
        parentBtn = parentEl2.find('#check_btn');     
    }
    
    if( parentBtn.length == 0 ) {
        parentBtn = null;
    }
    
    return parentBtn;
}




function checkForm( btnel ) {

return new Q.promise(function(resolve, reject) {

    var btn = $(btnel);
    var clickParent = true;
    var parentBtnObj = null;
    var casetype = 'check';

    var btnObj = new btnObject(btn);
    console.log('check form: name='+btnObj.name+', input='+btnObj.key+', type='+btnObj.type);

    parentBtnObj = new btnObject(btnObj.parentbtn);
    if( parentBtnObj && parentBtnObj.key != '' ) {
        clickParent = false;
    }

    if( clickParent ) {
        console.log('execute click parent then this');

//        checkForm( parentBtnObj.btn ).then(function(response) {
//            console.log("Success!", response);
//            executeClick( btnObj );
//            //checkForm( btnObj.btn );
//        }).then(function(response) {
//                console.log("Yey JSON!", response);
//                resolve("OK: "+response);
//            },function(error) {
//                console.error("Failed!", error);
//                reject(Error("Error on check form with parent"));
//            }
//        );

        checkForm( parentBtnObj.btn ).
        then(
            function(response) {
                console.log("Success!", response);
                return executeClick( btnObj );
            }
        ).
        then(
            function(response) {
                console.log("Yey JSON!", response);
                resolve("OK: "+response);
            }
        );


    } else {
        console.log('execute click this');
        executeClick( btnObj ).
        then(function(response) {
                console.log("Yey JSON!", response);
                resolve("OK: "+response);
        },function(error) {
                console.error("Failed!", error);
                reject(Error("Error on check form no parent"));
            }
        );
    }

});
    //return;
}



/////////////// called by button click //////////////////////
function checkForm_WORKING( btnel ) {
    
    var btn = $(btnel);         
    var btnObj = new btnObject(btn);      
    var parentBtnObj = null;
    var grandparentBtnObj = null;
      
    console.log('input='+btnObj.key+', type='+btnObj.type);        
    
    //if delete button?
    if( btnObj && btnObj.remove ) {
        console.log('execute click this');
        executeClick( btnObj );
        return;
    }
    
    //patient 
    if( btnObj.name == 'patient' ) {
        console.log('execute click this');
        executeClick( btnObj );
        return;
    }
    
    //accession 
    if( btnObj.name == 'accession' ) {
        console.log('execute click this');
        executeClick( btnObj );
        return;
    }
    
    //part
    if( btnObj.name == 'part' ) {
        console.log('execute click parent then this');
        
        parentBtnObj = new btnObject(btnObj.parentbtn);
        
        //working!
        executeClick( parentBtnObj ).
        then( 
            function(response) {
                console.log("Success!", response);
                return executeClick( btnObj );
            } 
        ).
        then(
            function(response) {
                console.log("Yey JSON!", response);
            }
        ); 
        return;
    }
    
    //block
    if( btnObj.name == 'block' ) {
        console.log('execute click grandparent then parent then this');
        
        parentBtnObj = new btnObject(btnObj.parentbtn);
        grandparentBtnObj = new btnObject(parentBtnObj.parentbtn);
        
        //working!
        executeClick( grandparentBtnObj ).
        then( 
            function(response) {
                console.log("Success!", response);
                return executeClick( parentBtnObj );
            } 
        ).
        then( 
            function(response) {
                console.log("Success!", response);
                return executeClick( btnObj );
            } 
        ).            
        then(
            function(response) {
                console.log("Yey JSON!", response);
            }
        );
        return;    
    }     
         
    return; 
}
/////////////// end of button click //////////////////////

function executeClick( btnObjInit ) {
       
    return Q.promise(function(resolve, reject) {

        var btnObj = new btnObject(btnObjInit.btn);
        var casetype = 'check';       
        var btn = btnObj.btn;
        var urlcasename = null;
        var ajaxType = 'GET';
        var key = btnObj.key;
        var type = btnObj.type;
        var parentKey = null;
        var parentType = null;
        var grandparentKey = null;
        var grandparentType = null;
        var single = false; //temp

        console.log('executeClick: name='+btnObj.name+', key='+key+', parentKey='+parentKey+', parentType='+parentType);
               
        if( btnObj && btnObj.key == '' && !btnObj.remove ) {
            console.log('Case 1: key not exists => generate');
            casetype = 'generate';      
        } else if( btnObj && btnObj.key != '' && !btnObj.remove ) {
            console.log('Case 2: key exists => check');
            casetype = 'check';
        } else if( btnObj && btnObj.remove ) {
            console.log('Case 3: key exists and button delete => delete');
            casetype = 'delete';
        } else {
            console.log('Logical error: invalid key');
        }
        
        console.log('executeClick: casetype='+casetype);
        
        urlcasename = btnObj.name+'/'+casetype;
        
        if( casetype == 'delete' ) {
            ajaxType = 'DELETE'; 
           
            var extraStr = "";
            if( type ) {
                extraStr = "?extra="+type;
            }           
           
           urlcasename = urlcasename + '/' + key + extraStr;
        } 
              
        //get parent
        var parentBtnObj = new btnObject(btnObj.parentbtn);
        if( parentBtnObj ) {
            parentKey = parentBtnObj.key;
            parentType = parentBtnObj.type;
        }
        
//        if( parentBtnObj && parentType && parentKey == '' ) {
//            console.log('parent key is empty');
//            reject(Error("parent key is empty"));
//            //return;
//        }
        
        //get grand parent
        var grandparentBtnObj = new btnObject(parentBtnObj.parentbtn);
        if( grandparentBtnObj ) {
            grandparentKey = grandparentBtnObj.key;
            grandparentType = grandparentBtnObj.type;
        }
                    
//        if( grandparentBtnObj && grandparentType && grandparentKey == '' ) {
//            console.log('grandparent key is empty');
//            reject(Error("grandparent key is empty"));
//            //return;
//        }
        
        //trim values
        key = trimWithCheck(key);
        type = trimWithCheck(type);
        parentKey = trimWithCheck(parentKey);
        parentType = trimWithCheck(parentType);
        grandparentKey = trimWithCheck(grandparentKey);
        grandparentType = trimWithCheck(grandparentType);
        
        //temp
        if( orderformtype == "single" ) {
            single = true;
        }
        
        btn.button('loading');
        
        $.ajax({
            url: urlCheck+urlcasename,
            type: ajaxType,
            contentType: 'application/json',
            dataType: 'json',
            async: true,    //use synchronous call
            data: {key: key, extra: type, parentkey: parentKey, parentextra: parentType, grandparentkey: grandparentKey, grandparentextra: grandparentType },
            success: function (data) {
                btn.button('reset');

                if( data == null && casetype == 'generate' ) {

                    console.debug("Object was not generated");
                    reject(Error("Object was not generated"));

                } else
                if( data == -2 ) {

                    //Existing Auto-generated object does not exist in DB
                    createErrorWell(btnObj.element,btnObj.name);
                    reject(Error("Existing Auto-generated object does not exist in DB"));

                } else
                if( data && data.id ) {

                    console.debug("ajax key value data is found");                                    
                    
                    if( casetype == 'generate' ) {
                        setElementBlock(btn, data, null, "key");
                        disableInElementBlock(btn, false, null, "notkey", null);
                    }
                    
                    if( casetype == 'check' ) {
                        setElementBlock(btn, data);
                        //second: disable or enable element. Make sure this function runs after set Element Block
                        disableInElementBlock(btn, true, "all", null, "notarrayfield");
                    }
                    
                    if( casetype == 'delete' ) {
                        if( data != '-1' || single ) {
                            //console.debug("Delete Success");
                            deleteSuccess(btn,single);
                        } else {
                            //console.debug("Delete ok with Error");
                            deleteError(btn,single);
                        }
                    }                  
                    
                    //if( casetype != 'check' ) {
                        invertButton(btn);
                    //}    
                    
                    resolve("ajax key value data is found");

                } else {
                    
                    if( casetype == 'check' ) {
                        invertButton(btn);
                    }
                    
                    if( casetype == 'delete' ) {
                        deleteError(btn,single);
                    }
                    
                    console.debug('set key data is null');
                    resolve("ajax key value data is found");
                } 

            },
            error: function () {
                btn.button('reset');
                console.debug("set key ajax error");
                reject(Error("set key ajax error"));
            }
        }); //ajax               
        
    }); //promise
     
}

