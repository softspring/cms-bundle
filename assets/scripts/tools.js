HTMLElement.prototype.showElement = function () {
  this.classList.remove("d-none", "hidden");
  return this;
};

HTMLElement.prototype.hideElement = function () {
  this.classList.add("d-none", "hidden");
  return this;
};

function registerFeature(module, callable) {
    if (!window[`__sfs_cms_${module}_registered`]) {
        window.addEventListener('load', callable);
    }
    window[`__sfs_cms_${module}_registered`] = true;
}

export {
  registerFeature,
}