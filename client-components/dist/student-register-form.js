
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"class": `mx-auto max-w-[700px] ${state.darkMode ? 'dark' : ''}`, "onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Nome completo`}, [
    h("input", {"type": `text`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `fullname`, "value": state.fullname, "oninput": this.fieldChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `E-mail`}, [
    h("input", {"type": `email`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `email`, "value": state.email, "oninput": this.fieldChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Telefone`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `telephone`, "value": state.telephone, "oninput": this.fieldChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Instituição`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `institution`, "value": state.institution, "oninput": this.fieldChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Cargo`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `instrole`, "value": state.instrole, "oninput": this.fieldChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Senha`}, [
    h("input", {"type": `password`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `password`, "value": state.password, "oninput": this.fieldChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Confirme a senha`}, [
    h("input", {"type": `password`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `password2`, "value": state.password2, "oninput": this.fieldChanged.bind(this)}, "")
]),
    h("ext-label", {"label": `Seu fuso horário`}, [
    h("select", {"onchange": this.fieldChanged.bind(this), "name": `timezone`}, [
    ((Parlaflix.Time.TimeZones).map((dtz) => (h("option", {"value": dtz, "selected": dtz === 'America/Sao_Paulo'}, `${dtz}`))))
])
]),
    h("ext-label", {"reverse": `1`, "label": `Sou associado da ABEL`}, [
    h("input", {"type": `checkbox`, "value": `1`, "checked": state.is_abel_member, "onchange": this.memberChecked.bind(this)}, "")
]),
    h("div", {"class": `mt-4`}, [
`
            Concorda com o termo de consentimento para uso dos seus dados pessoais?
            `,
    h("button", {"type": `button`, "class": `btn`, "onclick": this.showLgpd.bind(this)}, `Ler`)
]),
    h("ext-label", {"reverse": `1`, "label": `Concordo`}, [
    h("input", {"type": `checkbox`, "required": ``, "value": `${state.lgpdTermVersion}`, "checked": state.lgpdConsentCheck, "onchange": this.consentChecked.bind(this)}, "")
]),
    h("div", {"class": `text-center mt-4`}, [
    h("button", {"class": `btn`, "type": `submit`, "disabled": state.waiting}, `${state.waiting ? 'Aguarde...' : 'Concluir'}`)
])
]),
  h("dialog", {"id": `lgpdTermDialog`, "class": `md:w-[700px] w-screen h-screen backdrop:bg-gray-700/60 p-4 m-auto ${state.darkMode ? 'bg-neutral-800' : 'bg-neutral-100'}`}, [
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
    @import "/--file/assets/twoutput.css"
    
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
            timezone: 'America/Sao_Paulo',
            is_abel_member: false,
            lgpdConsentCheck: false,
            lgpdtermversion: 0,
            lgpdTermText: '',
            slotId: '',
            waiting: false,

            darkMode: false,
        }

        fieldChanged(e)
        {
            this.render({ ...this.state, [ e.target.name ]: e.target.value });
        }

        consentChecked(e)
        {
            this.render({ ...this.state, lgpdConsentCheck: e.target.checked });
        }

        memberChecked(e)
        {
            this.render({ ...this.state, is_abel_member: e.target.checked });
        }

        showLgpd()
        {
            this.shadowRoot.getElementById('lgpdTermDialog')?.showModal();
        }

        submit(e)
        {
            this.render({...this.state, waiting: true, lgpdTermText: document.getElementById('lgpdTermForm')?.elements['lgpdTerm']?.value ?? '***'});
            e.preventDefault();

            if (this.state.password !== this.state.password2)
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.info, "As senhas não coincidem!");
                this.render({ ...this.state, waiting: false });
                return;
            }

            const data = {};
            for (const prop in this.state)
                data['students:' + prop] = this.state[prop];

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl('/student/register'), { method: 'POST', headers, body })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(Parlaflix.Helpers.URLGenerator.goToPageOnSuccess('/student/login'))
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)))
            .finally(() => this.render({ ...this.state, waiting: false }));
            
        }

        connected()
        {
            const document = window.document.documentElement;
            if (document && document.classList.contains('dark'))
                this.render({ darkMode: true });

            document.addEventListener('dark-mode-toggle', e => this.render({ darkMode: e.detail.dark ?? false }));
        }
    }
