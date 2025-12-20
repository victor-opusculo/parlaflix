
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("div", {"class": `my-4`}, [
    h("button", {"class": `btn`, "type": `button`, "onclick": this.openDialog.bind(this)}, `Nova presença`),
    h("dialog", {"class": `m-auto`, "id": `diagMarkPresence`}, [
    h("form", {"onsubmit": this.onSubmit.bind(this), "class": `text-center min-w-[350px] p-4 dark:text-white dark:bg-zinc-800`}, [
    h("ext-label", {"label": `Marcar presença para`}, [
    h("select", {"onchange": this.changeOption.bind(this), "required": ``}, [
    h("option", {"value": ``}, `--- Selecione ---`),
    ((state.availableSubscriptions).map((opt) => (h("option", {"value": `${opt.id}`, "selected": opt === state.selectedOption}, `${opt.name} (${opt.email})`))))
])
]),
    h("button", {"class": `btn my-4 mr-2`, "type": `submit`}, `Marcar`),
    h("button", {"class": `btn my-4`, "type": `button`, "onclick": this.closeDialog.bind(this)}, `Cancelar`)
])
])
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
            lessonId: null,
            availableSubscriptions: [],
            selectedOption: null,
        }

        openDialog()
        {
            document.getElementById("diagMarkPresence")?.showModal();
        }

        closeDialog()
        {
            document.getElementById("diagMarkPresence")?.close();
        }

        onSubmit(e)
        {
            e.preventDefault();
            
            if (!this.state.selectedOption) return;

            const { selectedOption: { id }, lessonId } = this.state;

            const body = JSON.stringify({ studentId: id, lessonId });
            const headers = new Headers({ 'Content-Type': "application/json" });

            this.closeDialog();

            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl(`/administrator/panel/presences/mark`), { body, headers, method: 'POST' })
            .then(res => res.json())//.then(console.log);
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ret, json]) =>
            {
                if (json.success)
                    window.location.reload();
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, String(reason)));
        }

        changeOption(e)
        {
            this.render({ ...this.state, selectedOption: this.state.availableSubscriptions.find(opt => opt.id == e.target.value)});
        }

        connected()
        {
            this.render({ 
                availableSubscriptions: JSON.parse(this.getAttribute("available_subscriptions") || '[]'),
                lessonId: Number.parseInt(this.getAttribute("lesson_id"))
            });
        }
    }
