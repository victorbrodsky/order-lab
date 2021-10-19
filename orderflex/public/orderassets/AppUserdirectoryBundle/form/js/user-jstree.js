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
 * Basic jstree operations: create, move and edit node in separate page.
 * Created by DevServer on 6/23/15.
 */


//used by admin page
function getJstree(bundleName,entityName,menu,search,closeall,type) {

    console.log('getJstree: cycle='+cycle);

    if( typeof cycle === 'undefined' ) {
        var cycle = 'edit';
    }

    if( cycle.indexOf("show") == -1 ) {

        var targetid = ".composite-tree.composite-tree"+"-"+bundleName+"-"+entityName;
        console.log('targetid='+targetid);
        if( $(targetid).length == 0 ) {
            console.log('no target='+targetid);
            return;
        }

        var nodeShowPath = $(targetid).attr("data-compositetree-node-showpath"); //i.e. 'institutions_show'
        //console.log('nodeShowPath='+nodeShowPath);
        if( !nodeShowPath ) {
            throw new Error('Node show path is undefined, nodeShowPath='+nodeShowPath);
            //console.log('Node show path is undefined, nodeShowPath='+nodeShowPath);
        }

        //attach to url current filter url parameters
        var filterStr = window.location.search; //filter[types][]=default&filter[types][]=user-added
        if( filterStr ) {
            filterStr = filterStr.replace("?", '');
            filterStr = "&"+filterStr;
        }
        //console.log('filterStr='+filterStr);


        var withsearch = "search";
        if( typeof search != 'undefined' && search == "nosearch" ) {
            withsearch = null;
        }

        var withmenu = "contextmenu";
        if( typeof menu != 'undefined' && menu == "nomenu" ) {
            withmenu = null;
        }
        //console.log('withmenu='+withmenu);

        var withcloseall = false;
        if( typeof closeall != 'undefined' || closeall == "closeall" ) {
            withcloseall = true;
        }

        var withstate = "state";
        var withdnd = "dnd";
        //var expand_selected_onload = true;
        if( withcloseall ) {
            withstate = null;
            withdnd = null;
            //expand_selected_onload = false;
        }

        var withlazy = "lazy&";

        var withtype = '&type='+type;
        if( typeof type === 'undefined' ) {
            withtype = '';
        }

        var treeUrl = Routing.generate('employees_get_composition_tree');
        treeUrl = treeUrl + '?'+withlazy+'opt=none&classname='+entityName+'&bundlename='+bundleName+withtype+filterStr; //$opt=
        console.log('user-jstree.js: treeUrl='+treeUrl);

        if( typeof cycle === 'undefined' ) {
            cycle = 'new';
        }
        treeUrl = treeUrl + "&cycle="+cycle;
        //console.log('user-jstree.js: treeUrl='+treeUrl);

        //js tree
        $(targetid).jstree({
            'core' : {
                //"expand_selected_onload" : expand_selected_onload,
                "themes" : { "stripes" : true },
                'data' : {
                    "url" : treeUrl,     //"//www.jstree.com/fiddle/",
                    "dataType" : "json",        // needed only if you do not supply JSON headers
                    "data" : function (node) {
                        return { "id" : node.id };
                    }
                },
                "check_callback" : function (operation, node, parent, position, more) {

                    //console.log("operation="+operation);
                    //console.log('position='+position);
                    //console.log('parent.id='+parent.id);
                    //console.log(more);

                    //create_node, rename_node, delete_node, move_node, copy_node
                    if( operation === "copy_node" || operation === "move_node" ) {
                        if( parent.id === "#" ) {
                            console.log(operation+' is not supported, parent.id='+parent.id);
                            return false; // prevent moving a child above or below the root
                        }
                    }

                    if( operation === "move_node" ) {
                        //return true;
                        if( more && more.core ) {
                            if( !window.confirm("Are you sure?") ) {
                                return false;
                            }
                            return jstree_wrapper_action_node(bundleName, entityName, operation, node, parent, position, more);
                        } else {
                            return true;
                        }
                    }

                    if( operation === "create_node" ) {
                        if( !window.confirm("Are you sure?") ) {
                            return false;
                        }
                        return true;
                    }

                    if( operation === "rename_node" ) {
                        return true;
                    }

//                    if( operation === "delete_node" ) {
//                        return jstree_wrapper_action_node(entityName, operation, node, parent, position, more);
//                    }

                    console.log(operation+' is not supported');
                    return false; // do not allow everything else
                }
            },
            "contextmenu": {

                items: function(node) {

                    var tmp = $.jstree.defaults.contextmenu.items();
                    delete tmp.ccp;
                    delete tmp.rename;
                    delete tmp.remove;

                    //add Edit link to open a modal edit windows
                    if( nodeShowPath && nodeShowPath != 'undefined' ) {
                        tmp.editbyurl = {
                            "label": "View",
                            "action": function (obj) {
                                //this.edit_node(obj);
                                //console.log(obj);
                                var treeUrl = Routing.generate(nodeShowPath, {id: node.id});
                                window.open(treeUrl);
                                //open modal edit
                                //var parent = {id:node.parent};
                                //actionNodeModal(entityName,'edit_node',obj,node,parent);
                            }
                        };
                    }

                    return tmp;
                }
            },
            "types" : {
                "icon0" : {
                    "icon" : "glyphicon glyphicon-home"
                },
                "icon1" : {
                    "icon" : "glyphicon glyphicon-leaf"
                },
                "icon2" : {
                    "icon" : "glyphicon glyphicon-tag"
                },
                "icon3" : {
                    "icon" : "glyphicon glyphicon-ok"
                },
                "icon4" : {
                    "icon" : "glyphicon glyphicon-zoom-in"
                },
                "iconUser" : {
                    "icon" : "glyphicon glyphicon-user text-primary"
                }
            },
            "plugins" : [
                withmenu,   //"contextmenu",
                withdnd,    //"dnd",
                withsearch, //"search",
                withstate,  //"state",
                "types",
                "wholerow"
            ]

        });

//        $(targetid).on("changed.jstree", function (e, data) {
//            //console.log("The selected nodes are:");
//            //console.log(data.selected);
//            var selectedNode = data.selected[0];
//            console.log("selectedNode="+selectedNode);
//            //var targetInstitution = $(this).closest('.user-collection-holder').find('.ajax-combobox-institution');
//            //setElementToId( targetInstitution, _institution, selectedNode );
//        });

        $(".jstree-search").keyup(function(event){
            if( event.keyCode == 13 ) {
                event.preventDefault();
                var jstreeContainer = $(this).closest('.jstree-parent-container').find('.composite-tree');
                //console.log(jstreeContainer);
                //printF(jstreeContainer,"jstreeContainer:");
                var searchVal = $(this).val();
                //console.log('searchVal='+searchVal);
                jstreeContainer.jstree(true).search(searchVal);

            }
        });

//        $(targetid).bind('move_node.jstree', function(e, data) {
//            var res = jstree_move_node(entityName,data);
//            if( res == false ) {
//                failedOperation($(this),'move_node');
//            }
//        });

        $(targetid).bind('create_node.jstree', function(e, data) {
            //console.log('create_node');
            var operation = 'create_node';
            var newnodeid = jstree_action_node(bundleName, entityName, operation, null, data.node.text, data.parent, data.position, null, null, null, null);
            if( newnodeid != false ) {
                $(this).jstree(true).set_id(data.node,newnodeid);
            } else {
                failedOperation($(this),operation);
            }
        });

        $(targetid).bind('rename_node.jstree', function(e, data) {
            var operation = 'rename_node';
            var res = jstree_action_node(bundleName, entityName, operation, data.node.id, data.node.text, data.parent, data.position, null, null, null, null);
            if( res == false ) {
                failedOperation($(this),operation);
            }
        });




        if( withmenu == null ) {

            $(targetid).bind('changed.jstree', function(e, data) {
                //console.log('data.selected.length='+data.selected.length);
                if( data.selected.length == 1 ) {
                    var addnodeid = data.node.original.addnodeid;
                    //console.log('addnodeid='+addnodeid);
                    if( addnodeid ) {
                        var treeUrl = Routing.generate(nodeShowPath, {id: addnodeid});
                        window.open(treeUrl);
                    }
                }
            });

        }//if menu


    } //if

}

//node - node being copied
//parent - new parent
//position - the position to insert at (besides integer values, "first" and "last" are supported, as well as "before" and "after"), defaults to integer `0`
function jstree_wrapper_action_node(bundleName, entityName, operation, node, parent, position, more) {
    //console.log(entityName+':' + operation + ' id='+node.id+", text="+node.text);
    //console.log('position='+position);
    //console.log('parent.id='+parent.id);
    //console.log(more);
    return jstree_action_node(bundleName, entityName, operation, node.id, node.text, parent.id, position, null, null, more, null);
}

function jstree_move_node(bundleName, entityName,data) {

//    console.log('move_node');
//    console.log(data);
//    console.log("node.id="+data.node.id);
//    console.log("parent="+data.parent);
//    console.log("old_parent="+data.old_parent);
//    console.log("position="+data.position);
//    console.log("old_position="+data.old_position);

    var operation = 'move_node';
    return jstree_action_node(bundleName, entityName, operation, data.node.id, null, data.parent, data.position, data.old_parent, data.old_position, null, null);
}

function jstree_action_node(bundleName, entityName, operation, nodeid, nodetext, parentid, position, oldparentid, oldposition, more, opt) {
    var url = Routing.generate('employees_tree_edit_node');
    var res = false;

    $.ajax({
        type: "POST",
        url: url,
        timeout: _ajaxTimeout,
        async: false,
        data: { action: operation, pid: parentid, position: position, oldpid: oldparentid, oldposition: oldposition, nodeid: nodeid, nodetext: nodetext, bundlename: bundleName, classname: entityName, opt: opt }
    }).success(function(data) {
        //console.log('data='+data);
        if( isInt(data) ) {
            if( operation == "create_node" ) {
                res = data;
            }
        } else {
            if( data.indexOf("Failed") == -1 ) {
                res = true;
            }
        }
    }).fail(function(data) {
        console.log(operation+' failed. Data:');
        console.log(data);
    });

    return res;
}

function failedOperation(jstreeObj,operation) {
    alert('Operation ' + operation + 'failed. Tree will be refreshed.');
    jstreeObj.jstree("refresh");
}

//home page institution with user leafs
function displayInstitutionUserTree(type) {
    //$('#displayInstitutionUserTree').show();
    getJstree('UserdirectoryBundle','Institution_User','nomenu','nosearch','closeall',type);
}








//modal node edit: not used
//function actionNodeModal( entityName, operation, obj, node, parent ) {
//
//    console.log(obj);
//    console.log(node);
//
//    if( !$('#editNodeModal').length ) {
//
//        var dataNodeLabel = 'Edit ' + node.text + ' with ID:' + node.id + ' level:' + node.original.leveltitle;
//
//        var modalHtml =
//            '<div id="editNodeModal" class="modal fade data-node-modal">' +
//                '<div class="modal-dialog">' +
//                '<div class="modal-content">' +
//                '<div class="modal-header text-center">' +
//                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
//                '<h3 id="dataNodeLabel">' + dataNodeLabel + '</h3>' +
//                '</div>' +
//                '<div class="modal-body text-center">' +
//                '</div>' +
//                '<div class="modal-footer">' +
//                '<button class="btn btn-primary data-node-cancel" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
//                '<a class="btn btn-primary data-node-save" id="dataNodeSave">Save</a>' +
//                //'<button class="btn btn-primary data-node-save" onclick="submitNode("'+entityName+'","'+operation+'",'+node+','+parent+')">OK</button>' +
//                //'<button class="btn btn-primary data-node-save" onclick="submitNode(entityName,operation,node,parent)">OK</button>' +
//                '</div>' +
//                '</div>' +
//                '</div>' +
//                '</div>';
//
//        $('body').append(modalHtml);
//
//        var nameHtml = '<br><br>Name:' +
//            '<p><input id="'+node.id+'-name" class="form-control" type="text" value="'+node.text+'" required></input></p>';
//        $('#editNodeModal').find('.modal-body').append(nameHtml);
//
//    }
//
//
//    //$('#editNodeModal').find('.modal-body').text( $(this).attr('data-node') );
//    //$('#dataNodeSave').attr('href', href); //testing
//
//    $('#editNodeModal').modal({show:true});
//
//    $( "#dataNodeSave" ).click(function() {
//        submitNode(entityName,operation,node,parent);
//    });
//
//    return false;
//}
//
//function submitNode(entityName,operation,node,parent) {
//
//    //var nameValue = $('#dataConfirmModal').find('.modal-body').find('input').val();
//    var nameValue = node.text;
//    console.log("nameValue="+nameValue);
//
//    if( operation == 'rename_node' ) {
//        var position = nameValue;
//        var more = null;
//    }
//
//    jstree_wrapper_action_node(entityName, operation, node, parent, position, more)
//}
