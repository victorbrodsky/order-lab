/**
 * Created by oli2002 on 10/20/14.
 */

//dropzone globals
var _dz_maxFiles = 10;
var _dz_maxFilesize = 10; //MB


// Prevent Dropzone from auto discovering this element
if( typeof Dropzone !== 'undefined' ) {
    Dropzone.autoDiscover = false;
}

//File Uploads using Dropzone and Oneup\UploaderBundle
function initFileUpload( holder ) {

    //console.log('init File Upload');

    if( $('.dropzone').length == 0 ) {
        return;
    }

    var targetid = ".file-upload-dropzone";
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( targetid.length == 0 )
            return;
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

    Dropzone.autoDiscover = false;

    //console.log('cicle='+cicle);
    var clickable = true;
    var addRemoveLinks = true;
    if( cicle == "show_user" || cicle == "show" ) {
        clickable = false;
        addRemoveLinks = null;
    }

    //console.log('clickable='+clickable);
    //console.log('addRemoveLinks='+addRemoveLinks);

    var previewHtml =
        '<div class="dz-preview dz-file-preview" style="width:24%; height:220px; margin:0;">'+
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

            //var comments = commentHolder.find('.comment-field-id').first();
            //var commentFirst = comments.first();

//            var res = getNewDocumentInfoByHolder(commentHolder);
//
//            //insert document id input field
//            var bundleName = res['bundleName'];
//            var commentType = res['commentType'];
//            var commentCount = res['commentCount'];
//            var documentCount = res['documentCount'];
//
//            //var documentCount = maxFiles + comments.length;    //'1'; //maximum number of comments is limited, so use this number
//
//            var idHtml =    '<input type="hidden" id="oleg_'+bundleName+'_user_'+commentType+'_'+commentCount+'_documents_'+documentCount+'_id" '+
//                'name="oleg_'+bundleName+'_user['+commentType+']['+commentCount+'][documents]['+documentCount+'][id]" class="file-upload-id" value="'+documentid+'">';

            var idHtml = constractDocuemntIdFieldHtml(commentHolder,documentid);

            var showlinkHtml =  '<div style="overflow:hidden; white-space:nowrap;">'+
                '<a href="'+documentSrc+'" target="_blank">'+file.name+'</a>'+
                '</div>';

            if( file.previewElement ) {
                $(file.previewElement).append(idHtml);
                var showlinkDiv = $(file.previewElement).find('.file-upload-showlink');
                showlinkDiv.html(showlinkHtml);

                adjustHolderHeight(commentHolder);
            }

            //pupulate document id input field
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
            this.removeFile(file);
        },
        removedfile: function(file) {
            //console.log('remove js file name='+file.name);
            return removeUploadedFileByHolder( file.previewElement, this );
        },
//        init: function() {
//            console.log('manual init');
//            return;
//            thisDropzone = this;
//
//            //console.log(thisDropzone);
//            var holder = $(thisDropzone.element).closest('.files-upload-holder');
//
//            var existedfiles = holder.find('.file-holder');
//            console.log('existedfiles len='+existedfiles.length);
//
//            var data = new Array();
//
//            existedfiles.each( function() {
//                console.log('filename='+$(this).find('.file-upload-uniquename').val())
//                var fileArr = new Array();
//                fileArr['name'] = $(this).find('.file-upload-uniquename').val();
//                fileArr['size'] = $(this).find('.file-upload-size').val();
//                fileArr['dir'] = $(this).find('.file-upload-uploaddirectory').val();
//                data.push(fileArr);
//            });
//
//            console.log('data len='+data.length);
//
//            for( var i = 0; i < data.length; i++ ) {
//
//                var value = data[i];
//
//                console.log('name='+value.name);
//
//                var mockFile = { name: value.name, size: value.size };
//
//                thisDropzone.options.addedfile.call(thisDropzone, mockFile);
//
//                var filepath = "http://collage.med.cornell.edu/order/Uploaded/pathology-employees/Documents/"+value.name;
//                console.log('path='+filepath);
//
//                thisDropzone.options.thumbnail.call(thisDropzone, mockFile, filepath);
//            }
//            //See more at: http://www.startutorial.com/articles/view/dropzonejs-php-how-to-display-existing-files-on-server#sthash.sqF6KDsk.dpuf
//        }
//        confirm: function(question, accepted, rejected) {
//            console.log();
//            // Do your thing, ask the user for confirmation or rejection, and call
//            // accepted() if the user accepts, or rejected() otherwise. Make
//            // sure that rejected is actually defined!
//        }

    });


//    $('#jquery-fileupload').fileupload({});

}

function removeUploadedFileByHolder( previewElement, dropzone ) {

    var r = confirm('Are you sure you want to remove this document?'+', id='+documentid);
    if( r == false ) {
        return;
    }

    if( !previewElement ) {
        return;
    }

    var documentid = $(previewElement).find('.file-upload-id').val();
    //console.log('remove documentid='+documentid);

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
    if( commenttype != null ) {
        var url = getCommonBaseUrl("file-delete","employees");
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
    } else {
        var _ref;
        if( previewElement ) {
            if( (_ref = previewElement) != null ) {
                _ref.parentNode.removeChild(previewElement);
            }
        }
    }

    return dropzone._updateMaxFilesReachedClass();
}

function removeUploadedFile(btn) {
    var dropzoneEl = $(btn).closest('.file-upload-dropzone');
    var dropzoneDom = dropzoneEl.get(0);
    //console.log('className='+dropzoneDom.className);

    var myDropzone = dropzoneDom.dropzone;

    var previewElement = $(btn).closest('.dz-file-preview').get(0);

    removeUploadedFileByHolder( previewElement, myDropzone );
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
        //no existing documents in comment => use alternative id (i.e. first input id)
        //console.log('upload id does not exist');
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

    console.log('id='+id);

    if( !id || id == ""  ) {
        throw new Error("id is empty, id="+id+", name="+name);
    }

    if( id.indexOf("orderformbundle") !== -1 ) {
        var res = getElementInfoById_Scan( id, name );
    } else {
        var res = getElementInfoById_User( id, name );
    }

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

    //id=oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0_name

    //  0           1           2           3    4    5      6      7    8   9  10   11     12   13     14  15
    //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_paper_0_documents_0_id
    var idArr = id.split("_");
    var bundleName = idArr[1];
    //var commentType = idArr[3];
    //var commentCount = idArr[4];
    var documentCount = idArr[14];

    var idDel = null;
    var nameDel = null;

    if( id.indexOf("_documents_") !== -1 ) {
        idDel = "_paper_";  //"_documents_";
        nameDel = "[paper]";    //"[documents]";
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
    }

    if( idDel == null || nameDel == null ) {
        throw new Error("id or name delimeter is empty, idDel="+idDel+", nameDel="+nameDel);
    }

    //up to documents string: oleg_userdirectorybundle_user_publicComments_0_
    var idArr = id.split(idDel);
    var beginIdStr = idArr[0];
    beginIdStr = beginIdStr + "_paper_0";

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
        beginNameStr = beginNameStr + "[paper][0]";
        res['beginNameStr'] = beginNameStr;
    }

    //res = fixBeginStr(res);

    return res;
}


//fieldSelector - any element's id or class in the collection with proper id
function getNextCollectionCount( holder, fieldSelector ) {
    var maxCount = 0;

    var len = holder.find(fieldSelector).length;
    console.log('len='+len);

    var counter = 0;

    holder.find(fieldSelector).each( function(){

        var res = getElementInfoById( $(this).attr('id'), $(this).attr('name') );
        var count = res['documentCount'];
        console.log('count='+count);

        if( parseInt(count) > parseInt(maxCount) ) {
            maxCount = count;
        }
        console.log('iteration maxCount='+maxCount);

        counter++;
    });

    if( counter > 0 ) {
        maxCount = parseInt(maxCount)+1;
    }

    console.log('maxCount='+maxCount);

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

