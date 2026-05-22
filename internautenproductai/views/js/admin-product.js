(function () {
    var config = Object.assign({
        ajaxUrl: '',
        fallbackAjaxUrl: '',
        buttonLabel: '',
        loadingLabel: '',
        errorNoName: '',
        genericError: '',
        generationError: '',
        invalidJsonError: '',
        emptyResponseLabel: ''
    }, window.internautenProductAi || {});

    if (!config.ajaxUrl) {
        return;
    }

    function findProductName(langId) {
        var selectors = [];

        if (langId) {
            selectors.push('#form_step1_name_' + langId);
        }

        selectors = selectors.concat([
            'input[id^="form_step1_name_"]',
            'input[name*="[name]"]',
            '#name'
        ]);

        for (var i = 0; i < selectors.length; i += 1) {
            var field = document.querySelector(selectors[i]);
            if (field && field.value && field.value.trim()) {
                return field.value.trim();
            }
        }

        return '';
    }

    function findShortDescriptionField(langId) {
        var selectors = [];

        if (langId) {
            selectors.push('#form_step1_description_short_' + langId);
        }

        selectors = selectors.concat([
            'textarea[id^="form_step1_description_short_"]',
            'textarea[name*="[description_short]"]'
        ]);

        for (var i = 0; i < selectors.length; i += 1) {
            var field = document.querySelector(selectors[i]);
            if (field) {
                return field;
            }
        }

        return null;
    }

    function extractFirstParagraphHtml(content) {
        if (!content) {
            return '';
        }

        var paragraphMatch = content.match(/<p\b[^>]*>[\s\S]*?<\/p>/i);
        if (paragraphMatch && paragraphMatch[0]) {
            return paragraphMatch[0].trim();
        }

        var text = content
            .replace(/<[^>]*>/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        if (!text) {
            return '';
        }

        return '<p>' + text + '</p>';
    }

    function updateEditor(textarea, content) {
        var finalContent = content;

        textarea.value = finalContent;

        if (window.tinyMCE && textarea.id) {
            var editor = window.tinyMCE.get(textarea.id);
            if (editor) {
                editor.setContent(finalContent);
            }
        }

        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function requestDescription(textarea, button) {
        var match = textarea.id ? textarea.id.match(/_(\d+)$/) : null;
        var langId = match ? match[1] : '';
        var shortDescriptionField = findShortDescriptionField(langId);
        var productName = findProductName(langId);

        if (!productName) {
            window.alert(config.errorNoName);
            return;
        }

        var originalLabel = button.textContent;
        button.disabled = true;
        button.textContent = config.loadingLabel || originalLabel;

        var body = new URLSearchParams();
        body.append('product_name', productName);

        var requestUrls = [config.ajaxUrl, config.fallbackAjaxUrl].filter(Boolean);

        function fetchDescription(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: body.toString()
            })
                .then(function (response) {
                    return response.text().then(function (text) {
                        var data = null;

                        try {
                            data = JSON.parse(text);
                        } catch (error) {
                            var preview = (text || '')
                                .replace(/<[^>]*>/g, ' ')
                                .replace(/\s+/g, ' ')
                                .trim()
                                .slice(0, 220);

                            throw new Error(
                                (config.invalidJsonError || config.genericError)
                                + ' '
                                + (preview || config.emptyResponseLabel)
                            );
                        }

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || config.genericError || config.generationError);
                        }

                        return data;
                    });
                });
        }

        function tryRequest(index) {
            if (index >= requestUrls.length) {
                return Promise.reject(new Error(config.genericError || config.generationError));
            }

            return fetchDescription(requestUrls[index]).catch(function (error) {
                if (index < requestUrls.length - 1) {
                    return tryRequest(index + 1);
                }

                throw error;
            });
        }

        tryRequest(0)
            .then(function (data) {
                var generatedDescription = data.description || '';
                var firstParagraph = extractFirstParagraphHtml(generatedDescription);

                updateEditor(textarea, generatedDescription);

                if (shortDescriptionField && firstParagraph) {
                    updateEditor(shortDescriptionField, firstParagraph);
                }
            })
            .catch(function (error) {
                window.alert(error.message || config.genericError || config.generationError);
            })
            .finally(function () {
                button.disabled = false;
                button.textContent = originalLabel;
            });
    }

    function insertButton(textarea) {
        if (!textarea || textarea.dataset.internautenAiBound === '1') {
            return;
        }

        textarea.dataset.internautenAiBound = '1';

        var container = document.createElement('div');
        container.className = 'internauten-ai-actions';
        container.style.margin = '8px 0';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-primary';
        button.textContent = config.buttonLabel || 'Mit ChatGPT generieren';
        button.addEventListener('click', function () {
            requestDescription(textarea, button);
        });

        container.appendChild(button);
        textarea.parentNode.insertBefore(container, textarea);
    }

    function scanEditors() {
        var selectors = [
            'textarea[id^="form_step1_description_"]',
            'textarea[name*="[description]"]',
            'textarea.js-locale-input'
        ];

        document.querySelectorAll(selectors.join(',')).forEach(insertButton);
    }

    document.addEventListener('DOMContentLoaded', function () {
        scanEditors();

        var observer = new MutationObserver(function () {
            scanEditors();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
})();
