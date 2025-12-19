
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"class": `mx-auto max-w-[700px] ${state.darkMode ? 'dark' : ''}`, "onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Nome completo`}, [
    h("input", {"type": `text`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `fullname`, "value": state.fullname, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `E-mail`}, [
    h("input", {"type": `email`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `email`, "value": state.email, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Número de telefone`}, [
    h("input", {"type": `text`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `telephone`, "value": state.telephone, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Instituição`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `institution`, "value": state.institution, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Cargo`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `instrole`, "value": state.instrole, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Seu fuso horário`}, [
    h("select", {"onchange": this.changeField.bind(this), "name": `timezone`}, [
    ((Parlaflix.Time.TimeZones).map((dtz) => (h("option", {"value": dtz, "selected": dtz === this.state.timezone}, `${dtz}`))))
])
]),
    h("fieldset", {"class": `fieldset`}, [
    h("legend", {}, `Alterar senha`),
    h("ext-label", {"label": `Senha atual`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "name": `currpassword`, "value": state.currpassword, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Nova senha`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "name": `password`, "value": state.password, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Confirme a senha alterada`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "name": `password2`, "value": state.password2, "oninput": this.changeField.bind(this)}, "")
])
]),
    h("div", {"class": `mt-4`}, [
`
            Concorda com o termo de consentimento para uso dos seus dados pessoais?
            `,
    h("button", {"type": `button`, "class": `btn`, "onclick": this.showLgpd.bind(this)}, `Ler`)
]),
    h("ext-label", {"reverse": `1`, "label": `Concordo`}, [
    h("input", {"type": `checkbox`, "required": ``, "value": `${state.lgpdTermVersion}`, "checked": state.lgpdConsentCheck, "name": `lgpdConsentCheck`, "onchange": this.changeField.bind(this)}, "")
]),
    h("div", {"class": `text-center mt-4`}, [
    h("button", {"class": `btn`, "type": `submit`}, `Alterar dados`)
])
]),
  h("dialog", {"id": `lgpdTermDialog`, "class": `md:w-[700px] w-screen h-screen m-auto backdrop:bg-gray-700/60 p-4 ${state.darkMode ? 'bg-neutral-800' : 'bg-neutral-100'}`}, [
    h("form", {"id": `lgpdTermForm`, "method": `dialog`}, [
    h("slot", {"id": `${state.slotId}`}, ""),
    h("div", {"class": `text-center my-4`}, [
    h("button", {"class": `btn`, "type": `submit`}, `Fechar`)
])
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
            fullname: '',
            email: '',
            telephone: '',
            institution: '',
            instrole: '',
            password: '',
            password2: '',
            timezone: '',
            lgpdConsentCheck: false,
            lgpdtermversion: 0,
            lgpdTermText: '',
            studentid: 0,
            currpassword: '',
            slotId: '',

            darkMode: false
        }

        changeField(e)
        {
            if (e.target.type === 'checkbox')
                this.render({ ...this.state, [ e.target.name ]: e.target.checked });
            else
                this.render({ ...this.state, [ e.target.name ]: e.target.value });
        }

        showLgpd()
        {
            this.shadowRoot.getElementById('lgpdTermDialog')?.showModal();
        }

        submit(e)
        {
            this.render({...this.state, lgpdTermText: document.getElementById('lgpdTermForm')?.elements['lgpdTerm']?.value ?? '***'});
            e.preventDefault();

            if ((this.state.password || this.state.password2) && (this.state.password !== this.state.password2))
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.info, "As senhas não coincidem!");
                return;
            }

            if (this.state.currpassword && !this.state.password)
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.info, "Nova senha não pode ser em branco!");
                return;
            }

            const data = {};
            for (const prop in this.state)
                data['students:' + prop] = this.state[prop];

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl('/student/' + this.state.studentid), { method: 'PUT', headers, body })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json);
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }

        connected()
        {
            const document = window.document.documentElement;
            if (document && document.classList.contains('dark'))
                this.render({ darkMode: true });

            document.addEventListener('dark-mode-toggle', e => this.render({ darkMode: e.detail.dark ?? false }));
        }
    }
