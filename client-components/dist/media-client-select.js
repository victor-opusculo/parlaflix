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
        set_id_field_callback: _ => void 0,
        search_keywords: '',
        page_num: 1,
        total_items: 0,
        num_results_on_page: 10,
        data_rows: [],

        media_to_upload: null
    }

    const methods = 
    {
        searchAction(query)
        {
            this.render({ ...this.state, page_num: 1, search_keywords: query });
            this.fetchMedia();
        },

        selectMediaFromDataGrid(id)
        {
            if (typeof this.state.set_id_field_callback === "function")
                this.state.set_id_field_callback(id);
        },

        changePageAction(toPage)
        {
            this.render({ ...this.state, page_num: toPage });
            this.fetchMedia();
        },

        fetchMedia()
        {
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/administrator/panel/media", { page_num: this.state.page_num, results_on_page: this.state.num_results_on_page, q: this.state.search_keywords }))
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
                    this.render({ ...this.state, page_num: this.state.page_num, data_rows: transformed, total_items: json.data.allCount, search_keywords: this.state.search_keywords } );
                }
            });
        },

        newMediaFileChanged(e)
        {
            this.render({ ...this.state, media_to_upload: e.target.files[0] ?? null });
        },

        quickUploadMedia()
        {
            if (this.state.media_to_upload === null)
                return;

            const headers = new Headers();
            const formData = new FormData();

            formData.append('media:name', this.state.media_to_upload?.name ?? "Sem nome");
            formData.append('media:description', "");
            formData.append('mediaFile', this.state.media_to_upload ? this.state.media_to_upload : null);

            const route = Parlaflix.Helpers.URLGenerator.generateApiUrl('/administrator/panel/media/create');

            fetch(route, { headers, body: formData, method: 'POST' })
            .then(res => res.json())
            .then(json => 
            {
                Parlaflix.Alerts.pushFromJsonResult(json)
                .then(_ => 
                {
                    this.render({ ...this.state, page_num: 1, search_keywords: "" });
                    this.fetchMedia();
                });
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)))
        },
    }

    function setup()
    {
        this.fetchMedia();
    }


  const __template = function({ state }) {
    return [  
    h("div", {}, [
      h("ext-label", {"label": `Subir novo(a)`}, [
        h("input", {"type": `file`, "name": `newMediafile`, "class": `file:btn`, "onchange": this.newMediaFileChanged.bind(this)}, ""),
        h("button", {"type": `button`, "name": `newMediaUpload`, "class": `btn ml-2`, "onclick": this.quickUploadMedia.bind(this)}, `Upload`)
      ]),
      h("basic-search-field", {"searchcallback": this.searchAction.bind(this), "searchkeywords": state.search_keywords}, ""),
      h("data-grid", {"selectlinkparamname": `ID`, "returnidcallback": this.selectMediaFromDataGrid.bind(this), "datarows": state.data_rows}, ""),
      h("client-paginator", {"totalitems": state.total_items, "resultsonpage": state.num_results_on_page, "pagenum": state.page_num, "changepagecallback": this.changePageAction.bind(this)}, "")
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

  
