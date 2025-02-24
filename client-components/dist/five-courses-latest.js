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
        restriction: "open",
        mode: "latest",
        pageNum: 1,
        genUrlFn: () => ""
    };

    const methods =
    {
        fetchData(page = 1, replaceAll = true)
        {
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/course/get_courses`, 
            { 
                mode: this.state.mode || "latest", 
                restriction: this.state.restriction || "open", 
                page_num: page,
                num_results_on_page: 5
            }))
            .then(res => res.json())
            .then(replaceAll ? this.replaceAllData.bind(this) : this.appendData.bind(this))
            .catch(console.error)
            .finally(() => this.render({ ...this.state, pageNum: page }));
        },

        replaceAllData({ data })
        {
            this.render({ ...this.state, data });
        },

        appendData({ data })
        {
            this.render({ ...this.state, data: [ ...this.state.data, ...data ] });
        },

        changeMode(e)
        {
            this.render({ ...this.state, mode: e.target.value });
            this.fetchData(1, true);
        },

        changeRestriction(e)
        {
            this.render({ ...this.state, restriction: e.target.value });
            this.fetchData(1, true);
        }
    };

    function setup()
    {
        window.addEventListener('load', _ => 
        {
            document.getElementById("radioLatestCourses").checked = true;
            window.onscroll = evt => void 
            (
                window.scrollY + window.innerHeight >= document.body.offsetHeight 
                ? this.fetchData(this.state.pageNum + 1, false)
                : undefined
            );
        });
        this.render({ ...this.state, mode: "latest", restriction: "open", genUrlFn: Parlaflix.Helpers.URLGenerator.generatePageUrl });
        this.fetchData();
    }


  const __template = function({ state }) {
    return [  
    h("div", {"style": `margin-top: 20px; display: flex; flex-direction: column;`, "class": `items-center`}, [
      h("form", {"class": `my-2`}, [
        h("select", {"onchange": this.changeRestriction.bind(this), "value": state.restriction, "class": `mr-2`}, [
          h("option", {"value": `open`}, `Aberto a todos`),
          h("option", {"value": `exclusive`}, `Exclusivos para associados`),
          h("option", {"value": `all`}, `Todos os cursos`)
        ]),
        h("input", {"type": `radio`, "name": `courseListMode`, "value": `latest`, "class": `hidden peer/btnLatest`, "id": `radioLatestCourses`, "checked": state.mode === 'latest', "onchange": this.changeMode.bind(this)}, ""),
        h("label", {"for": `radioLatestCourses`, "class": `btn brightness-50 peer-checked/btnLatest:brightness-100`}, `Mais recentes`),
        h("input", {"type": `radio`, "name": `courseListMode`, "value": `most_subscriptions`, "class": `hidden peer/btnMostSubs`, "id": `radioMostSubscribedCourses`, "checked": state.mode === 'most_subscriptions', "onchange": this.changeMode.bind(this)}, ""),
        h("label", {"class": `btn ml-2 brightness-50 peer-checked/btnMostSubs:brightness-100`, "for": `radioMostSubscribedCourses`}, `Mais inscrições`)
      ]),
      h("div", {"class": `flex flex-row flex-wrap justify-center`}, [
        ((state.data).map((course) => (h("a", {"class": `block overflow-clip relative p-2 mx-4 mb-4 h-[300px] min-w-[300px] max-w-[400px] rounded-sm border border-neutral-300 dark:border-neutral-700 hover:brightness-75`, "href": `${state.genUrlFn('/info/course/' + course.id)}`}, [
          h("div", {"class": `absolute left-0 right-0 bottom-0 top-0 w-full`}, [
            h("img", {"class": `absolute m-auto left-0 right-0 top-0 bottom-0`, "src": `${course.imageUrl}`, "alt": `${course.name}`}, "")
          ]),
          h("div", {"class": `absolute bottom-0 left-0 right-0 z-10 dark:bg-neutral-700/80 bg-neutral-300/80 p-2 text-center`}, [
            h("div", {}, `${course.name}`),
            h("div", {"class": `text-[0.7rem]`}, `${course.hours}h ${course.subscriptionNumber ? ' - ' + course.subscriptionNumber + ' inscritos' : ''}`),
            h("div", {"class": `stars5Mask w-[100px] h-[24px] inline-block text-center`}, [
              h("progress", {"class": `w-full h-full starProgressBar inline`, "min": `0`, "max": `5`, "value": `${course.surveyPoints}`}, "")
            ]),
            h("div", {"class": `btn`}, `Inscreva-se!`)
          ])
        ]))))
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

  
