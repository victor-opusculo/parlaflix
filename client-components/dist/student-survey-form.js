
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("div", {"class": `my-2`}, [
    h("span", {}, `Nota: `),
    ((Array.from({ length: 5 }).map((_, i) => i + 1)).map((n) => (h("img", {"src": state.pointsGiven >= n ? state.filledStar : state.emptyStar, "data-points": n, "onclick": this.starClicked.bind(this), "width": `42`, "class": `inline-block mr-2 ${state.pointsGiven < n ? 'dark:invert' : ''} cursor-pointer`}, ""))))
]),
    h("div", {}, [
    h("span", {}, `Se quiser, deixe uma mensagem:`),
    h("textarea", {"rows": `5`, "class": `w-full`, "value": state.message, "onchange": this.onInputChange.bind(this), "name": `message`, "maxlength": `1000`}, "")
]),
    h("div", {"class": `text-center my-4`}, [
    h("button", {"type": `submit`, "class": `btn`}, `Enviar`)
])
])]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "/--file/assets/twoutput.css"
    
  `)}
}



export default class extends Lego
    {
        state =
        {
            subscription_id: null,
            filledStar: null,
            emptyStar: null,
            pointsGiven: 0,
            message: ""
        }

        submit(e)
        {
            e.preventDefault();

            if (this.state.pointsGiven < 1)
            {
                Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, "A nota deve ser marcada: Uma ou mais estrelas!");
                return;
            }

            const headers = new Headers({ 'Content-Type': 'application/json' });
            const body = JSON.stringify({ data: { 'course_surveys:pointsGiven': this.state.pointsGiven, 'course_surveys:message': this.state.message } });
            fetch(Parlaflix.Helpers.URLGenerator.generateApiUrl("/student/survey", { subscription_id: this.state.subscription_id ?? 0 }), { method: "POST", body, headers })
            .then(res => res.json())
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ret, jsonReturn ]) =>
            {
                if (jsonReturn.success)
                    window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl(`/student/panel/subscription/${this.state.subscription_id}`);
            })
            .catch(console.error);
        }

        starClicked(e)
        {
            const points = Number.parseInt(e.target.getAttribute('data-points'));
            this.render({ ...this.state, pointsGiven: points });
        }

        onInputChange(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        }

        connected()
        {
            const filledStar =  Parlaflix.Helpers.URLGenerator.generateFileUrl('assets/pics/star_filled.png');
            const emptyStar = Parlaflix.Helpers.URLGenerator.generateFileUrl('assets/pics/star_empty.png');

            this.render({ filledStar, emptyStar });
        }
    }
