
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this), "class": `${state.darkMode ? 'dark' : ''}`}, [
    h("div", {"class": `my-2`}, [
    h("label", {}, [
`
                Arquivo:
                `,
    h("select", {"required": ``, "onchange": this.changeFile.bind(this)}, [
    h("option", {"value": ``}, `-- Selecione --`),
    ((state.availableFiles).map((file) => (h("option", {"value": `${file}`, "checked": state.selectedItem}, `${file}`))))
])
]),
    h("button", {"type": `submit`, "class": `btn ml-2`}, `Carregar`)
])
]),
  ((state.loadedFile) ? h("textarea", {"readonly": ``, "class": `w-full font-mono`, "rows": `20`, "value": state.loadedFile}, "") : '')]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "/--file/assets/twoutput.css"
    
  `)}
}



export default class extends Lego
    {
        state = { selectedItem: null, availableFiles: [], loadedFile: "", darkMode: false }
        
        changeFile(e)
        {
            this.render({ selectedItem: e.target.value })
        }

        connected()
        {
            const afs = String(this.querySelector("[name='files']")?.innerText);
            const arrAfs = afs.split("|");
            this.render({ availableFiles: arrAfs });

            const document = window.document.documentElement;
            if (document && document.classList.contains('dark'))
                this.render({ darkMode: true });

            document.addEventListener('dark-mode-toggle', e => this.render({ darkMode: e.detail.dark ?? false }));
        
        }

        submit(e)
        {
            e.preventDefault();

            if (this.state.selectedItem)
                import(Parlaflix.functionUrl("/admin/panel/settings"))
                .then(({ fetchLog }) => fetchLog({ file: this.state.selectedItem }))
                .then(content => this.render({ loadedFile: content }))
                .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }
    }
