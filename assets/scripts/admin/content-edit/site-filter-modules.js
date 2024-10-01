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

import {cmsEditListener} from './event-listeners';

(function () {
    if (!window.__sfs_cms_content_edit_site_filter_modules_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_site_filter_modules_registered = true;
})();

function _register() {
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
};

export { siteHideModules };
