
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("ext-label", {"label": `Título`}, [
    h("input", {"type": `text`, "maxlength": `260`, "class": `w-full`, "name": `title`, "required": ``, "value": state.title, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Ícone (Mídia ID)`}, [
    h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `icon_media_id`, "value": state.icon_media_id, "oninput": this.changeField.bind(this)}, ""),
    h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.searchBtnClicked.bind(this)}, `Procurar`)
]),
    ((state.searchMedia.enabled) ? h("div", {}, [
    h("basic-search-field", {"searchkeywords": state.searchMedia.q, "searchcallback": this.searchKeyword.bind(this)}, ""),
    h("data-grid", {"datarows": state.searchMedia.dataRows, "returnidcallback": this.setMediaId.bind(this), "selectlinkparamname": `ID`}, ""),
    h("client-paginator", {"totalitems": state.searchMedia.allCount, "resultsonpage": state.searchMedia.resultsOnPage, "changepagecallback": this.mediaPageChange.bind(this), "pagenum": state.searchMedia.pageNum}, "")
]) : ''),
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
            id: null,
            title: '',
            icon_media_id: null,
            searchMedia: { enabled: false, pageNum: 1, dataRows: [], allCount: 0, resultsOnPage: 20, q: '' },
        }

        changeField(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        }

        searchBtnClicked(e)
        {
            this.fetchMedias();
            this.render({ ...this.state, searchMedia: { ...this.state.searchMedia, enabled: !this.state.searchMedia.enabled } });
        }

        fetchMedias(page = 1, query = '')
        {
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/administrator/panel/media", { page_num: page, results_on_page: this.state.searchMedia.resultsOnPage, q: query }))
            .then(res => res.json())
            .then(json =>
            {
                if (json.success && json.data && json.data.dataRows && typeof json.data.allCount === "number")
                {
                    const transformed = json.data.dataRows.map( m => (
                        {
                            'ID': String(m.id),
                            'Nome': m.name,
                            'Descrição': m.description?.substring(0, 80) ?? '',
                            'Extensão': m.file_extension,
                            'Prévia': { type: 'image', src: Parlaflix.Helpers.URLGenerator.generateFileUrl(`uploads/media/${m.id}.${m.file_extension}`), width: 64 }
                        })
                    );
                    this.render({ ...this.state, searchMedia: { ...this.state.searchMedia, pageNum: page, dataRows: transformed, allCount: json.data.allCount, q: query } });
                }
            });

        }

        mediaPageChange(page = 1)
        {
            this.fetchMedias(page, this.state.searchMedia.q);
        }

        searchKeyword(query = '')
        {
            this.fetchMedias(this.state.searchMedia.pageNum, query);
        }

        setMediaId(id)
        {
            this.render({ ...this.state, icon_media_id: Number(id) });
        }

        submit(e)
        {
            e.preventDefault();

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data: { 'categories:title': this.state.title, 'categories:icon_media_id': this.state.icon_media_id || null }});

            const route = this.state.id ? 
                    Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/categories/${this.state.id}`) :
                    Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/categories/create`);

            fetch(route, { headers, body, method: this.state.id ? 'PUT' : 'POST' })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json)
                .then(([ ret, json ]) =>
                {
                    if (json.success && json.data?.newId)
                    window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl(`/admin/panel/categories/${json.data.newId}/edit`);
                });
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));

        }
    }
