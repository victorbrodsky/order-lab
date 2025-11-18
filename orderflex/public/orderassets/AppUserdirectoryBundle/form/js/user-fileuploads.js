/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by oli2002 on 10/20/14.
 */

//dropzone globals
var _dz_maxFiles = 20;
var _dz_maxFilesize = 64; //MB


// Prevent Dropzone from auto discovering this element
if( typeof Dropzone !== 'undefined' ) {
    Dropzone.autoDiscover = false;
}

//File Uploads using Dropzone and Oneup\UploaderBundle
//addRemoveLinks: true or null
function initFileUpload( holder, data, addRemoveLinks ) {

    //console.log('init File Upload');

    if( $('.dropzone').length == 0 ) {
        return;
    }

    //console.log("dropzone holder=");
    //console.log(holder);

    var targetid = ".file-upload-dropzone";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( targetid.length == 0 )
            return;
    }

    if( typeof data === 'undefined' ) {
        data = null;
    } else {
        //console.log("dropzone data=");
        //console.log(data);

    }

    //console.log('cycle='+cycle);

    var showFlag = true;
    if( cycle.indexOf("show") === -1 ) {
        showFlag = false;
    }

    var dataElement = document.getElementById("form-prototype-data");
    //console.log('dataElement len='+dataElement);

//    if( dataElement.length == 0 || typeof dataElement.dataset === 'undefined' ) {
//        return;
//    }

    //var url = dataElement.dataset.uploadurl;
    var url = dataElement.getAttribute('data-uploadurl');
    //console.log('url='+url);

    //var userid = dataElement.dataset.userid;
    var userid = dataElement.getAttribute('data-userid');
    console.log('userid='+userid);

    //show upload success confirmation alert
    // var dropzoneConfirmationDisable = dataElement.getAttribute('data-dropzoneconfirmation-disable');
    // //console.log('dropzoneConfirmationDisable='+dropzoneConfirmationDisable);
    // if( dropzoneConfirmationDisable ) {
    //     dropzoneConfirmationDisable = true;
    // } else {
    //     dropzoneConfirmationDisable = false;
    // }
    //console.log('dropzoneConfirmationDisable='+dropzoneConfirmationDisable);

    var clickable = true;
    if( showFlag ) {
        clickable = false;
    }

    if( typeof addRemoveLinks === 'undefined' ) {
        var addRemoveLinks = true;
        if( !clickable ) {
            addRemoveLinks = null;
        }
    }

    console.log('dropzone: clickable='+clickable);
    //console.log('addRemoveLinks='+addRemoveLinks);

    //overwrite maxfiles
    var documentspercontainer = $(targetid).find('#documentcontainer-documentspercontainer').val();
    if( documentspercontainer && documentspercontainer != "undefined" ) {
        _dz_maxFiles = documentspercontainer;
    }
    //console.log('_dz_maxFiles='+_dz_maxFiles);


    var documentmaxfilesize = $(targetid).find('#documentcontainer-documentmaxfilesize').val();
    if( documentmaxfilesize && documentmaxfilesize != "undefined" ) {
        _dz_maxFilesize = documentmaxfilesize;
    }
    //console.log('_dz_maxFilesize='+_dz_maxFilesize);

    //var documentType = $(targetid).find('#documentcontainer-document-type').val();
    //console.log('documentType='+documentType);
    //if( documentType && documentType != "undefined" ) {
    //    _dz_documentType = documentType;
    //}
    //console.log('_dz_documentType='+_dz_documentType);

    var previewHtml =
        '<div class="dz-preview dz-file-preview" style="width:100%; height:220px; margin-left:1px; margin-right:0px;">'+
            '<div class="dz-details">'+
            //'<div class="dz-filename"><span data-dz-name></span></div>'+
            '<div class="dz-size" data-dz-size></div>'+
            '<img data-dz-thumbnail />'+
            '</div>'+
            '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>'+
            '<div class="dz-success-mark"><span>✔</span></div>'+
            '<div class="dz-error-mark"><span>✘</span></div>'+
            '<div class="dz-error-message"><span data-dz-errormessage></span></div>'+
            '<div class="file-upload-showlink"></div>'
//            '<button type="button" class="btn btn-danger" data-dz-remove>Delete</button>'+
    '</div>';

    $(targetid).each( function(){

        $(this).dropzone({
            url: url,
            clickable: clickable,
            addRemoveLinks: addRemoveLinks,
            maxFiles: _dz_maxFiles,
            maxFilesize: _dz_maxFilesize,
            previewTemplate: previewHtml,
            dictDefaultMessage: 'Drag and drop files here to upload or click to select a file',
            sending: function(file, xhr, formData){

                //disable all buttons
                //$(':input[type="submit"]').prop('disabled', true);
                dropzoneDisableButtons();

                //console.log('dropzone: userid='+userid);
                formData.append('userid', userid);

                var filename = file.name;
                //console.log('filename='+filename);
                formData.append('filename', filename);

                var documentType = $(this.element).find('#documentcontainer-document-type').val();
                //console.log('documentType='+documentType);
                formData.append('documenttype', documentType);

                //console.log('_sitename='+_sitename);
                formData.append('sitename', _sitename);

                //console.log('_authuser_id='+_authuser_id);
                formData.append('authuserid', _authuser_id);
            },
            success: function(file, responseText){
                //console.log('dropzone success');
                //console.log('responseText='+responseText);
                //console.log(responseText);
                //console.log(file);

                var documentid = responseText.documentid;
                //console.log('documentid='+documentid);
                var documentSrc = responseText.documentsrc;

                var commentHolder = $(this.element).closest('.user-collection-holder,.form-element-holder'); //commentHolder

                if( commentHolder.length == 0 ) {
                    throw new Error("Collection holder for file upload is not found");
                }

                var idHtml = constractDocumentIdFieldHtml(commentHolder,documentid);

                if( file.previewElement ) {
                    $(file.previewElement).append(idHtml);
                    var showlinkDiv = $(file.previewElement).find('.file-upload-showlink');
                    var showlinkHtml = constractShowLink(documentid,file.name);
                    showlinkDiv.html(showlinkHtml);

                    adjustHolderHeight(commentHolder);
                }

                //populate document id input field
                //var holder = $(this.element).closest('.files-upload-holder');
                //var fileIdField = holder.find('.file-upload-id');
                //fileIdField.val(documentid);
                //file.previewTemplate.appendChild(document.createTextNode(responseText));

                //parent function
                if( file.previewElement ) {
                    // if( !dropzoneConfirmationDisable ) {
                    //     alert("You must press the submit button on the bottom of this page to save your uploaded file.");
                    // }
                    if( commentHolder.hasClass('dropzoneconfirmation-disable') ) {
                        //don't show alert
                    } else {
                        alert("You must press the submit button on the bottom of this page to save your uploaded file.");
                    }
                    return file.previewElement.classList.add("dz-success");
                }
            },
            maxfilesexceeded: function(file) {
                alert('Maximum file upload limit reached');
                return removeUploadedFileByHolder( file.previewElement, this, false );
            },
            removedfile: function(file) {
                //console.log('remove js file name='+file.name);
                return removeUploadedFileByHolder( file.previewElement, this, true );
            },
            init: function() {

                //Called when all files in the queue finished uploading
                this.on("queuecomplete", function (file) {
                    //alert("queuecomplete: All files have uploaded ");
                    dropzoneEnableButtons();
                });

                var withRemoveLinks = true;
                //disable dropzone if dropzone has class file-upload-dropzone-inactive
                if( $(this.element).hasClass('file-upload-dropzone-inactive') ) {
                    //console.log('init: disable dropzone');
                    //console.log($(this.element));
                    var tooltipStr = 'This field can be edited in the Grants of the List Manager';
                    disableEnableDropzone( $(this.element), true, tooltipStr, true );
                    withRemoveLinks = false;
                }

                if( data == null ) {
                    //console.log('dropzone init: data is null');
                    return;
                }

                //console.log('manual init');

                var thisDropzone = this;

                populateDropzoneWithData(thisDropzone,data,withRemoveLinks);
                //See more at: http://www.startutorial.com/articles/view/dropzonejs-php-how-to-display-existing-files-on-server#sthash.sqF6KDsk.dpuf
            }

        });
    }); //each


//    $('#jquery-fileupload').fileupload({});

}

function dropzoneDisableButtons() {
    //$(':input[type="submit"]').prop('disabled', true);
    return;
}
function dropzoneEnableButtons() {
    //$(':input[type="submit"]').prop('disabled', false);
    return;
}

function populateDropzoneWithData(thisDropzone,data,withRemoveLinks) {
    //console.log(thisDropzone);
    var holder = $(thisDropzone.element).closest('.files-upload-holder');

    var existedfiles = holder.find('.file-holder');
    //console.log('existedfiles len='+existedfiles.length);

    //console.log('data len='+data.length);

    for( var i = 0; i < data.length; i++ ) {

        var value = data[i];

        //console.log('name='+value.uniquename);

        var mockFile = { name: value.uniquename, size: value.size };

        //console.log('mockFile=');
        //console.log(mockFile);

        thisDropzone.options.addedfile.call(thisDropzone, mockFile);

        var filepath = value.url;
        //console.log('path='+filepath);

        thisDropzone.options.thumbnail.call(thisDropzone, mockFile, filepath);

        //add showlink
        if( mockFile.previewElement ) {
            var showlinkDiv = $(mockFile.previewElement).find('.file-upload-showlink');
            var showlinkHtml = constractShowLink(value.id,value.originalname);
            showlinkDiv.html(showlinkHtml);
        }

        //hide removeLinks
        if( !withRemoveLinks ) {
            //console.log('removing remove button from the file='+value.uniquename);
            var removeLinks = $(mockFile.previewElement).find('.dz-remove');
            removeLinks.hide();
        }

    }
    //See more at: http://www.startutorial.com/articles/view/dropzonejs-php-how-to-display-existing-files-on-server#sthash.sqF6KDsk.dpuf
}

function constractShowLink(id,name) {
    var url = getCommonBaseUrl('file-download/'+id,"employees");
    var showlinkHtml =  '<div style="overflow:hidden; white-space:nowrap;">'+
        '<a target="_blank" href="'+url+'" target="_blank">'+name+'</a>'+
        '</div>';
    return showlinkHtml;
}

function removeUploadedFileByHolder( previewElement, dropzone, confirmFlag ) {

    var documentid = $(previewElement).find('.file-upload-id').val();
    //console.log('remove documentid='+documentid+", confirmFlag="+confirmFlag);

    if( confirmFlag == false ) {
        var _ref;
        if( previewElement ) {
            if( (_ref = previewElement) != null ) {
                _ref.parentNode.removeChild(previewElement);
            }
        }
        return;
    }

    var r = confirm('Are you sure you want to remove this document?'); //+', id='+documentid
    if( r == false ) {
        return;
    }

    if( !previewElement ) {
        //console.log('return: no previewElement');
        return;
    }

    var holderTop = $(dropzone.element).closest('.user-collection-holder,.form-element-holder');
    var commentid = holderTop.find('.comment-field-id').val();

//    var commenttype = null;
//    if( holderTop.hasClass('user-publiccomments') ) {
//        commenttype = "AppUserdirectoryBundle:PublicComment";
//    }
//    if( holderTop.hasClass('user-privatecomments') ) {
//        commenttype = "AppUserdirectoryBundle:PrivateComment";
//    }
//    if( holderTop.hasClass('user-admincomments') ) {
//        commenttype = "AppUserdirectoryBundle:AdminComment";
//    }
//    if( holderTop.hasClass('user-confidentialcomment') ) {
//        commenttype = "AppUserdirectoryBundle:ConfidentialComment";
//    }
//
//    if( holderTop.hasClass('user-CurriculumVitae') ) {
//        commenttype = "AppUserdirectoryBundle:CurriculumVitae";
//    }
//    if( holderTop.hasClass('user-FellowshipApplication') ) {
//        commenttype = "AppUserdirectoryBundle:FellowshipApplication";
//    }
//    if( holderTop.hasClass('user-Examination') ) {
//        commenttype = "AppUserdirectoryBundle:Examination";
//    }

    var commenttype = mapperHolderDocument(holderTop);

//    //for scan orders don't delete from DB. Just remove from form
//    if( holderTop.hasClass('scan-partpaper') ) {
//        //commenttype = "AppOrderformBundle:PartPaper";
//
//        var _ref;
//        if( previewElement ) {
//            if( (_ref = previewElement) != null ) {
//                _ref.parentNode.removeChild(previewElement);
//            }
//        }
//    }

    //if commenttype is not defined (i.e. scanorder form) don't delete from DB. Just remove from form
    //if( commenttype != null ) {

    // var dataElement = document.getElementById("form-prototype-data");
    // //show upload success confirmation alert
    // var dropzoneConfirmationDisable = dataElement.getAttribute('data-dropzoneconfirmation-disable');
    // //console.log('dropzoneConfirmationDisable='+dropzoneConfirmationDisable);
    // if( dropzoneConfirmationDisable ) {
    //     dropzoneConfirmationDisable = true;
    // } else {
    //     dropzoneConfirmationDisable = false;
    // }

    var url = getCommonBaseUrl("file-delete","employees");
    //console.log('url='+url);
    //use comment id and documentid
    $.ajax({
        type: "POST",   //"DELETE",
        url: url,
        timeout: _ajaxTimeout,
        async: true,
        data: { documentid: documentid, commentid: commentid, commenttype: commenttype, sitename: _sitename }
    }).success(function(data) {
        //if( parseInt(data) > 0 ) {
        //console.log('remove ok, data='+data);
        //parent function
        var _ref;
        if( previewElement ) {
            if( (_ref = previewElement) != null ) {
                _ref.parentNode.removeChild(previewElement);
            }
        }

        adjustHolderHeight(holderTop);

        // if( !dropzoneConfirmationDisable ) {
        //     alert("You must press the 'Update' or 'Save' button to save your changes.");
        // }
        if( holderTop.hasClass('dropzoneconfirmation-disable') ) {
            //don't show alert
        } else {
            alert("You must press the 'Update' or 'Save' button to save your changes.");
        }
        //update form
        //$('#fellapp-applicant-form').submit();

        //}
    }).fail(function(data) {
        //console.log('remove failed, data='+data);
        throw new Error('remove failed, data='+data);
    }) ;

    return dropzone._updateMaxFilesReachedClass();
}

function removeUploadedFile(btn) {
    var dropzoneEl = $(btn).closest('.file-upload-dropzone');
    var dropzoneDom = dropzoneEl.get(0);
    //console.log('className='+dropzoneDom.className);

    var myDropzone = dropzoneDom.dropzone;

    var previewElement = $(btn).closest('.dz-file-preview').get(0);

    removeUploadedFileByHolder( previewElement, myDropzone, true );
}

function adjustHolderHeight( commentHolder ) {
    return; //testing
    //console.log(commentHolder);
    //use dropzone element height changes
    var dropzoneElement = commentHolder.find('.file-upload-dropzone');
    var dropzoneH = dropzoneElement.height();
    //console.log('dropzoneH='+dropzoneH);

    var originalH = 150;
    var extraH = parseInt(dropzoneH) + parseInt(originalH);
    //console.log('extraH='+extraH);

    var commentH = commentHolder.height();
    var newH = parseInt(commentH) + parseInt(extraH);
    //console.log('newH='+newH);
    commentHolder.height( newH );

}


function mapperHolderDocument(holderTop) {
    var holdertype = null;
    var fieldname = 'documents';
    if( holderTop.hasClass('user-publiccomments') ) {
        holdertype = "AppUserdirectoryBundle:PublicComment";
        fieldname = 'documents';
    }
    if( holderTop.hasClass('user-privatecomments') ) {
        holdertype = "AppUserdirectoryBundle:PrivateComment";
        fieldname = 'documents';
    }
    if( holderTop.hasClass('user-admincomments') ) {
        holdertype = "AppUserdirectoryBundle:AdminComment";
        fieldname = 'documents';
    }
    if( holderTop.hasClass('user-confidentialcomment') ) {
        holdertype = "AppUserdirectoryBundle:ConfidentialComment";
        fieldname = 'documents';
    }

    if( holderTop.hasClass('user-FellowshipApplication') ) {
        holdertype = "AppFellAppBundle:FellowshipApplication";
        fieldname = ['avatars','cvs','coverLetters','lawsuitDocuments','reprimandDocuments'];
    }
    if( holderTop.hasClass('user-Examination') ) {
        holdertype = "AppUserdirectoryBundle:Examination";
        fieldname = 'scores';
    }

//    var res = new Array();
//    res['holdertype'] = holdertype;
//    res['fieldname'] = fieldname;

    return holdertype;
}



//output example:
// id=   oleg_userdirectorybundle_user_privateComments_0_documents_0_id
// name= oleg_userdirectorybundle_user[privateComments][0][documents][0][id]
function constractDocumentIdFieldHtml(commentHolder,documentid) {

    function isInt(n){
        return Number(n) === n && n % 1 === 0;
    }

    var res = getNewDocumentInfoByHolder(commentHolder);

    //insert document id input field
    //var bundleName = res['bundleName'];
    //var commentType = res['commentType'];
    //var commentCount = res['commentCount'];
    var documentCount = res['documentCount'];
    var beginIdStr = res['beginIdStr'];
    var beginNameStr = res['beginNameStr'];

    //console.log("documentid="+documentid);
    //console.log("documentCount="+documentCount);
    //console.log("beginIdStr="+beginIdStr);
    //console.log("beginNameStr="+beginNameStr);

    //var documentCount = maxFiles + comments.length;    //'1'; //maximum number of comments is limited, so use this number

    //var idHtml =    '<input type="hidden" id="'+beginIdStr+'_documents_'+documentCount+'_id" '+
    //    'name="'+beginNameStr+'[documents]['+documentCount+'][id]" class="file-upload-id" value="'+documentid+'">';

    var idHtml =    '<input type="hidden" id="'+beginIdStr+documentCount+'_id" '+
        'name="'+beginNameStr+'['+documentCount+'][id]" class="file-upload-id" value="'+documentid+'">';

    //replace __documentContainers__ by 0. Since we can't add/delete documentContainer by JS, then it's safe to use incremential id
    idHtml = fileUploadProcessNewDropzoneId(commentHolder,idHtml);

    //enable this field, because dummyprototypefield is disabled by default
    if( idHtml.indexOf("disabled") != -1 ) {
        var find = 'disabled="disabled"';
        var re = new RegExp(find, 'g');
        idHtml = idHtml.replace(re, "");
    }

    if( idHtml.indexOf("__") != -1 ) {
        throw new Error("Html input can not contain prototype substring '__': idHtml="+idHtml);
    }

    //console.log("idHtml="+idHtml);

    return idHtml;
}

//replace __documentContainers__ by 0. Since we can't add/delete documentContainer by JS, then it's safe to use the same documentContainer id.
function fileUploadProcessNewDropzoneId( holder, idHtml ) {
    if( idHtml.indexOf("__documentContainers__") != -1 ) {
        //console.log("idHtml="+idHtml);
        var documentContainerNewId = 0;
        //paperidElement = oleg_userdirectorybundle_user_credentials_boardCertification_1_attachmentContainer_documentContainers_0_id
        var paperidElement = holder.find('.documentcontainer-field-id');

        if( !paperidElement || paperidElement.length == 0 ) {
            printF(holder,"Container holder:");
            throw new Error("Container element is not found by class 'documentcontainer-field-id'");
        }

        //printF(paperidElement,"paperidElement:");
        //console.log(paperidElement);

        var documentContainerId = paperidElement.last().attr('id');
        //console.log("documentContainerId="+documentContainerId);

        if( documentContainerId.indexOf("_documentContainers_") != -1 ) {
            var nameArr = documentContainerId.split("_documentContainers_");
            if( nameArr.length > 1 ) {
                var endNameStr = nameArr[1]; //0_id
                if( endNameStr ) {
                    var endNameArr = endNameStr.split("_");
                    if( endNameArr.length > 0 ) {
                        var documentContainerCurrentId = endNameArr[0];
                        if( isInt(documentContainerCurrentId) ) {
                            documentContainerCurrentId = parseInt(documentContainerCurrentId);
                            documentContainerNewId = documentContainerCurrentId;    // + 1; //use the same container
                            //console.log("got documentContainerNewId="+documentContainerNewId);
                        }
                    }
                }
            }
        }
        //console.log("documentContainerNewId="+documentContainerNewId);
        //console.log("0 idHtml="+idHtml);

        idHtml = idHtml.replace("__documentContainers__", documentContainerNewId);
        //documentContainerNewId = 0;
        var find = '__documentContainers__';
        var re = new RegExp(find, 'g');
        idHtml = idHtml.replace(re, documentContainerNewId);
        //console.log("1 idHtml="+idHtml);
    }
    return idHtml;
}

//get document container id and name up to _documents_:
//example: oleg_userdirectorybundle_user_publicComments_0
//example: oleg_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0
//example: oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_paper_0
function getNewDocumentInfoByHolder( commentHolder ) {

    //console.log(commentHolder);

    if( commentHolder.length == 0 ) {
        throw new Error("Collection holder for file upload is not found");
    }

    //use dummyprototypefield to get id and name prototype for adding new document
    var uploadid = commentHolder.find('input.dummyprototypefield[id*="__documentsid__"]');

    if( uploadid.length == 0 ) {
        uploadid = commentHolder.find('input.dummyprototypefield');
    }

    if( uploadid.length == 0 ) {
        throw new Error("Can't find id and name prototype");
    }

    var id = uploadid.first().attr('id');
    var name = uploadid.first().attr('name');

    if( id == null  || name == null ) {
        throw new Error("upload id or name are null");
    }

    //console.log('id='+id+', name='+name);

    var res = getElementInfoById( id, name );

    res['documentCount'] = getNextCollectionCount( commentHolder, 'input.file-upload-id' );
    //res['documentCount'] = documentCount;

    return res;
}

//get id and name up to _documents_
function getElementInfoById( id, name ) {

    //console.log('id='+id);
    //console.log('name='+name);

    if( !id || id == ""  ) {
        throw new Error("id is empty, id="+id);
    }

    if( !name || name == ""  ) {
        throw new Error("name is empty, name="+name);
    }


//    if( id.indexOf("orderformbundle") !== -1 ) {
//        var res = getElementInfoById_Scan( id, name );
//    } else {
//        var res = getElementInfoById_User( id, name );
//    }

    //console.log("getElementInfoById id="+id);

    //id: oleg_userdirectorybundle_user[publicComments][0][documents][__documentsid__][id]
    var idArr = id.split("__documentsid__");
    var beginIdStr = idArr[0];
    console.log('beginIdStr='+beginIdStr);

    var nameArr = name.split("[__documentsid__]");
    var beginNameStr = nameArr[0];
    console.log('beginNameStr='+beginNameStr);


    var res = new Array();
    res['beginIdStr'] = beginIdStr;
    res['beginNameStr'] = beginNameStr;

    return res;
}

//fieldSelector - any element's id or class in the collection with proper id
function getNextCollectionCount( holder, fieldSelector ) {
    var maxCount = 0;

    var len = holder.find(fieldSelector).length;
    //console.log('len='+len);

    var counter = 0;

    holder.find(fieldSelector).each( function(){

        var count = getDocumentIndexById($(this).attr('id'));
        //console.log('count='+count);

        if( parseInt(count) > parseInt(maxCount) ) {
            maxCount = count;
        }
        //console.log('iteration maxCount='+maxCount);

        counter++;
    });

    if( counter > 0 ) {
        maxCount = parseInt(maxCount)+1;
    }

    //console.log('maxCount='+maxCount);

    return maxCount;
}

function getDocumentIndexById(id) {
    //console.log('doc id='+id);
    var documentIndex = 0;
    //id: oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_paper_0_documents_0_id  => get index id
    //id: oleg_userdirectorybundle_fellowshipapplication_coverLetters_1_id
    //id must have "_documentfieldname_index_id" string => get the last 3 elements from split
    var idArr = id.split("_");
    var documentfieldname = idArr[idArr.length-3];
    //console.log('documentfieldname='+documentfieldname);
    var documentspliter = "_"+documentfieldname+"_";

    if( id.indexOf(documentspliter) !== -1 ) {
        //get documentcount
        var docArr = id.split(documentspliter);
        var documentId = docArr[1];
        //console.log('documentId='+documentId);
        documentIndex = documentId.replace("_id", "");
        documentIndex = parseInt(documentIndex);
    }
    return documentIndex;
}

//function getElementInfoById_User( id, name ) {
//
//    var beginIdStr = null;
//    var beginNameStr = null;
//
//    //adding document to existing documentContainer with existing document(s)
//    //input: oleg_userdirectorybundle_user_publicComments_0_documents_1_id
//    //goal:  oleg_userdirectorybundle_user_publicComments_0
//    if( id.indexOf("_documents_") !== -1 ) {
//
//        /////////////// adding document //////////////////
//        var idArr = id.split("_documents");
//        beginIdStr = idArr[0];
//        var nameArr = name.split("[documents]");
//        beginNameStr = nameArr[0];
//
//    } else {
//
//        /////////////// new document //////////////////
//        //comment's document
//        //input: oleg_userdirectorybundle_user_publicComments_0_commentType
//        //goal:  oleg_userdirectorybundle_user_publicComments_0
//        if( id.indexOf("_commentType") !== -1 ) {
//            var idArr = id.split("_commentType");
//            beginIdStr = idArr[0];
//            var nameArr = name.split("[commentType]");
//            beginNameStr = nameArr[0];
//        }
//
//        //grant's document
//        //input: oleg_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0_id
//        //goal:  oleg_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0
//        if( id.indexOf("_documentContainers_") !== -1 ) {
//            var idArr = id.split("_documentContainers_");
//            var containerIndexArr = idArr[1].split("_id");
//            beginIdStr = idArr[0]+"_documentContainers_"+containerIndexArr[0];
//
//            var nameArr = name.split("[documentContainers]");
//            beginNameStr = nameArr[0]+"[documentContainers]"+"["+containerIndexArr[0]+"]";
//        }
//
//    }
//
//    if( beginIdStr == null ) {
//        throw new Error("beginIdStr is empty, beginIdStr="+beginIdStr);
//    }
//
//    if( beginNameStr == null ) {
//        throw new Error("beginNameStr is empty, beginNameStr="+beginNameStr);
//    }
//
//
//    var res = new Array();
//    res['beginIdStr'] = beginIdStr;
//    res['beginNameStr'] = beginNameStr;
//
//    return res;
//}

//function getElementInfoById_Scan( id, name ) {
//
//    //console.log('id='+id);
//    //console.log('name='+name);
//
//    //id=oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_paper_0_name
//
//    //result error:
//    //                                                        _accession_0_part_0_documents_0_id
//
//    //  0           1           2           3    4    5      6      7    8   9  10   11     12   13     14  15
//    //app_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_paper_0_documents_0_id
//
//    //var documentCount = 0;
//    var beginIdStr = null;
//    var beginNameStr = null;
//
//    if( id.indexOf("_documents_") !== -1 ) {
//        var idArr = id.split("_documents");
//        beginIdStr = idArr[0];
//        var nameArr = name.split("[documents]");
//        beginNameStr = nameArr[0];
//    } else {
//
//        //when collection does not have a file => use first collection field
//        //input: oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_partname_0_field
//        //goal:  oleg_orderformbundle_messagetype_patient_0_encounter_0_procedure_0_accession_0_part_0
//        if( id.indexOf("_partname_") !== -1 ) {
//            //need id up to _paper_0 => attach
//            var idArr = id.split("_partname_");
//            beginIdStr = idArr[0] + "_paper_0";
//            var nameArr = name.split("[partname]");
//            beginNameStr = nameArr[0] + "[paper][0]";
//        }
//
//        //TODO: test it
//        //input: oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0_sourceOrgan_0_field
//        //goal:  oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_part_0
////        if( id.indexOf("_sourceOrgan_") !== -1 ) {
////            var idArr = id.split("_sourceOrgan_");
////            beginIdStr = idArr[0];
////            var nameArr = name.split("[sourceOrgan]");
////            beginNameStr = nameArr[0];
////        }
//
//        //TODO: test it
//        //name: oleg_orderformbundle_messagetype[patient][0][procedure][0][accession][0][laborder][0][imageTitle]
//        //input: oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_laborder_0_imageTitle
//        //goal:  oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_laborder_0
////        if( id.indexOf("_imageTitle") !== -1 ) {
////            containerName = "";
////            containerId = "";
////            var idArr = id.split("_imageTitle");
////            beginIdStr = idArr[0];
////            var nameArr = name.split("[imageTitle]");
////            beginNameStr = nameArr[0];
////        }
//    }
//
//    //document is part of the document container
//    //name=oleg_orderformbundle_messagetype[patient][0][encounter][0][procedure][0][accession][0][laborder][0][documentContainer][id]
//    //input: oleg_orderformbundle_messagetype_patient_0_encounter_0_procedure_0_accession_0_laborder_0_documentContainer_id
//    //goal:  oleg_orderformbundle_messagetype_patient_0_encounter_0_procedure_0_accession_0_laborder_0
//    if( id.indexOf("_documentContainer_") !== -1 ) {
//        var idArr = id.split("_documentContainer_");
//        beginIdStr = idArr[0] + "_documentContainer";
//        var nameArr = name.split("[documentContainer]");
//        beginNameStr = nameArr[0] + "[documentContainer]";
//    }
//
//    if( beginIdStr == null ) {
//        throw new Error("beginIdStr is empty, beginIdStr="+beginIdStr);
//    }
//
//    if( beginNameStr == null ) {
//        throw new Error("beginNameStr is empty, beginNameStr="+beginNameStr);
//    }
//
//    var res = new Array();
//    res['beginIdStr'] = beginIdStr;
//    res['beginNameStr'] = beginNameStr;
//
//    return res;
//}







//create documentContainer and its documents by JS

//grant documents
function setGrantDocuments( parent, data ) {

    //console.log(parent);
    //console.log(data);

    if( !parent.hasClass('user-grants') ) {
        return;
    }

    var documentContainerData = null;
    if( data ) {
        documentContainerData = data['documentContainers']; //documentContainers
    }

    //disable grant document
//    var dropzoneElement = parent.find('.file-upload-dropzone');
//    disableEnableDropzone( dropzoneElement, true, null, true );

    //console.log("setGrantDocuments run");
    //console.log(documentContainerData);

    setDocumentsInDocumentConatiner(
        parent,
        documentContainerData,      //
        null,                       //tooltipName
        '.documentcontainer'        //documentHolderClass
    );
}

//common functions
function setDocumentsInDocumentConatiner( parent, documentContainerData, tooltipName, documentHolderClass ) {

    //clean fields
    if( documentContainerData == null  ) {

        if( parent.find('.file-upload-dropzone').length == 1 ) {
            parent.find('.file-upload-dropzone').removeClass('dropzone-keep-enabled');
            parent.find('.file-upload-dropzone').find('.dz-preview').remove();
            parent.find('.file-upload-dropzone').find('.dz-message').css('opacity','1');
            if( tooltipName ) {
                attachDropzoneTooltip(parent.find('.file-upload-dropzone'),true,tooltipName);
            }
            return;
        }

        parent.find('.file-upload-dropzone').not('.dropzone-keep-enabled').closest('.row').remove();
        parent.find('.file-upload-dropzone').removeClass('dropzone-keep-enabled');
        parent.find('.file-upload-dropzone').find('.dz-message').css('opacity','1');
        parent.find('.file-upload-dropzone').find('.dz-preview').remove();
        return;
    }

    //keep enabled first document container dropzone
    var existingDropzone = parent.find('.file-upload-dropzone').first();
    existingDropzone.addClass('dropzone-keep-enabled');

    //console.log('create dropzone');
    //console.log(documentContainerData);

    if( documentContainerData && documentContainerData != undefined ) {

        var papers = documentContainerData;

        if( papers.length == 0 ) {
            return;
        }

        //console.log('papers count=' + papers.length );

        for( var i=0; i<papers.length; i++ ) {

            var paper = papers[i];

            //console.log('paper id='+paper.id);
            //console.log(paper);

            //console.log('documents length='+paper['documents'].length);
            if( paper['documents'].length == 0 ) {
                //console.log('no documents in paper');
                continue;
            }

            //create paper prototype using data-prototype-partpaper
            var newDropzoneHolder = createDropzoneHolder(existingDropzone, documentHolderClass);
            var documentContainerData = processDocumentsInDocumentContainer(paper);

            //console.log('documentContainerData:');
            //console.log(documentContainerData);

            if( newDropzoneHolder ) {
                //create a new dropzone element (for part paper: existing paper dropzone prepend before new paper dropzone)
                var newDropzoneHolderEl = $(newDropzoneHolder);

                //attach paper prototype to part after Source Organ
                var documentContainerHolder = parent.find(documentHolderClass);
                //console.log('prepend to:');
                //console.log(documentContainerHolder);
                documentContainerHolder.prepend( newDropzoneHolderEl );

                //init dropzone
                initFileUpload( newDropzoneHolderEl, documentContainerData, null );

            } else {
                //find and process existing dropzone (for grant's documents)
                var dropzoneEl = parent.find(documentHolderClass).find('.file-upload-dropzone'); //file-upload-dropzone

                var dropzoneDom = dropzoneEl.get(0);
                //console.log('className='+dropzoneDom.className);
                var thisDropzone = dropzoneDom.dropzone;

                populateDropzoneWithData(thisDropzone,documentContainerData,false);

                //hide remove button
                //var removeLinks = $(thisDropzone.element).find('.dz-remove');
                //removeLinks.hide();

            } //if else

        } //for

    } //if


}


function processDocumentsInDocumentContainer( documentContainer ) {

    var documents = documentContainer['documents'];

    //console.log('documents count=' + documents.length );

    if( documents.length == 0 ) {
        return;
    }

    var data = new Array();

    for( var i=0; i<documents.length; i++ ) {

        var document = documents[i];

        var originalname = document['originalname'];
        var uniquename = document['uniquename'];
        var size = document['size'];
        var url = document['url'];
        var id = document['id'];

        //console.log('originalname='+originalname);

        var fileArr = new Array();
        fileArr['originalname'] = originalname;
        fileArr['uniquename'] = uniquename;
        fileArr['size'] = size;
        fileArr['url'] = url;
        fileArr['id'] = id;
        data.push(fileArr);

    }

    return data;
}




function createDropzoneHolder(existingDropzoneHolder, switchflag ) {
    //console.log('createDropzoneHolder switchflag='+switchflag);

    if( switchflag == '.partpaper' ) {
        return createDropzoneHolder_Paper(existingDropzoneHolder);
    } else {
        return null;    //createDropzoneHolder_Other(existingDropzoneHolder);
    }
}

//paper dropzone
function createDropzoneHolder_Paper(existingDropzoneHolder) {

    var dataElement = document.getElementById("form-prototype-data");
    var prototype = dataElement.getAttribute('data-prototype-partpaper');

    //console.log('paper prototype='+prototype);

    //printF(existingDropzoneHolder,"existingDropzoneHolder:");
    //console.log(existingDropzoneHolder);

    var paperidElement = existingDropzoneHolder.parent().find('.field-partpaperothers');
    //var paperidElement = existingDropzoneHolder.closest('.partpaper').find('.field-partpaperothers'); //this will get incorrect paperid

    //console.log('count paperidElement='+paperidElement.length);

    if( !paperidElement || paperidElement.length == 0 ) {
        throw new Error("Paper element is not found");
    }

    //printF(paperidElement,"paperidElement:");
    //console.log(paperidElement);

    var id = paperidElement.last().attr('id');

    if( !id || id == "" ) {
        throw new Error("Paper id element is not found");
    }

    var idArr = id.split("_");

    //  0       1               2           3    4     5     6     7     8      9   10  11  12  13  14  15
    //app_orderformbundle_messagetype_patient_0_encounter_0_procedure_0_accession_0_part_0_paper_0_others
    var patientid = idArr[4];
    var encounterid = idArr[6];
    var procedureid = idArr[8];
    var accessionid = idArr[10];
    var partid = idArr[12];
    var paperid = idArr[14];

    var newForm = prototype.replace(/__patient__/g, patientid);
    newForm = newForm.replace(/__encounter__/g, encounterid);
    newForm = newForm.replace(/__procedure__/g, procedureid);
    newForm = newForm.replace(/__accession__/g, accessionid);
    newForm = newForm.replace(/__part__/g, partid);
    newForm = newForm.replace(/__partpaper__/g, paperid);

    //console.log("paper newForm="+newForm);

    return newForm;
}

//Currently NOT USED. user directory: grants dropzone
function createDropzoneHolder_Other(existingDropzoneHolder) {

    var dataElement = document.getElementById("form-prototype-data");
    var prototype = dataElement.getAttribute('data-prototype-documentcontainers');

    //console.log('prototype='+prototype);

    //printF(existingDropzoneHolder,"existingDropzoneHolder:");
    //console.log(existingDropzoneHolder);

    var paperidElement = existingDropzoneHolder.closest('.well').find('.documentcontainer-field-id');

    //console.log('count paperidElement='+paperidElement.length);

    if( !paperidElement || paperidElement.length == 0 ) {
        throw new Error("Container element is not found");
    }

    //printF(paperidElement,"paperidElement:");
    //console.log(paperidElement);

    var id = paperidElement.last().attr('id');

    if( !id || id == "" ) {
        throw new Error("Container id element is not found");
    }

    //console.log("id="+id);

    var idArr = id.split("_");

    //  0       1               2     3    4          5                  6          7  8
    //app_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0_id
    var grantid = idArr[4];
    var documentContainerid = idArr[7];
    var documentid = 0;

    var newForm = prototype.replace(/__grants__/g, grantid);
    newForm = newForm.replace(/__documentContainers__/g, documentContainerid);
    newForm = newForm.replace(/__documentsid__/g, documentid);

    //console.log("newForm="+newForm);

    return newForm;
}


function disableEnable_Dropzone_NEW( dropzoneElement, disabled, tooltipName, forcedisable ) {

    var dropzoneDom = dropzoneElement.get(0);
    console.log('disable/enable dropzone className='+dropzoneDom.className);
    var myDropzone = dropzoneDom.dropzone;

    //if( !myDropzone.listeners[1] ) {
    //    return;
    //}

    if( typeof forcedisable === 'undefined' ) {
        forcedisable = false;
    }

    if( disabled ) {
        if( !dropzoneElement.hasClass('dropzone-keep-enabled') || forcedisable ) {
            //disable
            //console.log('disable dropzone');
            dropzoneElement.removeClass('dz-clickable'); // remove cursor
            dropzoneDom.removeEventListener('click', myDropzone.listeners[1].events.click);
        }
        //console.log('ignore disable dropzone');
        //add tooltip
        if( tooltipName ) {
            attachDropzoneTooltip(dropzoneElement,true,tooltipName);
        }
    } else {
        //enable
        //console.log('enable dropzone');
        dropzoneElement.addClass('dz-clickable'); // add cursor
        dropzoneDom.addEventListener('click', myDropzone.listeners[1].events.click);
        //remove tooltip
        if( tooltipName ) {
            attachDropzoneTooltip(dropzoneElement,false,tooltipName);
        }
    }
}


function disableEnableDropzone( dropzoneElement, disabled, tooltipName, forcedisable ) {

    var dropzoneDom = dropzoneElement.get(0);
    //console.log('disable/enable dropzone className='+dropzoneDom.className);
    var myDropzone = dropzoneDom.dropzone;

    if( !myDropzone.listeners[1] ) {
        return;
    }

    if( typeof forcedisable === 'undefined' ) {
        forcedisable = false;
    }

    if( (disabled && !dropzoneElement.hasClass('dropzone-keep-enabled')) || (disabled && forcedisable) ) {
        //disable
        //printF(dropzoneElement,'disable dropzone:');
        //dropzoneElement.removeClass('dz-clickable'); // remove cursor
        //dropzoneDom.removeEventListener('click', myDropzone.listeners[1].events.click);
        myDropzone.disable();
        //add tooltip
        if( tooltipName ) {
            attachDropzoneTooltip(dropzoneElement,true,tooltipName);
        }
    } else {
        //enable
        //printF(dropzoneElement,'enable dropzone:');
        //dropzoneElement.addClass('dz-clickable'); // add cursor
        //dropzoneDom.addEventListener('click', myDropzone.listeners[1].events.click);
        myDropzone.enable();
        //remove tooltip
        attachDropzoneTooltip(dropzoneElement,false,tooltipName);
    }

}

function attachDropzoneTooltip( element, attach, tooltipStr ) {
    if( attach ) {
        //printF(element,"attach dropzone tooltip");
        element.tooltip({
            'title':tooltipStr
        });
    } else {
        //printF(element,"destroy dropzone tooltip");
        element.tooltip('destroy');
    }
}
