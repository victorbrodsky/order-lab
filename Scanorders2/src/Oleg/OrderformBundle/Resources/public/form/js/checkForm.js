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

var keys = new Array("mrn", "accession", "partname", "blockname");   //TODO: change to patientmrn, accessionaccession, partname ...
var arrayFieldShow = new Array("clinicalHistory","age","diffDisident"); //display as array fields "sex"
var selectStr = 'input.form-control,div.patientsexclass,div.diseaseType,div.select2-container,[class^="ajax-combobox-"],[class^="combobox"],textarea,select';  //div.select2-container, select.combobox, div.horizontal_type

//  0         1              2           3   4  5  6   7
//oleg_orderformbundle_orderinfotype_patient_0_mrn_0_field
var fieldIndex = 3;     //get 'key'
var holderIndex = 5;    //get 'patient'
//console.log("urlCheck="+urlCheck);

function checkForm( elem, single ) {

    var element = $(elem);

    //console.log( "element.id=" + element.attr('id') + ", single="+single);

    var elementInput = element.parent().parent().find(".keyfield");

    if( single ) {
        var elementInput = element.parent().find(".keyfield");
    }

    //console.log("elementInput.class="+elementInput.attr('class') + ", id="+elementInput.attr('id'));

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

    if( element.find("i").attr("class") == "glyphicon glyphicon-remove" ) { //Remove Button Cliked

        //console.log("Remove Button Cliked");
        //setElementBlock(element, null, true);
        removeKeyFromDB(keyElement, element);
        cleanFieldsInElementBlock( element, "all", single );
        disableInElementBlock(element, true, null, "notkey", null);
        invertButton(element);
        return;

    } else {    //Check Button Cliked

        //console.log("Check Button Cliked");

        //get key field for this patient: oleg_orderformbundle_orderinfotype_patient_0_mrn

        var keyValue =keyElement.element.val();
        //console.log("keyElement id="+keyElement.element.attr("id")+", class="+keyElement.element.attr("class")+",val="+keyValue+",name="+name);

        if( name == "part" ) {
            var accessionNumberElement = getAccessionNumberElement(element,single);
            var accessionValue = accessionNumberElement.val();    //i.e. Accession #
        }

        if( name == "block" ) {
            var accessionNumberElement = getAccessionNumberElement(element, single);
            var accessionValue = accessionNumberElement.val();    //i.e. Accession #
            var partNumberElement = getPartNumberElement(element, single);
            var partValue = partNumberElement.select2("val"); //i.e. Part #                   
        }
        //console.log("accessionValue="+accessionValue+",partValue="+partValue);

        if( !keyValue ||
            keyValue && name == "part" && !accessionValue ||
            keyValue && name == "block" && (!accessionValue || !partValue)
        ) {
            //console.log("key undefined!");
//            $('#'+inputId).popover( {content:"Please fill out key field"} );
//            $('#'+inputId).popover('show');

            if( name == "part" || name == "block" ) {

                if( !single && (!accessionValue || (name == "block" && !partValue)) ) {

                    var blockError = "";
                    if( name == "block" ) {
                        var blockError = " and Part number";
                    }
                    var messageError = "Can not check "+capitaliseFirstLetter(name)+" name without Accession number"+blockError;

                    //console.log( "accessionValue is empty.");   // parentNumberElement id="+parentNumberElement.attr("id") + ", class=" + parentNumberElement.attr("class") );
                    accessionNumberElement.popover({
                        placement: 'bottom',
                        content: messageError
                    });
                    accessionNumberElement.popover('show');
                    //alert("Can not check "+capitaliseFirstLetter(name)+" name without Accession number");

                    if( name == "block" ) {
                        partNumberElement.popover({
                            placement: 'bottom',
                            content: messageError
                        });
                        partNumberElement.popover('show');
                    }

                    return;
                } else {
                    //console.log("accessionValue is not empty");
                    setKeyValue(element,name+fieldName,new Array(accessionValue,partValue));
                    return;
                }

            }

            setKeyValue(element,name+fieldName,null);
            //disableInElementBlock(element, false, null, "notkey", null);
            //invertButton(element);
            return;
        }

        element.button('loading');

        //console.log("get element name="+name+"key="+ keyValue+", parent="+ accessionValue + ", parent2="+ partValue);
        $.ajax({
            url: urlCheck+name,
            type: 'GET',
            data: {key: keyValue, parent: accessionValue, parent2: partValue},
            contentType: 'application/json',
            dataType: 'json',
            success: function (data) {
                console.debug("get object ajax ok "+name);
                var gonext = 1;
                element.button('reset');
                if( data.id ) {      

                    if( !single ) {
                        gonext = checkParent(element,keyValue,name,fieldName); //check if this key is not used yet
                        //console.debug("1 gonext="+gonext);
                    }
                    
                    if( name == "accession" && gonext == 1) {
                        var parentkeyvalue = data['parent'];
                        //console.debug("key parent="+parentkeyvalue);
                        gonext = setPatient(element,parentkeyvalue,single);
                    }                                          
                       
                    //console.debug("0 gonext="+gonext);                  
                    if( gonext == 1 ) {
                        //console.debug("continue gonext="+gonext);                         
                        //first: set elements
                        setElementBlock(element, data);
                        //second: disable or enable element. Make sure this function runs after setElementBlock
                        disableInElementBlock(element, true, "all", null, "notarrayfield");    
                        invertButton(element);
                    }                              
                } else {
                    console.debug("not found");
                    //cleanFieldsInElementBlock( element );
                    disableInElementBlock(element, false, null, "notkey", null);
                    invertButton(element);
                } 
                //invertButton(element);
            },
            error: function () {
                console.debug("get object ajax error "+name);
                element.button('reset');
                //setElementBlock(element, null);
                cleanFieldsInElementBlock( element );
                disableInElementBlock(element, false, "all", null, null);
                invertButton(element);
            }
        });
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

        if( $(this).find('#check_btn').find('i').attr("class") == "glyphicon glyphicon-remove" && sublingsKeyValue == keyValue ) {
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

function setPatient(element,keyvalue,single) {

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

    var keyBtnStatusClass = keyBtn.find("i").attr("class");
    //console.log("keyBtnStatusClass=" + keyBtnStatusClass);

    //check if parent is set already and has different keyfield value
    var parentKeyValue = parentEl.find(".patientmrn").find('.keyfield');

    //console.log("parentKeyValue.val()=" + parentKeyValue.val() + ", keyvalue="+keyvalue);

    if( parentKeyValue.val() == keyvalue ) {
        //console.log('keyvalues are the same');
        return 1;
    }
    
    //if this patient has already another checked accession, then check current accession is not possible
    //get patient accession buttons  
    var retval = 1;
    parentEl.find('.accessionaccession').each(function() {
        if( $(this).find('#check_btn').find('i').attr("class") == "glyphicon glyphicon-remove" ) {
            alert("The Patient has already checked accession. You can not use this accession, because it belongs to another patient");
            retval = 0;
        }
    });
    if( retval == 0 ) {
        return 0;
    }   
    
    if( keyBtnStatusClass == "glyphicon glyphicon-remove" && parentKeyValue.val() && parentKeyValue.val() != keyvalue ) { //Remove Button Cliked
        var r=confirm('Patient with MRN '+parentKeyValue.val()+' is already set in this form. Are you sure that you want to change the patient?');
        if( r == true ) {
            //console.log("you decide to continue");
        } else {
            //console.log("you canceled");
            return 0;
        } 
    }

    //if parent key field is already checked: clean it first
    if( keyBtnStatusClass == "glyphicon glyphicon-remove" ) { //Remove Button Cliked
        keyBtn.trigger("click");
    }

    waitWhenParentIsClean( keyElement, 0 );  //wait until check button is ready (cleaned)

    //keyElement.element.val(keyvalue);   //set parent key field
    //var keyBtn = keyElement.element.parent().parent().find('#check_btn');
    //keyBtn.trigger("click");

    function waitWhenParentIsClean( element, maxi ) {

        //var keyElement = findKeyElement(element);
        var keyBtn = keyElement.element.parent().parent().find('#check_btn');
        //console.log("keyBtn id=" + keyBtn.attr('id') + ", class="+keyBtn.attr('class'));
        var keyBtnStatusClass = keyBtn.find("i").attr("class");
        //console.log("keyBtnStatusClass=" + keyBtnStatusClass);

        setTimeout(function(){
            if( keyBtnStatusClass != "glyphicon glyphicon-check" ){
                if( maxi > 20 ) {
                    return 0;
                }
                maxi++;
                //console.log("parent key is not clean, maxi="+maxi);
                waitWhenParentIsClean(element,maxi);
            }
            else{
                //console.log("parent key is clean");
                keyElement.element.val(keyvalue);   //set parent key field
                var keyBtn = keyElement.element.parent().parent().find('#check_btn');
                keyBtn.trigger("click");
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

    //console.debug( "name=" + name + ", data.id=" + data.id + ", sex=" + data.sex );
    var parent = element.parent().parent().parent().parent().parent().parent();
    //console.log("set parent.id=" + parent.attr('id') + ", class=" + parent.attr('class') + ", key="+key);

    if( !parent.attr('id') ) {
        var parent = element.parent().parent().parent().parent().parent().parent().parent();
        //console.log("set parent.id=" + parent.attr('id') + ", class=" + parent.attr('class') + ", key="+key);
    }

    //var elements = parent.find('input,textarea,select');
    var elements = parent.find(selectStr);

    for (var i = 0; i < elements.length; i++) {

        //console.debug('\n\n'+"Element.id=" + elements.eq(i).attr("id"));

        //  0         1              2           3   4  5
        //oleg_orderformbundle_orderinfotype_patient_0_mrn  //length=6
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        var classs = elements.eq(i).attr("class");
        var value = elements.eq(i).attr("value");
        //console.log("id=" + id + ", type=" + type + ", class=" + classs + ", value=" + value );

        //exceptions
        if( id && id.indexOf("primaryOrgan") != -1 ) {
            //console.log("skip id="+id);
            continue;
        }

//        if( id && type != "hidden" ) {
        if( id ) {

            var idsArr = elements.eq(i).attr("id").split("_");
            var field = idsArr[idsArr.length-fieldIndex];    //default
            //console.log("field = " + field);// + ", data text=" + data[field]['text']);

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
                    if( holder != "part" && holder != "block" ) {
                        field = holder;
                        //console.log("new field="+field);
                    }
                }

                if( data[field] && data[field] != undefined && data[field] != "" ) {
                    //console.log("data is not null: set text field");    // = " + data[field][0]['text']);
                    setArrayField( elements.eq(i), data[field], parent );
                } else {
                    //console.log("data is not null: don't set text field");
                }

                //console.log("diseaseTypeRender");
                //diseaseTypeRender();

            }

        }

    }

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
    //console.debug("Set array: type=" + type + ", id=" + element.attr("id")+", classs="+classs + ", len="+dataArr.length + ", value="+value+", tagName="+tagName);

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

        var addFlag = true;

        //console.log("in loop parent.id=" + parent.attr("id") + ", tagName=" + tagName + ", type=" + type + ", classs=" + classs + ", text=" + text );

        var fieldName = elementIdArr[elementIdArr.length-fieldIndex];
        var holderame = elementIdArr[elementIdArr.length-holderIndex];
        var ident = holderame+fieldName;
        //console.log("ident=" + ident + ", coll="+coll );

        //var attachElement = element.parent().parent().parent().parent().parent();
        var attachElement = parent.find("."+ident.toLowerCase());   //patientsex

        if( $.inArray(fieldName, arrayFieldShow) != -1 && addFlag ) { //show all fields from DB

            //patient_0_0_0_0_0_0_0_0

            //var name = idsArr[0];
            var patient = idsArr[1];
            var procedure = idsArr[2];
            var accession = idsArr[3];
            var part = idsArr[4];
            var block = idsArr[5];
            var slide = idsArr[6];

            //console.log("Create patient=" + patient );

            //fieldName = "clinicalHistory";

            var newForm = getCollField( ident, patient, procedure, accession, part, block, slide, coll );
            //console.log("newForm="+newForm);

            var labelStr = " entered on " + date + " by "+provider + "</label>";
            newForm = newForm.replace("</label>", labelStr);

            var idStr = 'type="hidden" value="'+id+'" ';
            newForm = newForm.replace('type="hidden"', idStr);

            //console.log("newForm="+newForm);
            //console.log("attachElement class="+attachElement.attr("class")+",id="+attachElement.attr("id"));

//        attachElement.before(newForm);
            attachElement.prepend(newForm);

        } else {    //show the valid field (with validity=1)
            //console.log("NO SHOW");
        }

        if( tagName == "INPUT" ) {
            //console.log("input tagName: fieldName="+fieldName);

            if( type == "text" ) {
                //console.log("type text, text="+text);
                //find the last attached element to attachElement
                var firstAttachedElement = attachElement.find('input,textarea').first();
                //console.log("firstAttachedElement id="+firstAttachedElement.attr("id"));
                firstAttachedElement.val(text);

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
            var firstAttachedElement = attachElement.find('textarea').first();
            //console.log("textarea firstAttachedElement class="+firstAttachedElement.attr("class")+",id="+firstAttachedElement.attr("id"));
            firstAttachedElement.val(text);
        } else if ( tagName == "DIV" && classs.indexOf("select2") != -1 ) {

            //console.log("### select field, id="+id+",text="+text);
            //console.log("id="+element.attr("id"));

            element.select2('data', {id: text, text: text});  //TODO: make sure it sets in correct way!!!!!

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

function cleanArrayFieldSimple( element, field ) {
    //console.log( "clean array field id=" + element.attr("id") );
    //delete if id != 0
    if( element.attr("id") && element.attr("id").indexOf(field+"_0_field") != -1 ) {
        element.val(null);
    } else {
        element.parent().parent().remove();
    }
}

//element - input field element
function cleanArrayField( element, field ) {

    if( field != "diffDisident" ) {
        cleanArrayFieldSimple(element,field);
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
        //console.log("set parent.id=" + parent.attr('id') + ", class=" + parent.attr('class') + ", key="+key);
    }

    //console.log("clean parent.id=" + parent.attr('id'));
    var elements = parent.find(selectStr);

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

        if( type == "text" || !type ) {
            var clean = false;
            var idsArr = id.split("_");
            var field = idsArr[idsArr.length-fieldIndex];
            if( all == "all" ) {
                //elements.eq(i).val(null);
                clean = true;
            } else {
                //check if the field is not key
                if( !isKey(elements.eq(i), field) ) {
                    //elements.eq(i).val(null);
                    clean = true;
                }
            }
            if( clean ) {
                //console.log("in array field=" + field );
                if( $.inArray(field, arrayFieldShow) == -1 ) {
                   //console.log("clean not as arrayFieldShow");

                    if( tagName == "DIV" && classs.indexOf("select2") == -1 ) {
                        //console.log("clean as radio");
                        //cleanArrayField( elements.eq(i), field );
                        processGroup( elements.eq(i), "", "ignoreDisable" );
                    } else if( tagName == "DIV" && classs.indexOf("select2") != -1 ) {
                        //console.log("clean as select");
                        if( field == "mrn" ) {
                            elements.eq(i).select2('data', {id: 'New York Hospital MRN', text: 'New York Hospital MRN'});
                        } else {
                            elements.eq(i).select2('data', null);
                        }
                    } else {
                        //console.log("clean as regular");
                        elements.eq(i).val(null);
                    }

                } else {
                    //console.log("clean as an arrayFieldShow");
                    cleanArrayField( elements.eq(i), field );
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
    if( type ) {
        if( type == 'single' ) {    //single form

        } else {    //multi form
            initAllMulti();
        }
    }
}

function initAllMulti() {
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

    var elements = parent.find(selectStr);

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
        //don't process disident field: part's Diagnosis :
        if( single && id && id.indexOf("_disident_") != -1 ) {
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

    if( tagName == "DIV" && classs.indexOf("select2") != -1 ) { //only for select group
        //console.debug("select disable classs="+classs+", id="+element.attr('id')+", flag="+flag);
        //element.select2("disable", flag);
        if( flag ) {    //disable
            element.select2("readonly", true);
        } else {    //enable
            element.attr("readonly", false);
            element.removeAttr( "readonly" );
        }
        //element.select2("readonly", flag);
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
    //class="glyphicon glyphicon-check"
    if( btn.find("i").attr("class") == "glyphicon glyphicon-check" ) {
        //btn.removeClass("glyphicon glyphicon-check");
        //btn.addClass("glyphicon glyphicon-remove");
        btn.find("i").removeClass('glyphicon-check').addClass('glyphicon-remove');
    } else {
        //btn.removeClass("glyphicon glyphicon-remove");
        //btn.addClass("glyphicon glyphicon-check");
        btn.find("i").removeClass('glyphicon-remove').addClass('glyphicon-check');
    }

}

//name: field name, i.e. partpartname
function setKeyValue( btnElement, name, parentValueArr ) {

    if( parentValueArr ) {
        var parentValue = parentValueArr[0];
        var parentValue2 = parentValueArr[1];
    } else {
        var parentValue = '';
        var parentValue2 = '';
    }

    console.log("set key value name="+ name+", parentValue="+parentValue+",parentValue2="+parentValue2);
    btnElement.button('loading');

    $.ajax({
        url: urlCheck+name,
        type: 'GET',
        contentType: 'application/json',
        dataType: 'json',
        data: {key: parentValue, key2: parentValue2},
        success: function (data) {
            btnElement.button('reset');
            if( data ) {
                console.debug("key value data is found");
                setElementBlock(btnElement, data, null, "key");
                disableInElementBlock(btnElement, false, null, "notkey", null);
                invertButton(btnElement);
            } else {
                console.log('set key data is null');
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
function removeKeyFromDB(element, btnElement) {

    var name = element.name;
    //var keyValue =element.element.attr("value");
    var keyValue =element.element.val();
    //console.debug("delete name="+name +", keyvalue="+keyValue);

    if( !keyValue ) {
        return false;
    }

    btnElement.button('loading');
    $.ajax({
        url: urlCheck+name+"/check/"+keyValue,
        type: 'DELETE',
        contentType: 'application/json',
        dataType: 'json',
        success: function (data) {
            btnElement.button('reset');
//            //console.debug("delete key ok");
        },
        error: function () {
            btnElement.button('reset');
            console.debug("delete key ajax error");
        }
    });
}

function findKeyElement( element, single ) {

    var parent = element.parent().parent().parent().parent().parent().parent();

    if( single ) {
        var parent = element.parent();
    }

    //console.log("set key value: parent.id=" + parent.attr('id') + ", parent.class=" + parent.attr('class'));

    var elements = parent.find('input,select');
    //console.log("set key value: elements.length=" + elements.length);

    var keyElement = null;
    var name = "";
    for (var i = 0; i < elements.length; i++) {
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        if( id && type != "hidden" ) {
            var idsArr = elements.eq(i).attr("id").split("_");
            var field = idsArr[idsArr.length-fieldIndex];
            //console.log("set key value: field=(" + field + ")");

            if( $.inArray(field, keys) != -1 ) {
                //console.log("set key value: found key=(" + field + ")");
                name = field;
                keyElement = elements.eq(i);
                break;
            }
        }
    }

    var res = new Array;
    res.element = keyElement;
    res.name = name;

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
        var partNumberElement = element.parent().parent().not("*[id^='s2id_']").find('.keyfield');
        return partNumberElement.eq(1);
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

//Helpers
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

$(document).ready(function() {

    //popover hide for check button
    $('html').click(function(e) {

        var clickedEl = $(e.target);
        var clickedClass = clickedEl.attr("class");
        //console.debug("html clickedClass="+clickedClass);

        if( clickedClass && ( clickedClass.indexOf("glyphicon") != -1 || clickedEl.children().hasClass("glyphicon") ) ) {
            //console.debug("html no hide");
            return;
        } else {
            var elements = $('.keyfield');
            for( var i = 0; i < elements.length; i++ ) {
                var element = elements.eq(i);
                //console.debug("html hide, elem id="+element.attr("id"));
                var origTitle = element.attr('data-original-title');
                //console.log("origTitle=("+origTitle+")");
                if( origTitle != "" && origTitle != undefined ) {
                    //console.log("change title");
                    element.attr('title', origTitle);
                    $(".popover").delay(100).remove();
                }
            }
            return;
        }
    });

});



//Check form single
function checkFormSingle( elem ) {
    var element = $(elem);

    //console.log( "element.id=" + element.attr('id') );

    var elementInputs = element.parent().parent().find(".keyfield").not("*[id^='s2id_']").each(function() {

        var keyInput = $(this);
        //console.log("keyInput.class="+keyInput.attr('class')+", id="+keyInput.attr('id'));

        var keyBtn = keyInput.parent().parent().parent().find('#check_btn');
        //console.log("keyBtn.class="+keyBtn.attr('class')+", id="+keyBtn.attr('id'));

        keyBtn.trigger("click");
        invertButton(element);
    });

    return;
}

