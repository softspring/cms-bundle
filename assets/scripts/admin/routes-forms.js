import * as underscored from 'underscore.string/underscored';
import * as slugify from 'underscore.string/slugify';
import 'underscore.string/slugify';

import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_routes_forms', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {

    document.addEventListener('keyup', function (event) {
        if (!event.target.matches('[data-generate-underscore]') && !event.target.matches('[data-generate-slug]')) return;

        // generate underscore
        var element = document.querySelector('[' + event.target.dataset.generateUnderscore + ']');
        if (element && element.value === underscored(event.target.lastValue || '')) {
            element.value = underscored(event.target.value).removeAccents();
        }

        // generate slug
        element = document.querySelector('[' + event.target.dataset.generateSlug + ']');
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
}

/* ****************************************************************************************************** *
 * SLUG GENERATION ON CONTENT FORM
 * ****************************************************************************************************** */

String.prototype.removeAccents = function () {
    return this.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

