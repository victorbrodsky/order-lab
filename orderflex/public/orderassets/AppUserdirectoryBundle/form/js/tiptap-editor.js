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
 * Reusable Tiptap rich-text editor wrapper.
 *
 * Requires tiptap.min.js (window.Tiptap) to be loaded first.
 *
 * Usage:
 *   TiptapEditor.init(document.getElementById('my-editor'), {
 *       textarea: '#my-hidden-textarea',     // synced hidden field (required for forms)
 *       uploadUrl: '...',                    // oneup uploader endpoint for file inserts
 *       downloadUrl: '/site/file-download',  // used as downloadUrl + '/' + documentid
 *       viewUrl: '/site/file-view',          // optional, links images/viewable files inline
 *       userid: 123,
 *       sitename: 'wikiportal',
 *       documenttype: 'Wiki Page Attachment',
 *       authuserid: 456,
 *       placeholder: 'Start typing...',
 *       toolbar: true,                       // default true
 *       readonly: false                      // default false
 *   });
 *
 * The upload POST sends the same fields as the dropzone flow
 * (file, userid, filename, documenttype, sitename, authuserid) so
 * App\UserdirectoryBundle\Services\UploadListener creates a Document
 * and returns {documentid, documentsrc}.
 */
var TiptapEditor = (function () {

    function resolveElement(elOrSelector) {
        if (typeof elOrSelector === 'string') {
            return document.querySelector(elOrSelector);
        }
        return elOrSelector;
    }

    function makeButton(label, title, command, isActive) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-default btn-xs tiptap-toolbar-btn';
        btn.innerHTML = label;
        btn.title = title;
        btn.dataset.tiptapActive = isActive || '';
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            command();
        });
        return btn;
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function uploadFile(file, options, callback) {
        if (!options.uploadUrl) {
            alert('File upload is not configured for this editor.');
            return;
        }
        var formData = new FormData();
        formData.append('file', file);
        formData.append('filename', file.name);
        if (options.userid) formData.append('userid', options.userid);
        if (options.authuserid) formData.append('authuserid', options.authuserid);
        if (options.documenttype) formData.append('documenttype', options.documenttype);
        if (options.sitename) formData.append('sitename', options.sitename);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', options.uploadUrl, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        callback(null, response);
                    } catch (e) {
                        callback('Unexpected server response');
                    }
                } else {
                    callback('Upload failed (' + xhr.status + ')');
                }
            }
        };
        xhr.send(formData);
    }

    function isImageFile(file) {
        if (file.type && file.type.indexOf('image/') === 0) {
            return true;
        }
        return /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(file.name);
    }

    function fileInsertHtml(documentid, file, options) {
        var filename = file.name;
        if (isImageFile(file) && options.viewUrl) {
            var src = options.viewUrl.replace(/\/$/, '') + '/' + documentid;
            return '<img src="' + src + '" alt="' + escapeHtml(filename) + '" class="tiptap-image" />';
        }
        var href = options.downloadUrl
            ? options.downloadUrl.replace(/\/$/, '') + '/' + documentid
            : '#';
        return '<a href="' + href + '" class="tiptap-file-link">' + escapeHtml(filename) + '</a>';
    }

    function insertFileAtPos(editor, file, pos, options) {
        editor.chain().focus().insertContentAt(pos,
            '<span class="tiptap-uploading">Uploading ' + escapeHtml(file.name) + '...</span>'
        ).run();

        uploadFile(file, options, function (err, response) {
            if (err) {
                alert(err);
                return;
            }
            //insert the file/image right after the placeholder text
            editor.chain().focus().insertContentAt(
                pos,
                fileInsertHtml(response.documentid, file, options)
            ).run();
        });
    }

    function insertFileAtCursor(editor, file, options) {
        uploadFile(file, options, function (err, response) {
            if (err) {
                alert(err);
                return;
            }
            editor.chain().focus().insertContent(
                fileInsertHtml(response.documentid, file, options)
            ).run();
        });
    }

    function pickAndInsert(editor, kind, options) {
        var input = document.createElement('input');
        input.type = 'file';
        if (kind === 'image') {
            input.accept = 'image/*';
        }
        input.addEventListener('change', function () {
            if (input.files && input.files.length) {
                insertFileAtCursor(editor, input.files[0], options);
            }
        });
        input.click();
    }

    function tableButtons(editor) {
        if (typeof editor.commands.insertTable !== 'function') {
            return [];
        }
        return [
            makeButton('<span class="glyphicon glyphicon-th"></span>', 'Insert table (3x3)', function () {
                editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
            }, 'table'),
            makeButton('<span class="glyphicon glyphicon-plus"></span>C', 'Add column after', function () {
                editor.chain().focus().addColumnAfter().run();
            }),
            makeButton('<span class="glyphicon glyphicon-plus"></span>R', 'Add row after', function () {
                editor.chain().focus().addRowAfter().run();
            }),
            makeButton('<span class="glyphicon glyphicon-minus"></span>C', 'Delete column', function () {
                editor.chain().focus().deleteColumn().run();
            }),
            makeButton('<span class="glyphicon glyphicon-minus"></span>R', 'Delete row', function () {
                editor.chain().focus().deleteRow().run();
            }),
            makeButton('<span class="glyphicon glyphicon-trash"></span>', 'Delete table', function () {
                editor.chain().focus().deleteTable().run();
            })
        ];
    }

    function buildToolbar(editor, container, options) {
        var toolbar = document.createElement('div');
        toolbar.className = 'tiptap-toolbar';

        var groups = [
            [makeButton('<b>B</b>', 'Bold', function () { editor.chain().focus().toggleBold().run(); }, 'bold'),
             makeButton('<i>I</i>', 'Italic', function () { editor.chain().focus().toggleItalic().run(); }, 'italic'),
             makeButton('<s>S</s>', 'Strikethrough', function () { editor.chain().focus().toggleStrike().run(); }, 'strike')],
            [makeButton('H1', 'Heading 1', function () { editor.chain().focus().toggleHeading({ level: 1 }).run(); }, 'heading'),
             makeButton('H2', 'Heading 2', function () { editor.chain().focus().toggleHeading({ level: 2 }).run(); }, 'heading'),
             makeButton('H3', 'Heading 3', function () { editor.chain().focus().toggleHeading({ level: 3 }).run(); }, 'heading')],
            [makeButton('<span class="glyphicon glyphicon-list"></span>', 'Bullet list', function () { editor.chain().focus().toggleBulletList().run(); }, 'bulletList'),
             makeButton('<span class="glyphicon glyphicon-list-alt"></span>', 'Ordered list', function () { editor.chain().focus().toggleOrderedList().run(); }, 'orderedList'),
             makeButton('<span class="glyphicon glyphicon-comment"></span>', 'Blockquote', function () { editor.chain().focus().toggleBlockquote().run(); }, 'blockquote')],
            [makeButton('<span class="glyphicon glyphicon-link"></span>', 'Insert/edit link', function () {
                var previousUrl = editor.getAttributes('link').href;
                var url = window.prompt('URL', previousUrl || 'https://');
                if (url === null) return;
                if (url === '' || url === 'https://') {
                    editor.chain().focus().extendMarkRange('link').unsetLink().run();
                    return;
                }
                editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
            }),
             makeButton('<span class="glyphicon glyphicon-paperclip"></span>', 'Insert file at cursor', function () {
                pickAndInsert(editor, 'file', options);
            }),
             makeButton('<span class="glyphicon glyphicon-picture"></span>', 'Insert image at cursor', function () {
                pickAndInsert(editor, 'image', options);
            })],
            tableButtons(editor),
            [makeButton('<span class="glyphicon glyphicon-arrow-left"></span>', 'Undo', function () { editor.chain().focus().undo().run(); }),
             makeButton('<span class="glyphicon glyphicon-arrow-right"></span>', 'Redo', function () { editor.chain().focus().redo().run(); })]
        ];

        groups.forEach(function (group) {
            if (!group.length) return;
            var groupDiv = document.createElement('span');
            groupDiv.className = 'btn-group tiptap-toolbar-group';
            group.forEach(function (btn) { groupDiv.appendChild(btn); });
            toolbar.appendChild(groupDiv);
        });

        //update active state marks
        editor.on('selectionUpdate', function () { refreshToolbar(editor, toolbar); });
        editor.on('update', function () { refreshToolbar(editor, toolbar); });

        return toolbar;
    }

    function refreshToolbar(editor, toolbar) {
        var buttons = toolbar.querySelectorAll('.tiptap-toolbar-btn');
        buttons.forEach(function (btn) {
            var mark = btn.dataset.tiptapActive;
            if (!mark) return;
            if (mark === 'heading') {
                var lvl = btn.innerHTML;
                var active = (lvl === 'H1' && editor.isActive('heading', { level: 1 })) ||
                             (lvl === 'H2' && editor.isActive('heading', { level: 2 })) ||
                             (lvl === 'H3' && editor.isActive('heading', { level: 3 }));
                btn.classList.toggle('active', active);
            } else {
                btn.classList.toggle('active', editor.isActive(mark));
            }
        });
    }

    function init(elOrSelector, options) {
        var container = resolveElement(elOrSelector);
        if (!container) {
            console.error('TiptapEditor: container not found', elOrSelector);
            return null;
        }
        if (typeof window.Tiptap === 'undefined') {
            console.error('TiptapEditor: window.Tiptap is not loaded. Include tiptap.min.js first.');
            return null;
        }

        options = options || {};
        var textarea = options.textarea ? resolveElement(options.textarea) : null;

        var toolbar = null;
        var contentDiv = document.createElement('div');
        contentDiv.className = 'tiptap-content';

        var fileHandler = window.Tiptap.FileHandler.configure({
            onDrop: function (currentEditor, files, pos) {
                files.forEach(function (file) {
                    insertFileAtPos(currentEditor, file, pos, options);
                });
            },
            onPaste: function (currentEditor, files) {
                files.forEach(function (file) {
                    insertFileAtCursor(currentEditor, file, options);
                });
            }
        });

        var extensions = [
            window.Tiptap.StarterKit.configure({
                link: { openOnClick: false, autolink: true }
            }),
            window.Tiptap.Image.configure({
                inline: false,
                allowBase64: false
            })
        ];

        if (window.Tiptap.Table) {
            extensions.push(
                window.Tiptap.Table.configure({ resizable: true }),
                window.Tiptap.TableRow,
                window.Tiptap.TableHeader,
                window.Tiptap.TableCell
            );
        } else {
            console.warn('TiptapEditor: table extension not in bundle (stale tiptap.min.js?)');
        }

        extensions.push(fileHandler);

        var editor = new window.Tiptap.Editor({
            element: contentDiv,
            editable: !options.readonly,
            extensions: extensions,
            content: textarea ? textarea.value : (options.content || ''),
            onUpdate: function () {
                if (textarea) {
                    textarea.value = editor.getHTML();
                }
                if (typeof options.onUpdate === 'function') {
                    options.onUpdate(editor);
                }
            }
        });

        if (options.toolbar !== false && !options.readonly) {
            toolbar = buildToolbar(editor, container, options);
            container.appendChild(toolbar);
        }
        container.appendChild(contentDiv);

        //ensure the textarea is synced on form submit even if 'update' did not fire
        if (textarea) {
            var form = textarea.closest ? textarea.closest('form') : null;
            if (form) {
                form.addEventListener('submit', function () {
                    textarea.value = editor.getHTML();
                });
            }
        }

        container.classList.add('tiptap-editor');
        container.tiptapEditor = editor;
        return editor;
    }

    return { init: init };
})();
