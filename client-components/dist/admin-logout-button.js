
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("button", {"class": `btn`, "onclick": this.logout.bind(this), "type": `button`}, `Sair`)]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "/--file/assets/twoutput.css"
    
  `)}
}



export default class extends Lego
    {
        logout()
        {
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl('/administrator/logout'))
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(Parlaflix.Helpers.URLGenerator.goToPageOnSuccess('/admin/login', {}))
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }
    };
