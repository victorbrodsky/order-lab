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

                    if( operation === "copy_node" ) {
                        console.log(operation+' is not supported');
                        return false;
                    }

                    if( operation === "move_node" ) {
                        return jstree_move_node(entityName, node, parent, position, more);
                    }

                    if( operation === "create_node" ) {
                        return jstree_create_node(entityName, node, parent, position, more);
                    }

                    if( operation === "rename_node" ) {
                        return jstree_action_node(entityName, operation, node, parent, position, more);
                    }

                    if( operation === "delete_node" ) {
                        return jstree_action_node(entityName, operation, node, parent, position, more);
                    }

                    return false; // do not allow everything else
                }
            },
            "plugins" : [
                "contextmenu",
                "dnd",
                "search",
                "state",
                "types",
                "wholerow"
            ],
            "contextmenu": {
                items: function(node) {
                    var tmp = $.jstree.defaults.contextmenu.items();
                    delete tmp.ccp;
                    delete tmp.delete;

                    //add Edit link to open a modal edit windows
                    tmp.create = {
                        "label": "Edit",
                        "action": function (obj) {
                            //this.edit_node(obj);
                            console.log(obj);
                            //open modal edit
                            $('').modal('show');
                        }
                    };

                    return tmp;
                }
            }
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
    }

}

//node - node being copied
//parent - new parent
//position - the position to insert at (besides integer values, "first" and "last" are supported, as well as "before" and "after"), defaults to integer `0`
function jstree_action_node(entityName, operation, node, parent, position, more) {
    console.log(entityName+': rename node id='+node.id+", text="+node.text);
    console.log('position='+position);
    console.log('parent.id='+parent.id);
    console.log(more);

    var url = Routing.generate('employees_tree_edit_node');

    var res = false;

    $.ajax({
        type: "POST",
        url: url,
        timeout: _ajaxTimeout,
        async: false,
        data: { action: operation, pid: parent.id, id: node.id, position: position, more: more, entity: entityName }
    }).success(function(data) {
        if( data == 'ok' ) {
            res = true;
        }
    }).fail(function(data) {
        console.log('failed: '+data);
    }) ;

    return res;
}

function jstree_move_node(entityName, node, parent, position, more) {
    console.log(entityName+': move node id='+node.id+", text="+node.text);
    console.log('position='+position);
    console.log('parent.id='+parent.id);
    console.log(more);
    return true;
}

function jstree_create_node(entityName, node, parent, position, more) {
    console.log(entityName+': create node id='+node.id+", text="+node.text);
    console.log('position='+position);
    console.log('parent.id='+parent.id);
    console.log(more);
    return true;
}
