import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_site_filter_modules', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    const contentEditionSiteSelector = document.getElementById('contentEditionSiteSelection');

    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.hasAttribute('data-cms-module-site-filter')) return;

        let widget = event.target;
        let site = widget.value;
        let currentSite = contentEditionSiteSelector.value;

        if (site !== currentSite) return;

        let siteVisible = widget.checked;
        let modulePreview = widget.closest('.cms-module-edit').querySelector('.module-preview');
        if (siteVisible) {
            modulePreview.classList.remove('cms-module-site-hidden');
        } else {
            modulePreview.classList.add('cms-module-site-hidden');
        }
    });

    if (contentEditionSiteSelector) {
        contentEditionSiteSelector.addEventListener('change', function (event) {
            siteHideModules(event.target.value);
        });

        siteHideModules(contentEditionSiteSelector.value);
    }
}

function siteHideModules(site) {
    document.querySelectorAll('[data-cms-module-site-filter][value='+site+']').forEach(function (widget) {
        let siteVisible = widget.checked;
        let modulePreview = widget.closest('.cms-module-edit').querySelector('.module-preview');
        if (siteVisible) {
            modulePreview.classList.remove('cms-module-site-hidden');
        } else {
            modulePreview.classList.add('cms-module-site-hidden');
        }
    });
}

export { siteHideModules };
