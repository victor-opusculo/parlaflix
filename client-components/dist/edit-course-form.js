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
        name: '',
        presentation_html: '',
        cover_image_media_id: null,
        hours: 0,
        certificate_text: '',
        min_points_required: 1,
        is_visible: 1,
        
        categoriesAvailable: [],

        lessons_json: '[]',
        categories_ids_json: '[]',
        
        lessons: [],
        categories_ids: [],
        deleted_lessons_ids: [],
        searchMedia: { enabled: false, pageNum: 1, dataRows: [], allCount: 0, resultsOnPage: 20, q: '' },
    };

    const methods =
    {
        changeField(e)
        {
            if (e.target.type === 'checkbox')
                this.render({ ...this.state, [e.target.name]: Number(e.target.checked) });
            else
                this.render({ ...this.state, [e.target.name]: e.target.value });
        },

        searchBtnClicked(e)
        {
            this.fetchMedias();
            this.render({ ...this.state, searchMedia: { ...this.state.searchMedia, enabled: !this.state.searchMedia.enabled } });
        },

        fetchMedias(page = 1, query = '')
        {
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/administrator/panel/media", { page_num: page, results_on_page: this.state.searchMedia.resultsOnPage, q: query }))
            .then(res => res.json())
            .then(json =>
            {
                if (json.success && json.data && json.data.dataRows && typeof json.data.allCount === "number")
                {
                    const transformed = json.data.dataRows.map( m => (
                        {
                            'ID': String(m.id),
                            'Nome': m.name,
                            'Descrição': m.description?.substring(0, 80) ?? '',
                            'Extensão': m.file_extension,
                            'Prévia': { type: 'image', src: Parlaflix.Helpers.URLGenerator.generateFileUrl(`uploads/media/${m.id}.${m.file_extension}`), width: 64 }
                        })
                    );
                    this.render({ ...this.state, searchMedia: { ...this.state.searchMedia, pageNum: page, dataRows: transformed, allCount: json.data.allCount, q: query } });
                }
            });

        },

        mediaPageChange(page = 1)
        {
            this.fetchMedias(page, this.state.searchMedia.q);
        },

        searchKeyword(query = '')
        {
            this.fetchMedias(this.state.searchMedia.pageNum, query);
        },

        setMediaId(id)
        {
            this.render({ ...this.state, cover_image_media_id: Number(id) });
        },

        addLesson(e)
        {
            this.render({ ...this.state, lessons: [...this.state.lessons, 
                { 
                    id: null, 
                    course_id: this.state.id,
                    index: this.state.lessons?.length + 1,
                    title: '',
                    presentation_html: '',
                    video_host: 'youtube',
                    video_url: '',
                    completion_password: '',
                    completion_points: 1
                }
            ]});
        },

        removeLesson(index)
        {
            const newLessons = this.state.lessons
                .filter( l => l.index != index )
                .map( (l, newIndex) => ({...l, index: newIndex + 1 }) );
            
            this.render({ ...this.state, lessons: newLessons });
        },

        moveLesson(index, direction)
        {
            if (direction === 'up' && index > 1)
            {
                let moved = this.state.lessons[index - 1];
                let replaced = this.state.lessons[index - 2];
                this.state.lessons[index - 2] = moved;
                this.state.lessons[index - 1] = replaced;
            }
            else if (direction === 'down' && index < this.state.lessons.length)
            {
                let moved = this.state.lessons[index - 1];
                let replaced = this.state.lessons[index];
                this.state.lessons[index] = moved;
                this.state.lessons[index - 1] = replaced;
            }

            const newLessons = this.state.lessons.map( (l, newIndex) => ({ ...l, index: newIndex + 1 }) );

            this.render({ ...this.state, lessons: newLessons });
        },

        mutateLesson(index, field, value)
        {
            const lessons = this.state.lessons;
            const found = lessons.find( l => l.index == index );
            found[field] = value;

            this.render({ ...this.state, lessons: lessons });
        },

        submit(e)
        {
            e.preventDefault();

            const data = {...this.state};
            delete data.categoriesAvailable;
            delete data.lessons;
            delete data.categories_ids;
            delete data.searchMedia;

            const { deleted_lessons_ids, lessons_json, categories_ids_json, ...data2 } = data;

            const outputData = {};
            for (const prop in data2)
                outputData['courses:' + prop] = data2[prop];

            Object.assign(outputData, { deleted_lessons_ids, lessons_json, categories_ids_json });

            console.log(outputData);
        }
    };

    function setup()
    {
        this.render({ ...this.state, lessons: JSON.parse(this.getAttribute('lessons_json') || '[]'), categoriesAvailable: JSON.parse(this.getAttribute('categories_available_json') || '[]') });
    }


  const __template = function({ state }) {
    return [  
    h("form", {"onsubmit": this.submit.bind(this)}, [
      h("ext-label", {"label": `Visível (publicado)`, "reverse": `1`}, [
        h("input", {"type": `checkbox`, "name": `is_visible`, "value": `1`, "onchange": this.changeField.bind(this), "checked": Boolean(Number(state.is_visible))}, "")
      ]),
      h("ext-label", {"label": `Nome`}, [
        h("input", {"type": `text`, "class": `w-full`, "name": `name`, "value": state.name, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Mais informações (HTML permitido)`, "linebreak": `1`}, [
        h("textarea", {"class": `w-full`, "name": `presentation_html`, "rows": `8`, "oninput": this.changeField.bind(this)}, `${state.presentation_html}`)
      ]),
      h("ext-label", {"label": `Imagem ilustrativa (Mídia ID)`}, [
        h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `cover_image_media_id`, "value": state.cover_image_media_id, "oninput": this.changeField.bind(this)}, ""),
        h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.searchBtnClicked.bind(this)}, `Procurar`)
      ]),
      ((state.searchMedia.enabled) ? h("div", {}, [
        h("basic-search-field", {"searchkeywords": state.searchMedia.q, "searchcallback": this.searchKeyword.bind(this)}, ""),
        h("data-grid", {"datarows": state.searchMedia.dataRows, "returnidcallback": this.setMediaId.bind(this), "selectlinkparamname": `ID`}, ""),
        h("client-paginator", {"totalitems": state.searchMedia.allCount, "resultsonpage": state.searchMedia.resultsOnPage, "changepagecallback": this.mediaPageChange.bind(this), "pagenum": state.searchMedia.pageNum}, "")
      ]) : ''),
      h("ext-label", {"label": `Carga horária`}, [
        h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `hours`, "value": state.hours, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Texto para o certificado`, "linebreak": `1`}, [
        h("textarea", {"class": `w-full`, "name": `certificate_text`, "rows": `4`, "maxlength": `300`, "oninput": this.changeField.bind(this)}, `${state.certificate_text}`)
      ]),
      h("ext-label", {"label": `Mínimo de pontos necessário para aprovação`}, [
        h("input", {"type": `number`, "min": `1`, "step": `1`, "name": `min_points_required`, "value": state.min_points_required, "oninput": this.changeField.bind(this)}, "")
      ]),
      h("h2", {}, `Aulas`),
      ((state.lessons).map((lesson) => (h("edit-single-lesson", {"id": lesson.id, "index": lesson.index, "title": lesson.title, "presentation_html": lesson.presentation_html, "video_host": lesson.video_host, "video_url": lesson.video_url, "completion_password": lesson.completion_password, "completion_points": lesson.completion_points, "removelessoncallback": this.removeLesson.bind(this), "changefieldcallback": this.mutateLesson.bind(this), "movelessoncallback": this.moveLesson.bind(this)}, "")))),
      h("button", {"type": `button`, "class": `btn`, "onclick": this.addLesson.bind(this)}, `Adicionar aula`),
      h("h2", {}, `Categorias`),
      h("ul", {"class": `list-disc pl-4`}, [
        ((state.categoriesAvailable).map((cat) => (h("li", {}, [
          h("label", {}, [
            h("input", {"type": `checkbox`, "value": `${cat.id}`}, ""),
` ${cat.title}
                `
          ])
        ]))))
      ]),
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

  
