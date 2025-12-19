
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("fieldset", {"class": `fieldset`}, [
    h("legend", {}, `Aula nº ${state.index}`),
    h("ext-label", {"label": `Título`}, [
    h("input", {"type": `text`, "class": `w-full`, "maxlength": `280`, "data-fieldname": `title`, "required": `title`, "value": state.title, "oninput": this.changeField.bind(this)}, "")
]),
    h("ext-label", {"label": `Mais informações (HTML permitido)`, "linebreak": `1`}, [
    h("textarea", {"data-fieldname": `presentation_html`, "class": `w-full`, "rows": `4`, "maxlength": `1000`, "oninput": this.changeField.bind(this), "value": state.presentation_html}, "")
]),
    h("ext-label", {"label": `Link da sala (aula ao vivo)`}, [
    h("input", {"type": `text`, "data-fieldname": `live_meeting_url`, "class": `w-full`, "maxlength": `140`, "oninput": this.changeField.bind(this), "value": state.live_meeting_url}, "")
]),
    h("ext-label", {"label": `Data e hora da aula ao vivo`}, [
    h("input", {"type": `date`, "data-fieldname": `live_meeting_datetime`, "value": state.liveMeetingDate, "onchange": this.dateFieldChanged.bind(this)}, ""),
    h("input", {"type": `time`, "step": `1`, "data-fieldname": `live_meeting_datetime`, "value": state.liveMeetingTime, "onchange": this.timeFieldChanged.bind(this)}, ""),
`
            (${state.timezone})
        `
]),
    h("div", {"class": `ml-2`}, [
`
            Hospedagem do vídeo: 
            `,
    h("label", {}, [
    h("input", {"type": `radio`, "data-fieldname": `video_host`, "name": `video_host_idx${state.index}`, "value": `youtube`, "required": ``, "checked": state.video_host === 'youtube', "oninput": this.changeField.bind(this)}, ""),
` Youtube`
])
]),
    h("ext-label", {"label": `Código do vídeo`}, [
    h("input", {"type": `text`, "data-fieldname": `video_url`, "value": state.video_url, "oninput": this.changeField.bind(this), "class": `w-[calc(100%-130px)]`}, ""),
    h("button", {"type": `button`, "class": `btn ml-2`, "onclick": this.pasteVideoCode.bind(this)}, `Colar`)
]),
    h("div", {"class": `ml-2`}, [
`
            Tipo de marcação de presença/conclusão:
            `,
    h("label", {"class": `mr-4`}, [
    h("input", {"type": `radio`, "data-fieldname": `presence_method`, "name": `presence_method_idx${state.index}`, "value": `${presenceMethod.password}`, "required": ``, "checked": state.presence_method === presenceMethod.password, "oninput": this.changeField.bind(this)}, ""),
` Senha`
]),
    h("label", {"class": `mr-4`}, [
    h("input", {"type": `radio`, "data-fieldn": ``, "data-fieldname": `presence_method`, "name": `presence_method_idx${state.index}`, "value": `${presenceMethod.test}`, "required": ``, "checked": state.presence_method === presenceMethod.test, "oninput": this.changeField.bind(this)}, ""),
` Questionário`
]),
    h("label", {"class": `mr-4`}, [
    h("input", {"type": `radio`, "data-fieldname": `presence_method`, "name": `presence_method_idx${state.index}`, "value": `${presenceMethod.test_and_password}`, "required": ``, "checked": state.presence_method === presenceMethod.test_and_password, "oninput": this.changeField.bind(this)}, ""),
` Senha e Questionário`
]),
    h("label", {}, [
    h("input", {"type": `radio`, "data-fieldname": `presence_method`, "name": `presence_method_idx${state.index}`, "value": `${presenceMethod.auto}`, "required": ``, "checked": state.presence_method === presenceMethod.auto, "oninput": this.changeField.bind(this)}, ""),
` Automático`
])
]),
    ((state.presence_method === presenceMethod.password || state.presence_method === presenceMethod.test_and_password) ? h("ext-label", {"label": `Senha para atestar visualização da aula`}, [
    h("input", {"type": `text`, "data-fieldname": `completion_password`, "value": state.completion_password, "required": ``, "oninput": this.changeField.bind(this), "class": `w-full`}, "")
]) : ''),
    ((state.presence_method === presenceMethod.test || state.presence_method === presenceMethod.test_and_password) ? h("ext-label", {"label": `Questionário`}, [
    h("strong", {"class": `italic`}, `Cadastre o questionário pela página de visualização deste curso`)
]) : ''),
    h("ext-label", {"label": `Pontos pela visualização da aula`}, [
    h("input", {"type": `number`, "min": `0`, "step": `1`, "data-fieldname": `completion_points`, "value": state.completion_points, "required": ``, "oninput": this.changeField.bind(this)}, "")
]),
    h("div", {"class": `text-right`}, [
    h("button", {"type": `button`, "class": `btn min-w-[64px] mr-2`, "onclick": this.moveUpClicked.bind(this), "data-lesson-index": `${state.index}`}, `↑`),
    h("button", {"type": `button`, "class": `btn min-w-[64px] mr-2`, "onclick": this.moveDownClicked.bind(this), "data-lesson-index": `${state.index}`}, `↓`),
    h("button", {"type": `button`, "class": `btn min-w-[64px]`, "onclick": this.deleteClicked.bind(this), "data-lesson-index": `${state.index}`}, `×`)
])
])]
  }
  get vstyle() {
    return ({ state }) => h('style', {}, `
    @import "./assets/twoutput.css"
    
  `)}
}



const presenceMethod =
    {
        password: 'password',
        test: 'test',
        test_and_password: 'test_and_password',
        auto: 'auto',
        never: 'never',

        default()
        {
            return this.password;
        }
    }

    export default class extends Lego
    {
        state =
        {
            id: 0,
            course_id: 0,
            index: 1,
            title: '',
            presentation_html: '',
            live_meeting_url: '',
            live_meeting_datetime: '',
            liveMeetingDate: '',
            liveMeetingTime: '',
            video_host: 'youtube',
            video_url: '',
            presence_method: presenceMethod.default(),
            completion_password: '',
            completion_points: 1,
            timezone: ''
        }

        changeField(e)
        {
            this.render({ ...this.state, [e.target.getAttribute('data-fieldname')]: e.target.value });
            document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), e.target.value);
        }

        dateFieldChanged(e)
        {
            const dat = e.target.value;
            this.render({ ...this.state, liveMeetingDate: dat });

            if (!isNaN(new Date(`${dat} ${this.state.liveMeetingTime}`).valueOf()))
            {
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), `${dat} ${this.state.liveMeetingTime}`);
            }
            else if (!this.state.liveMeetingTime && !this.state.liveMeetingDate)
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), null);
        }

        timeFieldChanged(e)
        {
            const tim = e.target.value;
            this.render({ ...this.state, liveMeetingTime: tim });

            if (!isNaN(new Date(`${this.state.liveMeetingDate} ${tim}`).valueOf()))
            {
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), `${this.state.liveMeetingDate} ${tim}`);
            }
            else if (!this.state.liveMeetingTime && !this.state.liveMeetingDate)
                document.querySelector('edit-course-form').mutateLesson(this.state.index, e.target.getAttribute('data-fieldname'), null);
        }

        pasteVideoCode(e)
        {
            window.navigator.clipboard.readText()
            .then(str => new URL(str))
            .then(url => 
            {
                const host = 'youtube';
                const videoCode = url.searchParams?.get('v') ?? '';
                document.querySelector('edit-course-form').mutateLesson(this.state.index, 'video_host', host);
                document.querySelector('edit-course-form').mutateLesson(this.state.index, 'video_url', videoCode);
                this.render({ ...this.state, video_host: host, video_url: videoCode });
            })
            .catch(reason => Parlaflix.Alerts.push(Parlaflix.Alerts.types.error, reason instanceof TypeError ? 'O texto na sua área de transferência não é uma URL válida!' : String(reason)));
        }

        deleteClicked(e)
        {
            document.querySelector('edit-course-form').removeLesson(e.target.getAttribute('data-lesson-index'));
        }

        moveUpClicked(e)
        {
            document.querySelector('edit-course-form').moveLesson(e.target.getAttribute('data-lesson-index'), 'up');
        }

        moveDownClicked(e)
        {
            document.querySelector('edit-course-form').moveLesson(e.target.getAttribute('data-lesson-index'), 'down');
        }

        connected()
        {
            const date = this.getAttribute('live_meeting_datetime') ? new Date(this.getAttribute('live_meeting_datetime')) : null;
            const dateStr = date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, 0)}-${String(date.getDate()).padStart(2, 0)}` : '';
            const timeStr = date ? `${String(date.getHours()).padStart(2, 0)}:${String(date.getMinutes()).padStart(2, 0)}:${String(date.getSeconds()).padStart(2, 0)}` : '';

            const newPresenceMethod = this.state.presence_method
                ? (this.state.presence_method === "$default"
                    ? presenceMethod.default()
                    : this.state.presence_method
                )
                : (this.getAttribute('presence_method') === "$default" || !this.getAttribute('presence_method')
                    ? presenceMethod.default()
                    : this.getAttribute('presence_method')
                );

            this.render({
                liveMeetingDate: dateStr,
                liveMeetingTime: timeStr,
                presence_method: newPresenceMethod
            });
            document.querySelector('edit-course-form').mutateLesson(this.state.index, 'presence_method', newPresenceMethod);
        }
    }
