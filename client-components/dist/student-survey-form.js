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
        subscription_id: null,
        filledStar: null,
        emptyStar: null,
        pointsGiven: 0,
        message: ""
    };

    const methods =
    {
        submit(e)
        {
            e.preventDefault();

            if (this.state.pointsGiven < 1)
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, "A nota deve ser marcada: Uma ou mais estrelas!");
                return;
            }

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data: { 'course_surveys:pointsGiven': this.state.pointsGiven, 'course_surveys:message': this.state.message } });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/student/survey", { subscription_id: this.state.subscription_id ?? 0 }), { method: "POST", body, headers })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ret, jsonReturn ]) =>
            {
                if (jsonReturn.success)
                    window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl(`/student/panel/subscription/${this.state.subscription_id}`);
            })
            .catch(console.error);
        },

        starClicked(e)
        {
            const points = Number.parseInt(e.target.getAttribute('data-points'));
            this.render({ ...this.state, pointsGiven: points });
        },

        onInputChange(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        }
    };

    function setup()
    {
        const filledStar =  Parlaflix.Helpers.URLGenerator.generateFileUrl('assets/pics/star_filled.png');
        const emptyStar = Parlaflix.Helpers.URLGenerator.generateFileUrl('assets/pics/star_empty.png');

        this.state = { ...this.state, filledStar, emptyStar };
    }


  const __template = function({ state }) {
    return [  
    h("form", {"onsubmit": this.submit.bind(this)}, [
      h("div", {"class": `my-2`}, [
        h("span", {}, `Nota: `),
        ((Array.from({ length: 5 }).map((_, i) => i + 1)).map((n) => (h("img", {"src": state.pointsGiven >= n ? state.filledStar : state.emptyStar, "data-points": n, "onclick": this.starClicked.bind(this), "width": `42`, "class": `inline-block mr-2 ${state.pointsGiven < n ? 'dark:invert' : ''} cursor-pointer`}, ""))))
      ]),
      h("div", {}, [
        h("span", {}, `Se quiser, deixe uma mensagem:`),
        h("textarea", {"rows": `5`, "class": `w-full`, "value": state.message, "onchange": this.onInputChange.bind(this), "name": `message`, "maxlength": `1000`}, "")
      ]),
      h("div", {"class": `text-center my-4`}, [
        h("button", {"type": `submit`, "class": `btn`}, `Enviar`)
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

  
