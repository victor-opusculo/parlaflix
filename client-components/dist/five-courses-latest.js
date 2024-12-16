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
        data: [],
        mode: "latest",
        genUrlFn: () => ""
    };

    const methods =
    {
        fetchData()
        {
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/course/get_courses`, { mode: this.state.mode || "latest" }))
            .then(res => res.json())
            .then(({ data }) => this.render({ ...this.state, data }))
            .catch(console.error);
        },

        changeMode(e)
        {
            this.render({ ...this.state, mode: e.target.value });
            this.fetchData();
        }
    };

    function setup()
    {
        window.addEventListener('load', _ => void (document.getElementById("radioLatestCourses").checked = true));
        this.render({ ...this.state, mode: "latest", genUrlFn: Parlaflix.Helpers.URLGenerator.generatePageUrl });
        this.fetchData();
    }


  const __template = function({ state }) {
    return [  
    h("div", {"style": `margin-top: 20px; margin-left:auto; margin-right:auto; max-width: 600px; display: flex; flex-direction: column;`}, [
      h("form", {"class": `my-2`}, [
        h("input", {"type": `radio`, "name": `courseListMode`, "value": `latest`, "class": `hidden peer/btnLatest`, "id": `radioLatestCourses`, "checked": state.mode === 'latest', "onchange": this.changeMode.bind(this)}, ""),
        h("label", {"for": `radioLatestCourses`, "class": `btn brightness-50 peer-checked/btnLatest:brightness-100`}, `Mais recentes`),
        h("input", {"type": `radio`, "name": `courseListMode`, "value": `most_subscriptions`, "class": `hidden peer/btnMostSubs`, "id": `radioMostSubscribedCourses`, "checked": state.mode === 'most_subscriptions', "onchange": this.changeMode.bind(this)}, ""),
        h("label", {"class": `btn ml-2 brightness-50 peer-checked/btnMostSubs:brightness-100`, "for": `radioMostSubscribedCourses`}, `Mais inscrições`)
      ]),
      ((state.data).map((course) => (h("div", {"style": `display:grid; grid-template-columns: 200px auto; grid-template-rows: auto auto; margin-bottom: 15px;`}, [
        h("div", {"style": `margin-right: 15px;`}, [
          h("a", {"href": `${state.genUrlFn('/info/course/' + course.id)}`},             h("img", {"src": `${course.imageUrl}`, "alt": `${course.name}`}, ""))
        ]),
        h("div", {"style": `position:relative;`},           h("a", {"href": `${state.genUrlFn('/info/course/' + course.id)}`}, [
`
                ${course.name}
                `,
            h("br", {}, ""),
            h("span", {"class": `btn`, "style": `position:absolute; bottom: 0; `}, `Inscreva-se!`),
            h("span", {"class": `text-[0.7rem]`}, `${course.hours}h ${course.subscriptionNumber ? ' - ' + course.subscriptionNumber + ' inscritos' : ''}`)
          ]))
      ]))))
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

  
