
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `E-mail principal de notificação`}, [
    h("input", {"type": `email`, "size": `40`, "onchange": this.changeField.bind(this), "name": `main_inbox_mail`, "value": state.main_inbox_mail}, "")
]),
    h("div", {"class": `my-2`}, [
    h("button", {"type": `submit`, "class": `btn`}, `Salvar`)
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
            main_inbox_mail: ""
        }

    
        submit(e)
        {
            e.preventDefault();

            const data = this.state;
            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl('/administrator/panel/settings'), { method: 'PUT', headers, body })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json);
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }

        changeField(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        }
    }
