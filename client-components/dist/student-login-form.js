
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"class": `mx-auto max-w-[500px]`}, [
    h("ext-label", {"label": `E-mail`}, [
    h("input", {"type": `email`, "class": `w-full`, "value": state.email, "oninput": this.changeEmail.bind(this)}, "")
]),
    h("ext-label", {"label": `Senha`}, [
    h("input", {"type": `password`, "class": `w-full`, "value": state.password, "oninput": this.changePassword.bind(this)}, "")
]),
    h("div", {"class": `text-center`}, [
    h("button", {"class": `btn`, "type": `submit`, "onclick": this.submit.bind(this)}, `Entrar`)
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
            password: "",
            email: "" 
        }

        submit(e)
        {
            e.preventDefault();
            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data: { email: this.state.email, password: this.state.password } });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl('/student/login'), { method: 'POST', headers, body })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(Parlaflix.Helpers.URLGenerator.goToPageOrBackToOnSuccess('/student/panel', {}))
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));

            return true;
        }

        changeEmail(e)
        {
            this.render({ ...this.state, email: e.target.value });
        }

        changePassword(e)
        {
            this.render({ ...this.state, password: e.target.value }); 
        }
    }
