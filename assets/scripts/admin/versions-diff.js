import { diff } from 'jsondiffpatch';
import * as jsondiffpatchHtml from 'jsondiffpatch/formatters/html';
import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_versions_diff', _init);

function _init() {
    const target = document.getElementById('json-diff');

    if (!target || version1 === undefined || version2 === undefined) {
        return;
    }

    jsondiffpatchHtml.config = {
        propertyOrder: (names) => names // Key
    };

    const delta = diff(version1, version2);

    if (delta) {
        const html = jsondiffpatchHtml.format(delta, version1);
        target.innerHTML = html;
    } else {
        target.innerHTML = `
            <div class="ui positive message alert alert-success">
                <div class="header">No differences</div>
                <p>The versions are identic</p>
            </div>
        `;
    }

    const syncScrollElements = document.querySelectorAll('[data-sync-scroll]');

    syncScrollElements.forEach(el => {
        el.addEventListener('scroll', () => {
            const scrollTop = el.scrollTop;
            const scrollLeft = el.scrollLeft;

            syncScrollElements.forEach(otherEl => {
                if (otherEl !== el) {
                    otherEl.scrollTop = scrollTop;
                    otherEl.scrollLeft = scrollLeft;
                }
            });
        });
    });
}
