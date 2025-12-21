
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  ((Math.ceil(state.totalitems / state.resultsonpage) > 0) ? h("ul", {"class": `pagination`}, [
    ((state.pagenum > 1) ? h("li", {"class": `prev`},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${state.pagenum - 1}`}, `Anterior`)) : ''),
    ((state.pagenum > 3) ? h("li", {"class": `start`},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `1`}, `1`)) : ''),
    ((state.pagenum > 3) ? h("li", {"class": `dots`}, `...`) : ''),
    (((state.pagenum - 2) > 0) ? h("li", {},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${state.pagenum - 2}`}, `${state.pagenum - 2}`)) : ''),
    (((state.pagenum - 1) > 0) ? h("li", {},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${state.pagenum - 1}`}, `${state.pagenum - 1}`)) : ''),
    h("li", {"class": `currentPageNum`},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${state.pagenum}`}, `${state.pagenum}`)),
    (((state.pagenum + 1) < (Math.ceil(state.totalitems / state.resultsonpage) + 1)) ? h("li", {},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${state.pagenum + 1}`}, `${state.pagenum + 1}`)) : ''),
    (((state.pagenum + 2) < (Math.ceil(state.totalitems / state.resultsonpage) + 1)) ? h("li", {},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${state.pagenum + 2}`}, `${state.pagenum + 2}`)) : ''),
    ((state.pagenum < (Math.ceil(state.totalitems / state.resultsonpage) - 2)) ? h("li", {"class": `dots`}, `...`) : ''),
    ((state.pagenum < (Math.ceil(state.totalitems / state.resultsonpage) - 2)) ? h("li", {"class": `end`},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${Math.ceil(state.totalitems / state.resultsonpage)}`}, `${Math.ceil(state.totalitems / state.resultsonpage)}`)) : ''),
    ((state.pagenum < Math.ceil(state.totalitems / state.resultsonpage)) ? h("li", {"class": `next`},     h("a", {"href": `#`, "onclick": this.changePage.bind(this), "data-topage": `${state.pagenum + 1}`}, `Próxima`)) : '')
]) : '')]
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
            totalitems: 0,
            resultsonpage: 20,
            pagenum: 1,
            changepagecallback: null
        }

   
        changePage(e)
        {
            e.preventDefault();
            const toPage = e.target.getAttribute('data-topage');
            if (typeof this.state.changepagecallback === "function")
                this.state.changepagecallback(toPage);
        }
    }
