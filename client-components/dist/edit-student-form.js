
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `ID`, "labelbold": `1`}, `${state.id}`),
    h("ext-label", {"label": `Nome completo`}, [
    h("input", {"type": `text`, "required": ``, "maxlength": `280`, "class": `w-full`, "name": `fullname`, "value": state.fullname, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `E-mail`}, [
    h("input", {"type": `email`, "required": ``, "maxlength": `280`, "class": `w-full`, "name": `email`, "value": state.email, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Telefone`}, [
    h("input", {"type": `text`, "required": ``, "maxlength": `140`, "class": `w-full`, "name": `telephone`, "value": state.telephone, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Instituição`}, [
    h("input", {"type": `text`, "required": ``, "maxlength": `140`, "class": `w-full`, "name": `institution`, "value": state.institution, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Cargo`}, [
    h("input", {"type": `text`, "required": ``, "maxlength": `140`, "class": `w-full`, "name": `instrole`, "value": state.instrole, "oninput": this.changeField.bind(this)}, "")
]),
    h("div", {"class": `ml-2`}, [
`
            Associado da ABEL? 
            `,
    h("label", {}, [
    h("input", {"type": `radio`, "name": `is_abel_member`, "value": `1`, "onchange": this.changeRadio.bind(this), "checked": state.is_abel_member}, ""),
` Sim`
]),
    h("label", {"class": `ml-2`}, [
    h("input", {"type": `radio`, "name": `is_abel_member`, "value": `0`, "onchange": this.changeRadio.bind(this), "checked": state.is_abel_member == false}, ""),
` Não`
])
]),
    h("ext-label", {"label": `Fuso horário`}, [
    h("select", {"onchange": this.changeField.bind(this), "name": `timezone`}, [
    ((Parlaflix.Time.TimeZones).map((dtz) => (h("option", {"value": dtz, "selected": dtz === state.timezone}, `${dtz}`))))
])
]),
    h("ext-label", {"label": `Alterar senha`}, [
    h("input", {"type": `text`, "placeholder": `Deixe em branco para manter a senha atual`, "maxlength": `140`, "class": `w-full`, "name": `newPassword`, "value": state.newPassword, "oninput": this.changeField.bind(this)}, "")
]),
    h("div", {"class": `text-center`}, [
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
            id: null,
            fullname: null,
            email: null,
            telephone: null,
            institution: null,
            instrole: null,
            timezone: null,
            is_abel_member: false,

            newPassword: ""
        }

        changeField(e)
        {
            this.render({ ...this.state, [ e.target.name ]: e.target.value });
        }

        changeRadio(e)
        {
            if (e.target.checked)
                this.render({ ...this.state, [ e.target.name ]: Boolean(Number(e.target.value)) }); 
        }

        submit(e)
        {
            e.preventDefault();

            if (!this.state.id) return;

            const data = {};
            for (const field in this.state)
                data['students:' + field] = this.state[field];

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/students/${this.state.id}`), { headers, body, method: 'PUT' })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));

        }

        connected()
        {
            this.render({  is_abel_member: Boolean(Number(this.getAttribute("is_abel_member") ?? 0)) });
        }
    }
