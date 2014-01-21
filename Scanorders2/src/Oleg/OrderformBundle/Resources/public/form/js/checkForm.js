/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */

//var urlCheck = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/check/";
var urlBase = $("#baseurl").val();
var urlCheck = "http://"+urlBase+"/check/";

var keys = new Array("mrn", "accession", "partname", "blockname");
var arrayFieldShow = new Array("clinicalHistory","age","diffDisident"); //,"disident"); //display as array fields "sex"
var selectStr = 'input[type=file],input.form-control,div.patientsexclass,div.diseaseType,div.select2-container,[class^="ajax-combobox-"],[class^="combobox"],textarea,select';  //div.select2-container, select.combobox, div.horizontal_type

var orderformtype = $("#orderformtype").val();

var dataquality_message1 = new Array();
var dataquality_message2 = new Array();

var _autogenAcc = 8;
var _autogenMrn = 13;

//add disident to a single form array field
$(document).ready(function() {

    if( orderformtype == "single") {
        arrayFieldShow.push("disident")
    }

    $('#maincinglebtn').hide(); //hide remove button

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

function checkForm( elem, single ) {

    var element = $(elem);

    //console.log( "check element.id=" + element.attr('id') + ", single="+single);

    if( element.hasClass('disabled') ) {
        //console.log("loading button Cliked => return");
        return; //loading button => prevent multiple clicked
    }

    var elementInput = element.parent().parent().find(".keyfield");

    if( single ) {
        var elementInput = element.parent().find(".keyfield");
    }

    //console.log("elementInput.class="+elementInput.attr('class') + ", id="+elementInput.attr('id'));

    //print full id=check_btn, class=btn btn-default disabled
    //print full id=undefined, class=checkbtn glyphicon glyphicon-check
    //elementInput.class=form-control keyfield patientmrn-mask, id=oleg_orderformbundle_orderinfotype_patient_0_mrn_0_field
    //printF( element, "check button: " );
    //printF( element.find("i"), "check button find(i): " );

    //  0         1              2           3   4  5  6   7
    //oleg_orderformbundle_orderinfotype_patient_0_mrn_0_field
    var inputId = elementInput.attr('id');
    //console.log("\n\n inputId="+inputId);

    var idsArr = inputId.split("_");

    var name = idsArr[idsArr.length-holderIndex];   //i.e. "patient"
    var fieldName = idsArr[idsArr.length-fieldIndex];
    //console.log("name="+name+", fieldName="+fieldName);
    //var patient = idsArr[4];
    //var key = idsArr[4];

    var keyElement = findKeyElement(element, single);

    if( element.find("i").hasClass('removebtn') ) { //Remove Button Cliked
        //console.log("Remove Button Cliked: fieldName="+fieldName);
        removeKeyFromDB(keyElement, element, single);
        return;

    } else if( element.find("i").hasClass('glyphicon-check') ) { //Check Button Cliked

        if( validateMaskFields(element, fieldName) > 0 ) {
            return false;
        }

        //console.log("Check Button Cliked");

        //get key field for this patient: oleg_orderformbundle_orderinfotype_patient_0_mrn

        var keyValue =keyElement.element.val();
        var extra =keyElement.extra;
        //console.log("keyElement id="+keyElement.element.attr("id")+", class="+keyElement.element.attr("class")+",val="+keyValue+", extra="+keyElement.extra+",name="+name);

        var accessionValue = keyElement.accession;
        var partValue = keyElement.partname;

        //console.log("process: "+name+": keyValue="+keyValue+", accessionValue="+accessionValue+", partValue="+partValue+",extra="+extra);

        if( !keyValue ||
            keyValue && name == "part" && !accessionValue ||
            keyValue && name == "block" && (!accessionValue || !partValue)
        ) {
            //console.log("key undefined! "+fieldName);

            var extraArr = new Array();
            extraArr['p1'] = accessionValue;
            extraArr['p2'] = partValue;
            extraArr['keytype'] = extra;

            if( name == "part" || name == "block" ) {
                //console.log("accessionValue is not empty");
                setKeyValue(element,name+fieldName,extraArr,single);
                return;
            }

            //setFieldType(element,fieldName); //for mrn and accession: change type and field mask

            setKeyValue(element,name+fieldName,extraArr,single);
            return;
        }

        element.button('loading');

        //console.log("get element name="+name+"key="+ keyValue+", parent="+ accessionValue + ", parent2="+ partValue+",extra="+extra);
        //console.log("asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal+", mrnKeyGlobal="+mrnKeyGlobal+", mrnKeytypeGlobal="+mrnKeytypeGlobal);

        $.ajax({
            url: urlCheck+name,
            type: 'GET',
            data: {key: keyValue, extra: extra, parent: accessionValue, parent2: partValue},
            contentType: 'application/json',
            dataType: 'json',
            success: function (data) {
                console.debug("get object ajax ok "+name);
                var gonext = 1;
                element.button('reset');
                if( data.id ) {      

                    if( !single ) {
                        gonext = checkParent(element,keyValue,name,fieldName); //check if this key is not used yet, when a new key field is checked in the added entity
                        //console.debug("1 gonext="+gonext);
                    }
                    
                    if( name == "accession" && gonext == 1) {
                        var parentkeyvalue = data['parent'];
                        var extraid = data['extraid'];
                        console.debug("key parent="+parentkeyvalue+", extraid="+extraid);
                        gonext = setPatient(element,parentkeyvalue,extraid,single);
                    }                                          
                       
                    //console.debug("0 gonext="+gonext);
                    if( gonext == 1 ) {
                        //console.debug("continue gonext="+gonext);
                        //first: set elements
                        setElementBlock(element, data);
                        //second: disable or enable element. Make sure this function runs after set Element Block
                        disableInElementBlock(element, true, "all", null, "notarrayfield");    
                        invertButton(element);
                    }
                    //getAccessionInfoDebug("After find element "+name);
                } else {
                    console.debug("not found");
                    disableInElementBlock(element, false, null, "notkey", null);
                    invertButton(element);

                    //set global block and part key values
                    //console.log("############### SET GLOBAL "+name+", value="+keyValue+", extra="+extra);
                    if( single ) {
                       if( name == "part" ) {
                            partKeyGlobal = keyValue;
                        }
                        if( name == "block" ) {
                            blockKeyGlobal = keyValue;
                        }
                        if( name == "accession" ) {
                            asseccionKeyGlobal = keyValue;
                            asseccionKeytypeGlobal = extra;
                        }
                        if( name == "mrn" ) {
                            mrnKeyGlobal = keyValue;
                            mrnKeytypeGlobal = extra;
                        } 
                    }
                    
                } 

            },
            error: function () {
                console.debug("get object ajax error "+name);
                element.button('reset');
                cleanFieldsInElementBlock( element, null, single );
                disableInElementBlock(element, false, "all", null, null);
                invertButton(element);
            }
        });
    } else {
        //printF(element,"undefined button:"+name);
    }

    return;
}

//check if parent has checked sublings with the same key valuess
function checkParent(element,keyValue,name,fieldName) {
    var parentEl = element.parent().parent().parent().parent().parent().parent().parent().parent().parent();
    //console.log("checkParent parentEl.id=" + parentEl.attr('id') + ", class="+parentEl.attr('class'));
   
    //if this patient has already another checked accession, then check current accession is not possible
    //get patient accession buttons  
    var retval = 1;
    
    var sublingsKey = parentEl.find('.'+name+fieldName).each(function() {
             
        //console.log("checkParent this.id=" + $(this) + ", class="+$(this));      
        
        var keyField = $(this).find('.keyfield');
        
        if( $(this).val() == "" ) {
            var sublingsKeyValue = keyField.val();
        } else {
            var sublingsKeyValue = keyField.select2("val");
        }

        //console.log("checkParent sublingsKeyValue=" + sublingsKeyValue);

//        if( $(this).find('#check_btn').find('i').attr("class") == "glyphicon glyphicon-remove" && sublingsKeyValue == keyValue ) {
        if( $(this).find('#check_btn').find('i').hasClass('removebtn') && sublingsKeyValue == keyValue ) {
            alert("This keyfield is already in use and it is checked");
            retval = 0;
            return false;   //break each
        }
    });               
    
    if( retval == 0 ) {
        return 0;
    }
    return 1;
}

//element: accession button
function setPatient( element, keyvalue, extraid, single ) {

    if( single ) {
        //console.log("single!");
        var parentEl = element.parent().parent().parent().parent().parent().parent().parent();
        var parentBtn = parentEl.find(".patientmrn");
    } else {
        var parentEl = element.parent().parent().parent().parent().parent().parent().parent().parent().parent();
        var parentBtn = parentEl.find("#check_btn");
    }

    //console.log("@@@@@@@@@@@@ element set parentEl.id=" + parentEl.attr('id') + ", class="+parentEl.attr('class'));
    //console.log("parentBtn.id=" + parentBtn.attr('id') + ", class="+parentBtn.attr('class'));

    //get parent key element
    var keyElement = findKeyElement(parentBtn);
    //console.log("keyElement.id=" + keyElement.element.attr('id') + ", class="+keyElement.element.attr('class'));

    var keyBtn = keyElement.element.parent().parent().find('#check_btn');
    //console.log("keyBtn id=" + keyBtn.attr('id') + ", class="+keyBtn.attr('class')+", count="+keyBtn.length);

    //var keyBtnStatusClass = keyBtn.find("i").attr("class");
    //console.log("keyBtnStatusClass=" + keyBtnStatusClass);

    //check if parent is set already and has different keyfield value
    var parentKeyValue = parentEl.find(".patientmrn").find('.keyfield');

    //find mrntype
    var mrnholder = parentKeyValue.closest('.row');
    var mrntypeEl = mrnholder.find('select.combobox');
    var currentextraid = mrntypeEl.select2("val");

    //console.log("parentKeyValue.val()=" + parentKeyValue.val() + ", keyvalue="+keyvalue + "; extraid="+extraid+", currentextraid="+currentextraid);

    if( parentKeyValue.val() == keyvalue && extraid == currentextraid ) {
        //console.log('keyvalues are the same');
        return 1;
    }
    
    //if this patient has already another checked accession, then check current accession is not possible
    //get patient accession buttons  
    var retval = 1;
    parentEl.find('.accessionaccession').each(function() {
        //if( $(this).find('#check_btn').find('i').attr("class") == "glyphicon glyphicon-remove" ) {
        if( $(this).find('#check_btn').find('i').hasClass('removebtn') ) {
            alert("The Patient has already checked accession. You can not use this accession, because it belongs to another patient");
            retval = 0;
        }
    });
    if( retval == 0 ) {
        return 0;
    }   
    
    //if( keyBtnStatusClass == "glyphicon glyphicon-remove" && parentKeyValue.val() && parentKeyValue.val() != keyvalue ) { //Remove Button Cliked
    if( keyBtn.find("i").hasClass('removebtn') && parentKeyValue.val() && parentKeyValue.val() != keyvalue ) {
        var r=confirm('Patient with MRN '+parentKeyValue.val()+' is already set in this form. Are you sure that you want to change the patient?');
        if( r == true ) {
            //console.log("you decide to continue");
        } else {
            //console.log("you canceled");
            return 0;
        } 
    }

    //if parent key field is already checked: clean it first
    //if( keyBtnStatusClass == "glyphicon glyphicon-remove" ) { //Remove Button Cliked
    if( keyBtn.find("i").hasClass('removebtn') ) {
        keyBtn.trigger("click");
    }

    waitWhenParentIsClean( keyElement, 0 );  //wait until check button is ready (cleaned)

    //keyElement.element.val(keyvalue);   //set parent key field
    //var keyBtn = keyElement.element.parent().parent().find('#check_btn');
    //keyBtn.trigger("click");

    function waitWhenParentIsClean( element, maxi ) {

        var keyBtn = keyElement.element.parent().parent().find('#check_btn');
        //console.log("keyBtn id=" + keyBtn.attr('id') + ", class="+keyBtn.attr('class'));
        //var keyBtnStatusClass = keyBtn.find("i").attr("class");
        var isRemoveBtn = keyBtn.find("i").hasClass('checkbtn');
        //console.log("keyBtnStatusClass=" + keyBtnStatusClass);

        setTimeout(function(){
            if( !isRemoveBtn ) {
                if( maxi > 20 ) {
                    return 0;
                }
                maxi++;
                //console.log("parent key is not clean, maxi="+maxi);
                waitWhenParentIsClean(element,maxi);
            }
            else{
                //console.log("parent key is clean");
                //set parent key and extra (mrntype)
                var mrnArr = new Array();
                mrnArr['text'] = keyvalue;
                mrnArr['keytype'] = extraid;
                var keytypeElement = keyElement.element.closest('.row').find('.combobox').not("*[id^='s2id_']");
                setKeyGroup( keytypeElement, mrnArr );

                var keyBtn = keyElement.element.parent().parent().find('#check_btn');
                keyBtn.trigger("click");
                //getAccessionInfoDebug("set Patient:");
                return 1;
            }
        }, 300);
    }
    return 1;  
}


//set Element. Element is a block of fields
//element: check_btn element
//cleanall: clean all fields
//key: set only key field
function setElementBlock( element, data, cleanall, key ) {

    //console.debug( "element.id=" + element.attr('id') + ", class=" + element.attr('class') );
    var parent = element.parent().parent().parent().parent().parent().parent();
    //console.log("set parent.id=" + parent.attr('id') + ", class=" + parent.attr('class') + ", key="+key);

    var single = false;
    if( !parent.attr('id') ) {
        var single = true;
        var parent = element.parent().parent().parent().parent().parent().parent().parent();
        //console.log("Single set! parent.id=" + parent.attr('id') + ", class=" + parent.attr('class') + ", key="+key);
    }

    //console.log("key="+key+", single="+single);
    //printF(parent,"Set Element Parent: ");

    if( key == "key" && single ) {
        var inputField = element.parent().find('.keyfield').not("*[id^='s2id_']");
        //console.log("inputField.id=" + inputField.attr('id') + ", class=" + inputField.attr('class'));
        var idsArrTemp = inputField.attr("id").split("_");
        var field = idsArrTemp[idsArrTemp.length-fieldIndex];    //default
        //console.log("Single Key field=" + field);
        if( field == "partname" ) {
            var elements = $('#part-single').find('.keyfield').not("*[id^='s2id_']");
        } else if( field == "blockname" ) {
            var elements = $('#block-single').find('.keyfield').not("*[id^='s2id_']");
        } else if( field == "accession" ) {
            //var elements = $('#accession-single').find('.keyfield').not("*[id^='s2id_']");
            var elements = $('.singleorderinfo').find('.accessiontype-combobox').not("*[id^='s2id_']");    //treat accession as a group
        } else if( field == "mrn" ) {
            var elements = $('.singleorderinfo').find('.mrntype-combobox').not("*[id^='s2id_']");    //treat mrn as a group
        } else {
            console.debug('WARNING: logical error! No key for single order form is found: field='+field);
        }
    } else {
        //console.log("regular set element block");
        var elements = parent.find(selectStr).not("*[id^='s2id_']");
    }

    //console.log("elements.length=" + elements.length);

    for( var i = 0; i < elements.length; i++ ) {

        //console.log('\n\n'+"Element.id=" + elements.eq(i).attr("id")+", class="+elements.eq(i).attr("class"));

        //  0         1              2           3   4  5
        //oleg_orderformbundle_orderinfotype_patient_0_mrn  //length=6
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        var classs = elements.eq(i).attr("class");
        var value = elements.eq(i).attr("value");
        //console.log("id=" + id + ", type=" + type + ", class=" + classs + ", value=" + value );

        //exception
        if( id && id.indexOf("primaryOrgan") != -1 ) {
            //console.log("skip id="+id);
            continue;
        }

        if( id ) {

            var idsArr = elements.eq(i).attr("id").split("_");
            var field = idsArr[idsArr.length-fieldIndex];    //default
            //console.log("######## field = " + field);// + ", data text=" + data[field]['text']);

            if( key == "key" ) {

                if( $.inArray(field, keys) != -1 ) {
                    //console.log("set key field = " + data[field][0]['text'] );
                    setArrayField( elements.eq(i), data[field], parent );
                    //elements.eq(i).val(data[field]);
                    break;
                }
            }

//            if( type == "radio" ) {
//                field = idsArr[idsArr.length-(fieldIndex + 1)];
//            }

            if( type == "hidden" ) {
                field = idsArr[idsArr.length-(fieldIndex + 1)];
            }

            if( data == null  ) {   //clean fields
                //console.log("data is null");
                if( $.inArray(field, keys) == -1 || cleanall) {
                    elements.eq(i).val(null);   //clean non key fields
                } else {
                    //console.log("In array. Additional check for field=("+field+")");
                    if( field == "partname" ) {
                        var holder = idsArr[idsArr.length-holderIndex];
                        //console.log("holder="+holder);
                        if( holder != "part" && holder != "block" ) {
                            //console.log("disable!!!!");
                            elements.eq(i).val(null);   //clean non key fields with filed "name"
                        }
                    }
                }
            } else {

                //get field name for select fields i.e. procedure
                if( classs && classs.indexOf("select2") != -1 ) {

                    holder = idsArr[idsArr.length-holderIndex];
                    //console.log("select2 holder="+holder);
                    if( holder != "part" && holder != "block" && holder != "patient" ) {
                        field = holder;
                        //console.log("new field="+field);
                    }
                }

                //console.log("2 field = " + field);
                if( data[field] && data[field] != undefined && data[field] != "" ) {
                    //console.log("data is not null: set text field");    // = " + data[field][0]['text']);
                    setArrayField( elements.eq(i), data[field], parent );
                } else {
                    //console.log("data is empty: don't set text field");
                }

                //console.log("diseaseTypeRender");
                //diseaseTypeRender();

            }

        }

    } //for

}

//set array field such as ClinicalHistory array fields
//element is an input element jquery object
function setArrayField(element, dataArr, parent) {

    if( !dataArr ) {
        return false;
    }

    var type = element.attr("type");
    var classs = element.attr("class");
    var tagName = element.prop("tagName");
    var value = element.attr("value");
    //console.log("Set array: type=" + type + ", id=" + element.attr("id")+", classs="+classs + ", len="+dataArr.length + ", value="+value+", tagName="+tagName);

    for (var i = 0; i < dataArr.length; i++) {

        //var dataArr = data[field];
        var id = dataArr[i]["id"];
        var text = dataArr[i]["text"];
        var provider = dataArr[i]["provider"];
        var date = dataArr[i]["date"];
        var validity = dataArr[i]["validity"];
        var coll = i+1;

        //console.log( "set array field i="+i+", text=" + text + ", provider="+provider+", date="+date + ", validity="+validity );

        //console.log("parent id=" + parent.attr("id"));
        var idsArr = parent.attr("id").split("_");
        var elementIdArr = element.attr("id").split("_");
        // 0        1               2           3    4      5          6        7
        //oleg_orderformbundle_orderinfotype_patient_0_clinicalHistory_0_clinicalHistory
        // 0        1               2           3    4      5   6     7     8   9  10      11      12 13
        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_diffDisident_0_name

        //console.log("in loop parent.id=" + parent.attr("id") + ", tagName=" + tagName + ", type=" + type + ", classs=" + classs + ", text=" + text );

        var fieldName = elementIdArr[elementIdArr.length-fieldIndex];
        var holderame = elementIdArr[elementIdArr.length-holderIndex];
        var ident = holderame+fieldName;
        //console.log("ident=" + ident + ", coll="+coll );

        //var attachElement = element.parent().parent().parent().parent().parent();

        var attachElement = parent.find("."+ident.toLowerCase());   //patientsex

        //console.log("attachElement class="+attachElement.attr("class")+",id="+attachElement.attr("id"));

        if( $.inArray(fieldName, arrayFieldShow) != -1 ) { //show all fields from DB

            //patient_0_0_0_0_0_0_0_0

            //var name = idsArr[0];
            var patient = idsArr[1];
            var procedure = idsArr[2];
            var accession = idsArr[3];
            var part = idsArr[4];
            var block = idsArr[5];
            var slide = idsArr[6];

            //console.log("Create array empty field, fieldName=" + fieldName + ", patient="+patient+", part="+part );

            //fieldName = "clinicalHistory";

            var newForm = getCollField( ident, patient, procedure, accession, part, block, slide, coll );
            //console.log("newForm="+newForm);

            var labelStr = " entered on " + date + " by "+provider + "</label>";
            newForm = newForm.replace("</label>", labelStr);

            var idStr = 'type="hidden" value="'+id+'" ';
            newForm = newForm.replace('type="hidden"', idStr);

            //console.log("newForm="+newForm);

            if( fieldName == "disident" && orderformtype == "single" ) {
                //attachElement
                attachElement = $('.partdiffdisident');
                //console.log("attachElement class="+attachElement.attr("class")+",id="+attachElement.attr("id"));
                $('#partdisident_marker').append(newForm);
            } else {
                //console.log("attachElement class="+attachElement.attr("class")+",id="+attachElement.attr("id"));
                attachElement.prepend(newForm);
            }

        } else {    //show the valid field (with validity=1)
            //console.log("NO SHOW");
        }

        //set data
        if( tagName == "INPUT" ) {
            //console.log("input tagName: fieldName="+fieldName);

            if( type == "file" ) {

                element.hide();
                //var paperLink = '<a href="../../../../web/uploads/documents/'+dataArr[i]["path"]+'" target="_blank">'+dataArr[i]["name"]+'</a>';
                var paperLink = text;
                //console.log("paperLink="+paperLink);
                element.parent().append(paperLink);

            } else if( type == "text" ) {
                //console.log("type text, text="+text);

                if( fieldName == "accession" || fieldName == "mrn" ) {
                    setKeyGroup(element,dataArr[i]);
                    continue;
                }

                //save keys for single form, because all keys will be removed by the first clean functions
                if( orderformtype == "single") {
                    if( fieldName == "partname" ) {
                        partKeyGlobal = text;
                    }
                    if( fieldName == "blockname" ) {
                        blockKeyGlobal = text;
                    }
                }

                //find the last attached element to attachElement
                if( fieldName != "diffDisident" && orderformtype == "single" ) {
                    var firstAttachedElement = element; //for single & disident (Diagnosis) there is only one field: this element
                } else {
                    var firstAttachedElement = attachElement.find('input,textarea').first();
                }
                
                //printF(firstAttachedElement,"firstAttachedElement: ");
                if( fieldName == "partname" || fieldName == "blockname" ) {
                    if( orderformtype == "single" ) {
                        var firstAttachedElement = element;
                    } else {
                        var firstAttachedElement = attachElement.find('.keyfield ').first();
                    }
                    firstAttachedElement.select2('data', {id: text, text: text});
                } else {
                    //console.log("!!!!!!!!!!!! Set Value text="+text);
                    firstAttachedElement.val(text);
                }


            } else if( classs && classs.indexOf("datepicker") != -1 ) {
                //console.log("datepicker");
                var firstAttachedElement = attachElement.find('input').first();
                if( text && text != "" ) {
                    firstAttachedElement.datepicker( 'setDate', new Date(text) );
                    firstAttachedElement.datepicker( 'update');
                } else {
                    firstAttachedElement.datepicker({autoclose: true});
                    //firstAttachedElement.val( 'setDate', new Date() );
                    //firstAttachedElement.datepicker( 'update');
                }
            }

        } else if ( tagName == "TEXTAREA" ) {

            if( fieldName == "disident" && orderformtype == "single" ) {
                var firstAttachedElement = $('#partdisident_marker').find('.row').find('textarea'); //the last diffDiagnosis field is part's disident field
                //console.log("disident: " + firstAttachedElement.attr("class")+",id="+firstAttachedElement.attr("id") + ", text="+text);
            } else {
                var firstAttachedElement = attachElement.find('textarea').first();
            }

            //console.log("textarea firstAttachedElement class="+firstAttachedElement.attr("class")+",id="+firstAttachedElement.attr("id") + ", text="+text);
            firstAttachedElement.val(text);

        } else if ( (tagName == "DIV" && classs.indexOf("select2") != -1) || tagName == "SELECT" ) {

            //console.log("### DIV select2:  select field, id="+id+",text="+text);
            //console.log("id="+element.attr("id"));

            //set mrntype
            if( fieldName == "mrn" || fieldName == "accession" ) {
                //mrnKeyGlobal = text;
                //mrnKeytypeGlobal = dataArr[i]["keytype"];
                setKeyGroup(element,dataArr[i]);
            } else {
                element.select2('data', {id: text, text: text}); //TODO: set by id .select2.('val':id);
            }

        } else if ( tagName == "DIV" ) {
            //console.log("### set array field as DIV, id="+element.attr("id")+", text="+text );
            //get the first (the most recent added) group
            var firstAttachedElement = attachElement.find('.horizontal_type').first();
            processGroup( firstAttachedElement, dataArr[i], "ignoreDisable" );
        } else {
            //console.log("logical error: undefined tagName="+tagName);
        }

        //set hidden id of the element
        var directParent = element.parent().parent().parent();
        //console.log("hidden directParent="+directParent.attr("id") + ", class="+directParent.attr("class") );
        if( $.inArray(fieldName, arrayFieldShow) == -1 ) {
            var hiddenElement = directParent.find('input[type=hidden]');
            hiddenElement.val(id);
            //console.log("set hidden "+fieldName+", set id="+id + " hiddenId="+hiddenElement.attr("id") + " hiddenClass="+hiddenElement.attr("class") );
        }

    } //for loop

}

//set key type field
//element - is key type element (combobox)
function setKeyGroup( element, data ) {
    //console.log("########### set key group: element id="+element.attr("id") + ", class="+element.attr("class")+", keytype="+data['keytype']+", text="+data['text']);

    if( element.attr('class').indexOf("combobox") == -1 ) {
        //console.log("key group: not a a keytype combobox => return");
        return;
    }

    var holder = element.closest('.row');
    //printF(holder,"Holder of key group:");

    //var keytypeEl = holder.find('select.combobox');
    var keytypeEl = holder.find('.combobox');
    //printF(keytypeEl,"Set Key Group: keytype Element:");
    keytypeEl.select2('val', data['keytype']);

    if( element.hasClass('mrntype-combobox') ) {
        setMrntypeMask(element,true);
        if( orderformtype == "single" ) {
            mrnKeyGlobal = data['text'];
            mrnKeytypeGlobal = data['keytype'];
        }
    }
    if( element.hasClass('accessiontype-combobox') ) {
        setAccessiontypeMask(element,true);
        if( orderformtype == "single" ) {
            asseccionKeyGlobal = data['text'];
            asseccionKeytypeGlobal = data['keytype'];
            //console.log("Set Key Group: asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal);
        }
    }
    //console.log("Set Key Group: asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal+", mrnKeyGlobal="+mrnKeyGlobal+", mrnKeytypeGlobal="+mrnKeytypeGlobal);

    var inputholder = getButtonParent(element);
    var keyEl = inputholder.find('input.keyfield');
    //console.log("set keytype group: keyEl id="+keyEl.attr("id") + ", class="+keyEl.attr("class")+", keyEl.length="+keyEl.length);
    keyEl.val(data['text']);
}

//process groups such as radio button group
function processGroup( element, data, disableFlag ) {

    var elementIdArr = element.attr("id").split("_");
    var fieldName = elementIdArr[elementIdArr.length-(fieldIndex+1)];

    //var element = elementInside.parent().parent().parent();
    //var radios = element.find("input:radio");

    //console.log("process group id="+element.attr("id")+ ", class="+element.attr("class") + ", fieldName="+fieldName );

    var partId = 'input[id*="'+fieldName+'_"]:radio';
    var members = element.find(partId);

    for( var i = 0; i < members.length; i++ ) {
        var localElement = members.eq(i);
        var value = localElement.attr("value");
        //console.log("radio id: " + localElement.attr("id") + ", value=" + value );

        if( disableFlag == "ignoreDisable" ) {  //use to set radio box

            if( data && data != "" ) {  //set fields with data
                //console.log("data ok, check radio (data): " + value + "?=" + data['text'] );
                if( value == data['text'] ) {
                    //console.log("Match!" );
                    //console.log("show and set children: disableFlag="+disableFlag+", origin="+data['origin']+", primaryorgan="+data['primaryorgan']);
                    localElement.prop('checked',true);
                    diseaseTypeRenderCheckForm(element,data['origin'],data['primaryorgan']);    //set diseaseType group
                }
            } else {
                //console.log("no data radio: value=" + value);
                //console.log("hide children: disableFlag="+disableFlag);
                localElement.prop('checked',false);
                hideDiseaseTypeChildren( element ); //unset and hide diseaseType group

            }

        } else  {
            if( disableFlag ) {
                //console.log("disable radio: value=" + value);
                if( localElement.is(":checked") ){
                    localElement.attr("disabled", false);
                } else {
                    localElement.attr("disabled", true);
                }
            } else {
                //console.log("enable radio: value=" + value);
                localElement.prop("disabled", false);
            }
        }

    }

}

function cleanArrayFieldSimple( element, field, single ) {
    //console.log( "clean array field id=" + element.attr("id") );

    //delete if id != 0
    if( element.attr("id") && element.attr("id").indexOf(field+"_0_field") != -1 ) {
        element.val(null);
    } else {
        element.parent().parent().remove();
    }
}

//element - input field element
function cleanArrayField( element, field, single ) {

    if( field != "diffDisident" ) {
        cleanArrayFieldSimple(element,field,single);
        return;
    }

    //clean array field id=oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_diffDisident_2_field
    //console.log( "clean array element id=" + element.attr("id") + ", field=" + field );
    //delete if id != 0 or its not the last element

    //get row element - fieldHolder
    if( element.is('[readonly]') ) {    //get row for gray out fields without buttons
        var fieldHolder = element.parent().parent();
    } else {                            //get row for enabled fields with buttons
        //var fieldHolder = element.parent().parent().parent().parent().parent();
        var fieldHolder = element.parent().parent().parent().parent().parent();
    }

    //console.log( "fieldHolder id=" + fieldHolder.attr("id") + ", field=" + fieldHolder.attr("class") );

    var rows = fieldHolder.parent().find('.row');

    //console.log( "rows.length=" + rows.length );

    if( rows.length == 0 ) {
        return false;
    }

    //if( element.attr("id") && element.attr("id").indexOf(field+"_0_field") != -1 || rows.length == 1 ) {
    if( rows.length == 1 ) {

        element.val(null);

        //change - button (if exists) by + button
        var delBtn = element.parent().find('.delbtnCollField');
        //console.log("work on delBtn id="+delBtn.attr("id")+",class="+delBtn.attr("class"));
        if( delBtn.length != 0 ) {

            //console.log("delBtn exists !");
            //add + btn if not exists
            var addBtn = element.parent().find('.addbtnCollField');
            //console.log("work on addBtn id="+addBtn.attr("id")+",class="+addBtn.attr("class"));
            if( addBtn.length == 0 ) {
                delBtn.after( getAddBtn() );
            }

            delBtn.remove();
        } else {
            //console.log("no delBtn");
        }

        //Optional: change id of all element in row to '0'. This will bring the form to the initial state.
        changeIdtoIndex(element,field,0);

    } else {
        //delete hole row
        //console.log( "delete: fieldHolder id=" + fieldHolder.attr("id") + ", class=" + fieldHolder.attr("class") );
        fieldHolder.remove();
    }
}

function changeIdtoIndex( element, field, index ) {

    //get row element - fieldHolder
    if( element.is('[readonly]') ) {    //get row for gray out fields without buttons
        var fieldHolder = element.parent().parent();
    } else {                            //get row for enabled fields with buttons
        //var fieldHolder = element.parent().parent().parent().parent().parent();
        var fieldHolder = element.parent().parent().parent().parent().parent();
    }

    //change id of the field to 0
    var fieldId = element.attr("id");
    var fieldIdOrig = fieldId;
    var fieldName = element.attr("name");
    //console.log("fieldId="+fieldId+", fieldName="+fieldName);

    var idArr = fieldId.split("_"+field+"_");
    var idValue = idArr[1].split("_")[0];
    //console.log("idValue="+idValue);

    //var regexId = new RegExp( field + '_' + idValue, 'g' );
    fieldId = fieldId.replace( field + '_' + idValue, field + '_' + index);

    //change name of the field to 0
    var nameArr = fieldName.split("["+field+"]");
    var nameValueStr = nameArr[1];
    var nameValueArr = nameValueStr.split("[");
    var nameValue = nameValueArr[1].split("]")[0];
    //console.log("nameValue="+nameValue);

    var strTofind = '[' + field + ']' + '[' + nameValue + ']';
    //console.log("strTofind="+strTofind);    //strTofind=[diffDisident][6]
    var strReplace = '[' + field + ']' + '['+index+']';
    //console.log("strReplace="+strReplace);

    fieldName = fieldName.replace(strTofind, strReplace);   //[diffDisident][0]

    //console.log("fieldId="+fieldId+", fieldName="+fieldName);

    element.attr('id',fieldId);
    element.attr('name',fieldName);

    //replace id of label
    var rows = fieldHolder.parent().find('.row').first();
    //console.log( "rows id=" + rows.attr("id") + ", class=" + rows.attr("class") );

    var rowLabel = rows.first().find($('label[for='+fieldIdOrig+']'));
    //console.log( "rowLabel id=" + rowLabel.attr("id") + ", class=" + rowLabel.attr("class") );

    //var textLabel = rows.first().find($('label[for='+fieldIdOrig+']')).text();
    //console.log( "textLabel=" + textLabel );

    rowLabel.attr('id',fieldId);
    rowLabel.attr('for',fieldId);

    return;
}

//clean fields in Element Block, except key field
//all: if set to "all" => clean all fields, including key field
function cleanFieldsInElementBlock( element, all, single ) {

    var parent = element.parent().parent().parent().parent().parent().parent();

    //if( !parent.attr('id') ) {
    if( single ) {
        var parent = element.parent().parent().parent().parent().parent().parent().parent();
        //console.log("set parent.id=" + parent.attr('id') + ", class=" + parent.attr('class') + ", all="+all);
    }

    //console.log("clean parent.id=" + parent.attr('id'));
    //printF(parent,"clean => parent");

    var elements = parent.find(selectStr).not("*[id^='s2id_']");

    for (var i = 0; i < elements.length; i++) {

        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        var tagName = elements.eq(i).prop('tagName');
        var classs = elements.eq(i).attr('class');

        //don't process slide fields
        if( id && id.indexOf("_slide_") != -1 ) {
            continue;
        }
        //don't process fields not containing patient (orderinfo fields)
        if( id && id.indexOf("_patient_") == -1 ) {
            continue;
        }
        //don't process patient fields if the form was submitted by single form: click on accession,part,block delete button
        if( single && id && id.indexOf("_procedure_") == -1 ) {
            continue;
        }

        //console.log("clean id="+id+", type="+type+", tagName="+tagName);

        if( type == "file" ) {

            elements.eq(i).parent().find('a').remove();
            elements.eq(i).show();

        } else if( type == "text" || !type ) {
            var clean = false;
            var idsArr = id.split("_");
            var field = idsArr[idsArr.length-fieldIndex];
            if( all == "all" ) {
                clean = true;
            } else {
                //check if the field is not key
                if( !isKey(elements.eq(i), field) ) {
                    clean = true;
                }
            }
            if( clean ) {
                //console.log("in array field=" + field );
                if( $.inArray(field, arrayFieldShow) == -1 ) {
                   //console.log("clean not as arrayFieldShow");

                    if( tagName == "DIV" && classs.indexOf("select2") == -1 ) {
                        //console.log("clean as radio");
                        processGroup( elements.eq(i), "", "ignoreDisable" );
                    } else if( classs.indexOf("select2") != -1 ) {
                        //console.log("clean as select, field="+field);
                        if( field == "mrn" || field == "accession" ) { //special case keytype: preset to the first default value
                            //console.log("preset default keytype to 1, id="+elements.eq(i).attr('id')+", class="+elements.eq(i).attr('class'));
                            //elements.eq(i).select2('data', {id: '1', text: 'New York Hospital MRN'});
                            elements.eq(i).select2("val", "1" );
                        } else {
                            elements.eq(i).select2('data', null);
                        }

                    } else {
                        //console.log("clean as regular");
                        elements.eq(i).val(null);
                    }

                } else {
                    //console.log("clean as an arrayFieldShow");
                    cleanArrayField( elements.eq(i), field, single );
                }
            }
        }

    }
}

function isKey(element, field) {
    var idsArr = element.attr("id").split("_");
    if( $.inArray(field, keys) == -1 ) {
        return false;
    } else {
        if( field == "name" ) {
            var holder = idsArr[idsArr.length-holderIndex];
            //console.log("holder="+holder);
            if( holder == "part" || holder == "block" ) {
                return true
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}


function initAllElements() {

    if( cicle == "new" ) {
        var check_btns = $("[id=check_btn]");
        //console.log("check_btns.length="+check_btns.length);
        for (var i = 0; i < check_btns.length; i++) {
            var idArr = check_btns.eq(i).attr("id").split("_");
            if( idArr[2] != "slide" && check_btns.eq(i).attr('flag') != "done" ) {
                check_btns.eq(i).attr('flag', 'done');
                disableInElementBlock(check_btns.eq(i), true, null, "notkey", null);
            }
        }
    }

}

//all: "all" => disable/enable all fields including key field
//flagKey: "notkey" => disable/enable all fields, but not key field (inverse key)
//flagArrayField: "notarrayfield" => disable/enable array fields
function disableInElementBlock( element, disabled, all, flagKey, flagArrayField ) {

    //console.log("disable element.id=" + element.attr('id'));

    var parent = element.parent().parent().parent().parent().parent().parent();

    var single = false;
    if( !parent.attr('id') ) {
        var parent = element.parent().parent().parent().parent().parent().parent().parent();
        var single = true;
    }

    //console.log("parent.id=" + parent.attr('id') + ", parent.class=" + parent.attr('class'));

    var elements = parent.find(selectStr).not("*[id^='s2id_']");

    //console.log("elements.length=" + elements.length);

    for (var i = 0; i < elements.length; i++) {

        //console.log("element.id=" + elements.eq(i).attr("id"));
        //  0         1              2           3   4  5
        //oleg_orderformbundle_orderinfotype_patient_0_mrn  //length=6
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");

        //don't process slide fields
        if( id && id.indexOf("_slide_") != -1 ) {
            continue;
        }
        //don't process fields not containing patient (orderinfo fields)
        if( id && id.indexOf("_patient_") == -1 ) {
            continue;
        }
        //don't process patient fields if the form was submitted by single form: click on accession,part,block delete button
        if( single && id && id.indexOf("_procedure_") == -1 ) {
            continue;
        }
        //don't process 0 disident field: part's Diagnosis :
        if( single && id && id.indexOf("disident_0_field") != -1 ) {
            continue;
        }

        if( id && type != "hidden" ) {

            var thisfieldIndex = fieldIndex;
            if( type == "radio" ) {
                var thisfieldIndex = fieldIndex + 1;
            }

            var idsArr = elements.eq(i).attr("id").split("_");
            var field = idsArr[idsArr.length-thisfieldIndex];
            //console.log("disable field=(" + field + ")");

            if( all == "all" ) {
                disableElement(elements.eq(i),disabled);
            }

            if( flagKey == "notkey" ) {
                //check if the field is not key
                if( isKey(elements.eq(i), field) && flagKey == "notkey" ) {
                    if( disabled ) {    //inverse disable flagKey for key field
                        disableElement(elements.eq(i),false);
                    } else {
                        disableElement(elements.eq(i),true);
                    }
                } else {
                    disableElement(elements.eq(i),disabled);
                }
            }

            if( flagArrayField == "notarrayfield" ) {
                if( $.inArray(field, arrayFieldShow) != -1 ) {
                    //console.log("notarrayfield (not '_0_field'): disable array id="+elements.eq(i).attr("id"));
                    if( elements.eq(i).attr("id") && elements.eq(i).attr("id").indexOf(field+"_0_field") != -1 ) {
                        if( disabled ) {    //inverse disable flag for key field
                            disableElement(elements.eq(i),false);
                        } else {
                            disableElement(elements.eq(i),true);
                        }
                    }
                }               
            }

        }

    }
}

function disableElement(element, flag) {

    var type = element.attr('type');
    var classs = element.attr('class');
    var tagName = element.prop('tagName');

    //console.log("disable classs="+classs+", tagName="+tagName+", type="+type+", id="+element.attr('id'));

    if( tagName == "DIV" && classs.indexOf("select2") == -1 ) { //only for radio group
        //console.debug("radio disable classs="+classs+", id="+element.attr('id'));
        processGroup( element, "", flag );
        return;
    }

    if( tagName == "SELECT" || tagName == "DIV" && classs.indexOf("select2") != -1 ) { //only for select group
        //console.debug("select disable classs="+classs+", id="+element.attr('id')+", flag="+flag);
        if( flag ) {    //disable
            element.select2("readonly", true);
        } else {    //enable
            element.select2("readonly", false);
            element.attr("readonly", false);
            element.removeAttr( "readonly" );
        }
        return;
    }

    if( flag ) {

        if( type == "file" ) {
            //console.log("file disable field id="+element.attr("id"));
            element.attr('disabled', true);
        } else {
            //console.log("general disable field id="+element.attr("id"));
            element.attr('readonly', true);
        }

        if( classs && classs.indexOf("datepicker") != -1 ) {
            //console.log("disable datepicker classs="+classs);
            initDatepicker(element,"remove");
        }

        //disable children buttons
        element.parent().find("span[type=button]").attr("disabled", "disabled");

    } else {

        if( type == "file" ) {
            //console.log("file enable field id="+element.attr("id"));
            element.attr('disabled', false);
        } else {
            //console.log("general enable field id="+element.attr("id"));
            element.attr("readonly", false);
            element.removeAttr( "readonly" );
        }

        //enable children buttons
        element.parent().find("span[type=button]").removeAttr("disabled");

        if( classs && classs.indexOf("datepicker") != -1 ) {
            //console.log("enable datepicker classs="+classs);
            initDatepicker(element);
        }

    }
}

function invertButton(btn) {
    //console.log("invert Button: glyphicon class="+btn.find("i").attr("class"));
//    if( btn.find("i").attr("class") == "glyphicon glyphicon-check" ) {
    if( btn.find("i").hasClass('checkbtn') ) {
        //console.log("check=>remove");
        btn.find("i").removeClass('glyphicon-check').addClass('glyphicon-remove');
        btn.find("i").removeClass('checkbtn').addClass('removebtn');
    } else {
        //console.log("remove=>check");
        btn.find("i").removeClass('glyphicon-remove').addClass('glyphicon-check');
        btn.find("i").removeClass('removebtn').addClass('checkbtn');
    }
    //console.log("finish invert Button: glyphicon class="+btn.find("i").attr("class"));
}


function setKeyValue( btnElement, name, parentValueArr, single ) {

    //console.log("\n\nset Key Value name="+name);

    if( name == "patientmrn" ) {
        setKeyValueSingle( btnElement, name, parentValueArr );
        return false;
    }

    if( name == "accessionaccession" ) {
        setKeyValueSingle( btnElement, name, parentValueArr );
        return false;
    }

    if( name == "partpartname" ) {

        var accessionNumberElement = getAccessionNumberElement(btnElement,single);
        var accessionValue = accessionNumberElement.val();    //i.e. Accession #
        //console.log("set Key Value: accessionValue="+accessionValue);

        if( accessionValue && accessionValue != "" ) {
            //console.log("set Key Value: accesion field is set => generate part");
            setKeyValueSingle( btnElement, name, parentValueArr );
            return false;
        } else {    //generate accession #
            //console.log("set Key Value: accesion field is not set => generate accession");
            var holder = btnElement.closest('.panel-procedure');
            var accessionBtn = holder.find('.accessionaccession').find("#check_btn");
            if( single ) {
                var accessionBtn = $('.accessionbtn');
            }
            //console.log("set Key Value: accessionBtn.id="+accessionBtn.attr("id")+", class="+accessionBtn.attr("class"));
            accessionBtn.trigger("click");
            waitWhenParentIsGenerated( btnElement, name, 0, single );
            return false;
        }
        return false;
    }

    if( name == "blockblockname" ) {

        var partNumberElement = getPartNumberElement(btnElement, single);
        var partValue = partNumberElement.select2("val"); //i.e. Part #
        //console.log("blockblockname: partNumberElement.id=" + partNumberElement.attr('id') + ", class=" + partNumberElement.attr('class'));

        if( partValue && partValue != "" ) {
            //console.log("generate block! partValue ="+partValue);
            setKeyValueSingle( btnElement, name, parentValueArr );  //generate block
            return false;
        } else {    //generate partname
            //console.log("partvalue is empty! partValue ="+partValue);
            var holder = btnElement.closest('.panel-part');
            var partBtn = holder.find('.partpartname').find("#check_btn");
            if( single ) {
                var partBtn = $('.partbtn');
            }
            partBtn.trigger("click");
            waitWhenParentIsGenerated( btnElement, name, 0, single );
            return false;
        }

    }

    function waitWhenParentIsGenerated( origBtnElement, name, maxi, single ) {

        if( name == "partpartname" ) {
            var accessionNumberElement = getAccessionNumberElement(origBtnElement,single);
            var ParentValue = accessionNumberElement.val();    //i.e. Accession #
        } else {
            var partNumberElement = getPartNumberElement(origBtnElement, single);
            var ParentValue = partNumberElement.select2("val"); //i.e. Part #
        }

        //console.log("wait for "+name+": ParentValue="+ParentValue);

        setTimeout( function(){
            if( ParentValue && ParentValue != "" ) {
                //console.log("triger!, maxi="+maxi+", ParentValue="+ParentValue);
                origBtnElement.trigger("click");
                return 1;
            }
            else{
                if( maxi > 10 ) {
                    return 0;
                }
                maxi++;
                //console.log("gen: parent key is not set, maxi="+maxi);
                waitWhenParentIsGenerated(origBtnElement, name, maxi, single);
            }
        }, 300);
    }

}


//Generate new element by controller
//name: field name, i.e. partpartname
function setKeyValueSingle( btnElement, name, parentValueArr ) {

    if( parentValueArr ) {
        var parentValue = parentValueArr['p1'];
        var parentValue2 = parentValueArr['p2'];
        var keytype = parentValueArr['keytype'];
    } else {
        var parentValue = '';
        var parentValue2 = '';
        var keytype = '';
    }

    //console.log("ajax set key value name="+ name+", parentValue="+parentValue+",parentValue2="+parentValue2+", keytype="+keytype);
    btnElement.button('loading');

    $.ajax({
        url: urlCheck+name,
        type: 'GET',
        contentType: 'application/json',
        dataType: 'json',
        data: {key: parentValue, key2: parentValue2, extra: keytype },
        success: function (data) {
            btnElement.button('reset');
            if( data ) {
                console.debug("ajax key value data is found");
                setElementBlock(btnElement, data, null, "key");
                disableInElementBlock(btnElement, false, null, "notkey", null);
                invertButton(btnElement);
            } else {
                console.debuf('set key data is null');
            }
        },
        error: function () {
            btnElement.button('reset');
            console.debug("set key ajax error");
        }
    });

    return;
}

//remove key NOTPROVIDED if it was created by check on empty key field (entity status="reserved").
function removeKeyFromDB( element, btnElement, single ) {

    var name = element.name;
    //var keyValue =element.element.attr("value");
    var keyValue = element.element.val();
    //console.log("delete: name="+name+", keyValue="+keyValue+", single="+single);

    if( name == "partname" || name == "blockname" ) {
        var keyValue =element.element.select2("val");
        //console.log("select2 case, keyValue="+keyValue+", blockKeyGlobal="+blockKeyGlobal);
    }

    var accessionValue = element.accession;
    var partValue = element.partname;
    var extra = element.extra;

    //check global keys for single form, because all keys will be removed by the first clean functions
    if( orderformtype == "single" ) {
        if( name == "accession" && !keyValue ) {
            keyValue = asseccionKeyGlobal;
            extra = asseccionKeytypeGlobal;
        }
        if( name == "partname" ) {
            keyValue = partKeyGlobal;
            accessionValue = asseccionKeyGlobal;
            extra = asseccionKeytypeGlobal;
        }
        if( name == "blockname" ) {
            keyValue = blockKeyGlobal;
            partValue = partKeyGlobal;
            accessionValue = asseccionKeyGlobal;
            extra = asseccionKeytypeGlobal;
        }
        if( name == "mrn" ) {
            keyValue = mrnKeyGlobal;
            extra = mrnKeytypeGlobal;           
        }
    }

    //console.log("delete name="+name +", keyvalue="+keyValue + ", accessionValue="+accessionValue+", partValue="+partValue+", extra="+extra);
    //console.log("delete: asseccionKeyGlobal="+asseccionKeyGlobal+", asseccionKeytypeGlobal="+asseccionKeytypeGlobal+", partKeyGlobal="+partKeyGlobal+", blockKeyGlobal="+blockKeyGlobal+", mrnKeyGlobal="+mrnKeyGlobal+", mrnKeytypeGlobal="+mrnKeytypeGlobal);

    if( !keyValue ) {
        return false;
    }

    if( extra || accessionValue || partValue ) {
        var extraStr = "?";
    } else {
        var extraStr = "";
    }

    if( extra ) {
        extraStr = extraStr + "extra="+extra;
    }

    if( accessionValue ) {
        if( extraStr ) extraStr = extraStr + "&";
        extraStr = extraStr + "accession="+accessionValue;
    }

    if( partValue ) {
        if( extraStr ) extraStr = extraStr + "&";
        extraStr = extraStr + "partname="+partValue;
    }

    //btnElement.button('loading');
    function deleteSuccess(btnElement,single) {
        //printF(btnElement,"Delete on Success:")
        cleanFieldsInElementBlock( btnElement, "all", single );
        disableInElementBlock(btnElement, true, null, "notkey", null);
        invertButton(btnElement);
        setDefaultMask(btnElement);
    }

    function deleteError(btnElement,single) {
        if( !single ) {
            //printF(btnElement,"btnElement:");
            //check if all children buttons are not checked == has class removebtn
            var errors = 0;
            var checkBtns = btnElement.closest('.panel').find('#check_btn');
            //console.log('checkBtns.length='+checkBtns.length);
            checkBtns.each( function() {
                //printF($(this),'check btn=');
                //printF(btnElement,'btnElement=');
                if( $(this).attr('class') != btnElement.attr('class') ) {
                    if( $(this).find('i').hasClass('removebtn') ) {
                        errors++;
                    }
                }
            });

            //console.log('errors='+errors);
            if( errors == 0 ) {
                deleteSuccess(btnElement,single);
                return;
            }

            var childStr = "Child";
            if( name == "accession" ) {
                childStr = "Part";
            }
            if( name == "partname" ) {
                childStr = "Block";
            }
            alert("Can not delete this element. Make sure if " + childStr + " is deleted.");
        }
    }

    $.ajax({
        url: urlCheck+name+"/check/"+keyValue+extraStr,
        type: 'DELETE',
        contentType: 'application/json',
        dataType: 'json',
        async: false,
        success: function (data) {
            //console.debug("Delete data="+data);
            if( data != '-1' || single ) {
                //console.debug("Delete Success");
                deleteSuccess(btnElement,single);
            } else {
                //console.debug("Delete ok with Error");
                deleteError(btnElement,single);
            }
        },
        error: function () {
            //console.debug("Delete Error");
            deleteError(single);
        }
    });

    //console.log("remove exit: glyphicon class="+btnElement.find("i").attr("class"));
    return;
}

function findKeyElement( element, single ) {

    var parent = element.parent().parent().parent().parent().parent().parent();

    if( single ) {
        var parent = element.parent();
    }
    //console.log("find key element:: parent.id=" + parent.attr('id') + ", parent.class=" + parent.attr('class'));

    //var elements = parent.find('input,select').not("*[id^='s2id_']");
    var elements = parent.find('.keyfield').not("*[id^='s2id_']");

    //console.log("elements.length=" + elements.length);
    //printF(elements," found elements under button parent:");

    var keyElement = null;
    var name = "";
    for (var i = 0; i < elements.length; i++) {
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        //console.log("id=" + id + ", class=" + elements.eq(i).attr('class'));
        if( id && type != "hidden" ) {
            var idsArr = id.split("_");
            var field = idsArr[idsArr.length-fieldIndex];
            //console.log("set key value: field=(" + field + ")");
            if( $.inArray(field, keys) != -1 && id.indexOf('_keytype') == -1 ) {
                //console.log("set key value: found key=(" + field + "), id="+elements.eq(i).attr("id"));
                name = field;
                keyElement = elements.eq(i);
                break;
            }
        }
    }

    //printF(keyElement,"found input key field:");
    //console.log("keyElement val=" + keyElement.val());

    //find extra key: keytype
    var extra = null;
    if( name == "mrn" || name == "accession" ) {
        //var keytype = element.closest('.row').find( "."+name+"type-combobox").not("*[id^='s2id_']");
        var keytype = getKeyGroupParent(element).find( "."+name+"type-combobox").not("*[id^='s2id_']");
        //console.log("find key element: keytype.length="+keytype.length);
        if( keytype.length > 0 ) {
            extra = keytype.select2("val");
            //console.log("find key element: keytype id="+keytype.attr("id")+", class="+keytype.attr("class")+", extra="+extra);
        }
    }

    if( name == "partname" ) {
        var accessionNumberElement = getAccessionNumberElement(element,single);
        //printF(accessionNumberElement,"partname: accessionNumberElement=");
        var accessionValue = accessionNumberElement.val();    //i.e. Accession #
        //get extra for accession: keytype
        var keytype = getKeyGroupParent(accessionNumberElement).find('.accessiontype-combobox');
        extra = keytype.select2("val");
    }

    if( name == "blockname" ) {
        var accessionNumberElement = getAccessionNumberElement(element, single);
        //printF(accessionNumberElement,"blockname: accessionNumberElement=");
        var accessionValue = accessionNumberElement.val();    //i.e. Accession #
        var keytype = getKeyGroupParent(accessionNumberElement).find('.accessiontype-combobox');
        extra = keytype.select2("val");
        var partNumberElement = getPartNumberElement(element, single);
        var partValue = partNumberElement.select2("val"); //i.e. Part #
    }

    var res = new Array;
    res.element = keyElement;
    res.name = name;
    res.extra = extra;  //mrn type
    res.accession = accessionValue;
    res.partname = partValue;

    return res;
}

function getAccessionNumberElement( element, single ) {

    if( single ) {
        var accessionNumberElement = element.parent().parent().not("*[id^='s2id_']").find('.keyfield');
        //console.log("accessionNumberElement.id=" + accessionNumberElement.attr('id') + ", class=" + accessionNumberElement.attr('class'));
        return accessionNumberElement.eq(0);
    } else {
        var parent = element.parent().parent().parent().parent().parent().parent().parent().parent().parent();

        if( parent.attr('id') && parent.attr('id').indexOf("form_body_procedure") == -1 ) {
            var parent = element.parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
        }

        //console.log("get accession number: parent.id=" + parent.attr('id') + ", parent.class=" + parent.attr('class'));

        var accessionNumberHolder = parent.find('.accessionaccession');
        var accessionNumberElement = accessionNumberHolder.find('.keyfield');

        return accessionNumberElement.eq(0);
    }

}

function getPartNumberElement( element, single ) {
    if( single ) {
        var partNumberElement = $('#part-single').find('.keyfield').not("*[id^='s2id_']");
        //console.log("partNumberElement.id=" + partNumberElement.attr('id') + ", class=" + partNumberElement.attr('class'));
        return partNumberElement.first();
    } else {
        var parent = element.parent().parent().parent().parent().parent().parent().parent().parent().parent();

        if( parent.attr('id') && parent.attr('id').indexOf("form_body_part") == -1 ) {
            var parent = element.parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
        }
        //console.log("get part number: parent.id=" + parent.attr('id') + ", parent.class=" + parent.attr('class'));
        var partNumberHolder = parent.find('.partpartname');
        var partNumberElement = partNumberHolder.find('.keyfield');
        //console.log("1="+partNumberElement.val() + " 2="+partNumberElement.select2("val")  );
        return partNumberElement;
    }
}

//accesion-MRN link validation when the user clicks "Submit" on multi-slide form
function validateForm() {

    var totalError = 0;

    if( validateMaskFields() > 0 ) {
        return false;
    }

    //Initial check: get total number of checkboxes
    var totalcheckboxes = 0;

    var reruncount = 0;

    //console.log( "dataquality_message1[0]="+dataquality_message1[0] );
    //console.log( "dataquality_message2[0]="+dataquality_message2[0] );

    var countErrorBoxes = 0;

    var errorBoxes = $('#validationerror').find('.validationerror-added');
    //console.log("errorBoxes.length="+errorBoxes.length);

    for (var i = 0; i < errorBoxes.length; i++) {

        var errorBox = errorBoxes.eq(i);

        var checkedEl = errorBox.find("input:checked");
        //console.log("checkedEl="+checkedEl.val()+", id="+checkedEl.attr("id")+", class="+checkedEl.attr("class"));

            //console.log("value="+checkedEl.val());
            if( checkedEl.is(":checked") ){
                //console.log("checked value="+checkedEl.val());
                if( checkedEl.val() == "OPTION3" ) {
                    reruncount++;
                }
                if( checkedEl.val() == "OPTION1" ) {
                    setDataquality( countErrorBoxes, dataquality_message1[countErrorBoxes] );
                }
                if( checkedEl.val() == "OPTION2" ) {
                    setDataquality( countErrorBoxes, dataquality_message2[countErrorBoxes] );
                }
            } else {
                //
            }
            totalcheckboxes++;

        countErrorBoxes++;

    }

    //clear array
    dataquality_message1.length = 0;
    dataquality_message2.length = 0;

    //console.log("totalcheckboxes="+totalcheckboxes+",reruncount="+reruncount);


    if( totalcheckboxes == 0 ) {
        //continue
//    } else if( totalcheckboxes != 0 && totalcheckboxes == reruncount ) {    //all error boxes have third option checked
//        cleanValidationAlert();
    } else if( totalcheckboxes > 0 && reruncount > 0 ) { //submit was already pressed before and the third option is checked
        cleanValidationAlert();
    } else {    //return true;
        //return false; //testing
        return true;
    }

    if( orderformtype == "single") {
        var accessions = $('#accession-single').find('.keyfield');
        //console.log("singleform");
    } else {
        var accessions = $('.accessionaccession').find('.keyfield');
        //console.log("not singleform");
    }

    //console.log("accessions.length="+accessions.length + ", first id=" + accessions.first().attr('id') + ", class=" + accessions.first().attr('class') );
    var prototype = $('#form-prototype-data').data('prototype-dataquality');
    //console.log("prototype="+prototype);
    var index = 0;

    //for all accession fields
    accessions.each(function() {

        var accInput = $(this);
        var accValue = accInput.val();
        var acctypeField = accInput.closest('.row').find('.accessiontype-combobox').not("*[id^='s2id_']").first();
        var acctypeValue = acctypeField.select2("val");
        var acctypeText = acctypeField.select2("data").text;

        if( orderformtype == "single") {
            var mrnHolder = $('.panel-patient').find(".patientmrn");
        } else {
            var mrnHolder = accInput.closest('.panel-patient').find(".patientmrn");
        }

        var patientInputs = mrnHolder.find('.keyfield').not("*[id^='s2id_']").first();
        var mrnValue = patientInputs.val();
        //console.log("patientInputs.first().id=" + patientInputs.first().attr('id') + ", class=" + patientInputs.first().attr('class'));

        var patientMrnInputs = mrnHolder.find('.mrntype-combobox').not("*[id^='s2id_']").first();
        //var mrntypeValue = patientMrnInputs.select2("val");
        var mrntypeValue = patientMrnInputs.select2("val");
        var mrntypeData = patientMrnInputs.select2("data");
        //console.log("sel id="+mrntypeData.id);
        var mrntypeText = mrntypeData.text;
        //console.log("patientInputs.last().id=" + patientInputs.last().attr('id') + ", class=" + patientInputs.last().attr('class'));

        //console.log("accValue="+accValue + " mrnValue="+mrnValue+", mrntypeValue="+mrntypeValue  );

        if(
            accValue && accValue !="" && acctypeValue && acctypeValue !="" &&
            mrnValue && mrnValue !="" && mrntypeValue && mrntypeValue !=""
        ) {
            //console.log("validate accession-mrn-mrntype");

//            var mrn = "";
//            var mrntype = "";
//            var provider = "";
//            var date = "";

            $.ajax({
                url: urlCheck+"accession",
                type: 'GET',
                data: {key: accValue, extra: acctypeValue},
                contentType: 'application/json',
                dataType: 'json',
                async: false,
                success: function (data) {
                    //console.debug("get accession ajax ok");
                    if( data.id ) {

                        var mrn = data['parent'];
                        var mrntype = data['extraid'];
                        var mrnstring = data['mrnstring'];
                        var orderinfo = data['orderinfo'];

                        //console.log('mrn='+mrn+', mrntype='+mrntype);

                        if( mrn == mrnValue && mrntype == mrntypeValue ) {
                            //console.log("validated successfully !");
                        } else {
                            //console.log('mrn='+mrn+', mrntype='+mrntype+ " do not match to form's "+" mrnValue="+mrnValue+", mrntypeValue="+mrntypeValue);

                            var nl = "\n";    //"&#13;&#10;";

                            var message_short = "MRN-ACCESSION CONFLICT :"+nl+"Entered Accession Number "+accValue+","+acctypeValue+" belongs to Patient with "+mrnstring+", not Patient with MRN "
                                                +mrnValue+", "+mrntypeText+" as you have entered.";
                            var message = message_short + " Please correct ether the MRN or the Accession Number above.";


                            var message1 = "If you believe MRN "+mrn+" and MRN "+mrnValue + " belong to the same patient, please mark here:";
                            var dataquality_message_1 = message_short+nl+"I believe "+mrnstring+" and MRN "+mrnValue+", "+mrntypeText+" belong to the same patient";
                            dataquality_message1.push(dataquality_message_1);

                            var message2 = "If you believe Accession Number "+accValue+" belongs to patient MRN "+mrnValue+" and not patient MRN "+mrn+" (as stated by "+orderinfo+"), please mark here:";
                            var dataquality_message_2 = message_short+nl+"I believe Accession Number "+accValue+" belongs to patient MRN "+mrnValue+", "+mrntypeText+" and not patient "+mrnstring+" (as stated by "+orderinfo+")";
                            dataquality_message2.push(dataquality_message_2);

                            var message3 = "If you have changed the involved MRN "+mrnValue+" or the Accession Number "+accValue+" in the form above, please mark here:";

                            if( !prototype ) {
                                return false;
                            }

                            var newForm = prototype.replace(/__dataquality__/g, index);

                            newForm = newForm.replace("MRN-ACCESSION CONFLICT", message);

                            newForm = newForm.replace("TEXT1", message1);
                            newForm = newForm.replace("TEXT2", message2);
                            newForm = newForm.replace("TEXT3", message3);

                            //console.log("newForm="+newForm);

                            var newElementsAppended = $('#validationerror').append(newForm);
                            //var newElementsAppended = newForm.appendTo("#validationerror");

                            //red
                            accInput.parent().addClass("has-error");
                            patientInputs.parent().addClass("has-error");

                            setDataqualityData( index, accValue, acctypeValue, mrnValue, mrntypeValue );

                            totalError++;
                            index++;

                        }

                    } else {
                        console.debug("validation: accession object not found");
                    }
                },
                error: function () {
                    console.debug("validation: get object ajax error accession");
                }
            });

        }

    });

    //console.log("totalError="+totalError);
    //return false; //testing

    if( totalError == 0 ) {
        return true;
    } else {
        return false;
    }

}

function setDataquality(index,message) {
    var partid = "#oleg_orderformbundle_orderinfotype_dataquality_"+index+"_";
    //console.log("message=" + message);
    $(partid+'description').val(message);
}


function setDataqualityData( index, accession, acctype, mrn, mrntype ) {
    var partid = "#oleg_orderformbundle_orderinfotype_dataquality_"+index+"_";
    //console.log(accession + " " + acctype + " " + mrn + " " + mrntype);
    $(partid+'accession').val(accession);
    $(partid+'accessiontype').val(acctype);
    $(partid+'mrn').val(mrn);
    $(partid+'mrntype').val(mrntype);
}

function cleanValidationAlert() {
    if( cicle == "new" || cicle == "amend" ) {
        $('.validationerror-added').each(function() {
            $(this).remove();
        });
        //$('#validationerror').html('')
        dataquality_message1.length = 0;
        dataquality_message2.length = 0;
    }
}

function addKeyListener() {
    //remove has-error class from mrn and accession inputs
    $('.accessionaccession').find('.keyfield').parent().keypress(function() {
        $(this).removeClass('has-error');
    });
    $('.patientmrn').find('.keyfield').parent().keypress(function() {
        //console.log("remove has-error on keypress");
        $(this).removeClass('has-error');
    });
}

