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

(function (factory) {
  if (typeof define === "function" && define.amd) {
    define(["jquery"], factory);
  } else {
    factory(jQuery);
  }
})(function ($) {

  "use strict";

  var console = window.console || {
        log: $.noop
      };

  function CropAvatar($element) {
      this.$container = $element;

      this.$avatarView = this.$container.find(".avatar-view");
      //this.$avatarView = this.$container.find("#user-current-avatar-view");

      this.$avatar = this.$avatarView.find("img");
      this.$avatarModal = this.$container.find("#avatar-modal");
      this.$loading = this.$container.find(".loading");

      this.$avatarForm = this.$avatarModal.find(".avatar-form");
      this.$avatarUpload = this.$avatarForm.find(".avatar-upload");
      this.$avatarSrc = this.$avatarForm.find(".avatar-src");
      this.$avatarData = this.$avatarForm.find(".avatar-data");
      this.$avatarInput = this.$avatarForm.find(".avatar-input");
      this.$avatarSave = this.$avatarForm.find(".avatar-save");

      this.$avatarWrapper = this.$avatarModal.find(".avatar-wrapper");
      this.$avatarPreview = this.$avatarModal.find(".avatar-preview");

      this.init();
      //console.log(this);
  }

  CropAvatar.prototype = {
    constructor: CropAvatar,

    support: {
      fileList: !!$("<input type=\"file\">").prop("files"),
      fileReader: !!window.FileReader,
      formData: !!window.FormData
    },

    init: function () {
      this.support.datauri = this.support.fileList && this.support.fileReader;

      if (!this.support.formData) {
        this.initIframe();
      }

      this.initTooltip();
      this.initModal();
      this.addListener();
    },

    addListener: function () {
      this.$avatarView.on("click", $.proxy(this.click, this));
      this.$avatarInput.on("change", $.proxy(this.change, this));
      //this.$avatarForm.on("submit", $.proxy(this.submit, this));
      this.$avatarSave.on("click", $.proxy(this.submit, this));
    },

//    submitModalForm: function() {
//        alert('submitModalForm');
//        this.$avatarModal.find(".avatar-form").submit;
//    },

    initTooltip: function () {
      this.$avatarView.tooltip({
        placement: "bottom"
      });
    },

    initModal: function () {
      this.$avatarModal.modal("hide");
      this.initPreview();
    },

    initPreview: function () {
      var url = this.$avatar.attr("src");
      this.$avatarPreview.empty().html('<img src="' + url + '">');

        //optional: show original upload image
//        var urlUpload = url.replace("avatar", "upload");
//        this.$img.cropper("replace", urlUpload);
//        this.startCropper();
//        var urlUpload = url.replace("avatar", "upload");
//        this.$avatarSrc.val(urlUpload);
//        this.startCropper();
//      var _this = this;
//      var urlUpload = url.replace("avatar", "upload");
//      this.$img = $('<img src="' + urlUpload + '">');
//      this.$avatarWrapper.empty().html(this.$img);
//      this.$img.cropper({
//            aspectRatio: 1,
//            preview: this.$avatarPreview.selector,
//            done: function (data) {
//                var json = [
//                    '{"x":' + data.x,
//                    '"y":' + data.y,
//                    '"height":' + data.height,
//                    '"width":' + data.width + "}"
//                ].join();
//
//                _this.$avatarData.val(json);
//            }
//      });

    },

    initIframe: function () {
      var iframeName = "avatar-iframe-" + Math.random().toString().replace(".", ""),
          $iframe = $('<iframe name="' + iframeName + '" style="display:none;"></iframe>'),
          firstLoad = true,
          _this = this;

      this.$iframe = $iframe;
      this.$avatarForm.attr("target", iframeName).after($iframe);

      this.$iframe.on("load", function () {
        var data,
            win,
            doc;

        try {
          win = this.contentWindow;
          doc = this.contentDocument;

          doc = doc ? doc : win.document;
          data = doc ? doc.body.innerText : null;
        } catch (e) {}

        if (data) {
          _this.submitDone(data);
        } else {
          if (firstLoad) {
            firstLoad = false;
          } else {
            _this.submitFail("Image upload failed!");
          }
        }

        _this.submitEnd();
      });
    },

    click: function () {
      //console.log('click function');
      this.$avatarModal.modal("show");
    },

    change: function () {
      //alert('change function');
      var files,
          file;

      if (this.support.datauri) {
        files = this.$avatarInput.prop("files");

        if (files.length > 0) {
          file = files[0];

          if (this.isImageFile(file)) {
            this.read(file);
          }
        }
      } else {
        file = this.$avatarInput.val();

        if (this.isImageFile(file)) {
          this.syncUpload();
        }
      }
    },

    submit: function () {

      //alert('submit function');

      if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
          //alert('src is null => return false');
        return false;
      }

      if (this.support.formData) {
        this.ajaxUpload();
        return false;
      }
    },

    isImageFile: function (file) {
      if (file.type) {
        return /^image\/\w+$/.test(file.type);
      } else {
        return /\.(jpg|jpeg|png|gif)$/.test(file);
      }
    },

    read: function (file) {
      var _this = this,
          fileReader = new FileReader();

      fileReader.readAsDataURL(file);

      fileReader.onload = function () {
        _this.url = this.result
        _this.startCropper();
      };
    },

    startCropper: function () {
      var _this = this;
        //alert('start cropper, this.url='+this.url);
        //console.log("this.url="+this.url);

      if (this.active) {
        this.$img.cropper("replace", this.url);
      } else {
        this.$img = $('<img src="' + this.url + '">');
        this.$avatarWrapper.empty().html(this.$img);
        this.$img.cropper({
            //resizable: false,
            //zoomable: false,
            aspectRatio: 0.9206,
//            data: {   //126,116 => 251,231
//                x: 242,
//                y: 182,
//                width: 116,
//                height: 126
//            },
            preview: this.$avatarPreview.selector,
            done: function (data) {
                var json = [
                      '{"x":' + data.x,
                      '"y":' + data.y,
                      '"height":' + data.height,
                      '"width":' + data.width + "}"
                    ].join();

                _this.$avatarData.val(json);
            }
        });

        this.active = true;
      }
    },

    stopCropper: function () {
      if (this.active) {
        this.$img.cropper("destroy");
        this.$img.remove();
        this.active = false;
      }
    },

    ajaxUpload: function () {
      var url = this.$avatarForm.attr("action"),
          data = new FormData(this.$avatarForm[0]),
          _this = this;

      $.ajax(url, {
        type: "post",
        data: data,
        processData: false,
        contentType: false,

        beforeSend: function () {
          _this.submitStart();
        },

        success: function (data) {
          _this.submitDone(data);
        },

        error: function (XMLHttpRequest, textStatus, errorThrown) {
          _this.submitFail(textStatus || errorThrown);
        },

        complete: function () {
          _this.submitEnd();
        }
      });
    },

    syncUpload: function () {
      this.$avatarSave.click();
    },

    submitStart: function () {
      this.$loading.fadeIn();
    },

    submitDone: function (data) {

        try {
            data = $.parseJSON(data);
        } catch (e) {};

        //console.log(data);

        if (data && data.state === 200) {
            if (data.result) {
                this.url = data.result;
                //console.log("this.url="+this.url);

                if( this.support.datauri || this.uploaded ) {
                    this.uploaded = false;
                    this.cropDone();
                } else {
                    this.uploaded = true;
                    this.$avatarSrc.val(this.url);
                    //printF(this.$avatarSrc,"this.$avatarSrc");
                    this.startCropper();
                }

                this.$avatarInput.val("");

                //set avatar id="user-avatar-id"
                if( data.avatarid ) {
                    var avatarIdField = $('#user-avatar-id').find('input');
                    avatarIdField.val(data.avatarid);
                }

                //set avatar path="user-avatar-id"
                if( data.avatarpath ) {
                    //console.log("data.avatarpath="+data.avatarpath);
                    this.$avatar.attr("src", data.avatarpath);
                    this.initPreview();
                }

            } else if (data.message) {
                this.alert(data.message);
            }
        } else {
            this.alert("Failed to response");
        }

    },

    submitFail: function (msg) {
      this.alert(msg);
    },

    submitEnd: function () {
      this.$loading.fadeOut();
    },

    cropDone: function () {
      this.$avatarSrc.val("");
      this.$avatarData.val("");
      this.$avatar.attr("src", this.url);
      this.stopCropper();
      this.$avatarModal.modal("hide");
    },

    alert: function (msg) {
      var $alert = [
            '<div class="alert alert-danger avater-alert">',
              '<button type="button" class="close" data-dismiss="alert">&times;</button>',
              msg,
            '</div>'
          ].join("");

      this.$avatarUpload.after($alert);
    }
  };

  $(function () {
    var example = new CropAvatar($("#crop-avatar"));
  });
});
