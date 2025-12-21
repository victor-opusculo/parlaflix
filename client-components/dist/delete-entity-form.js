
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("slot", {"id": `${state.slotId}`}, ""),
    h("div", {"class": `text-center my-4`}, [
    h("button", {"type": `submit`, "class": `btn mr-4`}, `Sim, excluir`),
    h("button", {"type": `button`, "class": `btn`, "onclick": this.goBack.bind(this)}, `Não excluir`)
])
])]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "/--file/assets/twoutput.css"
    
  `)}
}



export default class extends Lego
    {
        state =
        {
            deletescripturl: '',
            gobacktourl: '',
            slotId: ''
        }

        submit(e)
        {
            e.preventDefault();

            fetch(this.state.deletescripturl, { method: 'DELETE' })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(Parlaflix.Helpers.URLGenerator.goToPageOnSuccess(this.state.gobacktourl, {}))
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }

        goBack() { history.back(); }
    }
