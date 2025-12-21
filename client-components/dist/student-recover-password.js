
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"class": `mx-auto max-w-[700px]`, "onsubmit": this.onSubmit.bind(this)}, [
    ((state.mode === 'askEmail') ? h("div", {}, [
    h("ext-label", {"label": `Seu e-mail`}, [
    h("input", {"type": `email`, "class": `w-full`, "maxlength": `140`, "value": state.email, "oninput": this.emailChange.bind(this), "required": ``}, "")
])
]) : ''),
    ((state.mode === 'changePassword') ? h("div", {}, [
    h("ext-label", {"label": `Código enviado para seu e-mail`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `6`, "value": state.currentOtp, "oninput": this.otpChange.bind(this), "required": ``}, "")
]),
    h("ext-label", {"label": `Defina sua nova senha`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "value": state.newPassword, "oninput": this.newPasswordChange.bind(this), "required": ``}, "")
]),
    h("ext-label", {"label": `Confirme sua nova senha`}, [
    h("input", {"type": `password`, "class": `w-full`, "maxlength": `140`, "value": state.newPassword2, "oninput": this.newPassword2Change.bind(this), "required": ``}, "")
])
]) : ''),
    h("div", {"class": `text-center my-4`}, [
    h("button", {"type": `submit`, "class": `btn`, "disabled": state.waiting}, `${state.waiting ? 'Aguarde...' : 'Prosseguir'}`)
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
            email: '',
            mode: 'askEmail',
            otpId: null,
            waiting: false,
            currentOtp: '',
            newPassword: '',
            newPassword2: ''
        }

        emailChange(e) { this.render({ ...this.state, email: e.target.value }); }
        otpChange(e) { this.render({ ...this.state, currentOtp: e.target.value }); }
        newPasswordChange(e) { this.render({ ...this.state, newPassword: e.target.value }); }
        newPassword2Change(e) { this.render({ ...this.state, newPassword2: e.target.value }); }

        onSubmit(e)
        {
            e.preventDefault();

            this.render({ ...this.state, waiting: true });

            if (this.state.mode === 'askEmail')
            {
                const headers = new Headers({ 'Content-Type': 'application/json' });
                const body = JSON.stringify({ data: { email: this.state.email } });
                fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/student/recover_password/request_otp"), { headers, body, method: 'POST' })
                .then(res => res.json())
                .then(json =>
                {
                    Parlaflix.Alerts.pushFromJsonResult(json);
                    if (json.success && json.data?.otpId)
                        this.render({ ...this.state, otpId: json.data.otpId, mode: 'changePassword' });
                    this.render({ ...this.state, waiting: false });
                })
                .catch(reason => 
                {
                    Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason));
                    this.render({ ...this.state, waiting: false });
                });
            }
            else if (this.state.mode === 'changePassword')
            {
                if (this.state.newPassword !== this.state.newPassword2)
                {
                    Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, 'As senhas não coincidem!');
                    this.render({ ...this.state, waiting: false });
                    return;
                }

                const headers = new Headers({ 'Content-Type': 'application/json' });
                const body = JSON.stringify({ data: { otpId: this.state.otpId, givenOtp: this.state.currentOtp, newPassword: this.state.newPassword  } });
                fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/student/recover_password/change_password"), { headers, body, method: 'POST' })
                .then(res => res.json())
                .then(json =>
                {
                    Parlaflix.Alerts.pushFromJsonResult(json)
                    .then(([ ret, json ]) => 
                    {
                        if (json.success)
                            window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl('/student/login');
                        else if (json.error && json.reset)
                            this.render({ ...this.state, mode: 'askEmail', otpId: null, currentOtp: '', newPassword: '', newPassword2: '' });

                        this.render({ ...this.state, waiting: false });
                    });
                })
                .catch(reason => 
                {
                    Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason));
                    this.render({ ...this.state, waiting: false });
                });
            }
        }
    }
