HTMLElement.prototype.showElement = function () {
    this.classList.remove('d-none', 'hidden');
    return this;
};

HTMLElement.prototype.hideElement = function () {
    this.classList.add('d-none', 'hidden');
    return this;
}
