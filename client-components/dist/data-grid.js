
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  ((!(state.datarows?.length)) ? h("p", {}, `Não há dados disponíveis.`) : ''),
  ((state.datarows?.length > 0) ? h("table", {}, [
    h("thead", {}, [
    h("tr", {}, [
    ((Object.keys(state.datarows[0])).map((header) => (h("th", {}, `
                    ${header}
                `)))),
    ((state.returnidcallback) ? h("th", {}, `Selecionar`) : '')
])
]),
    h("tbody", {}, [
    ((state.datarows).map((row) => (h("tr", {}, [
    ((Object.keys(row)).map((header) => (h("td", {"data-th": `${header}`}, [
    ((typeof row[header] === 'string') ? h("span", {}, `${row[header]}`) : ''),
    ((typeof row[header] === 'object' && row[header].type === 'image') ? h("img", {"src": `${row[header].src}`, "width": `${row[header].width}`}, "") : '')
])))),
    ((state.returnidcallback) ? h("td", {}, [
    h("a", {"class": `link text-lg`, "onclick": this.selectClick.bind(this), "data-th": `Selecionar`, "data-id": `${row[state.selectlinkparamname]}`, "href": `#`}, `Selecionar`)
]) : '')
]))))
])
]) : '')]
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
            datarows: [],
            columnstohide: [],
            returnidcallback: null,
            selectlinkparamname: 'id'
        }

        selectClick(e)
        {
            e.preventDefault();
            const param = e.target.getAttribute('data-id');
            this.state.returnidcallback(param);
        }
        
    }
