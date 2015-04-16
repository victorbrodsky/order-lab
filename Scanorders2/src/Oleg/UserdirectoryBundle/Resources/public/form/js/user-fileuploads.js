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

    //Dropzone.autoDiscover = false;

    //console.log('cycle='+cycle);
    var clickable = true;

    if( cycle == "show_user" || cycle == "show" ) {
        clickable = false;
    }

    if( typeof addRemoveLinks === 'undefined' ) {
        var addRemoveLinks = true;
        if( cycle == "show_user" || cycle == "show" ) {
            addRemoveLinks = null;
        }
    }

    //console.log('clickable='+clickable);
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

    $(targetid).dropzone({
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

            var idHtml = constractDocuemntIdFieldHtml(commentHolder,documentid);

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

            if( data == null ) {
                return;
            }

            //console.log('manual init');

            var thisDropzone = this;

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

            }
            //See more at: http://www.startutorial.com/articles/view/dropzonejs-php-how-to-display-existing-files-on-server#sthash.sqF6KDsk.dpuf
        }

    });


//    $('#jquery-fileupload').fileupload({});

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

//get comment type and count
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

function getElementInfoById( id, name ) {

    //console.log('id='+id);

    if( !id || id == ""  ) {
        throw new Error("id is empty, id="+id+", name="+name);
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

    //id=oleg_userdirectorybundle_user_publicComments_0_commentType
    // name=oleg_userdirectorybundle_user[publicComments][0][commentType]

    //  0           1           2       3          4    5      6  7
    //oleg_userdirectorybundle_user_publicComments_0_documents_1_id
    var idArr = id.split("_");
    var bundleName = idArr[1];
    var commentType = idArr[3];
    var commentCount = idArr[4];
    var documentCount = idArr[6];

    if( id.indexOf("_documents_") !== -1 ) {
        var idDel = "_documents_";
        var nameDel = "[documents]";
    } else {
        //when collection does not have a file => use first collection field
        var idDel = "_commentType";
        var nameDel = "[commentType]";
    }

    //up to documents string: oleg_userdirectorybundle_user_publicComments_0_
    var idArr = id.split(idDel);
    var beginIdStr = idArr[0];

    var res = new Array();
    res['bundleName'] = bundleName;
    res['commentType'] = commentType;
    res['commentCount'] = commentCount;
    res['documentCount'] = documentCount;
    res['beginIdStr'] = beginIdStr;

    if( typeof name !== 'undefined' ) {
        //up to documents string: oleg_orderformbundle_orderinfotype[patient][0][procedure][0][accession][0][part][0]
        var nameArr = name.split(nameDel);
        var beginNameStr = nameArr[0];
        res['beginNameStr'] = beginNameStr;
    }

    return res;
}

function getElementInfoById_Scan( id, name ) {

    //console.log('id='+id);
    //console.log('name='+name);

    //id=oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0_name

    //  0           1           2           3    4    5      6      7    8   9  10   11     12   13     14  15
    //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0_documents_0_id
    var idArr = id.split("_");
    var bundleName = idArr[1];
    //var commentType = idArr[3];
    //var commentCount = idArr[4];
    //var documentCount = idArr[14];
    var documentCount = 0;

    var idDel = null;
    var nameDel = null;
    var docname = "[paper][0]";
    var docid = "_paper_0";

    if( id.indexOf("_documents_") !== -1 ) {
        idDel = "_paper_";  //"_documents_";
        nameDel = "[paper]";    //"[documents]";

        //get documentcount
        var docArr = id.split("_documents_");
        var documentId = docArr[1];
        documentCount = documentId.replace("_id", "");
        documentCount = parseInt(documentCount);

    } else {
        //when collection does not have a file => use first collection field
        if( id.indexOf("_partname_") !== -1 ) {
            //id: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_partname_0_field
            idDel = "_partname_";
            nameDel = "[partname]";
        }
        if( id.indexOf("_sourceOrgan_") !== -1 ) {
            //id: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_sourceOrgan_0_field
            idDel = "_sourceOrgan_";
            nameDel = "[sourceOrgan]";
        }
        if( id.indexOf("_imageTitle") !== -1 ) {
            //id: oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_laborder_0_imageTitle
            //name: oleg_orderformbundle_orderinfotype[patient][0][procedure][0][accession][0][laborder][0][imageTitle]
            idDel = "_imageTitle";
            nameDel = "[imageTitle]";
            docname = "";
            docid = "";
        }
    }

    //document is part of the document container
    if( id.indexOf("_documentContainer_") !== -1 ) {
        //id=oleg_orderformbundle_orderinfotype_patient_0_encounter_0_procedure_0_accession_0_laborder_0_documentContainer_id,
        //name=oleg_orderformbundle_orderinfotype[patient][0][encounter][0][procedure][0][accession][0][laborder][0][documentContainer][id]
        idDel = "_documentContainer_";
        nameDel = "[documentContainer]";
        docname = "[documentContainer]";
        docid = "_documentContainer";
    }

    if( idDel == null || nameDel == null ) {
        throw new Error("id or name delimeter is empty, idDel="+idDel+", nameDel="+nameDel);
    }

    //up to documents string: oleg_userdirectorybundle_user_publicComments_0_
    var idArr = id.split(idDel);
    var beginIdStr = idArr[0];
    beginIdStr = beginIdStr + docid;

    var res = new Array();
    res['bundleName'] = bundleName;
    //res['commentType'] = commentType;
    //res['commentCount'] = commentCount;
    res['documentCount'] = documentCount;
    res['beginIdStr'] = beginIdStr;

    if( typeof name !== 'undefined' ) {
        //up to documents string: oleg_orderformbundle_orderinfotype[patient][0][procedure][0][accession][0][part][0]
        var nameArr = name.split(nameDel);
        var beginNameStr = nameArr[0];
        beginNameStr = beginNameStr + docname;
        res['beginNameStr'] = beginNameStr;
    }

    //res = fixBeginStr(res);

    return res;
}


//fieldSelector - any element's id or class in the collection with proper id
function getNextCollectionCount( holder, fieldSelector ) {
    var maxCount = 0;

    var len = holder.find(fieldSelector).length;
    //console.log('len='+len);

    var counter = 0;

    holder.find(fieldSelector).each( function(){

        var res = getElementInfoById( $(this).attr('id'), $(this).attr('name') );
        var count = res['documentCount'];
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

function constractDocuemntIdFieldHtml(commentHolder,documentid) {

    var res = getNewDocumentInfoByHolder(commentHolder);

    //insert document id input field
    var bundleName = res['bundleName'];
    var commentType = res['commentType'];
    var commentCount = res['commentCount'];
    var documentCount = res['documentCount'];
    var beginIdStr = res['beginIdStr'];
    var beginNameStr = res['beginNameStr'];

    //var documentCount = maxFiles + comments.length;    //'1'; //maximum number of comments is limited, so use this number

    var idHtml =    '<input type="hidden" id="'+beginIdStr+'_documents_'+documentCount+'_id" '+
        'name="'+beginNameStr+'[documents]['+documentCount+'][id]" class="file-upload-id" value="'+documentid+'">';

    return idHtml;
}


