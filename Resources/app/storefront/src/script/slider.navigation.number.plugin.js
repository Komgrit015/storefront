import Plugin from 'src/plugin-system/plugin.class';

export default class SliderNavigationNumberPlugin extends Plugin {
    init() {
        let nav = parseInt(this.el.getAttribute('data-nav')) + 1;
        this.el.innerHTML = '<span>' + nav + '</span>';
        console.log('test')
    }
}
