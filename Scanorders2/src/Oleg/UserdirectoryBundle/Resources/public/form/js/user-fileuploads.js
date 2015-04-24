/**
 * Created by oli2002 on 10/20/14.
 */

//dropzone globals
var _dz_maxFiles = 3;
var _dz_maxFilesize = 10; //MB


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

    console.log('cycle='+cycle);

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
    //console.log('userid='+userid);

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

    console.log('clickable='+clickable);
    //console.log('addRemoveLinks='+addRemoveLinks);

    //overwrite maxfiles
    var documentspercontainer = $(targetid).find('#documentcontainer-documentspercontainer').val();
    if( documentspercontainer && documentspercontainer != "undefined" ) {
        _dz_maxFiles = documentspercontainer;
    }
    //console.log('_dz_maxFiles='+_dz_maxFiles);

    var previewHtml =
        '<div class="dz-preview dz-file-preview" style="width:32%; height:220px; margin-left:1px; margin-right:0px;">'+
            '<div class="dz-details">'+
            '<div class="dz-filename"><span data-dz-name></span></div>'+
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
                formData.append('userid', userid);
                var filename = file.name;
                //console.log('filename='+filename);
                formData.append('filename', filename);
            },
            success: function(file, responseText){
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
                if (file.previewElement) {
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

                var withRemoveLinks = true;
                //disable dropzone if dropzone has class file-upload-dropzone-inactive
                if( $(this.element).hasClass('file-upload-dropzone-inactive') ) {
                    //console.log('init: disable dropzone');
                    //console.log($(this.element));
                    disableEnableDropzone( $(this.element), true, null, true );
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

    var commenttype = null;
    if( holderTop.hasClass('user-publiccomments') ) {
        commenttype = "OlegUserdirectoryBundle:PublicComment";
    }
    if( holderTop.hasClass('user-privatecomments') ) {
        commenttype = "OlegUserdirectoryBundle:PrivateComment";
    }
    if( holderTop.hasClass('user-admincomments') ) {
        commenttype = "OlegUserdirectoryBundle:AdminComment";
    }
    if( holderTop.hasClass('user-confidentialcomment') ) {
        commenttype = "OlegUserdirectoryBundle:ConfidentialComment";
    }

//    //for scan orders don't delete from DB. Just remove from form
//    if( holderTop.hasClass('scan-partpaper') ) {
//        //commenttype = "OlegOrderformBundle:PartPaper";
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

        var url = getCommonBaseUrl("file-delete","employees");
        //console.log('url='+url);
        //use comment id and documentid
        $.ajax({
            type: "POST",
            url: url,
            timeout: _ajaxTimeout,
            async: true,
            data: { documentid: documentid, commentid: commentid, commenttype: commenttype }
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
            //}
        }).fail(function(data) {
            console.log('remove failed, data='+data);
        }) ;

    //}
//    else {
//        var _ref;
//        if( previewElement ) {
//            if( (_ref = previewElement) != null ) {
//                _ref.parentNode.removeChild(previewElement);
//            }
//        }
//    }

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




function constractDocumentIdFieldHtml(commentHolder,documentid) {

    var res = getNewDocumentInfoByHolder(commentHolder);

    //insert document id input field
    //var bundleName = res['bundleName'];
    //var commentType = res['commentType'];
    //var commentCount = res['commentCount'];
    var documentCount = res['documentCount'];
    var beginIdStr = res['beginIdStr'];
    var beginNameStr = res['beginNameStr'];

    //var documentCount = maxFiles + comments.length;    //'1'; //maximum number of comments is limited, so use this number

    var idHtml =    '<input type="hidden" id="'+beginIdStr+'_documents_'+documentCount+'_id" '+
        'name="'+beginNameStr+'[documents]['+documentCount+'][id]" class="file-upload-id" value="'+documentid+'">';

    //console.log("idHtml="+idHtml);

    return idHtml;
}


//get document container id and name up to _documents_:
//example: oleg_userdirectorybundle_user_publicComments_0
//example: oleg_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0
//example: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0
function getNewDocumentInfoByHolder( commentHolder ) {

    //console.log(commentHolder);

    if( commentHolder.length == 0 ) {
        throw new Error("Collection holder for file upload is not found");
    }

    var uploadid = commentHolder.find('input.file-upload-id');

    if( uploadid.length == 0 ) {
        //no existing documents in comment => use hidden document container container id
        //console.log('upload id does not exist: try class=documentcontainer-field-id');
        uploadid = commentHolder.find('.documentcontainer-field-id');
    }

    if( uploadid.length == 0 ) {
        //no existing documents in comment => use alternative id (i.e. first input id)
        //console.log('upload id does not exist: try alternative id');
        uploadid = commentHolder.find('input').filter(':visible').not("*[id^='s2id_']"); //.ajax-combobox-partname
    }

    var id = uploadid.first().attr('id');
    var name = uploadid.first().attr('name');

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
        throw new Error("id is empty, id="+id+", name="+name);
    }

    if( !name || name == ""  ) {
        throw new Error("name is empty, name="+name);
    }


    if( id.indexOf("orderformbundle") !== -1 ) {
        var res = getElementInfoById_Scan( id, name );
    } else {
        var res = getElementInfoById_User( id, name );
    }

    //check document count
//    var holder = $('#'+id).closest('.file-upload-dropzone');
//    var documents = holder.find('.dz-image-preview');
//    var documentCount = documents.length;
//    console.log( parseInt(res['documentCount']) + "<" + parseInt(documentCount) );
//    if( parseInt(res['documentCount']) < parseInt(documentCount) ) {
//        res['documentCount'] = documentCount;
//        console.log( "documentCount=" + res['documentCount'] );
//    }

    return res;
}

function getElementInfoById_User( id, name ) {

    var beginIdStr = null;
    var beginNameStr = null;

    //adding document to existing documentContainer with existing document(s)
    //input: oleg_userdirectorybundle_user_publicComments_0_documents_1_id
    //goal:  oleg_userdirectorybundle_user_publicComments_0
    if( id.indexOf("_documents_") !== -1 ) {

        /////////////// adding document //////////////////
        var idArr = id.split("_documents");
        beginIdStr = idArr[0];
        var nameArr = name.split("[documents]");
        beginNameStr = nameArr[0];

    } else {

        /////////////// new document //////////////////
        //comment's document
        //input: oleg_userdirectorybundle_user_publicComments_0_commentType
        //goal:  oleg_userdirectorybundle_user_publicComments_0
        if( id.indexOf("_commentType") !== -1 ) {
            var idArr = id.split("_commentType");
            beginIdStr = idArr[0];
            var nameArr = name.split("[commentType]");
            beginNameStr = nameArr[0];
        }

        //grant's document
        //input: oleg_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0_id
        //goal:  oleg_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0
        if( id.indexOf("_documentContainers_") !== -1 ) {
            var idArr = id.split("_documentContainers_");
            var containerIndexArr = idArr[1].split("_id");
            beginIdStr = idArr[0]+"_documentContainers_"+containerIndexArr[0];

            var nameArr = name.split("[documentContainers]");
            beginNameStr = nameArr[0]+"[documentContainers]"+"["+containerIndexArr[0]+"]";
        }

    }

    if( beginIdStr == null ) {
        throw new Error("beginIdStr is empty, beginIdStr="+beginIdStr);
    }

    if( beginNameStr == null ) {
        throw new Error("beginNameStr is empty, beginNameStr="+beginNameStr);
    }


    var res = new Array();
    res['beginIdStr'] = beginIdStr;
    res['beginNameStr'] = beginNameStr;

    return res;
}

function getElementInfoById_Scan( id, name ) {

    //console.log('id='+id);
    //console.log('name='+name);

    //id=oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0_name

    //result error:
    //                                                        _accession_0_part_0_documents_0_id

    //  0           1           2           3    4    5      6      7    8   9  10   11     12   13     14  15
    //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0_documents_0_id

    //var documentCount = 0;
    var beginIdStr = null;
    var beginNameStr = null;

    if( id.indexOf("_documents_") !== -1 ) {
        var idArr = id.split("_documents");
        beginIdStr = idArr[0];
        var nameArr = name.split("[documents]");
        beginNameStr = nameArr[0];
    } else {

        //when collection does not have a file => use first collection field
        //input: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_partname_0_field
        //goal:  oleg_orderformbundle_orderinfotype_patient_0_encounter_0_procedure_0_accession_0_part_0
        if( id.indexOf("_partname_") !== -1 ) {
            //need id up to _paper_0 => attach
            var idArr = id.split("_partname_");
            beginIdStr = idArr[0] + "_paper_0";
            var nameArr = name.split("[partname]");
            beginNameStr = nameArr[0] + "[paper][0]";
        }

        //TODO: test it
        //input: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_sourceOrgan_0_field
        //goal:  oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0
//        if( id.indexOf("_sourceOrgan_") !== -1 ) {
//            var idArr = id.split("_sourceOrgan_");
//            beginIdStr = idArr[0];
//            var nameArr = name.split("[sourceOrgan]");
//            beginNameStr = nameArr[0];
//        }

        //TODO: test it
        //name: oleg_orderformbundle_orderinfotype[patient][0][procedure][0][accession][0][laborder][0][imageTitle]
        //input: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_laborder_0_imageTitle
        //goal:  oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_laborder_0
//        if( id.indexOf("_imageTitle") !== -1 ) {
//            containerName = "";
//            containerId = "";
//            var idArr = id.split("_imageTitle");
//            beginIdStr = idArr[0];
//            var nameArr = name.split("[imageTitle]");
//            beginNameStr = nameArr[0];
//        }
    }

    //document is part of the document container
    //name=oleg_orderformbundle_orderinfotype[patient][0][encounter][0][procedure][0][accession][0][laborder][0][documentContainer][id]
    //input: oleg_orderformbundle_orderinfotype_patient_0_encounter_0_procedure_0_accession_0_laborder_0_documentContainer_id
    //goal:  oleg_orderformbundle_orderinfotype_patient_0_encounter_0_procedure_0_accession_0_laborder_0
    if( id.indexOf("_documentContainer_") !== -1 ) {
        var idArr = id.split("_documentContainer_");
        beginIdStr = idArr[0] + "_documentContainer";
        var nameArr = name.split("[documentContainer]");
        beginNameStr = nameArr[0] + "[documentContainer]";
    }

    if( beginIdStr == null ) {
        throw new Error("beginIdStr is empty, beginIdStr="+beginIdStr);
    }

    if( beginNameStr == null ) {
        throw new Error("beginNameStr is empty, beginNameStr="+beginNameStr);
    }

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
    var documentIndex = 0;
    //id: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0_documents_0_id  => get index id
    //id must have "_documents_index" string
    if( id.indexOf("_documents_") !== -1 ) {
        //get documentcount
        var docArr = id.split("_documents_");
        var documentId = docArr[1];
        documentIndex = documentId.replace("_id", "");
        documentIndex = parseInt(documentIndex);
    }
    return documentIndex;
}





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
        '.documentcontainer'              //documentHolderClass
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
                attachTooltip(parent.find('.file-upload-dropzone'),true,tooltipName);
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
    //oleg_orderformbundle_orderinfotype_patient_0_encounter_0_procedure_0_accession_0_part_0_paper_0_others
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

    printF(paperidElement,"paperidElement:");
    //console.log(paperidElement);

    var id = paperidElement.last().attr('id');

    if( !id || id == "" ) {
        throw new Error("Container id element is not found");
    }

    //console.log("id="+id);

    var idArr = id.split("_");

    //  0       1               2     3    4          5                  6          7  8
    //oleg_userdirectorybundle_user_grants_0_attachmentContainer_documentContainers_0_id
    var grantid = idArr[4];
    var documentContainerid = idArr[7];
    var documentid = 0;

    var newForm = prototype.replace(/__grants__/g, grantid);
    newForm = newForm.replace(/__documentContainers__/g, documentContainerid);
    newForm = newForm.replace(/__documents__/g, documentid);

    //console.log("newForm="+newForm);

    return newForm;
}


function disableEnableDropzone_NEW( dropzoneElement, disabled, tooltipName, forcedisable ) {

    var dropzoneDom = dropzoneElement.get(0);
    //console.log('disable/enable dropzone className='+dropzoneDom.className);
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
            attachTooltip(dropzoneElement,true,tooltipName);
        }
    } else {
        //enable
        //console.log('enable dropzone');
        dropzoneElement.addClass('dz-clickable'); // add cursor
        dropzoneDom.addEventListener('click', myDropzone.listeners[1].events.click);
        //remove tooltip
        if( tooltipName ) {
            attachTooltip(dropzoneElement,false,tooltipName);
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
        dropzoneElement.removeClass('dz-clickable'); // remove cursor
        dropzoneDom.removeEventListener('click', myDropzone.listeners[1].events.click);
        //add tooltip
        if( tooltipName ) {
            attachTooltip(dropzoneElement,true,tooltipName);
        }
    } else {
        //enable
        dropzoneElement.addClass('dz-clickable'); // add cursor
        dropzoneDom.addEventListener('click', myDropzone.listeners[1].events.click);
        //remove tooltip
        if( tooltipName ) {
            attachTooltip(dropzoneElement,false,tooltipName);
        }
    }

}
