window.addEventListener('load', (event) => {
    /* ************************************************************************************************************* *
     * EVENT LISTENERS
     * ************************************************************************************************************* */

    // CLICK ADD BUTTON
    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.classList.contains('collection-add-button')) return;

        const addNodeLink = event.target;
        const collection = document.getElementById(addNodeLink.dataset.collection);
        const prototypeName = collection.dataset.prototypeName;
        const prototype = collection.dataset.prototype;

        addCollectionNode(collection, prototypeName, prototype);
    });

    // CLICK REMOVE BUTTON
    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.classList.contains('collection-remove-button')) return;

        event.preventDefault();

        const nodeRow = event.target.closest('.collection-node-row');
        const collection = nodeRow.closest('.collection-widget');

        removeCollectionNodeRow(collection, nodeRow);
    });

    // TODO IN THE FUTURE, SORT ELEMENTS

    // $(document).on('click', '.collection-down-node-button', function(event){
    //     event.preventDefault();
    //     var nodeRow = $(event.target).closest('.collection-node-row');
    //     var collection = nodeRow.closest('.collection-collection-widget');
    //     moveDownNode(collection, nodeRow);
    //     document.dispatchEvent(new Event('cms.module.move.down'));
    // })
    //
    // $(document).on('click', '.collection-up-node-button', function(event){
    //     event.preventDefault();
    //
    //     var nodeRow = $(event.target).closest('.collection-node-row');
    //     var collection = nodeRow.closest('.collection-collection-widget');
    //     moveUpNode(collection, nodeRow);
    //     document.dispatchEvent(new Event('cms.module.move.up'));
    // });
    //
    // $(document).on('change', '.collection-node-row input', function(event){
    //     // store value to prevent loosing it on moving
    //     var $target = $(event.target);
    //     $target.attr('value', $target.val());
    // });

    /* ************************************************************************************************************* *
     * ACTIONS
     * ************************************************************************************************************* */

    function addCollectionNode (collection, prototypeName, prototype)
    {
        var lastIndex = getCollectionLastIndex(collection);
        var index = isNaN(lastIndex) ? 0 : lastIndex+1;

        // create and process prototype
        const newRow = document.createElement('div');
        // append node to form
        collection.appendChild(newRow);
        newRow.outerHTML = prototype.replace(new RegExp(prototypeName, 'g'), index)

        newRow.dispatchEvent(new Event('add_collection_node', {bubbles: true}));

        // // element which needs to be scrolled to
        // let id = newRow.attr('id'),
        //     element = document.querySelector("#"+id)
        // // scroll to element
        // element.scrollIntoView();

        // updateCollectionButtons(collection);
    }

    function removeCollectionNodeRow(collection, nodeRow)
    {
        let nodeRowIterator = nodeRow;
        while (nodeRowIterator = nodeRowIterator.nextElementSibling) {
            if ( nodeRowIterator.matches('.collection-node-row') ) {
                modifyIndexes(nodeRowIterator, -1);
            }
        }

        nodeRow.dispatchEvent(new Event('removed_collection_node', {bubbles: true}));
        nodeRow.remove();

        // updateCollectionButtons(collection);
    }

    function getCollectionLastIndex(collection)
    {
        const nodeRowList = collection.querySelectorAll('.collection-node-row');

        if (!nodeRowList.length) {
            return -1;
        }

        return parseInt(nodeRowList.item(nodeRowList.length - 1).dataset.index);
    }

    // function moveUpNode(collection, nodeRow)
    // {
    //     var $prevNodeRow = nodeRow.prev('.collection-node-row');
    //
    //     if ($prevNodeRow.length) {
    //         nodeRow.insertBefore($prevNodeRow);
    //         modifyIndexes(nodeRow, -1);
    //         modifyIndexes($prevNodeRow, +1);
    //     }
    //
    //     updateCollectionButtons(collection);
    // }
    //
    // function moveDownNode(collection, nodeRow)
    // {
    //     var $nextNodeRow = nodeRow.next('.collection-node-row');
    //
    //     if ($nextNodeRow.length) {
    //         nodeRow.insertAfter($nextNodeRow);
    //         modifyIndexes(nodeRow, +1);
    //         modifyIndexes($nextNodeRow, -1);
    //     }
    //
    //     updateCollectionButtons(collection);
    // }

    // // init
    // $('.collection-collection-widget').each(function() {
    //     var collection = $(this);
    //     updateCollectionButtons(collection);
    // });

    /* ************************************************************************************************************* *
     * UTILS
     * ************************************************************************************************************* */

    // function updateCollectionButtons(collection)
    // {
    //     var nodeRows = collection.children('.collection-node-row');
    //
    //     nodeRows.each(function (i, nodeRow) {
    //         var nodeRow = $(nodeRow);
    //         var id = nodeRow.attr('id');
    //         var index = parseInt(nodeRow.attr('data-index'));
    //         var lastIndex = nodeRows.length - 1;
    //
    //         if (index===0) {
    //             $('#'+id+'_up_node_button').hideElement();
    //         } else {
    //             $('#'+id+'_up_node_button').showElement();
    //         }
    //
    //         if (index === lastIndex) {
    //             $('#'+id+'_down_node_button').hideElement();
    //         } else {
    //             $('#'+id+'_down_node_button').showElement();
    //         }
    //     });
    // }
    //
    function modifyIndexes(rowElement, increment) {
        var oldIndex = parseInt(rowElement.dataset.index);
        var newIndex = oldIndex + increment;
        rowElement.dataset.index = newIndex;
        rowElement.setAttribute('data-index', newIndex);
        rowElement.querySelectorAll('.collection-node-index').forEach((nodeIndex) => nodeIndex.innerHTML = newIndex);

        var oldRowId = rowElement.getAttribute('id');
        rowElement.setAttribute('id', replaceLastOccurence(rowElement.getAttribute('id'), '_'+oldIndex, '_'+newIndex));
        var newRowId = rowElement.getAttribute('id');

        var oldRowFullName = rowElement.getAttribute('data-full-name');
        rowElement.setAttribute('data-full-name', replaceLastOccurence(rowElement.getAttribute('data-full-name'), '['+oldIndex+']', '['+newIndex+']'));
        var newRowFullName = rowElement.getAttribute('data-full-name');

        rowElement.innerHTML = rowElement.innerHTML.replaceAll(oldRowId, newRowId).replaceAll(oldRowFullName, newRowFullName);
    }

    function replaceLastOccurence(text, search, replace) {
        var lastIndex = text.lastIndexOf(search);
        return text.substr(0, lastIndex) + text.substr(lastIndex).replace(search, replace);
    }
});
