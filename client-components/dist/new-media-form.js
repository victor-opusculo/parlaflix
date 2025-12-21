
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Nome`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "required": ``, "data-fieldname": `name`, "value": `${state.name}`, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Descrição`, "linebreak": `1`}, [
    h("textarea", {"class": `w-full`, "row": `6`, "data-fieldname": `description`, "oninput": this.changeField.bind(this)}, `${state.description}`)
]),
    h("ext-label", {"label": `Arquivo`}, [
    h("input", {"type": `file`, "class": `file:btn`, "data-fieldname": `filename`, "required": state.id ? false : true, "onchange": this.changeField.bind(this)}, "")
]),
    h("div", {"class": `text-center mt-2`}, [
    h("button", {"type": `submit`, "class": `btn`, "disabled": state.waiting}, [
    ((state.waiting) ? h("loading-spinner", {"additionalclasses": `invert w-[1em] h-[1em]`}, "") : ''),
`
                Salvar
            `
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
            id: 0,
            waiting: false,
            name: '',
            description: '',
            filename: null,
            files: null
        }

        changeField(e)
        {
            if (e.target.getAttribute('data-fieldname') === 'filename')
            {
                this.render({ ...this.state, files: e.target.files });
            }

            this.render({ ...this.state, [e.target.getAttribute('data-fieldname')]: e.target.value });
        }

        submit(e)
        {
            e.preventDefault();

            const headers = new Headers();
            const formData = new FormData();

            formData.append('media:name', this.state.name);
            formData.append('media:description', this.state.description);
            formData.append('mediaFile', this.state.files && this.state.files[0] ? this.state.files[0] : null);

            this.render({ ...this.state, waiting: true });

            const route = this.state.id ? 
                Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/media/${this.state.id}`)
                :
                Parlaflix.Helpers.URLGenerator.generateApiUrl('/administrator/panel/media/create');

            fetch(route, { headers, body: formData, method: 'POST' })
            .then(res => res.json())
            .then(json => 
            {
                Parlaflix.Alerts.pushFromJsonResult(json)
                .then(([ ret, json ]) =>
                {
                    if (json.success && json.data?.newId)
                        window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl('/admin/panel/media/' + json.data.newId);
                    else
                        this.render({ ...this.state, waiting: false });
                });
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }
    }
