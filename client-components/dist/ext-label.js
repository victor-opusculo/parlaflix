
// Lego version 1.10.1
import { h, Component } from 'https://cdn.jsdelivr.net/npm/@polight/lego@1.10.1/dist/lego.min.js'

class Lego extends Component {
  useShadowDOM = true

  get vdom() {
    return ({ state }) => [
  h("label", {"class": `flex m-2 ${state.linebreak ? 'flex-col' : 'flex-row items-center'}`}, [
    ((!state.reverse) ? h("span", {"class": `shrink mr-2 text-base ${state.labelbold ? 'font-bold' : ''}`}, `${state.label}: `) : ''),
    ((!state.reverse) ? h("span", {"class": `grow text-base flex flex-row flex-wrap`}, [
    h("slot", {"class": `inline-block w-full`}, "")
]) : ''),
    ((state.reverse) ? h("span", {"class": `text-base`}, [
    h("slot", {"class": `inline-block w-full`}, "")
]) : ''),
    ((state.reverse) ? h("span", {"class": `ml-2 text-base ${state.labelbold ? 'font-bold' : ''}`}, ` ${state.label}`) : '')
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
            linebreak: false,
            reverse: false,
            labelbold: false,
            label: "...",
            slotId: ''
        }
    }
