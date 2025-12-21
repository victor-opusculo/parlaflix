
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("button", {"type": `button`, "class": `btn`, "onclick": this.submit.bind(this)}, `Cancelar inscrição`)]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "/--file/assets/twoutput.css"
    
  `)}
}



export default class extends Lego
    {
        state = { subscription_id: null }

        submit(e)
        {
            Parlaflix.Alerts.push(Parlaflix.Alerts.types.question, "Tem certeza de que deseja remover sua inscrição neste curso? Qualquer dado de presença será removido também.")
            .then(returnValue => returnValue === 'yes')
            .then(confirm => 
            {
                if (confirm)
                    import(Parlaflix.functionUrl("/student/panel/subscription"))
                    .then(({ removeSubscription }) => removeSubscription({ id: this.state.subscription_id }))
                    .then(Parlaflix.Alerts.pushFromJsonResult)
                    .then(([ret, jsonDecoded]) =>
                    {
                        if (jsonDecoded.success)
                            window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl("/student/panel/subscription/");
                    })
                    .catch(Parlaflix.Alerts.pushError("Erro ao solicitar remoção!"));
            });
        }
    }
