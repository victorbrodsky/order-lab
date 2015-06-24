/**
 * Created by DevServer on 6/23/15.
 */


//used by admin page
function getJstree(entityName) {

    //console.log('cycle='+cycle);

    if( typeof cycle === 'undefined' ) {
        var cycle = 'edit';
    }

    if( cycle.indexOf("show") == -1 ) {

        var targetid = ".composite-tree";
        if( $(targetid).length == 0 ) {
            return;
        }

        //employees_get_institution
        var institutionUrl = Routing.generate('employees_get_institution_tree');
        institutionUrl = institutionUrl + '?lazy';
        //console.log('institutionUrl='+institutionUrl);

        //_institution
        $(targetid).jstree({
            'core' : {
                "themes" : { "stripes" : true },
                'data' : {
                    "url" : institutionUrl,     //"//www.jstree.com/fiddle/",
                    "dataType" : "json",        // needed only if you do not supply JSON headers
                    "data" : function (node) {
                        return { "id" : node.id };
                    }
                },
                "check_callback" : function (operation, node, parent, position, more) {
                    //create_node, rename_node, delete_node, move_node, copy_node
                    if( operation === "copy_node" || operation === "move_node" ) {
                        if( parent.id === "#" ) {
                            return false; // prevent moving a child above or below the root
                        }
                    }

                    if( operation === "move_node" ) {
                        //jstree_move_node(entityName, operation, node, parent, position, more);
                        return true;
                    }

                    if( operation === "create_node" ) {
                        return true;
                        //return jstree_wrapper_action_node(entityName, operation, node, parent, position, more);
                    }

                    if( operation === "rename_node" ) {
                        return jstree_wrapper_action_node(entityName, operation, node, parent, position, more);
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
                    //delete tmp.rename;
                    delete tmp.remove;

                    //add Edit link to open a modal edit windows
                    tmp.create = {
                        "label": "Edit",
                        "action": function (obj) {
                            //this.edit_node(obj);
                            console.log(obj);
                            var treeUrl = Routing.generate('institutions_show', {id: node.id});
                            window.open(treeUrl);
                            //open modal edit
                            //var parent = {id:node.parent};
                            //actionNodeModal(entityName,'edit_node',obj,node,parent);
                        }
                    };

                    return tmp;
                }
            },
            "plugins" : [
                "contextmenu",
                "dnd",
                "search",
                "state",
                "types",
                "wholerow"
            ]

        });

        $(targetid).on("changed.jstree", function (e, data) {
            //console.log("The selected nodes are:");
            //console.log(data.selected);
            var selectedNode = data.selected[0];
            console.log("selectedNode="+selectedNode);
            //var targetInstitution = $(this).closest('.user-collection-holder').find('.ajax-combobox-institution');
            //setElementToId( targetInstitution, _institution, selectedNode );
        });

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

//        $(document).on('dnd_start.vakata', function (e, data) {
//            console.log('Started');
//            console.log(data);
//        });
//
//        $(document).on('dnd_stop.vakata', function (e, data) {
//            console.log('Stoped');
//            console.log(data);
//        });

        $(targetid).bind('move_node.jstree', function(e, data) {
            jstree_move_node(entityName,data);
        });

    } //if

}

//node - node being copied
//parent - new parent
//position - the position to insert at (besides integer values, "first" and "last" are supported, as well as "before" and "after"), defaults to integer `0`
function jstree_wrapper_action_node(entityName, operation, node, parent, position, more) {
    console.log(entityName+':' + operation + ' id='+node.id+", text="+node.text);
    console.log('position='+position);
    console.log('parent.id='+parent.id);
    console.log(more);

    return jstree_action_node(entityName, operation, node.id, parent.id, null, position, more);
}

function jstree_move_node(entityName,data) {

    console.log('move_node');
    console.log(data);
    console.log("node.id="+data.node.id);
    console.log("parent="+data.parent);
    console.log("old_parent="+data.old_parent);
    console.log("position="+data.position);

    var operation = 'move_node';
    var more = null;

    return jstree_action_node(entityName, operation, data.node.id, data.parent, data.old_parent, data.position, more);


function jstree_action_node(entityName, operation, nodeid, parentid, oldparentid, position, more) {
    var url = Routing.generate('employees_tree_edit_node');
    var res = false;

    $.ajax({
        type: "POST",
        url: url,
        timeout: _ajaxTimeout,
        async: false,
        data: { action: operation, pid: parentid, oldpid: oldparentid, id: nodeid, position: position, entity: entityName }
    }).success(function(data) {
        if( data == 'ok' ) {
            res = true;
        }
    }).fail(function(data) {
        console.log('failed: '+data);
    }) ;

    return res;
}

}






//modal node edit: not used
function actionNodeModal( entityName, operation, obj, node, parent ) {

    console.log(obj);
    console.log(node);

    if( !$('#editNodeModal').length ) {

        var dataNodeLabel = 'Edit ' + node.text + ' with ID:' + node.id + ' level:' + node.original.leveltype;

        var modalHtml =
            '<div id="editNodeModal" class="modal fade data-node-modal">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header text-center">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '<h3 id="dataNodeLabel">' + dataNodeLabel + '</h3>' +
                '</div>' +
                '<div class="modal-body text-center">' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button class="btn btn-primary data-node-cancel" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                '<a class="btn btn-primary data-node-save" id="dataNodeSave">Save</a>' +
                //'<button class="btn btn-primary data-node-save" onclick="submitNode("'+entityName+'","'+operation+'",'+node+','+parent+')">OK</button>' +
                //'<button class="btn btn-primary data-node-save" onclick="submitNode(entityName,operation,node,parent)">OK</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

        $('body').append(modalHtml);

        var nameHtml = '<br><br>Name:' +
            '<p><input id="'+node.id+'-name" class="form-control" type="text" value="'+node.text+'" required></input></p>';
        $('#editNodeModal').find('.modal-body').append(nameHtml);

    }


    //$('#editNodeModal').find('.modal-body').text( $(this).attr('data-node') );
    //$('#dataNodeSave').attr('href', href); //testing

    $('#editNodeModal').modal({show:true});

    $( "#dataNodeSave" ).click(function() {
        submitNode(entityName,operation,node,parent);
    });

    return false;
}

function submitNode(entityName,operation,node,parent) {

    //var nameValue = $('#dataConfirmModal').find('.modal-body').find('input').val();
    var nameValue = node.text;
    console.log("nameValue="+nameValue);

    if( operation == 'rename_node' ) {
        var position = nameValue;
        var more = null;
    }

    jstree_wrapper_action_node(entityName, operation, node, parent, position, more)
}
