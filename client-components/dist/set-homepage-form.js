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
        page_id: null,
        remove: false,
        searchPages: { enabled: false, pageNum: 1, dataRows: [], allCount: 0, resultsOnPage: 20, q: '' },
    };

    const methods =
    {
        changeField(e)
        {
            if (e.target.type == 'checkbox')
                this.render({ ...this.state, [ e.target.name ]: e.target.checked });
            else
                this.render({ ...this.state, [e.target.name]: e.target.value });
        },

        searchBtnClicked(e)
        {
            this.fetchPages();
            this.render({ ...this.state, searchPages: { ...this.state.searchPages, enabled: !this.state.searchPages.enabled } });
        },

        fetchPages(page = 1, query = '')
        {
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/administrator/panel/pages", { page_num: page, results_on_page: this.state.searchPages.resultsOnPage, q: query }))
            .then(res => res.json())
            .then(json =>
            {
                if (json.success && json.data && json.data.dataRows && typeof json.data.allCount === "number")
                {
                    const transformed = json.data.dataRows.map( p => (
                        {
                            'ID': String(p.id),
                            'Título': p.title,
                            'Início do Conteúdo': p.content?.substring(0, 80) ?? '',
                            'Publicada?': Number(p.is_published) ? 'Sim' : 'Não' 
                        })
                    );
                    this.render({ ...this.state, searchPages: { ...this.state.searchPages, pageNum: page, dataRows: transformed, allCount: json.data.allCount, q: query } });
                }
            });

        },

        pagePageChange(page = 1)
        {
            this.fetchPages(page, this.state.searchPages.q);
        },

        searchKeyword(query = '')
        {
            this.fetchPages(this.state.searchPages.pageNum, query);
        },

        setPageId(id)
        {
            this.render({ ...this.state, page_id: Number(id) });
        },

        submit(e)
        {
            e.preventDefault();

            if (!this.state.page_id && !this.state.remove)
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, "Especifique uma página ou marque a opção de remover.");
                return;
            }

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data: { 'page_id': this.state.page_id, 'remove': this.state.remove }});

            const route = Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/pages/set_homepage`);

            fetch(route, { headers, body, method: 'PUT' })
            .then(res => res.json())
            .then(json =>
            {
                Parlaflix.Alerts.pushFromJsonResult(json);
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));

        }
    };


  const __template = function({ state }) {
    return [  
    h("form", {"onsubmit": this.submit.bind(this)}, [
      h("ext-label", {"label": `Página ID`}, [
        h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `page_id`, "value": state.page_id, "oninput": this.changeField.bind(this)}, ""),
        h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.searchBtnClicked.bind(this)}, `Procurar`)
      ]),
      h("ext-label", {"label": `Remover`, "reverse": `1`}, [
        h("input", {"type": `checkbox`, "name": `remove`, "value": `1`, "onchange": this.changeField.bind(this)}, "")
      ]),
      ((state.searchPages.enabled) ? h("div", {}, [
        h("basic-search-field", {"searchkeywords": state.searchPages.q, "searchcallback": this.searchKeyword.bind(this)}, ""),
        h("data-grid", {"datarows": state.searchPages.dataRows, "returnidcallback": this.setPageId.bind(this), "selectlinkparamname": `ID`}, ""),
        h("client-paginator", {"totalitems": state.searchPages.allCount, "resultsonpage": state.searchPages.resultsOnPage, "changepagecallback": this.pagePageChange.bind(this), "pagenum": state.searchPages.pageNum}, "")
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

  
