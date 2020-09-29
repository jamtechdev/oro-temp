/**
 * @plugin
 * GrapesJS plugin
 *
 * Overwrite and reassing html parsing
 */
const modelAttrStart = 'data-gjs-';
const textTags = ['ul', 'ol', 'li'];
const wrappedTags = ['ul', 'ol'];

/**
 *
 * @param html
 * @param config
 * @param compTypes
 * @returns {{html: Array, css: string}}
 */
export const htmlParser = (html, config, compTypes, parserCss) => {
    const res = {
        html: [],
        css: ''
    };
    const el = document.createElement('div');
    el.innerHTML = html;

    res.html = parseNodes(el, config, compTypes);

    // Detach style tags and parse them
    if (parserCss) {
        let styleStr = '';
        const styles = el.querySelectorAll('style');
        let j = styles.length;

        while (j--) {
            styleStr = styles[j].innerHTML + styleStr;
            styles[j].parentNode.removeChild(styles[j]);
        }

        if (styleStr) {
            res.css = parserCss(styleStr);
        }
    }

    return res;
};

/**
 * Parse node style
 * @param str
 */
function parseStyle(str) {
    const result = {};
    const decls = str.split(';');

    for (let i = 0, len = decls.length; i < len; i++) {
        const decl = decls[i].trim();

        if (!decl) {
            continue;
        }

        const prop = decl.split(':');

        result[prop[0].trim()] = prop
            .slice(1)
            .join(':')
            .trim();
    }

    return result;
}

/**
 * Parse class names list
 * @param str
 * @returns {Array}
 */
function parseClass(str) {
    const result = [];
    const cls = str.split(' ');

    for (let i = 0, len = cls.length; i < len; i++) {
        const cl = cls[i].trim();

        if (!cl || cl.indexOf('gjs-') !== -1) {
            continue;
        }

        result.push(cl);
    }

    return result;
}

/**
 * Parse nodes generate component tree array
 * @param el
 * @param config
 * @param ct
 * @param parent
 * @returns {Array}
 */
function parseNodes(el, config, ct = '', parent = false) {
    const result = [];
    const nodes = el.childNodes;

    for (let i = 0, len = nodes.length; i < len; i++) {
        const node = nodes[i];
        const attrs = node.attributes || [];
        const attrsLen = attrs.length;
        const nodePrev = result[result.length - 1];
        const nodeChild = node.childNodes.length;
        let model = {};

        // Start with understanding what kind of component it is
        if (ct) {
            let obj = '';
            const type =
                node.getAttribute && node.getAttribute(`${modelAttrStart}type`);

            // If the type is already defined, use it
            if (type) {
                model = {type};
            } else {
                // Iterate over all available Component Types and
                // the first with a valid result will be that component
                for (let it = 0; it < ct.length; it++) {
                    const compType = ct[it];
                    obj = compType.model.isComponent(node);

                    if (obj) {
                        if (typeof obj !== 'object') {
                            obj = {type: compType.id};
                        }
                        break;
                    } else if (obj === 0) {
                        throw new Error();
                    }
                }

                model = obj;
            }
        }

        // Set tag name if not yet done
        if (!model.tagName) {
            model.tagName = node.tagName ? node.tagName.toLowerCase() : '';
        }

        if (model.tagName === 'style') {
            break;
        }

        if (attrsLen) {
            model.attributes = {};
        }

        // Parse attributes
        for (let j = 0; j < attrsLen; j++) {
            const nodeName = attrs[j].nodeName;
            let nodeValue = attrs[j].nodeValue;

            // Isolate attributes
            if (nodeName === 'style') {
                model.style = parseStyle(nodeValue);
            } else if (nodeName === 'class') {
                model.classes = parseClass(nodeValue);
            } else if (nodeName === 'contenteditable') {
                continue;
            } else if (nodeName === 'data-highlightable') {
                continue;
            } else if (textTags.includes(model.tagName) && nodeName === 'id') {
                continue;
            } else if (nodeName.indexOf(modelAttrStart) === 0) {
                const modelAttr = nodeName.replace(modelAttrStart, '');
                const valueLen = nodeValue.length;
                const firstChar = nodeValue && nodeValue.substr(0, 1);
                const lastChar = nodeValue && nodeValue.substr(valueLen - 1);
                nodeValue = nodeValue === 'true' ? true : nodeValue;
                nodeValue = nodeValue === 'false' ? false : nodeValue;

                // Try to parse JSON where it's possible
                // I can get false positive here (eg. a selector '[data-attr]')
                // so put it under try/catch and let fail silently
                try {
                    nodeValue =
                        (firstChar === '{' && lastChar === '}') ||
                        (firstChar === '[' && lastChar === ']')
                            ? JSON.parse(nodeValue)
                            : nodeValue;
                } catch (e) {}

                model[modelAttr] = nodeValue;
            } else {
                model.attributes[nodeName] = nodeValue;
            }
        }

        // Check for nested elements but avoid it if already provided
        if (nodeChild && !model.components) {
            // Avoid infinite nested text nodes
            const firstChild = node.childNodes[0];

            // If there is only one child and it's a TEXTNODE
            // just make it content of the current node
            if (nodeChild === 1 && firstChild.nodeType === 3) {
                !model.type && (model.type = 'text');
                model.content = firstChild.nodeValue;
            } else {
                model.components = parseNodes(node, config, ct, model);
            }
        }

        // Check if it's a text node and if could be moved to the prevous model
        if (model.type === 'textnode') {
            if (nodePrev && nodePrev.type === 'textnode') {
                nodePrev.content += model.content;
                continue;
            }

            // Throw away empty nodes (keep spaces)
            if (!config.keepEmptyTextNodes) {
                const content = node.nodeValue;
                if (content !== ' ' && !content.trim()) {
                    continue;
                }
            }
        }

        // If all children are texts and there is some textnode the parent should
        // be text too otherwise I'm unable to edit texnodes
        const comps = model.components;
        if (!model.type && comps) {
            let allTxt = 1;
            let foundTextNode = 0;

            for (let ci = 0; ci < comps.length; ci++) {
                const comp = comps[ci];
                const cType = comp.type;

                if (
                    ['text', 'textnode'].indexOf(cType) < 0 &&
                    ['br', 'b', 'i', 'u', 'a', 'ul', 'ol'].indexOf(comp.tagName) < 0
                ) {
                    allTxt = 0;
                    break;
                }

                if (cType === 'textnode') {
                    foundTextNode = 1;
                }
            }

            if (allTxt && foundTextNode) {
                model.type = 'text';
            }
        }

        // If tagName is still empty and is not a textnode, do not push it
        if (!model.tagName && model.type !== 'textnode') {
            continue;
        }

        if (textTags.includes(model.tagName)) {
            model.layerable = 0;
            model.selectable = 0;
            model.hoverable = 0;
            model.editable = 0;
            model.highlightable = 0;
            model.type = '';
        }

        if (wrappedTags.includes(model.tagName)) {
            if (parent && parent.type !== 'text' && parent.type !== 'default') {
                model = {
                    tagName: 'div',
                    type: 'text',
                    layerable: 1,
                    components: [{...model}]
                };
            }
        }

        if (model.type === 'cell') {
            if (model.components && model.components[0].type !== 'text') {
                model.components = [
                    {
                        tagName: 'div',
                        type: 'text',
                        components: model.components
                    }
                ];
            }

            if (model.content) {
                model.components = [
                    {
                        tagName: 'div',
                        type: 'text',
                        content: model.content
                    }
                ];
            }

            delete model.content;
        }

        result.push(model);
    }

    return result;
}

/**
 * @constructor
 * Content parser initialize
 * @param editor
 * @constructor
 */
export default function ContentParser(editor) {
    const cTypes = editor.DomComponents.componentTypes;
    editor.Parser.parseHtml = html => htmlParser(html, editor.getConfig(), cTypes, editor.Parser.parseCss);
}
