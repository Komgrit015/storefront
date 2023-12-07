import Plugin from 'src/plugin-system/plugin.class';

export default class ExamplePlugin extends Plugin {
    init() {
        const equalsButton = document.querySelector('.cms-element-text');

        equalsButton.addEventListener('click', ()=>{
            console.log('test')
        });



        window.addEventListener('scroll', this.onScroll.bind(this));
    }

    onScroll() {
       console.log('test');

    }
}
console.log('test');
