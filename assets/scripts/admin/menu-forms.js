(function($) {
    function removeMenuItem($removeNodeLink)
    {
        var $removeRow = $removeNodeLink.closest('.node-row');

        // custom for this
        var $nodes = $removeRow.parent().find('.node-row');
        var nodes = $nodes.length;

        $removeRow.remove();

        // custom for this
        if (nodes-1 <= 1) {
            $nodes.find('.remove_menu_item').hide();
        }
    }

    function getMenuItemCollectionLastIndex($collection)
    {
        return $collection.find('.node-row').length - 1; // parseInt($collection.find('.node-row').last().data('index'));
    }

    function addMenuItem ($addNodeLink)
    {
        var $collection = $($addNodeLink.data('collection'));
        var initialIndex = 0; // parseInt($collection.attr('data-initial-index'));
        var lastIndex = getMenuItemCollectionLastIndex($collection);
        var index = isNaN(lastIndex) ? 0 : (lastIndex <= initialIndex ? initialIndex+1 : lastIndex+1);

        // create and process prototype
        var prototype = $collection.data('prototype');
        var newRow = prototype.replace(/__name__/g, index);
        var $newRow = $(newRow);

        // append node to form
        $collection.append($newRow);

        $collection.find('.remove_menu_item').show();
    }

    $('.menu-item-collection').each(function() {
        var $collection = $(this);
        var lastIndex = getMenuItemCollectionLastIndex($collection);
        $collection.attr('data-initial-index', isNaN(lastIndex) ? '' : lastIndex);
    });

    $(document).on('click', '.add_menu_item', function(event){
        event.preventDefault();
        addMenuItem($(this));
    });

    $(document).on('click', '.remove_menu_item', function(event){
        event.preventDefault();
        removeMenuItem($(this));
    });
})(jQuery);