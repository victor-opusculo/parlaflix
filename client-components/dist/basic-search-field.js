
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("span", {"class": `flex flex-row items-center`}, [
    h("label", {}, [
`
            Pesquisar:
            `,
    h("input", {"type": `search`, "value": state.searchkeywords, "oninput": this.changeInput.bind(this), "onkeydown": this.keydown.bind(this)}, "")
]),
    h("button", {"type": `button`, "class": `btn ml-2 min-w-[32px]`, "onclick": this.buttonClicked.bind(this)}, [
    h("img", {"src": `${Parlaflix.Helpers.URLGenerator.generateFileUrl('assets/pics/search.png')}`, "alt": `Pesquisar`, "width": `28`}, "")
])
])]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "./assets/twoutput.css"
    
  `)}
}



export default class extends Lego
    {
        state =
        {
            searchkeywords: '',
            searchcallback: null
        }

    
        changeInput(e)
        {
            this.render({ ...this.state, searchkeywords: e.target.value });
        }

        keydown(e)
        {
            if (e.keyCode === 13)
            {
                e.preventDefault();
                this.buttonClicked();
            }
        }

        buttonClicked(e)
        {
            if (typeof this.state.searchcallback === "function")
                this.state.searchcallback(this.state.searchkeywords);
        }
    }
