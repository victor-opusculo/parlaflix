
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("fieldset", {"class": `fieldset`}, [
    h("legend", {}, `Questões`),
    h("ol", {"class": `list-decimal pl-8`}, [
    ((state.test_data.questions).map((quest, qi) => (h("li", {}, [
    h("div", {"class": `whitespace-pre-line mb-2`}, `${quest.text}`),
    ((quest.pictureMediaId) ? h("div", {"class": `mb-2 text-center`}, [
    h("img", {"src": `${genPictureUrl(quest.pictureMediaId, quest.pictureMediaExt)}`, "class": `max-h-[300px] h-auto w-auto`}, "")
]) : ''),
    h("div", {}, [
    h("ol", {"class": `list-[lower-alpha] pl-4`}, [
    ((quest.options).map((opt, oi) => (h("li", {}, [
    h("label", {}, [
    h("input", {"type": `${doesQuestionHaveMultipleAnswers(quest) ? 'checkbox' : 'radio'}`, "name": `quest${qi}_options_input`, "required": !doesQuestionHaveMultipleAnswers(quest), "data-qi": `${qi}`, "data-oi": `${oi}`, "onchange": this.buildAnswer.bind(this), "value": `${oi}`}, ""),
`
                                    ${opt.text}
                                    `,
    ((opt.pictureMediaId) ? h("img", {"src": `${genPictureUrl(opt.pictureMediaId, opt.pictureMediaExt)}`, "class": `max-h-[150px] h-auto w-auto`}, "") : '')
])
]))))
])
])
]))))
])
]),
    h("div", {"class": `mt-2 text-center`}, [
    h("button", {"class": `btn`, "type": `submit`}, `Enviar`)
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
            id: null,
            subscription_id: null, 
            lesson_id: null,
            student_id: null,
            test_data: { questions: [] },

            answers: []
        }

        buildAnswer(e)
        {
            const checked = e.target.checked ?? false;
            const qi = Number.parseInt(e.target.dataset.qi);
            const oi = Number.parseInt(e.target.dataset.oi);
            const question = this.state.test_data.questions.at(qi);

            const oldAnswer = this.state.answers.at(qi);
            const newAnswer = question.mulipleAnswers ? [ ...oldAnswer ].with(oi, checked) : Array.from({ length: oldAnswer.length }).fill(false).with(oi, checked);

            this.render({ ...this.state, answers: this.state.answers.with(qi, newAnswer) });
        }

        submit(e)
        {
            e.preventDefault();

            const data = this.state;

            import(Parlaflix.functionUrl(`/student/panel/subscription`))
            .then(({ receiveTestAnswers }) => receiveTestAnswers(data))
            //.then(console.log);
            .then(Parlaflix.Alerts.pushFromJsonResult)
            .then(([ret, jsonDecoded]) =>
            {
                if (jsonDecoded.success)
                    window.location.href = Parlaflix.Helpers.URLGenerator.generatePageUrl(`/student/panel/subscription/${this.state.subscription_id}`);
            })
            .catch(Parlaflix.Alerts.pushError("Erro ao enviar respostas!"));
        }

        connected()
        {
            if (this.querySelector(`[name="test-data-json"]`))
            {
                const TestDataJson = this.querySelector(`[name="test-data-json"]`).innerText;
                const testData = JSON.parse(TestDataJson);
                const answersArray = [];

                for (const quest of testData.questions)
                    answersArray.push(Array.from({ length: quest.options.length || 0 }).fill(false));

                this.render({ ...this.state, test_data: testData, answers: answersArray });
            }    
        }
    }

    const genPictureUrl = (id, ext) =>
    {
        return Parlaflix.Helpers.URLGenerator.generateFileUrl(`/uploads/media/${id}.${ext}`);
    };

    const doesQuestionHaveMultipleAnswers = (quest) =>
    {
        return quest.mulipleAnswers;
    }
