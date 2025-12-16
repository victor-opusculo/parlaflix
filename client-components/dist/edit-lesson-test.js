
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("form", {"onsubmit": this.submit.bind(this)}, [
    h("fieldset", {"class": `fieldset`}, [
    h("legend", {}, `Informações do Questionário`),
    h("ext-label", {"label": `Nome`},     h("input", {"type": `text`, "name": `name`, "value": state.name, "onchange": this.changeField.bind(this), "required": ``}, "")),
    h("ext-label", {"label": `Texto introdutório`, "linebreak": ``}, [
    h("textarea", {"class": `w-full`, "type": `text`, "name": `presentation_text`, "value": state.presentation_text, "onchange": this.changeField.bind(this), "rows": `5`}, "")
]),
    h("ext-label", {"label": `Nota mínima para aprovação`}, [
    h("input", {"type": `number`, "min": `0`, "max": `100`, "step": `1`, "name": `min_percent_for_approval`, "value": state.min_percent_for_approval, "onchange": this.changeField.bind(this), "required": ``}, ""),
` %`
])
]),
    h("fieldset", {"class": `fieldset`}, [
    h("legend", {}, `Questões`),
    ((Array.isArray(state.test_data.questions)) ? h("ol", {"class": `list-decimal pl-8`}, [
    ((state.test_data.questions).map((quest, qi) => (h("li", {"class": `list-item`}, [
    h("ext-label", {"label": `Enunciado`, "linebreak": ``}, [
    h("textarea", {"data-qfield": `text`, "data-qi": `${qi}`, "value": quest.text, "rows": `5`, "class": `w-full`, "onchange": this.mutateQuestion.bind(this), "required": ``}, "")
]),
    h("div", {"class": `ml-2`}, [
    h("label", {}, [
`Imagem anexa (opcional):
                            `,
    h("input", {"type": `number`, "min": `1`, "step": `1`, "onchange": this.mutateQuestion.bind(this), "value": quest.pictureMediaId || ''}, "")
]),
    h("button", {"type": `button`, "class": `btn ml-2`, "data-qi": `${qi}`, "onclick": this.searchPictureQuestion.bind(this)}, `Procurar`),
    ((this.isSearchingPicture(['question', qi])) ? h("media-client-select", {"@set-id-field-callback": this.searchPictureQuestionCallback.bind(this)}, "") : '')
]),
    h("div", {"class": `mt-4`}, [
    h("span", {"class": `font-bold block`}, `Alternativas:`),
    ((Array.isArray(quest.options)) ? h("ol", {"class": `list-[lower-alpha] pl-4`}, [
    ((quest.options).map((opt, oi) => (h("li", {"class": `list-item mb-2`}, [
    h("div", {"class": `flex flex-row`}, [
    h("input", {"class": `grow mr-2`, "type": `text`, "data-qi": `${qi}`, "data-oi": `${oi}`, "data-ofield": `text`, "value": opt.text, "onchange": this.mutateOption.bind(this)}, ""),
    h("button", {"type": `button`, "class": `shrink btn mr-2`, "data-qi": `${qi}`, "data-oi": `${oi}`, "onclick": this.searchPictureOption.bind(this)}, `Figura: ${opt.pictureMediaId || 'Nenhuma'}`),
    h("label", {"class": `shrink mr-2`}, [
    h("input", {"type": `checkbox`, "data-qi": `${qi}`, "data-oi": `${oi}`, "checked": opt.isCorrect, "onchange": this.mutateCorrectAnswers.bind(this)}, ""),
` correta
                                    `
]),
    h("button", {"type": `button`, "class": `shrink btn min-w-[32px]`, "data-qi": `${qi}`, "data-oi": `${oi}`, "onclick": this.removeOption.bind(this)}, `×`),
    h("dialog", {"id": `quest${qi}_opt${oi}_search_picdiag`, "class": `md:w-[700px] w-screen h-screen backdrop:bg-gray-700/60 p-4 bg-neutral-100 dark:bg-neutral-800 m-auto`}, [
    ((this.isSearchingPicture(['option', qi, oi])) ? h("media-client-select", {"@set-id-field-callback": this.searchPictureOptionCallback.bind(this)}, "") : ''),
    h("div", {"class": `text-center mt-4`}, [
    h("button", {"type": `button`, "class": `btn mr-2`, "data-qi": `${qi}`, "data-oi": `${oi}`, "onclick": this.closeSearchPicDiag.bind(this)}, `Fechar`),
    h("button", {"type": `button`, "class": `btn`, "data-qi": `${qi}`, "data-oi": `${oi}`, "data-preset": ``, "onclick": this.closeSearchPicDiag.bind(this)}, `Nenhuma`)
])
])
])
]))))
]) : ''),
    h("button", {"type": `button`, "class": `btn mt-2`, "data-qi": `${qi}`, "onclick": this.addOption.bind(this)}, `+ Alternativa`)
]),
    h("div", {"class": `text-right`}, [
    h("button", {"type": `button`, "class": `btn my-2`, "data-qi": `${qi}`, "onclick": this.removeQuestion.bind(this)}, `× Remover questão`)
]),
    h("hr", {}, "")
]))))
]) : ''),
    h("button", {"class": `btn my-2`, "type": `button`, "onclick": this.addQuestion.bind(this)}, `+ Adicionar questão`)
]),
    h("div", {"class": `text-center mt-4`}, [
    h("button", {"type": `submit`, "class": `btn`}, `Salvar`)
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
            id: null,
            lesson_id: null,
            name: 'Questionário sem nome',
            presentation_text: '',
            test_data: { questions: [] },
            min_percent_for_approval: 70,

            searchPictureEnabled: null
        }

        changeField(e)
        {
            this.render({ ...this.state, [e.target.name]: e.target.value });
        }

        addQuestion()
        {
            this.render({ ...this.state, test_data: { ...this.state.test_data, questions: [ ...this.state.test_data.questions, this.questionDefault() ] }})
        }

        removeQuestion(e)
        {
            const index = Number.parseInt(e.target.dataset.qi);

            if (typeof index === "number" && this.state.test_data.questions.at(index))
                this.render({ ...this.state, test_data: { ...this.state.test_data, questions: this.state.test_data.questions.filter((_, i) => i !== index) }});  
        }

        mutateQuestion(e)
        {
            const index = Number.parseInt(e.target.dataset.qi);
            const field = e.target.dataset.qfield;

            const thisQuestion = this.state.test_data.questions.at(index);
            if (thisQuestion && field in thisQuestion)
            {
                thisQuestion[field] = e.target.value;
                this.render({ ...this.state, test_data: { ...this.state.test_data, questions: this.state.test_data.questions.with(index, thisQuestion)  }});
            }
        }

        mutateOption(e)
        {
            const questIndex = Number.parseInt(e.target.dataset.qi);
            const optIndex = Number.parseInt(e.target.dataset.oi);
            const field = e.target.dataset.ofield;

            const thisQuestion = this.state.test_data.questions.at(questIndex);
            const thisOption = thisQuestion?.options?.at(optIndex);

            if (thisQuestion && thisOption && field in thisOption)
            {
                const newOption = { ...thisOption, [field]: e.target.value };
                thisQuestion.options = thisQuestion.options.with(optIndex, newOption);

                this.render({ ...this.state, test_data: { ...this.state.test_data, questions: this.state.test_data.questions.with(questIndex, thisQuestion)  }});
            }
        }

        mutateCorrectAnswers(e)
        {
            const isCorrect = e.target.checked;
            const questIndex = Number.parseInt(e.target.dataset.qi);
            const optIndex = Number.parseInt(e.target.dataset.oi);

            const thisQuestion = this.state.test_data.questions.at(questIndex);
            const thisOption = thisQuestion?.options?.at(optIndex);

            if (thisQuestion)
            {
                const newOption = { ...thisOption, isCorrect: isCorrect };
                thisQuestion.options = thisQuestion.options.with(optIndex, newOption);

                this.render({ ...this.state, test_data: { ...this.state.test_data, questions: this.state.test_data.questions.with(questIndex, thisQuestion)  }});
            }
        }

        setQuestionPicture(qi, picId)
        {
            this.mutateQuestion({ target: { dataset: { qi, qfield: 'pictureMediaId' }, value: picId }});
            this.render({ ...this.state, searchPictureEnabled: null });
        }

        setOptionPicture(qi, oi, picId)
        {
            this.mutateOption({ target: { dataset: { qi, oi, ofield: 'pictureMediaId' }, value: picId }});
            this.render({ ...this.state, searchPictureEnabled: null });
            this.shadowRoot.getElementById(`quest${qi}_opt${oi}_search_picdiag`).close();
        }

        searchPictureQuestion(e)
        {
            const index = Number.parseInt(e.target.dataset.qi);

            if (this.isSearchingPicture([ 'question', index ]))
            {
                this.render({ ...this.state, searchPictureEnabled: null});
                return;
            }

            this.render({ ...this.state, searchPictureEnabled: ['question', index ]});
        }

        searchPictureOption(e)
        {
            const questIndex = Number.parseInt(e.target.dataset.qi);
            const optIndex = Number.parseInt(e.target.dataset.oi);

            if (this.isSearchingPicture(['option', questIndex, optIndex]))
            {
                this.render({ ...this.state, searchPictureEnabled: null});
                return;
            }

            this.render({ ...this.state, searchPictureEnabled: ['option', questIndex, optIndex ]});
            this.shadowRoot.getElementById(`quest${questIndex}_opt${optIndex}_search_picdiag`).showModal();
        }

        closeSearchPicDiag(e)
        {
            const questIndex = Number.parseInt(e.target.dataset.qi);
            const optIndex = Number.parseInt(e.target.dataset.oi);

            if (e.target.hasAttribute('data-preset'))
                this.setOptionPicture(questIndex, optIndex, e.target.getAttribute('data-preset') || null);
            else
                this.render({ ...this.state, searchPictureEnabled: null});

            this.shadowRoot.getElementById(`quest${questIndex}_opt${optIndex}_search_picdiag`).close();
        }

        addOption(e)
        {
            const questIndex = Number.parseInt(e.target.dataset.qi);
            const question = this.state.test_data.questions.at(questIndex);
            const options = question?.options;

            if (options)
            {
                question.options = [ ...options, this.optionDefault() ];

                const questions = this.state.test_data.questions.with(questIndex, question);
                this.render({ ...this.state, test_data: { ...this.state.test_data, questions } });
            }
        }

        removeOption(e)
        {
            const questIndex = Number.parseInt(e.target.dataset.qi);
            const optionIndex = Number.parseInt(e.target.dataset.oi);

            const question = this.state.test_data.questions.at(questIndex);
            const options = question?.options;

            if (options && options.at(optionIndex))
            {
                question.options = options.filter((_, i) => i !== optionIndex);

                const questions = this.state.test_data.questions.with(questIndex, question);
                this.render({ ...this.state, test_data: { ...this.state.test_data, questions } });
            }
        }

        submit(e)
        {
            e.preventDefault();

            console.log(this.state.test_data);
        }

        connected()
        {
            if (this.hasAttribute('test-data-json') && this.getAttribute('test-data-json'))
                this.render({ ...this.state, test_data: JSON.parse(this.getAttribute('test-data-json')) });
        }

        questionDefault()
        {
            return { 
                pictureMediaId: null,
                text: '',
                options: [],
                studentAnswers: null
            };
        }

        optionDefault()
        {
            return {
                pictureMediaId: null,
                text: '',
                isCorrect: false
            };
        }

        isSearchingPicture(expectedArr)
        {
            const arr1 = this.state.searchPictureEnabled;
            const arr2 = expectedArr;

            if (arr1 === arr2) return true; // Check if they are the exact same reference
            if (arr1 == null || arr2 == null) return false; // Check for null/undefined
            if (arr1.length !== arr2.length) return false; // Check if lengths are different

            for (let i = 0; i < arr1.length; i++) {
                // For arrays of objects or nested arrays, you would need a deep comparison function here
                if (arr1[i] !== arr2[i]) return false;
            }

            return true;
        }

        searchPictureQuestionCallback({ detail: { id } })
        {
            const qi = this.state.searchPictureEnabled?.at(0) === 'question' ? this.state.searchPictureEnabled?.at(1) : null;
            this.setQuestionPicture(qi, id || null);
        }

        searchPictureOptionCallback({ detail: { id }})
        {
            const qi = this.state.searchPictureEnabled?.at(0) === 'option' ? this.state.searchPictureEnabled?.at(1) : null;
            const oi = this.state.searchPictureEnabled?.at(0) === 'option' ? this.state.searchPictureEnabled?.at(2) : null;
            this.setOptionPicture(qi, oi, id || null);
        }
    }
