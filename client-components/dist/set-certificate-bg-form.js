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
        media_id: null,
        media2_id: null,
        searchMedia: false,
        searchMedia2: false
    };

    const methods =
    {
        changeField(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        },

        searchBtnClicked(e)
        {
            this.render({ ...this.state, searchMedia: !this.state.searchMedia });
        },

        searchBtn2Clicked(e)
        {
            this.render({ ...this.state, searchMedia2: !this.state.searchMedia2 });
        },

        setMediaId(id)
        {
            this.render({ ...this.state, media_id: Number(id) });
        },

        setMedia2Id(id)
        {
            this.render({ ...this.state, media2_id: Number(id) });
        },

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
    };


  const __template = function({ state }) {
    return [  
    h("form", {"onsubmit": this.submit.bind(this)}, [
      h("ext-label", {"label": `Imagem (Mídia ID)`}, [
        h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `media_id`, "value": state.media_id, "oninput": this.changeField.bind(this)}, ""),
        h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.searchBtnClicked.bind(this)}, `Procurar`)
      ]),
      ((state.searchMedia) ? h("media-client-select", {"set_id_field_callback": this.setMediaId.bind(this)}, "") : ''),
      h("ext-label", {"label": `Imagem do verso (Mídia ID)`}, [
        h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `media2_id`, "value": state.media2_id, "oninput": this.changeField.bind(this)}, ""),
        h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.searchBtn2Clicked.bind(this)}, `Procurar`)
      ]),
      ((state.searchMedia2) ? h("media-client-select", {"set_id_field_callback": this.setMedia2Id.bind(this)}, "") : ''),
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

  
