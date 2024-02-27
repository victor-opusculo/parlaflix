 // Lego version 1.0.0
  import { h, Component } from './lego.min.js'
   
    import { render } from './lego.min.js';
   
    Component.prototype.render = function(state)
    {
      const childs = Array.from(this.childNodes);
      this.__originalChildren = childs.length && !this.__originalChildren?.length ? childs : this.__originalChildren;

       this.__state.slotId = `slot_${performance.now().toString().replace('.','')}_${Math.floor(Math.random() * 1000)}`;
   
      this.setState(state);
      if(!this.__isConnected) return
   
      const rendered = render([
        this.vdom({ state: this.__state }),
        this.vstyle({ state: this.__state }),
      ], this.document);
   
      const slot = this.document.querySelector(`#${this.__state.slotId}`);
      if (slot)
         for (const c of this.__originalChildren)
             slot.appendChild(c);
            
      return rendered;
    };

  
    const state =
    {
        id: null,
        title: '',
        icon_media_id: null,
        searchMedia: { enabled: false, pageNum: 1, dataRows: [], allCount: 0, resultsOnPage: 20, q: '' },
    };

    const methods =
    {
        changeField(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        },

        searchBtnClicked(e)
        {
            this.fetchMedias();
            this.render({ ...this.state, searchMedia: { ...this.state.searchMedia, enabled: !this.state.searchMedia.enabled } });
        },

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
                            'ID': m.id,
                            'Nome': m.name,
                            'Descrição': m.description?.substring(0, 80) ?? '',
                            'Extensão': m.file_extension,
                            'Prévia': { type: 'image', src: Parlaflix.Helpers.URLGenerator.generateFileUrl(`uploads/media/${m.id}.${m.file_extension}`), width: 64 }
                        })
                    );
                    this.render({ ...this.state, searchMedia: { ...this.state.searchMedia, pageNum: page, dataRows: transformed, allCount: json.data.allCount, q: query } });
                }
            });

        },

        mediaPageChange(page = 1)
        {
            this.fetchMedias(page, this.state.searchMedia.q);
        },

        searchKeyword(query = '')
        {
            this.fetchMedias(this.state.searchMedia.pageNum, query);
        },

        setMediaId(id)
        {
            this.render({ ...this.state, icon_media_id: Number(id) });
        },

        submit(e)
        {
            e.preventDefault();

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data: { 'categories:title': this.state.title, 'categories:icon_media_id': this.state.icon_media_id || null }});
console.log(body);
            const route = this.state.id ? 
                    Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/categories/${this.state.id}`) :
                    Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/categories/create`);

            fetch(route, { headers, body, method: this.state.id ? 'PUT' : 'POST' })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json);

                if (json.success && json.data?.newId)
                    window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl(`/admin/panel/categories/${json.data.newId}/edit`);
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));

        }
    };


  const __template = function({ state }) {
    return [  
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
    ])
  ]
  }

  const __style = function({ state }) {
    return h('style', {}, `
      
      
    `)
  }

  // -- Lego Core
  export default class Lego extends Component {
    init() {
      this.useShadowDOM = false
      if(typeof state === 'object') this.__state = Object.assign({}, state, this.__state)
      if(typeof methods === 'object') Object.keys(methods).forEach(methodName => this[methodName] = methods[methodName])
      if(typeof connected === 'function') this.connected = connected
      if(typeof setup === 'function') setup.bind(this)()
    }
    get vdom() { return __template }
    get vstyle() { return __style }
  }
  // -- End Lego Core

  
