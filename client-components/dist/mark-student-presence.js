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
        lessonId: null,
        availableSubscriptions: [],
        selectedOption: null,
    };

    const methods = 
    {
        openDialog()
        {
            document.getElementById("diagMarkPresence")?.showModal();
        },

        closeDialog()
        {
            document.getElementById("diagMarkPresence")?.close();
        },

        onSubmit(e)
        {
            e.preventDefault();
            
            if (!this.state.selectedOption) return;

            const { selectedOption: { id }, lessonId } = this.state;

            const body = JSON.stringify({ studentId: id, lessonId });
            const headers = new Headers({ 'Content-Type': "application/json" });

            this.closeDialog();

            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/presences/mark`), { body, headers, method: 'POST' })
            .then(res => res.json())//.then(console.log);
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ret, json]) =>
            {
                if (json.success)
                    window.location.reload();
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        },

        changeOption(e)
        {
            this.render({ ...this.state, selectedOption: this.state.availableSubscriptions.find(opt => opt.id == e.target.value)});
        }
    };

    function setup()
    {
        this.state = { 
            ...this.state, 
            availableSubscriptions: JSON.parse(this.getAttribute("available_subscriptions") || '[]'),
            lessonId: Number.parseInt(this.getAttribute("lesson_id"))
        };
    }


  const __template = function({ state }) {
    return [  
    h("div", {"class": `my-4`}, [
      h("button", {"class": `btn`, "type": `button`, "onclick": this.openDialog.bind(this)}, `Nova presença`),
      h("dialog", {"class": `m-auto`, "id": `diagMarkPresence`}, [
        h("form", {"onsubmit": this.onSubmit.bind(this), "class": `text-center min-w-[350px] p-4 dark:text-white dark:bg-zinc-800`}, [
          h("ext-label", {"label": `Marcar presença para`}, [
            h("select", {"onchange": this.changeOption.bind(this), "required": ``}, [
              h("option", {"value": ``}, `--- Selecione ---`),
              ((state.availableSubscriptions).map((opt) => (h("option", {"value": `${opt.id}`, "selected": opt === state.selectedOption}, `${opt.name} (${opt.email})`))))
            ])
          ]),
          h("button", {"class": `btn my-4 mr-2`, "type": `submit`}, `Marcar`),
          h("button", {"class": `btn my-4`, "type": `button`, "onclick": this.closeDialog.bind(this)}, `Cancelar`)
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

  
