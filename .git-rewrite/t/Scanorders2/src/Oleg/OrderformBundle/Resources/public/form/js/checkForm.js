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


function checkForm( btnel ) {
    
    var btn = $(btnel);
    var clickParent = true;
    var parentBtnObj = null;   
    var casetype = 'check';
    
    var btnObj = new btnObject(btn);
    console.log('input='+btnObj.key+', type='+btnObj.type);
    
    parentBtnObj = new btnObject(btnObj.parentbtn);
    if( parentBtnObj && parentBtnObj.key != '' ) {
        clickParent = false;
    }     
       
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
    
    if( clickParent ) {
        console.log('execute click parent then this');

//        Q.spread([
//                executeClick( parentBtnObj, casetype ),
//                executeClick( btnObj, casetype )
//            ],
//            function( parentel, thisel ){
//                console.log("finished with parentel="+parentel+", thisel="+thisel);
//            }
//        );
            
//        executeClick( parentBtnObj, casetype )
//        .then( executeClick( btnObj, casetype ) )
//        .then( function (result) {
//           console.log("finished with result="+result);
//        })
//        .catch(function (error) {
//            // Handle any error from all above steps
//            console.log("Error:"+error);
//        })
//        .done( console.log("Done!") );

//        executeClick( parentBtnObj, casetype ).then(function(response) {
//            console.log("Success!", response);
//            executeClick( btnObj, casetype )
//        }, function(error) {
//            console.error("Failed!", error);
//        });
        
        
        executeClick( parentBtnObj, casetype ).then(function(response) {
            console.log("Success!", response);
            return executeClick( btnObj, casetype );
        }).then(function(response) {
            console.log("Yey JSON!", response);
        });
        

    } else {
        console.log('execute click this');
        executeClick( btnObj, casetype );
    }
         
    return; 
}

//object contains: input value, type, parent (btn element), name, fieldname
function btnObject( btn ){

    if( !btn ) {
        return null;
    }

   this.btn = btn;
   this.key = "";
   this.type = null;
   this.typename = null;
   this.parentbtn = null;
   this.name = null;
   this.fieldname = null;
   this.remove = false;

   var inputEl = btn.closest('.row').find('input.keyfield'); 
   if( inputEl.attr('class').indexOf("ajax-combobox") != -1 ) {    //select2
       if( inputEl.select2('data') ) {
           this.key = inputEl.select2('data').text;
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
    if( btn.find('i').hasClass('removebtn') ) {
        this.remove = true;
    }
    
    console.log(this);
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

function executeClick( btnObj, casetype ) {
    
    return Q.promise(function(resolve, reject) {
    
        console.log('executeClick: casetype='+casetype);

        var btn = btnObj.btn;
        var urlcasename = btnObj.name+'/'+casetype;
        var ajaxType = 'GET';
        var key = btnObj.key;
        var type = btnObj.type;
        var parentKey = null;
        var parentType = null;
        var grandparentKey = null;
        var grandparentType = null;

        console.log('key='+key+', parentKey='+parentKey+', parentType='+parentType);
                
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
        
        if( parentBtnObj && parentType && parentKey == '' ) {
            console.log('parent key is empty');
            reject(Error("parent key is empty"));          
            //return;
        }
        
        //get grand parent
        var grandparentBtnObj = new btnObject(parentBtnObj.parentbtn);
        if( grandparentBtnObj ) {
            grandparentKey = grandparentBtnObj.key;
            grandparentType = grandparentBtnObj.type;
        }
                    
        if( parentBtnObj && parentType && parentKey == '' ) {
            console.log('parent key is empty');
            reject(Error("parent key is empty"));          
            //return;
        }
        
        //trim values
        key = trimWithCheck(key);
        type = trimWithCheck(type);
        parentKey = trimWithCheck(parentKey);
        parentType = trimWithCheck(parentType);
        grandparentKey = trimWithCheck(grandparentKey);
        grandparentType = trimWithCheck(grandparentType);
        
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
                if( data ) {
                    console.debug("ajax key value data is found");                 
                    invertButton(btn);
                    setElementBlock(btn, data, null, "key");
                    disableInElementBlock(btn, false, null, "notkey", null);
                    //resolve("ajax key value data is found");
                } else {
                    console.debug('set key data is null');
                }              
                resolve("ajax key value data is found");
            },
    //        always: function() {
    //            //_lbtn.stop();
    //            //$('.spinner-image').remove();
    //        },
            error: function () {
                btn.button('reset');
                reject(Error("set key ajax error"));
                //resolve("set key ajax error");
                console.debug("set key ajax error");
            }
        }); //ajax
        
    }); //promise
       
}

