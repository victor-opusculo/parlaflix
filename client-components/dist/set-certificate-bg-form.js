
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Imagem (Mídia ID)`}, [
    h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `media_id`, "value": state.media_id, "oninput": this.changeField.bind(this)}, ""),
    h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.searchBtnClicked.bind(this)}, `Procurar`)
]),
    ((state.searchMedia) ? h("media-client-select", {"@set-id-field-callback": this.setMediaId.bind(this)}, "") : ''),
    h("ext-label", {"label": `Imagem do verso (Mídia ID)`}, [
    h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `media2_id`, "value": state.media2_id, "oninput": this.changeField.bind(this)}, ""),
    h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.searchBtn2Clicked.bind(this)}, `Procurar`)
]),
    ((state.searchMedia2) ? h("media-client-select", {"@set-id-field-callback": this.setMedia2Id.bind(this)}, "") : ''),
    h("div", {"class": `text-center mt-4`}, [
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
            media_id: null,
            media2_id: null,
            searchMedia: false,
            searchMedia2: false
        }

        changeField(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        }

        searchBtnClicked(e)
        {
            this.render({ ...this.state, searchMedia: !this.state.searchMedia });
        }

        searchBtn2Clicked(e)
        {
            this.render({ ...this.state, searchMedia2: !this.state.searchMedia2 });
        }

        setMediaId({ detail: { id } })
        {
            this.render({ ...this.state, media_id: Number(id) });
        }

        setMedia2Id({ detail: { id } })
        {
            this.render({ ...this.state, media2_id: Number(id) });
        }

        submit(e)
        {
            e.preventDefault();

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data: { 'media_id': this.state.media_id, 'media2_id': this.state.media2_id }});

            const route = Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/certificates/set_bg_image`);

            fetch(route, { headers, body, method: 'PUT' })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json);
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));

        }
    }
