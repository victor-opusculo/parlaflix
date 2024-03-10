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
        fullname: null,
        email: null,
        telephone: null,
        institution: null,
        instrole: null,
        timezone: null
    };

    const methods = 
    {
        changeField(e)
        {
            this.render({ ...this.state, [ e.target.name ]: e.target.value });
        },

        submit(e)
        {
            e.preventDefault();

            if (!this.state.id) return;

            const data = {};
            for (const field in this.state)
                data['students:' + field] = this.state[field];

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/students/${this.state.id}`), { headers, body, method: 'PUT' })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));

        }
    };


  const __template = function({ state }) {
    return [  
    h("form", {"onsubmit": this.submit.bind(this)}, [
      h("ext-label", {"label": `ID`, "labelbold": `1`}, `${state.id}`),
      h("ext-label", {"label": `Nome completo`}, [
        h("input", {"type": `text`, "required": ``, "maxlength": `280`, "class": `w-full`, "name": `fullname`, "value": state.fullname, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `E-mail`}, [
        h("input", {"type": `email`, "required": ``, "maxlength": `280`, "class": `w-full`, "name": `email`, "value": state.email, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Telefone`}, [
        h("input", {"type": `text`, "required": ``, "maxlength": `140`, "class": `w-full`, "name": `telephone`, "value": state.telephone, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Instituição`}, [
        h("input", {"type": `text`, "required": ``, "maxlength": `140`, "class": `w-full`, "name": `institution`, "value": state.institution, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Cargo`}, [
        h("input", {"type": `text`, "required": ``, "maxlength": `140`, "class": `w-full`, "name": `instrole`, "value": state.instrole, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Fuso horário`}, [
        h("select", {"onchange": this.changeField.bind(this), "name": `timezone`}, [
          ((Parlaflix.Time.TimeZones).map((dtz) => (h("option", {"value": dtz, "selected": dtz === state.timezone}, `${dtz}`))))
        ])
      ]),
      h("div", {"class": `text-center`}, [
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

  
