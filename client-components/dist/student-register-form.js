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
        fullname: '',
        email: '',
        telephone: '',
        institution: '',
        instrole: '',
        password: '',
        password2: '',
        timezone: 'America/Sao_Paulo',
        is_abel_member: false,
        lgpdConsentCheck: false,
        lgpdtermversion: 0,
        lgpdTermText: '',
        slotId: ''
    };

    const methods =
    {
        fieldChanged(e)
        {
            this.render({ ...this.state, [ e.target.name ]: e.target.value });
        },

        consentChecked(e)
        {
            this.render({ ...this.state, lgpdConsentCheck: e.target.checked });
        },

        memberChecked(e)
        {
            this.render({ ...this.state, is_abel_member: e.target.checked });
        },

        showLgpd()
        {
            document.getElementById('lgpdTermDialog')?.showModal();
        },

        submit(e)
        {
            this.render({...this.state, lgpdTermText: document.getElementById('lgpdTermForm')?.elements['lgpdTerm']?.value ?? '***'});
            e.preventDefault();

            if (this.state.password !== this.state.password2)
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.info, "As senhas não coincidem!");
                return;
            }

            const data = {};
            for (const prop in this.state)
                data['students:' + prop] = this.state[prop];

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl('/student/register'), { method: 'POST', headers, body })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(Parlaflix.Helpers.URLGenerator.goToPageOnSuccess('/student/login'))
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
            
        }
    };


  const __template = function({ state }) {
    return [  
    h("form", {"class": `mx-auto max-w-[700px]`, "onsubmit": this.submit.bind(this)}, [
      h("ext-label", {"label": `Nome completo`}, [
        h("input", {"type": `text`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `fullname`, "value": state.fullname, "oninput": this.fieldChanged.bind(this)}, "")
      ]),
      h("ext-label", {"label": `E-mail`}, [
        h("input", {"type": `email`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `email`, "value": state.email, "oninput": this.fieldChanged.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Telefone`}, [
        h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `telephone`, "value": state.telephone, "oninput": this.fieldChanged.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Instituição`}, [
        h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `institution`, "value": state.institution, "oninput": this.fieldChanged.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Cargo`}, [
        h("input", {"type": `text`, "class": `w-full`, "maxlength": `140`, "name": `instrole`, "value": state.instrole, "oninput": this.fieldChanged.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Senha`}, [
        h("input", {"type": `password`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `password`, "value": state.password, "oninput": this.fieldChanged.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Confirme a senha`}, [
        h("input", {"type": `password`, "required": ``, "class": `w-full`, "maxlength": `140`, "name": `password2`, "value": state.password2, "oninput": this.fieldChanged.bind(this)}, "")
      ]),
      h("ext-label", {"label": `Seu fuso horário`}, [
        h("select", {"onchange": this.fieldChanged.bind(this), "name": `timezone`}, [
          ((Parlaflix.Time.TimeZones).map((dtz) => (h("option", {"value": dtz, "selected": dtz === 'America/Sao_Paulo'}, `${dtz}`))))
        ])
      ]),
      h("ext-label", {"reverse": `1`, "label": `Sou associado da ABEL`}, [
        h("input", {"type": `checkbox`, "value": `1`, "checked": state.is_abel_member, "onchange": this.memberChecked.bind(this)}, "")
      ]),
      h("div", {"class": `mt-4`}, [
`
            Concorda com o termo de consentimento para uso dos seus dados pessoais?
            `,
        h("button", {"type": `button`, "class": `btn`, "onclick": this.showLgpd.bind(this)}, `Ler`)
      ]),
      h("ext-label", {"reverse": `1`, "label": `Concordo`}, [
        h("input", {"type": `checkbox`, "required": ``, "value": `${state.lgpdTermVersion}`, "checked": state.lgpdConsentCheck, "onchange": this.consentChecked.bind(this)}, "")
      ]),
      h("div", {"class": `text-center mt-4`}, [
        h("button", {"class": `btn`, "type": `submit`}, `Concluir`)
      ])
    ]),
    h("dialog", {"id": `lgpdTermDialog`, "class": `md:w-[700px] w-screen h-screen backdrop:bg-gray-700/60 p-4 bg-neutral-100 dark:bg-neutral-800`}, [
      h("form", {"id": `lgpdTermForm`, "method": `dialog`}, [
        h("slot", {"id": `${state.slotId}`}, ""),
        h("div", {"class": `text-center my-4`}, [
          h("button", {"class": `btn`, "type": `submit`}, `Fechar`)
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

  
