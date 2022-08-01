// @ts-check

/**
 * Shorthand for `document.getElementById`
 *
 * @param {String} id - The selector's id
 *
 * @returns {Element|HTMLElement|null} - The selected element
 */
function byId(id) {
  return document.getElementById(id);
}
/**
 * Shorthand for `document.getElementsByClassName`
 *
 * @param {String} selClass - The selector's class
 * @param {Element|HTMLElement|Document} [parent=document] - Parent element
 *
 * @returns {HTMLCollectionOf<Element>} - The selected elements
 */

function byClass(selClass, parent = document) {
  return parent.getElementsByClassName(selClass);
}
/**
 * Shorthand for `document.querySelector`
 *
 * @param {String} selector - Selector
 * @param {Element|HTMLElement|Document} [parent=document] - Parent element
 *
 * @returns {Element|HTMLElementTagNameMap|SVGElementTagNameMap|null} - The selected element
 */

function query(selector, parent = document) {
  return parent.querySelector(selector);
}
/**
 * Shorthand for `document.querySelectorAll`
 *
 * @param {String} selector - Selector
 * @param {Element|HTMLElement|Document} [parent=document] - Parent element
 *
 * @returns {NodeList} - The selected element
 */

function queryAll(selector, parent = document) {
  return parent.querySelectorAll(selector);
}

// @ts-check

/**
 * Foreach polyfill for NodeList and HTMLCollection
 * https://toddmotto.com/ditch-the-array-foreach-call-nodelist-hack/
 *
 * @param {Array<any>|NodeList|HTMLCollection} els - A list of elements
 * @param {foreachCB} fn - Callback containing ( value, index ) as arguments
 * @param {Function} [scope] - Scope
 */
function forEachHTML(els, fn, scope) {
  for (let i = 0, numEls = els.length; i < numEls; i++) fn.call(scope, els[i], i);
}
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

function nextFrame(fn) {
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      // @ts-ignore
      fn.call();
    });
  });
}
/**
 * Gets an element top position
 *
 * @param {Element|HTMLElement} el - An HTML element
 * @param {Element|HTMLElement} [topEl=document.body] - Wrapping element
 *
 * @returns {Number} Element's top position
 */

function getTopPos(el, topEl = document.body) {
  return el.getBoundingClientRect().top - topEl.getBoundingClientRect().top;
}
/**
 * Gets an element left position
 *
 * @param {Element|HTMLElement} el - An HTML element
 * @param {Element|HTMLElement} [topEl=document.body] - Wrapping element
 *
 * @returns {Number} Element's left position
 */

function getLeftPos(el, topEl = document.body) {
  return el.getBoundingClientRect().left - topEl.getBoundingClientRect().left;
}
/**
 * Similar to jQuery `$( el ).index()`
 * index start at 0
 *
 * @param {Element|HTMLElement} el - An HTML element
 *
 * @returns {Number} - The element's index
 */

function getElementIndex(el) {
  let index = 0; // @ts-ignore

  while (el = el.previousElementSibling) index++;

  return index;
}

// @ts-check
/**
 * Shorthand for `element.classList.add`, works with multiple nodes
 *
 * @param {Element|HTMLElement|HTMLCollection|NodeList} el - A list of elements
 * @param {...String} classes - Classes to add
 */

function addClass(el, ...classes) {
  // @ts-ignore
  if (el.length === undefined) {
    // @ts-ignore
    addClassEl(el, ...classes);
  } else {
    // @ts-ignore
    forEachHTML(el, currEl => {
      addClassEl(currEl, ...classes);
    });
  }
  /**
   * Adds classes to a single element
   *
   * @param {Element|HTMLElement} elem - An HTML element
   * @param {...String} remClass - Classes to add
   */


  function addClassEl(elem, ...remClass) {
    remClass.forEach(singleClass => {
      elem.classList.add(singleClass);
    });
  }
}
/**
 * Shorthand for `element.classList.remove`, works with multiple nodes
 *
 * @param {Element|HTMLElement|HTMLCollection|NodeList} el - A list of elements
 * @param {...String} classes - Classes to remove
 */

function removeClass(el, ...classes) {
  // @ts-ignore
  if (el.length === undefined) {
    // @ts-ignore
    removeClassEl(el, ...classes);
  } else {
    // @ts-ignore
    forEachHTML(el, currEl => {
      removeClassEl(currEl, ...classes);
    });
  }
  /**
   * Removes classes to a single element
   *
   * @param {Element|HTMLElement} elem - An HTML element
   * @param {...String} remClass - Classes to remove
   */


  function removeClassEl(elem, ...remClass) {
    remClass.forEach(singleClass => {
      elem.classList.remove(singleClass);
    });
  }
}
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

function hasClass(el, ...classes) {
  let hasCls = false; // @ts-ignore

  if (el.length === undefined) {
    // @ts-ignore
    hasCls = hasClassEl(el, ...classes);
  } else {
    let numClasses = 0; // @ts-ignore

    forEachHTML(el, currEl => {
      if (hasClassEl(currEl, ...classes)) numClasses++;
    }); // @ts-ignore

    hasCls = numClasses === el.length;
  }

  return hasCls;
  /**
   * Checks if an element has a class or not
   *
   * @param {Element|HTMLElement} elem - An HTML element
   * @param {...String} hasClasses - Classes to check the presence of
   *
   * @returns {Boolean} - The element has the class
   */

  function hasClassEl(elem, ...hasClasses) {
    let numClasses = 0;
    hasClasses.forEach(cls => {
      if (elem.classList.contains(cls)) numClasses++;
    });
    return numClasses === hasClasses.length;
  }
}

// @ts-check

/**
 * Shorthand for `element.addEventListener`
 *
 * @param {Element|HTMLElement|Window|Document|MediaQueryList} el - A list of elements
 * @param {String} ev - Event's name
 * @param {EventListenerOrEventListenerObject} fn - Event's function
 * @param {Object} [opts] - Optional event options
 */
function addEvent(el, ev, fn, opts) {
  el.addEventListener(ev, fn, opts);
}
/**
 * Shorthand for `element.removeEventListener`
 *
 * @param {Element|HTMLElement|Window|Document|MediaQueryList} el - A list of elements
 * @param {String} ev - Event's name
 * @param {EventListenerOrEventListenerObject} fn - Event's function
 * @param {Object} [opts] - Optional event options
 */

function removeEvent(el, ev, fn, opts) {
  el.removeEventListener(ev, fn, opts);
}

// @ts-check

/**
 * Shorthand for `element.getAttribute`
 *
 * @param {Element|HTMLElement} el - An HTML element
 * @param {String} attr - The attribute to retrieve
 *
 * @returns {String|null} - The attribute's value
 */
function getAttr(el, attr) {
  return el.getAttribute(attr);
}
/**
 * Shorthand for `element.setAttribute`
 *
 * @param {Element|HTMLElement} el - An HTML element
 * @param {String} attr - The attribute to retrieve
 * @param {String} val - The value to set to the attribute
 */

function setAttr(el, attr, val) {
  el.setAttribute(attr, val);
}
/**
 * Shorthand for `element.removeAttribute`
 *
 * @param {Element|HTMLElement} el - An HTML element
 * @param {String} attr - The attribute to remove
 */

function remAttr(el, attr) {
  el.removeAttribute(attr);
}
/**
 * Shorthand for `element.hasAttribute`
 *
 * @param {Element|HTMLElement} el - An HTML element
 * @param {String} attr - The attribute to check the existance of
 *
 * @returns {Boolean} - Whether the attribute exists
 */

function hasAttr(el, attr) {
  return el.hasAttribute(attr);
}

export { addClass, addEvent, byClass, byId, forEachHTML, getAttr, getElementIndex, getLeftPos, getTopPos, hasAttr, hasClass, nextFrame, query, queryAll, remAttr, removeClass, removeEvent, setAttr };
