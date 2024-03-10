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
        student_id: null,
        lesson_id: null,
        given_password: '',
        is_correct: 0
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

            const headers = new Headers({ 'Content-Type': 'application/json' });

            const data = {};
            for (const field in this.state)
                data['student_lesson_passwords:' + field] = this.state[field];

            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/student/presence`), { headers, body, method: 'POST' })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ alertReturn, jsonDecoded ]) =>
            {
                if (jsonDecoded.success)
                    window.location.reload();
                else
                    this.render({ ...this.state, is_correct: false, given_password: '' });
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason))); 

        }
    };

    function setup()
    {
        this.state.is_correct = Boolean(Number(this.getAttribute('iscorrect') ?? 0));
    }


  const __template = function({ state }) {
    return [  
    ((!state.is_correct) ? h("form", {"onsubmit": this.submit.bind(this)}, [
      h("ext-label", {"label": `Senha desta aula`}, [
        h("input", {"type": `text`, "name": `given_password`, "value": state.given_password, "oninput": this.changeField.bind(this), "required": ``, "class": `w-[calc(100%-120px)] mr-2`, "maxlength": `100`}, ""),
        h("button", {"type": `submit`, "class": `btn`}, `Validar`)
      ])
    ]) : ''),
    ((state.is_correct) ? h("p", {}, `Você já marcou presença/visualização desta aula.`) : '')
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

  
