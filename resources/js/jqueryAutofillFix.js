export function installJqueryAutofillFix() {
    if (!window.jQuery) {
        return;
    }

    const $ = window.jQuery;

    function ensureAttrs(el, idx) {
        const tag = (el.tagName || 'field').toLowerCase();
        const type = (el.getAttribute('type') || '').toLowerCase();

        if (!el.getAttribute('id')) {
            el.setAttribute('id', `kukija_${tag}_${type || 't'}_${idx}`);
        }
        if (!el.getAttribute('name')) {
            el.setAttribute('name', el.getAttribute('id'));
        }
    }

    function fixAll() {
        const fields = $('input, select, textarea').toArray();
        fields.forEach((el, idx) => {
            ensureAttrs(el, idx);
        });
    }

    $(document).ready(() => {
        fixAll();

        if (window.MutationObserver) {
            const obs = new MutationObserver(() => {
                fixAll();
            });

            obs.observe(document.body, {
                childList: true,
                subtree: true,
            });
        }
    });
}
