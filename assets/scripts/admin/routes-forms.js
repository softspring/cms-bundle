var underscored = require("underscore.string/underscored");
var slugify = require("underscore.string/slugify");

window.addEventListener('load', (event) => {
    String.prototype.removeAccents = function () {
        return this.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    document.addEventListener('keyup', function (event) {
        if (!event.target.matches('[data-generate-underscore]') && !event.target.matches('[data-generate-slug]')) return;

        // generate underscore
        var element = document.querySelector('[' + event.target.dataset.generateUnderscore + ']');
        if (element && element.value === underscored(event.target.lastValue || '')) {
            element.value = underscored(event.target.value).removeAccents();
        }

        // generate slug
        var element = document.querySelector('[' + event.target.dataset.generateSlug + ']');
        if (element && element.value.replace(/^\/+/, '').replace(/\/+$/, '') === slugify(event.target.lastValue || '')) {
            element.value = slugify(event.target.value).removeAccents();
        }

        event.target.lastValue = event.target.value.removeAccents();
    });

    document.addEventListener('keyup', function (event) {
        if (!event.target.matches('.snake-case')) return;
        event.target.value = underscored(event.target.value);
    });

    document.addEventListener('keyup', function (event) {
        if (!event.target.matches('.sluggize')) return;
        event.target.value = slugify(event.target.value);
    });
});

(function ($) {
    function removeRoutePath($removeNodeLink) {
        var $removeRow = $removeNodeLink.closest('.node-row');

        // custom for this
        var $nodes = $removeRow.parent().find('.node-row');
        var nodes = $nodes.length;

        $removeRow.remove();

        // custom for this
        if (nodes - 1 <= 1) {
            $nodes.find('.remove_route_path').hide();
        }
    }

    function getRoutePathCollectionLastIndex($collection) {
        return $collection.find('.node-row').length - 1; // parseInt($collection.find('.node-row').last().data('index'));
    }

    function addRoutePath($addNodeLink) {
        var $collection = $($addNodeLink.data('collection'));
        var initialIndex = 0; // parseInt($collection.attr('data-initial-index'));
        var lastIndex = getRoutePathCollectionLastIndex($collection);
        var index = isNaN(lastIndex) ? 0 : (lastIndex <= initialIndex ? initialIndex + 1 : lastIndex + 1);

        // create and process prototype
        var prototype = $collection.data('prototype');
        var newRow = prototype.replace(/__name__/g, index);
        var $newRow = $(newRow);

        // append node to form
        $collection.append($newRow);

        $collection.find('.remove_route_path').show();
    }

    $('.route-path-collection').each(function () {
        var $collection = $(this);
        var lastIndex = getRoutePathCollectionLastIndex($collection);
        $collection.attr('data-initial-index', isNaN(lastIndex) ? '' : lastIndex);
    });

    $(document).on('click', '.add_route_path', function (event) {
        event.preventDefault();
        addRoutePath($(this));
    });

    $(document).on('click', '.remove_route_path', function (event) {
        event.preventDefault();
        removeRoutePath($(this));
    });

    // function removeDynamicForm($removeNodeLink)
    // {
    //     var $removeRow = $removeNodeLink.closest('.node-row');
    //
    //     // custom for this
    //     var $nodes = $removeRow.parent().children('fieldset');
    //     var nodes = $nodes.length;
    //
    //     $removeRow.remove();
    //
    //     // custom for this
    //     if (nodes-1 <= 1) {
    //         $nodes.find('.remove_dynamic_form').hide();
    //     }
    // }
    //
    // function getDynamicFormCollectionLastIndex($collection)
    // {
    //     return $collection.children('fieldset').length - 1; // parseInt($collection.find('.node-row').last().data('index'));
    // }
    //
    // function addDynamicForm ($addNodeLink)
    // {
    //     var $collection = $($addNodeLink.data('collection'));
    //     var lastIndex = $collection.children('fieldset').length - 1;
    //     var index = lastIndex + 1;
    //
    //     // create and process prototype
    //     var prototype = $collection.data('prototype');
    //     var newRow = prototype.replace(/__name__/g, index);
    //     var $newRow = $(newRow);
    //
    //     // append node to form
    //     $collection.append($newRow);
    //
    //     $collection.find('.remove_dynamic_form').show();
    // }
    //
    // $(document).on('click', '.add_dynamic_form', function(event){
    //     event.preventDefault();
    //     addDynamicForm($(this));
    // });
    //
    // $(document).on('click', '.remove_dynamic_form', function(event){
    //     event.preventDefault();
    //     removeDynamicForm($(this));
    // });
})(jQuery);