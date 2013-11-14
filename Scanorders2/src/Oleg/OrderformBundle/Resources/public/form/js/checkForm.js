/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/18/13
 * Time: 2:05 PM
 * To change this template use File | Settings | File Templates.
 */

var keys = new Array("mrn", "accession", "partname", "blockname");   //TODO: change to patientmrn, accessionaccession, partname ...
var arrayFieldShow = new Array("clinicalHistory","age"); //display as array fields "sex"
var urlCheck = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/check/";
var selectStr = 'input.form-control,div.horizontal_type,div.select2-container,[class^="ajax-combobox-"],textarea,select';  //div.select2-container, select.combobox

//  0         1              2           3   4  5  6   7
//oleg_orderformbundle_orderinfotype_patient_0_mrn_0_field
var fieldIndex = 3;     //get 'key'
var holderIndex = 5;    //get 'patient'
//console.log("urlCheck="+urlCheck);

function checkForm( elem ) {

    var element = $(elem);

    //console.log( "element.id=" + element.attr('id') );

    //var elementInput = element.parent().parent().find("input");  //find("input[type=text]");
    var elementInput = element.parent().parent().find(".keyfield");
    //console.log("elementInput.class="+elementInput.attr('class'));

    //  0         1              2           3   4  5  6   7
    //oleg_orderformbundle_orderinfotype_patient_0_mrn_0_field
    var inputId = elementInput.attr('id');
    //console.log("inputId="+inputId);

    var idsArr = inputId.split("_");

    var name = idsArr[idsArr.length-holderIndex];   //i.e. "patient"
    var fieldName = idsArr[idsArr.length-fieldIndex];
    //console.log("name="+name);
    //var patient = idsArr[4];
    //var key = idsArr[4];

    var keyElement = findKeyElement(element);

    if( element.find("i").attr("class") == "glyphicon glyphicon-remove" ) { //Remove Button Cliked

        //console.log("Remove Button Cliked");
        //setElementBlock(element, null, true);
        removeKeyFromDB(keyElement);
        cleanFieldsInElementBlock( element, "all" );
        disableInElementBlock(element, true, null, "notkey", null);
        invertButton(element);
        return;

    } else {    //Check Button Cliked

        //console.log("Check Button Cliked");

        //get key field for this patient: oleg_orderformbundle_orderinfotype_patient_0_mrn

        var keyValue =keyElement.element.val();
        //console.log("keyElement id="+keyElement.element.attr("id")+", class="+keyElement.element.attr("class")+",val="+keyValue+",name="+name);

        if( !keyValue ) {
            //console.log("key undefinded!");
//            $('#'+inputId).popover( {content:"Please fill out key field"} );
//            $('#'+inputId).popover('show');

            if( name == "part" || name == "block" ) {

                var accessionNumberElement = getAccessionNumberElement(element);
                var accessionNumber = accessionNumberElement.val();
                //console.log(name+": accessionNumber="+accessionNumber);

                if( !accessionNumber ) {
                    //console.log( "accessionNumber is empty. accessionNumberElement id="+accessionNumberElement.attr("id") + ", class=" + accessionNumberElement.attr("class") );
                    accessionNumberElement.popover({
                        placement:'bottom',
                        content:"Can not check "+capitaliseFirstLetter(name)+" name without Accession number"
                    });
                    accessionNumberElement.popover('show');
                    //alert("Can not check "+capitaliseFirstLetter(name)+" name without Accession number");
                    return;
                } else {
                    //console.log("accessionNumber is not empty");
                    setKeyValue(element,name+"partname",accessionNumber);   //TODO: fix it
                    return;
                }

            }

            setKeyValue(element,name+fieldName,"");
            //disableInElementBlock(element, false, null, "notkey", null);
            //invertButton(element);
            return;
        }

        $.ajax({
            url: urlCheck+name,
            type: 'GET',
            data: {key: keyValue},
            contentType: 'application/json',
            dataType: 'json',
            success: function (data) {
                console.debug("get object ajax ok "+name);
                if( data.id ) {
                    //first: set elements
                    setElementBlock(element, data);
                    //second: disable or enable element. Make sure this function runs after setElementBlock
                    disableInElementBlock(element, true, "all", null, "notarrayfield");
                } else {
                    console.debug("not found");
                    cleanFieldsInElementBlock( element );
                    disableInElementBlock(element, false, null, "notkey", null);
                }
                invertButton(element);
            },
            error: function () {
                console.debug("get object ajax error "+name);
                //setElementBlock(element, null);
                cleanFieldsInElementBlock( element );
                disableInElementBlock(element, false, "all", null, null);
                invertButton(element);
            }
        });
    }

    return;
}

//set Element. Element is a block of fields
//element: check_btn element
//cleanall: clean all fields
//key: set only key field
function setElementBlock( element, data, cleanall, key ) {

    //console.debug( "name=" + name + ", data.id=" + data.id + ", sex=" + data.sex );
    var parent = element.parent().parent().parent().parent().parent().parent();
    //console.log("set parent.id=" + parent.attr('id') + ", key="+key);

    //var elements = parent.find('input,textarea,select');
    var elements = parent.find(selectStr);

    for (var i = 0; i < elements.length; i++) {

        //console.log("Element.id=" + elements.eq(i).attr("id"));
        //  0         1              2           3   4  5
        //oleg_orderformbundle_orderinfotype_patient_0_mrn  //length=6
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        var classs = elements.eq(i).attr("class");
        var value = elements.eq(i).attr("value");
        console.log("id=" + id + ", type=" + type + ", class=" + classs + ", value=" + value );

        //if( id && type != "hidden" ) {
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

                //get field name for select fields such as classs=select2-container ajax-combobox-procedure
                if( classs && classs.indexOf("select2") != -1 ) {
                    field = idsArr[idsArr.length-holderIndex];
                    //console.log("new field="+field);
                }

                if( data[field] && data[field] != undefined && data[field] != "" ) {
                    //console.log("data is not null: set text field = " + data[field][0]['text']);
                    setArrayField( elements.eq(i), data[field], parent );
                }

            }

        }

    }

}

//set array field such as ClinicalHistory array fields
//element is an input element jquery object
function setArrayField(element, dataArr, parent) {

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

                //find the last attached element to attachElement
//            var firstAttachedElement = attachElement.find('*[type="'+type+'"]').first();
                var firstAttachedElement = attachElement.find('input').first();
                firstAttachedElement.val(text);

            } else if( classs && classs.indexOf("datepicker") != -1 ) {
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
            console.log("select field, id="+id+",text="+text);
            element.select2('data', {id: id, text: text});  //TODO: make sure it sets in correct way!!!!!
        } else if ( tagName == "DIV" ) {
            //get the first (the most recent added) group
            var firstAttachedElement = attachElement.find('.horizontal_type').first();
            processGroup( firstAttachedElement, text, "ignoreDisable" );
        } else {
            console.log("logical error: undefined tagName="+tagName);
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
function processGroup( element, text, disableFlag ) {

    var elementIdArr = element.attr("id").split("_");
    var fieldName = elementIdArr[elementIdArr.length-(fieldIndex+1)];

    //var element = elementInside.parent().parent().parent();
    //var radios = element.find("input:radio");

    //console.log("process group id="+element.attr("id")+ ", class="+element.attr("class") + ", fieldName="+fieldName );

    var partId = 'input[id*="'+fieldName+'_"]:radio';   //TODO: make sure it works for other groups (as select?)
    var members = element.find(partId);

    for (var i = 0; i < members.length; i++) {
        var localElement = members.eq(i);
        var value = localElement.attr("value");
        //console.log("radio id: " + localElement.attr("id") + ", value=" + value );

        if( disableFlag == "ignoreDisable" ) {
            if( text != "" ) {
                //console.log("text ok, check radio (data): " + value + "?=" + text );
                if( value == text ) {
                    //console.log("Match!" );
                    localElement.prop('checked',true);
                }
            } else {
                //console.log("no text radio: value=" + value);
                localElement.prop('checked',false);
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

function cleanArrayField( element, field ) {
    //console.log( "clean array field id=" + element.attr("id") );
    //delete if id != 0
    if( element.attr("id") && element.attr("id").indexOf(field+"_0_field") != -1 ) {
        element.val(null);
    } else {
        element.parent().parent().remove();
    }
}

//clean fields in Element Block, except key field
//all: if set to "all" => clean all fields, including key field
function cleanFieldsInElementBlock( element, all ) {
    //console.debug( "name=" + name + ", data.id=" + data.id + ", sex=" + data.sex );
    var parent = element.parent().parent().parent().parent().parent().parent();
    //console.log("clean parent.id=" + parent.attr('id'));
    var elements = parent.find(selectStr);

    for (var i = 0; i < elements.length; i++) {

        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");
        var tagName = elements.eq(i).prop('tagName');
        var classs = elements.eq(i).attr('class');
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
                        elements.eq(i).select2('data', null);
                    } else {
                        elements.eq(i).val(null);
                    }

                } else {
                    //console.log("clean as an arrayFieldShow");
                    cleanArrayField( elements.eq(i), field );
                }
            }
        }

//            if( type == "radio" ) {
//                console.log("clean as radio");
//                elements.eq(i).prop('checked',false);
//            }


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

    return;
    //console.log("disable element.id=" + element.attr('id'));

    var parent = element.parent().parent().parent().parent().parent().parent();

    //console.log("parent.id=" + parent.attr('id') + ", parent.class=" + parent.attr('class'));

    var elements = parent.find(selectStr);

    //console.log("elements.length=" + elements.length);

    for (var i = 0; i < elements.length; i++) {

        //console.log("element.id=" + elements.eq(i).attr("id"));
        //  0         1              2           3   4  5
        //oleg_orderformbundle_orderinfotype_patient_0_mrn  //length=6
        var id = elements.eq(i).attr("id");
        var type = elements.eq(i).attr("type");

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

    //if( !element ) return;

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
        console.debug("select disable classs="+classs+", id="+element.attr('id'));
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

    } else {

        if( type == "file" ) {
            //console.log("file enable field id="+element.attr("id"));
            element.attr('disabled', false);
        } else {
            //console.log("general enable field id="+element.attr("id"));
            element.attr("readonly", false);
            element.removeAttr( "readonly" );
        }

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

function setKeyValue( btnElement, name, parentValue ) {

    //console.log("set key value name="+ name);

    //if( name == "name" ) return;

//    if( name != "accession" ) {
//        //data = new Array();
//        //data[name] = "Automatic Generated";
//        //setElementBlock(element, data, null, "key");
//        keyElement.val("Automatic Generated");
//    }

    $.ajax({
        url: urlCheck+name,
        type: 'GET',
        contentType: 'application/json',
        dataType: 'json',
        data: {key: parentValue},
        success: function (data) {
            if( data ) {
                //console.debug(name+" text="+ data["partname"]);
                setElementBlock(btnElement, data, null, "key");
                disableInElementBlock(btnElement, false, null, "notkey", null);
                invertButton(btnElement);
            } else {
                console.log('set key data is null');
            }
        },
        error: function () {
            console.debug("set key ajax error");
        }
    });

    return;
}

//remove key NOTPROVIDED if it was created by check on empty key field (entity status="reserved").
function removeKeyFromDB(element) {

    var name = element.name;
    //var keyValue =element.element.attr("value");
    var keyValue =element.element.val();
    console.debug("delete name="+name +", keyvalue="+keyValue);

    $.ajax({
        url: urlCheck+name+"/check/"+keyValue,
        type: 'DELETE',
        contentType: 'application/json',
        dataType: 'json',
//        success: function (data) {
//            //console.debug("delete key ok");
//        },
        error: function () {
            console.debug("delete key ajax error");
        }
    });
}

function findKeyElement( element ) {

    var parent = element.parent().parent().parent().parent().parent().parent();
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

function getAccessionNumberElement( element ) {
    var parent = element.parent().parent().parent().parent().parent().parent().parent().parent().parent();
    //console.log("get accession number: parent.id=" + parent.attr('id') + ", parent.class=" + parent.attr('class'));
    var accessionNumberHolder = parent.find('.accessionaccession');
    var accessionNumberElement = parent.find('.keyfield');

    return accessionNumberElement.eq(0);
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
