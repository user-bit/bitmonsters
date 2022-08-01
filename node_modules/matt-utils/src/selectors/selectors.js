// @ts-check
/**
 * Shorthand for `document.getElementById`
 *
 * @param {String} id - The selector's id
 *
 * @returns {Element|HTMLElement|null} - The selected element
 */
export function byId( id ) {
	return document.getElementById( id );
}

/**
 * Shorthand for `document.getElementsByClassName`
 *
 * @param {String} selClass - The selector's class
 * @param {Element|HTMLElement|Document} [parent=document] - Parent element
 *
 * @returns {HTMLCollectionOf<Element>} - The selected elements
 */
export function byClass( selClass, parent = document ) {
	return parent.getElementsByClassName( selClass );
}


/**
 * Shorthand for `document.querySelector`
 *
 * @param {String} selector - Selector
 * @param {Element|HTMLElement|Document} [parent=document] - Parent element
 *
 * @returns {Element|HTMLElementTagNameMap|SVGElementTagNameMap|null} - The selected element
 */
export function query( selector, parent = document ) {
	return parent.querySelector( selector );
}


/**
 * Shorthand for `document.querySelectorAll`
 *
 * @param {String} selector - Selector
 * @param {Element|HTMLElement|Document} [parent=document] - Parent element
 *
 * @returns {NodeList} - The selected element
 */
export function queryAll( selector, parent = document ) {
	return parent.querySelectorAll( selector );
}