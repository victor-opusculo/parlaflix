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
        availableDestinations: [],
        back_to_url: "",
        mode: "all",
        setDestinations: [],
        title: "",
        message: "",
        waiting: false
    };

    const methods = 
    {
        onSubmit(e)
        {
            e.preventDefault();

            const setDests = [ ...new Set(this.state.setDestinations) ];

            if (setDests.length < 1)
                return;

            this.render({ ...this.state, waiting: true });

            const body = 
            { 
                title: this.state.title,
                message: this.state.message,
                destinations: setDests.map(destIndex => this.state.availableDestinations[destIndex] ?? { name: 'n/a', email: 'n@a' })
            };

            const headers = new Headers({ "Content-Type": "application/json" });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/subscriptions/send_email`), { body: JSON.stringify(body), headers, method: 'POST' })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ _, json ]) =>
            {
                if (json.success && this.state.back_to_url)
                    window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl(this.state.back_to_url);
                else
                    this.render({ ...this.state, waiting: false });
            })
            .catch(reason => 
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason));
                this.render({ ...this.state, waiting: false });
            });
        },

        onModeChanged(e)
        {
            this.render({ ...this.state, mode: e.target.value, setDestinations: e.target.value === 'all' ? this.state.availableDestinations?.map((_, idx) => idx) : [] });
        },

        onSetDestinationChanged(e)
        {
            const setDestIndex = Number(e.target.getAttribute("data-index"));
            const newSetDest = [ ...this.state.setDestinations ];

            if (setDestIndex < 0 || setDestIndex >= newSetDest.length)
                return;

            newSetDest[setDestIndex] = e.target.value;
            this.render({ ...this.state, setDestinations: newSetDest });
        },

        removeSetDestClick(e)
        {
            const setDestIndex = Number(e.target.getAttribute("data-index"));
            const newSetDest = this.state.setDestinations;

            if (setDestIndex < 0 || setDestIndex >= newSetDest.length)
                return;

            this.render({ ...this.state, setDestinations: newSetDest.filter((_, idx) => idx !== setDestIndex) });
        },

        addSetDest(e)
        {
            const newSetDest = [ ...this.state.setDestinations ];
            newSetDest.push(0);

            this.render({ ...this.state, setDestinations: newSetDest });
        },

        changeField(e)
        {
            this.render({ ...this.state, [ e.target.name ]: e.target.value });
        }
    };

    function setup()
    {
        const avDests = JSON.parse(this.getAttribute("availableDestinationsJson") ?? '[]');
        this.state = { ...this.state, availableDestinations: avDests, setDestinations: avDests.map((_, idx) => idx) };
    }



  const __template = function({ state }) {
    return [  
    h("form", {"onsubmit": this.onSubmit.bind(this)}, [
      h("div", {"class": `ml-2`}, [
        h("label", {}, [
          h("input", {"type": `radio`, "name": `mode`, "value": `all`, "onchange": this.onModeChanged.bind(this), "checked": state.mode === 'all'}, ""),
` Enviar para todos os inscritos`
        ]),
        h("br", {}, ""),
        h("label", {}, [
          h("input", {"type": `radio`, "name": `mode`, "value": `some`, "onchange": this.onModeChanged.bind(this), "checked": state.mode === 'some'}, ""),
` Enviar para alguns inscritos somente`
        ])
      ]),
      ((state.mode === 'some') ? h("div", {"class": `mt-4 ml-2`}, [
        h("p", {}, `Destinatários:`),
        h("ol", {"class": `list-decimal pl-4`}, [
          ((state.setDestinations).map((destIndex, idx) => (h("li", {}, [
            h("select", {"data-index": `${idx}`, "oninput": this.onSetDestinationChanged.bind(this)}, [
              ((state.availableDestinations).map((avDest, idx2) => (h("option", {"value": idx2, "selected": idx2 === destIndex}, `${avDest.name} (${avDest.email})`))))
            ]),
            h("button", {"type": `button`, "class": `btn ml-2`, "data-index": `${idx}`, "onclick": this.removeSetDestClick.bind(this)}, `×`)
          ]))))
        ]),
        h("button", {"type": `button`, "class": `btn`, "onclick": this.addSetDest.bind(this)}, `Adicionar`)
      ]) : ''),
      h("ext-label", {"label": `Título da mensagem`}, [
        h("input", {"type": `text`, "maxlength": `280`, "name": `title`, "required": ``, "onchange": this.changeField.bind(this), "class": `w-full`, "value": state.title}, "")
      ]),
      h("ext-label", {"label": `Mensagem`, "linebreak": `1`}, [
        h("textarea", {"maxlength": `2000`, "name": `message`, "required": ``, "onchange": this.changeField.bind(this), "rows": `6`, "class": `w-full`, "value": state.message}, "")
      ]),
      h("div", {"class": `text-center mt-2`}, [
        h("button", {"type": `submit`, "class": `btn`, "disabled": state.waiting}, [
          ((state.waiting) ? h("loading-spinner", {"additionalclasses": `invert w-[1em] h-[1em] mr-2`}, "") : ''),
`
                Enviar
            `
        ])
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

  
