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
        id: 0,
        course_id: 0,
        index: 1,
        title: '',
        presentation_html: '',
        live_meeting_url: '',
        live_meeting_datetime: '',
        liveMeetingDate: '',
        liveMeetingTime: '',
        video_host: 'youtube',
        video_url: '',
        completion_password: '',
        completion_points: 1,
        timezone: ''
    };

    const methods = 
    {
        changeField(e)
        {
            document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), e.target.value);
        },

        dateFieldChanged(e)
        {
            const dat = e.target.value;
            this.render({ ...this.state, liveMeetingDate: dat });

            if (!isNaN(new Date(`${dat} ${this.state.liveMeetingTime}`).valueOf()))
            {
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), `${dat} ${this.state.liveMeetingTime}`);
            }
            else if (!this.state.liveMeetingTime && !this.state.liveMeetingDate)
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), null);
        },

        timeFieldChanged(e)
        {
            const tim = e.target.value;
            this.render({ ...this.state, liveMeetingTime: tim });

            if (!isNaN(new Date(`${this.state.liveMeetingDate} ${tim}`).valueOf()))
            {
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), `${this.state.liveMeetingDate} ${tim}`);
            }
            else if (!this.state.liveMeetingTime && !this.state.liveMeetingDate)
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), null);
        },

        pasteVideoCode(e)
        {
            window.navigator.clipboard.readText()
            .then(str => new URL(str))
            .then(url => 
            {
                const host = 'youtube';
                const videoCode = url.searchParams?.get('v') ?? '';
                document.querySelector('edit-course-form').mutateLesson(this.state.index, 'video_host', host);
                document.querySelector('edit-course-form').mutateLesson(this.state.index, 'video_url', videoCode);
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, reason instanceof TypeError ? 'O texto na sua área de transferência não é uma URL válida!' : String(reason)));
        },

        deleteClicked(e)
        {
            document.querySelector('edit-course-form').removeLesson(e.target.getAttribute('data-lesson-index'));
        },

        moveUpClicked(e)
        {
            document.querySelector('edit-course-form').moveLesson(e.target.getAttribute('data-lesson-index'), 'up');
        },

        moveDownClicked(e)
        {
            document.querySelector('edit-course-form').moveLesson(e.target.getAttribute('data-lesson-index'), 'down');
        }
    };

    function setup()
    {
        const date = this.getAttribute('live_meeting_datetime') ? new Date(this.getAttribute('live_meeting_datetime')) : new Date();
        const dateStr = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, 0)}-${String(date.getDate()).padStart(2, 0)}`;
        const timeStr = `${String(date.getHours()).padStart(2, 0)}:${String(date.getMinutes()).padStart(2, 0)}:${String(date.getSeconds()).padStart(2, 0)}`;

        this.state.liveMeetingDate = dateStr;
        this.state.liveMeetingTime = timeStr;
    }


  const __template = function({ state }) {
    return [  
    h("fieldset", {"class": `fieldset`}, [
      h("legend", {}, `Aula nº ${state.index}`),
      h("ext-label", {"label": `Título`}, [
        h("input", {"type": `text`, "class": `w-full`, "maxlength": `280`, "data-fieldname": `title`, "required": `title`, "value": state.title, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Mais informações (HTML permitido)`, "linebreak": `1`}, [
        h("textarea", {"data-fieldname": `presentation_html`, "class": `w-full`, "rows": `4`, "maxlength": `1000`, "oninput": this.changeField.bind(this), "value": state.presentation_html}, "")
      ]),
      h("ext-label", {"label": `Link da sala (aula ao vivo)`}, [
        h("input", {"type": `text`, "data-fieldname": `live_meeting_url`, "class": `w-full`, "maxlength": `140`, "oninput": this.changeField.bind(this), "value": state.live_meeting_url}, "")
      ]),
      h("ext-label", {"label": `Data e hora da aula ao vivo`}, [
        h("input", {"type": `date`, "data-fieldname": `live_meeting_datetime`, "value": state.liveMeetingDate, "onchange": this.dateFieldChanged.bind(this)}, ""),
        h("input", {"type": `time`, "step": `1`, "data-fieldname": `live_meeting_datetime`, "value": state.liveMeetingTime, "onchange": this.timeFieldChanged.bind(this)}, ""),
`
            (${state.timezone})
        `
      ]),
      h("div", {"class": `ml-2`}, [
`
            Hospedagem do vídeo: 
            `,
        h("label", {}, [
          h("input", {"type": `radio`, "data-fieldname": `video_host`, "name": `video_host_idx${state.index}`, "value": `youtube`, "required": ``, "checked": state.video_host === 'youtube', "onchange": this.changeField.bind(this)}, ""),
` Youtube`
        ])
      ]),
      h("ext-label", {"label": `Código do vídeo`}, [
        h("input", {"type": `text`, "data-fieldname": `video_url`, "value": state.video_url, "oninput": this.changeField.bind(this), "class": `w-[calc(100%-130px)]`}, ""),
        h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.pasteVideoCode.bind(this)}, `Colar`)
      ]),
      h("ext-label", {"label": `Senha para atestar visualização da aula`}, [
        h("input", {"type": `text`, "data-fieldname": `completion_password`, "value": state.completion_password, "required": ``, "oninput": this.changeField.bind(this), "class": `w-full`}, "")
      ]),
      h("ext-label", {"label": `Pontos pela visualização da aula`}, [
        h("input", {"type": `number`, "min": `1`, "step": `1`, "data-fieldname": `completion_points`, "value": state.completion_points, "required": ``, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("div", {"class": `text-right`}, [
        h("button", {"type": `button`, "class": `btn min-w-[64px] mr-2`, "onclick": this.moveUpClicked.bind(this), "data-lesson-index": `${state.index}`}, `↑`),
        h("button", {"type": `button`, "class": `btn min-w-[64px] mr-2`, "onclick": this.moveDownClicked.bind(this), "data-lesson-index": `${state.index}`}, `↓`),
        h("button", {"type": `button`, "class": `btn min-w-[64px]`, "onclick": this.deleteClicked.bind(this), "data-lesson-index": `${state.index}`}, `×`)
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

  
