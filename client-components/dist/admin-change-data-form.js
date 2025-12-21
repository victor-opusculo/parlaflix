
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"class": `mx-auto max-w-[700px]`, "onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Nome completo`}, [
    h("input", {"type": `text`, "required": ``, "class": `w-full`, "maxlength": `140`, "value": state.fullname, "oninput": this.nameChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `E-mail`}, [
    h("input", {"type": `email`, "required": ``, "class": `w-full`, "maxlength": `140`, "value": state.email, "oninput": this.emailChanged.bind(this)}, "")
]),
    h("fieldset", {"class": `fieldset`}, [
    h("legend", {}, `Alterar senha`),
    h("ext-label", {"label": `Senha atual`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "value": state.currpassword, "oninput": this.currpasswordChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Nova senha`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "value": state.password, "oninput": this.passwordChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Confirme a senha alterada`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "value": state.password2, "oninput": this.password2Changed.bind(this)}, "")
])
]),
    h("div", {"class": `text-center mt-4`}, [
    h("button", {"class": `btn`, "type": `submit`}, `Alterar dados`)
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
            fullname: '',
            email: '',
            password: '',
            password2: '',
            adminid: 0,
            currpassword: '',
            slotId: ''
        }

        nameChanged(e)
        {
            this.render({ ...this.state, fullname: e.target.value });
        }

        emailChanged(e)
        {
            this.render({ ...this.state, email: e.target.value });
        }

        currpasswordChanged(e)
        {
            this.render({ ...this.state, currpassword: e.target.value });
        }

        passwordChanged(e)
        {
            this.render({ ...this.state, password: e.target.value });
        }

        password2Changed(e)
        {
            this.render({ ...this.state, password2: e.target.value });
        }

        submit(e)
        {
            e.preventDefault();

            if ((this.state.password || this.state.password2) && (this.state.password !== this.state.password2))
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.info, "As senhas não coincidem!");
                return;
            }

            const data = {};
            for (const prop in this.state)
                data['administrators:' + prop] = this.state[prop];

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl('/administrator/' + this.state.adminid), { method: 'PUT', headers, body })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json);
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }
    };
