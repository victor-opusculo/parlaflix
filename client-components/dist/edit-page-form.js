
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Título`}, [
    h("input", {"type": `text`, "maxlength": `140`, "class": `w-full`, "data-fieldname": `title`, "required": ``, "value": `${state.title}`, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Conteúdo`, "linebreak": `1`}, [
    h("textarea", {"rows": `20`, "class": `w-full`, "data-fieldname": `content`, "oninput": this.changeField.bind(this)}, `${state.content}`)
]),
    h("ext-label", {"label": `Habilitar HTML`, "reverse": `1`}, [
    h("input", {"type": `checkbox`, "value": `1`, "data-fieldname": `html_enabled`, "onchange": this.changeField.bind(this), "checked": Boolean(Number(state.html_enabled)) ? true : false}, "")
]),
    h("ext-label", {"label": `Publicada`, "reverse": `1`}, [
    h("input", {"type": `checkbox`, "value": `1`, "data-fieldname": `is_published`, "onchange": this.changeField.bind(this), "checked": Boolean(Number(state.is_published)) ? true : false}, "")
]),
    h("div", {"class": `text-center mt-2`}, [
    h("button", {"type": `submit`, "class": `btn`}, `Salvar`)
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
            id: 0,
            title: '',
            content: '',
            html_enabled: 0,
            is_published: 1
        }

        changeField(e)
        {
            this.render({ ...this.state, [ e.target.getAttribute('data-fieldname') ]: e.target.type !== 'checkbox' ? e.target.value : (e.target.checked ? 1 : 0) });
        }

        submit(e)
        {
            e.preventDefault();

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const data = {};

            for (const prop in this.state)
                data['pages:' + prop] = this.state[prop];

            const body = JSON.stringify({ data });
            const route = this.state.id ? 
                    Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/pages/${this.state.id}`) :
                    Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/pages/create`);

            fetch(route, { headers, body, method: this.state.id ? 'PUT' : 'POST' })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json)
                .then(([ ret, json ]) =>
                {
                    if (json.success && json.data?.newId)
                        window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl(`/admin/panel/pages/${json.data.newId}/edit`);
                });
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }
    }
