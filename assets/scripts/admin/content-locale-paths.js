function initContentLocalePaths() {
    document.addEventListener('change', (event) => {
        const target = event.target;

        if (target.matches('[data-locale-add-path]')) {
            syncLocalePath(target);
            return;
        }

        if (target.matches('select[data-locale-disables-choice]')) {
            syncDefaultLocalePath(target);
        }
    });
}

function syncDefaultLocalePath(defaultLocaleSelect) {
    const localeCheckbox = findLocaleCheckbox(defaultLocaleSelect);
    if (!localeCheckbox) {
        return;
    }

    localeCheckbox.checked = true;
    syncLocalePath(localeCheckbox);
}

function syncLocalePath(localeCheckbox) {
    if (!localeCheckbox.dataset.localeAddPath) {
        return;
    }

    const routePathsContainer = document.getElementById(localeCheckbox.dataset.localeAddPath);
    if (!routePathsContainer) {
        return;
    }

    if (localeCheckbox.checked) {
        addLocalePath(localeCheckbox, routePathsContainer);
        return;
    }

    removeLocalePath(localeCheckbox, routePathsContainer);
}

function addLocalePath(localeCheckbox, routePathsContainer) {
    if (findLocalePathSelect(localeCheckbox.value, routePathsContainer)) {
        return;
    }

    const addButton = findCollectionAction(routePathsContainer, 'add');
    if (!addButton) {
        return;
    }

    addButton.click();

    const localePathSelects = routePathsContainer.querySelectorAll('[data-collection=node] [data-route-form=path-locale]');
    const lastLocalePathSelect = localePathSelects.item(localePathSelects.length - 1);
    if (!lastLocalePathSelect) {
        return;
    }

    lastLocalePathSelect.value = localeCheckbox.value;
    lastLocalePathSelect.dispatchEvent(new Event('change', {bubbles: true}));

    fillNewLocalePathFromGeneratedSlug(lastLocalePathSelect);
}

function removeLocalePath(localeCheckbox, routePathsContainer) {
    if (countLocalePaths(routePathsContainer) <= 1) {
        localeCheckbox.checked = true;
        return;
    }

    const localePathSelect = findLocalePathSelect(localeCheckbox.value, routePathsContainer);
    if (!localePathSelect) {
        return;
    }

    const routePathNode = localePathSelect.closest('[data-collection=node]');
    const deleteButton = routePathNode?.querySelector('[data-collection-action=delete]');
    if (!deleteButton) {
        return;
    }

    deleteButton.click();
}

function findLocalePathSelect(locale, routePathsContainer) {
    const localePathSelects = routePathsContainer.querySelectorAll('[data-collection=node] [data-route-form=path-locale]');

    return [...localePathSelects].find((localePathSelect) => localePathSelect.value === locale);
}

function findLocaleCheckbox(defaultLocaleSelect) {
    const choicesWidget = document.getElementById(defaultLocaleSelect.dataset.localeDisablesChoice);
    if (!choicesWidget) {
        return null;
    }

    return choicesWidget.querySelector(`input[type="checkbox"][value="${defaultLocaleSelect.value}"][data-locale-add-path]`);
}

function countLocalePaths(routePathsContainer) {
    return routePathsContainer.querySelectorAll('[data-collection=node] [data-route-form=path-locale]').length;
}

function fillNewLocalePathFromGeneratedSlug(localePathSelect) {
    const routePathNode = localePathSelect.closest('[data-collection=node]');
    const pathInput = routePathNode?.querySelector('[data-route-path]');
    const generatedSlug = getGeneratedSlugValue();

    if (!pathInput || !generatedSlug || pathInput.value) {
        return;
    }

    pathInput.value = generatedSlug;
    pathInput.dispatchEvent(new Event('keyup', {bubbles: true}));
    pathInput.dispatchEvent(new Event('change', {bubbles: true}));
}

function getGeneratedSlugValue() {
    const slugSource = document.querySelector('[data-generate-slug]');
    if (!slugSource) {
        return '';
    }

    return slugSource.lastSlugValue || slug(slugSource.value);
}

function slug(value) {
    return value.replace(/-+$/g, '')
        .replace(/[\s_]+/g, '-')
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-zA-Z0-9-]/g, '')
        .replace(/-+/g, '-')
        .toLowerCase();
}

function findCollectionAction(collection, action) {
    return collection.querySelector(`[data-collection-action=${action}]`)
        || document.querySelector(`[data-collection-action=${action}][data-collection-target="${collection.id}"]`);
}

initContentLocalePaths();
