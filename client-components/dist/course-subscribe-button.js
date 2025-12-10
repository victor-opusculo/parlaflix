
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("div", {"class": `text-center p-8`}, [
    h("button", {"class": `btn`, "type": `button`, "onclick": this.onClick.bind(this)}, `Inscrever-se`)
])]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "./assets/twoutput.css"
    
  `)}
}



export default class extends Lego
    {
        state = 
        {
            courseid: null
        }

    
        onClick()
        {
            if (!this.state.courseid) return;

            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/student/subscribe/${this.state.courseid}`), { method: 'POST' })
            .then(res => res.json())
           .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(Parlaflix.Helpers.URLGenerator.goToPageOnSuccess('student/panel/subscription'))
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }
    }
