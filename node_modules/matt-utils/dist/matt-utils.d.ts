declare module "matt-utils" {
    /**
     * Foreach callback
     */
    export type foreachCB = (value: Element | HTMLElement, index?: number | undefined) => any;
    /**
     * Shorthand for `element.classList.add`, works with multiple nodes
     *
     * @param {Element|HTMLElement|HTMLCollection|NodeList} el - A list of elements
     * @param {...String} classes - Classes to add
     */
    export function addClass(el: Element | HTMLElement | HTMLCollection | NodeList, ...classes: string[]): void;
    /**
     * Shorthand for `element.addEventListener`
     *
     * @param {Element|HTMLElement|Window|Document|MediaQueryList} el - A list of elements
     * @param {String} ev - Event's name
     * @param {EventListenerOrEventListenerObject} fn - Event's function
     * @param {Object} [opts] - Optional event options
     */
    export function addEvent(el: Element | HTMLElement | Window | Document | MediaQueryList, ev: string, fn: EventListenerOrEventListenerObject, opts?: Object | undefined): void;
    /**
     * Shorthand for `document.getElementsByClassName`
     *
     * @param {String} selClass - The selector's class
     * @param {Element|HTMLElement|Document} [parent=document] - Parent element
     *
     * @returns {HTMLCollectionOf<Element>} - The selected elements
     */
    export function byClass(selClass: string, parent?: Element | Document | HTMLElement | undefined): HTMLCollectionOf<Element>;
    /**
     * Shorthand for `document.getElementById`
     *
     * @param {String} id - The selector's id
     *
     * @returns {Element|HTMLElement|null} - The selected element
     */
    export function byId(id: string): Element | HTMLElement | null;
    /**
     * Foreach polyfill for NodeList and HTMLCollection
     * https://toddmotto.com/ditch-the-array-foreach-call-nodelist-hack/
     *
     * @param {Array<any>|NodeList|HTMLCollection} els - A list of elements
     * @param {foreachCB} fn - Callback containing ( value, index ) as arguments
     * @param {Function} [scope] - Scope
     */
    export function forEachHTML(els: Array<any> | NodeList | HTMLCollection, fn: foreachCB, scope?: Function | undefined): void;
    /**
     * Shorthand for `element.getAttribute`
     *
     * @param {Element|HTMLElement} el - An HTML element
     * @param {String} attr - The attribute to retrieve
     *
     * @returns {String|null} - The attribute's value
     */
    export function getAttr(el: Element | HTMLElement, attr: string): string | null;
    /**
     * Similar to jQuery `$( el ).index()`
     * index start at 0
     *
     * @param {Element|HTMLElement} el - An HTML element
     *
     * @returns {Number} - The element's index
     */
    export function getElementIndex(el: Element | HTMLElement): number;
    /**
     * Gets an element left position
     *
     * @param {Element|HTMLElement} el - An HTML element
     * @param {Element|HTMLElement} [topEl=document.body] - Wrapping element
     *
     * @returns {Number} Element's left position
     */
    export function getLeftPos(el: Element | HTMLElement, topEl?: Element | HTMLElement | undefined): number;
    /**
     * Gets an element top position
     *
     * @param {Element|HTMLElement} el - An HTML element
     * @param {Element|HTMLElement} [topEl=document.body] - Wrapping element
     *
     * @returns {Number} Element's top position
     */
    export function getTopPos(el: Element | HTMLElement, topEl?: Element | HTMLElement | undefined): number;
    /**
     * Shorthand for `element.hasAttribute`
     *
     * @param {Element|HTMLElement} el - An HTML element
     * @param {String} attr - The attribute to check the existance of
     *
     * @returns {Boolean} - Whether the attribute exists
     */
    export function hasAttr(el: Element | HTMLElement, attr: string): boolean;
    /**
     * Checks if an element has a class or not.
     * If multiple elements are passed the result is true only if all
     * the elements have all the specified classes.
     *
     * @param {Element|HTMLElement|HTMLCollection|NodeList} el - A list of elements
     * @param {...String} classes - Classes to check the presence of
     *
     * @returns {Boolean} - The element has the class
     */
    export function hasClass(el: Element | HTMLElement | HTMLCollection | NodeList, ...classes: string[]): boolean;
    /**
     * Foreach callback
     *
     * @callback foreachCB
     * @param {Element|HTMLElement} value - The element
     * @param {Number} [index] - The index of the element
     */
    /**
     * Runs a function the next frame useful for effects
     * from `display:none` to `display:block` and transition
     *
     * @param {Function} fn - Callback
     */
    export function nextFrame(fn: Function): void;
    /**
     * Shorthand for `document.querySelector`
     *
     * @param {String} selector - Selector
     * @param {Element|HTMLElement|Document} [parent=document] - Parent element
     *
     * @returns {Element|HTMLElementTagNameMap|SVGElementTagNameMap|null} - The selected element
     */
    export function query(selector: string, parent?: Element | Document | HTMLElement | undefined): Element | HTMLElementTagNameMap | SVGElementTagNameMap | null;
    /**
     * Shorthand for `document.querySelectorAll`
     *
     * @param {String} selector - Selector
     * @param {Element|HTMLElement|Document} [parent=document] - Parent element
     *
     * @returns {NodeList} - The selected element
     */
    export function queryAll(selector: string, parent?: Element | Document | HTMLElement | undefined): NodeList;
    /**
     * Shorthand for `element.removeAttribute`
     *
     * @param {Element|HTMLElement} el - An HTML element
     * @param {String} attr - The attribute to remove
     */
    export function remAttr(el: Element | HTMLElement, attr: string): void;
    /**
     * Shorthand for `element.classList.remove`, works with multiple nodes
     *
     * @param {Element|HTMLElement|HTMLCollection|NodeList} el - A list of elements
     * @param {...String} classes - Classes to remove
     */
    export function removeClass(el: Element | HTMLElement | HTMLCollection | NodeList, ...classes: string[]): void;
    /**
     * Shorthand for `element.removeEventListener`
     *
     * @param {Element|HTMLElement|Window|Document|MediaQueryList} el - A list of elements
     * @param {String} ev - Event's name
     * @param {EventListenerOrEventListenerObject} fn - Event's function
     * @param {Object} [opts] - Optional event options
     */
    export function removeEvent(el: Element | HTMLElement | Window | Document | MediaQueryList, ev: string, fn: EventListenerOrEventListenerObject, opts?: Object | undefined): void;
    /**
     * Shorthand for `element.setAttribute`
     *
     * @param {Element|HTMLElement} el - An HTML element
     * @param {String} attr - The attribute to retrieve
     * @param {String} val - The value to set to the attribute
     */
    export function setAttr(el: Element | HTMLElement, attr: string, val: string): void;
}
