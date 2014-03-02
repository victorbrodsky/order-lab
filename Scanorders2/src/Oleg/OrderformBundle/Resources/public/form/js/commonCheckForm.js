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


function trimWithCheck(val) {
    if( val && typeof val != 'undefined' && val != "" ) {
        val = val.toString();
        val = val.trim();
    }
    return val;
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

//all: "all" => disable/enable all fields including key field
//flagKey: "notkey" => disable/enable all fields, but not key field (inverse key)
//flagArrayField: "notarrayfield" => disable/enable array fields
function disableInElementBlock( element, disabled, all, flagKey, flagArrayField ) {

    //console.log("disable element.id=" + element.attr('id') + ", class=" + element.attr("class") );

    var parentname = ""; //for multi form
    if( element.hasClass('accessionbtn') ) {
        parentname = "accession";
    }
    if( element.hasClass('partbtn') ) {
        parentname = "part";
    }
    if( element.hasClass('blockbtn') ) {
        parentname = "block";
    }

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
                disableElement(parentname, elements.eq(i),disabled);
            }

            if( flagKey == "notkey" ) {
                //check if the field is not key
                if( isKey(elements.eq(i), field) && flagKey == "notkey" ) {
                    if( disabled ) {    //inverse disable flagKey for key field
                        disableElement(parentname,elements.eq(i),false);
                    } else {
                        disableElement(parentname,elements.eq(i),true);
                    }
                } else {
                    disableElement(parentname,elements.eq(i),disabled);
                }
            }

            if( flagArrayField == "notarrayfield" ) {
                if( $.inArray(field, arrayFieldShow) != -1 ) {
                    //console.log("notarrayfield (not '_0_field'): disable array id="+elements.eq(i).attr("id"));
                    if( elements.eq(i).attr("id") && elements.eq(i).attr("id").indexOf(field+"_0_field") != -1 ) {
                        if( disabled ) {    //inverse disable flag for key field
                            disableElement(parentname,elements.eq(i),false);
                        } else {
                            disableElement(parentname,elements.eq(i),true);
                        }
                    }
                }               
            }

        }

    }
}

function disableElement(parentname,element, flag) {

    var type = element.attr('type');
    var classs = element.attr('class');
    var tagName = element.prop('tagName');

    //console.log("disable classs="+classs+", tagName="+tagName+", type="+type+", id="+element.attr('id'));

    //return if this element does not belong to a pressed key element
    var idArr = element.attr('id').split("_");
    var fieldParentName = idArr[idArr.length-holderIndex];
    if( fieldParentName == "procedure" ) {
        fieldParentName = "accession";
    }
    //console.log("fieldParentName="+fieldParentName+", parentname="+parentname);
    if( parentname == "" || parentname == fieldParentName ) {
        //console.log("continue");
    } else {
        return;
    }

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
            processDatepicker(element,"remove");
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
            processDatepicker(element);
        }

    }
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
                    //console.log("data is not null: set text for field " + field);
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

        //console.log( "set array field i="+i+", id="+id+", text=" + text + ", provider="+provider+", date="+date + ", validity="+validity );

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

            //var name = idsArr[0];
            var patient = idsArr[1];
            var procedure = idsArr[2];
            var accession = idsArr[3];
            var part = idsArr[4];
            var block = idsArr[5];
            var slide = idsArr[6];

            //console.log("Create array empty field, fieldName=" + fieldName + ", patient="+patient+", part="+part );

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

//                if( fieldName != "diffDisident" && orderformtype == "single" ) {
//
//                    //var firstAttachedElement = element; //for single & disident (Diagnosis) there is only one field: this element
//                    var firstAttachedElement = attachElement.find('input,textarea').first();
//                    //console.log(fieldName+': single');
//
//                } else {
//                    var firstAttachedElement = attachElement.find('input,textarea').first();
//                }

                var firstAttachedElement = attachElement.find('input,textarea').first();
                
                //printF(firstAttachedElement,"firstAttachedElement: ");

                if( fieldName == "partname" || fieldName == "blockname" ) {
                    if( orderformtype == "single" ) {
                        var firstAttachedElement = element;
                    } else {
                        var firstAttachedElement = attachElement.find('.keyfield ').first();
                    }
                    //printF(firstAttachedElement,"firstAttachedElement=");
                    firstAttachedElement.select2('data', {id: text, text: text});
                } else {
                    if( classs.indexOf("select2") != -1 ) {
                        var firstAttachedElement = element;
                        //printF(firstAttachedElement,"firstAttachedElement=");
                        //console.log("!!!!!!!!!!!! Set Value as select="+text+", id="+id);
                        firstAttachedElement.select2('data', {id: text, text: text});
                        //firstAttachedElement.select2('val', id);
                    } else {
                        //console.log("!!!!!!!!!!!! Set Value text="+text);
                        firstAttachedElement.val(text);
                    }
                }


            } else if( classs && classs.indexOf("datepicker") != -1 ) {
                //console.log("datepicker");
                var firstAttachedElement = attachElement.find('input').first();
                if( text && text != "" ) {
                    firstAttachedElement.datepicker( 'setDate', new Date(text) );
                    firstAttachedElement.datepicker( 'update');
                } else {
                    //firstAttachedElement.datepicker({autoclose: true});
                    initSingleDatepicker(firstAttachedElement);
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

    //do not change type only if current type is "existing.." and returned keytypename is "auto-generated"
    var currentKeytypeText = keytypeEl.select2("data").text;

    var tosetKeytypeText = data['keytypename'];
    if( tosetKeytypeText && tosetKeytypeText.indexOf("Auto-generated") != -1 && currentKeytypeText.indexOf("Existing Auto-generated") != -1 ) {
        //don't change type
        //console.log('tosetKeytypeText='+tosetKeytypeText);
    } else {
        keytypeEl.select2('val', data['keytype']);
    }

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