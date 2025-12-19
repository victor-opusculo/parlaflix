
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  ((!state.is_correct) ? h("form", {"onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Senha`}, [
    h("input", {"type": `text`, "name": `given_password`, "value": state.given_password, "oninput": this.changeField.bind(this), "required": ``, "class": `w-[calc(100%-120px)] mr-2`, "maxlength": `100`}, ""),
    h("button", {"type": `submit`, "class": `btn`}, `Validar`)
])
]) : ''),
  ((state.is_correct) ? h("ext-label", {"label": `Senha`}, [
    h("span", {"class": `italic`}, [
    h("img", {"class": `inline mr-2`, "src": `${Parlaflix.Helpers.URLGenerator.generateFileUrl('/assets/pics/check.png')}`, "width": `32`}, ""),
`
            Você já inseriu a senha correta
        `
])
]) : '')]
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
            student_id: null,
            lesson_id: null,
            given_password: '',
            is_correct: 0
        }

        changeField(e)
        {
            this.render({ ...this.state, [ e.target.name ]: e.target.value });
        }

        submit(e)
        {
            e.preventDefault();

            const headers = new Headers({ 'Content-Type': 'application/json' });

            const data = {};
            for (const field in this.state)
                data['student_lesson_passwords:' + field] = this.state[field];

            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/student/presence`), { headers, body, method: 'POST' })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ alertReturn, jsonDecoded ]) =>
            {
                if (jsonDecoded.success)
                    window.location.reload();
                else
                    this.render({ ...this.state, is_correct: false, given_password: '' });
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason))); 

        }

        connected()
        {
            this.render({ ...this.state, is_correct: Boolean(Number(this.getAttribute('iscorrect') ?? 0)) });
        }
    }
