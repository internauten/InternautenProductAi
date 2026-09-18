document.addEventListener('DOMContentLoaded', function () {
    if (!window.internautenManufacturerAi || !window.internautenManufacturerAi.ajaxUrl) {
        return;
    }

    var nameField = document.querySelector('#manufacturer_name, input[name="manufacturer[name]"], input[name="name"]');
    if (!nameField) {
        return;
    }

    var form = nameField.closest('form');
    if (!form) {
        return;
    }

    function findManufacturerIdFromPage() {
        var selectors = [
            'input[name="id_manufacturer"]',
            'input[name="manufacturer[id_manufacturer]"]',
            'input[name="manufacturer_id"]',
            'input[id^="id_manufacturer"]',
            'input[id^="manufacturer_id"]'
        ];

        for (var i = 0; i < selectors.length; i += 1) {
            var field = document.querySelector(selectors[i]);
            if (field && field.value && String(field.value).trim() !== '') {
                return String(field.value).trim();
            }
        }

        var search = new URLSearchParams(window.location.search || '');
        var searchId = search.get('id_manufacturer');
        if (searchId && String(searchId).trim() !== '') {
            return String(searchId).trim();
        }

        var pathMatch = window.location.pathname.match(/(?:\/brands\/|\/manufacturers\/)(\d+)(?:\/|$)/i)
            || window.location.href.match(/[?&]id_manufacturer=(\d+)/i);
        if (pathMatch && pathMatch[1]) {
            return pathMatch[1];
        }

        return '0';
    }

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-primary';
    button.textContent = window.internautenManufacturerAi.buttonLabel || 'Brand-Bild generieren';
    button.style.marginTop = '12px';
    button.style.marginRight = '8px';

    var status = document.createElement('div');
    status.className = 'alert alert-info';
    status.style.display = 'none';
    status.style.marginTop = '12px';
    status.style.marginBottom = '0';

    var target = form.querySelector('.card-footer, .panel-footer, .form-footer, .card-body');
    if (target && target.parentNode) {
        target.parentNode.insertBefore(button, target.nextSibling || null);
        target.parentNode.insertBefore(status, button.nextSibling || null);
    } else {
        form.appendChild(button);
        form.appendChild(status);
    }

    button.addEventListener('click', function () {
        var brandName = (nameField && nameField.value ? nameField.value : '').trim();
        var idManufacturer = findManufacturerIdFromPage();

        if (!brandName) {
            alert(window.internautenManufacturerAi.errorNoName || 'Bitte zuerst einen Markenname eintragen.');
            return;
        }

        if (!idManufacturer || idManufacturer === '0') {
            status.className = 'alert alert-danger';
            status.textContent = 'Es wurde keine Marke ausgewählt. Bitte die Brand zuerst speichern bzw. im Edit-Modus öffnen.';
            status.style.display = 'block';
            return;
        }

        button.disabled = true;
        status.textContent = window.internautenManufacturerAi.loadingLabel || 'Bild wird generiert...';
        status.style.display = 'block';

        var params = new URLSearchParams();
        params.append('id_manufacturer', idManufacturer || 0);
        params.append('brand_name', brandName);

        fetch(window.internautenManufacturerAi.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: params.toString()
        })
            .then(function (response) {
                return response.text().then(function (text) {
                    var data = null;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error((text || '').replace(/<[^>]*>/g, ' ').trim() || 'Ungültige Server-Antwort.');
                    }
                    if (!response.ok || !data || !data.success) {
                        throw new Error((data && data.message) || (window.internautenManufacturerAi.genericError || 'Das Bild konnte nicht generiert werden.'));
                    }
                    return data;
                });
            })
            .then(function (data) {
                status.className = 'alert alert-success';
                status.textContent = (data && data.message) || 'Markenbild wurde erstellt.';
                if (window.location && window.location.reload) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(function (error) {
                status.className = 'alert alert-danger';
                status.textContent = error && error.message ? error.message : (window.internautenManufacturerAi.genericError || 'Das Bild konnte nicht generiert werden.');
            })
            .finally(function () {
                button.disabled = false;
            });
    });
});
