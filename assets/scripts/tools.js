HTMLElement.prototype.show = function () {
    this.classList.remove('d-none', 'hidden');
    return this;
};

HTMLElement.prototype.hide = function () {
    this.classList.add('d-none', 'hidden');
    return this;
}