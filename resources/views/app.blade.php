<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&family=Cookie&family=Dancing+Script:wght@400;500;600;700&family=Kalam:wght@300;400;700&display=swap" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
            (function () {
                function ensureJquery(cb) {
                    if (window.jQuery) {
                        window.$ = window.jQuery;
                        cb();
                        return;
                    }

                    var s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js';
                    s.onload = function () {
                        if (window.jQuery) {
                            window.$ = window.jQuery;
                        }
                        cb();
                    };
                    document.head.appendChild(s);
                }

                function fixAll() {
                    if (!window.jQuery) return;
                    var $ = window.jQuery;
                    var i = 0;
                    $('input, select, textarea').each(function () {
                        var el = this;

                        var id = el.getAttribute('id');
                        var name = el.getAttribute('name');

                        if (!id || !String(id).trim()) {
                            var tag = (el.tagName || 'field').toLowerCase();
                            var type = (el.getAttribute('type') || 't').toLowerCase();
                            id = 'kukija_' + tag + '_' + type + '_' + i;
                            el.setAttribute('id', id);
                        }

                        if (!name || !String(name).trim()) {
                            el.setAttribute('name', id);
                        }

                        i++;
                    });
                }

                function start() {
                    fixAll();

                    document.addEventListener('focusin', function () {
                        fixAll();
                    });

                    if (window.MutationObserver) {
                        var obs = new MutationObserver(function () {
                            fixAll();
                        });
                        obs.observe(document.documentElement, { childList: true, subtree: true });
                    }

                    setInterval(fixAll, 1000);
                }

                function boot() {
                    ensureJquery(start);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', boot);
                } else {
                    boot();
                }
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
