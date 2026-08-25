/* Rio Park Operador Lite — plate keyboard (ES5) */
var PlateKeyboard = (function () {
    var KEYS = [
        '1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
        'U', 'V', 'W', 'X', 'Y', 'Z'
    ];

    function init(container) {
        if (!container) return;

        var displayId = container.getAttribute('data-plate-keyboard');
        var inputId = container.getAttribute('data-plate-input');
        var displayEl = document.getElementById(displayId);
        var inputEl = document.getElementById(inputId);
        var value = '';

        function render() {
            if (displayEl) {
                displayEl.textContent = value || '———';
            }
            if (inputEl) {
                inputEl.value = value;
            }
        }

        var grid = document.createElement('div');
        grid.className = 'lite-kb-grid';

        for (var i = 0; i < KEYS.length; i++) {
            (function (key) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'lite-kb-key';
                btn.textContent = key;
                btn.addEventListener('click', function () {
                    if (value.length >= 7) return;
                    value += key;
                    render();
                });
                grid.appendChild(btn);
            })(KEYS[i]);
        }

        var backBtn = document.createElement('button');
        backBtn.type = 'button';
        backBtn.className = 'lite-kb-key lite-kb-small';
        backBtn.textContent = 'Apagar';
        backBtn.addEventListener('click', function () {
            value = value.slice(0, -1);
            render();
        });
        grid.appendChild(backBtn);

        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'lite-kb-key lite-kb-small';
        clearBtn.textContent = 'Limpar';
        clearBtn.addEventListener('click', function () {
            value = '';
            render();
        });
        grid.appendChild(clearBtn);

        container.appendChild(grid);
        render();
    }

    return { init: init };
})();
