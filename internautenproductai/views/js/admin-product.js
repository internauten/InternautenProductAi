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
            selectors.push('#description_short_' + langId);
        }

        selectors = selectors.concat([
            'textarea[id^="form_step1_description_short_"]',
            'textarea[id^="description_short_"]',
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

    function findAllDescriptionFields() {
        var fields = [];
        var selectors = [
            'textarea[id^="form_step1_description_"]',
            'textarea[id^="description_"]',
            'textarea[name*="[description]"]',
            'textarea.js-locale-input'
        ];

        document.querySelectorAll(selectors.join(',')).forEach(function (field) {
            if (isDetailedDescriptionField(field) && fields.indexOf(field) === -1) {
                fields.push(field);
            }
        });

        return fields;
    }

    function findAllShortDescriptionFields() {
        var fields = [];
        var selectors = [
            'textarea[id^="form_step1_description_short_"]',
            'textarea[id^="description_short_"]',
            'textarea[name*="[description_short]"]'
        ];

        document.querySelectorAll(selectors.join(',')).forEach(function (field) {
            if (isShortDescriptionField(field) && fields.indexOf(field) === -1) {
                fields.push(field);
            }
        });

        return fields;
    }

    function isShortDescriptionField(field) {
        if (!field) {
            return false;
        }

        var id = (field.id || '').toLowerCase();
        var name = (field.name || '').toLowerCase();

        return id.indexOf('description_short') !== -1 || name.indexOf('[description_short]') !== -1;
    }

    function isDetailedDescriptionField(field) {
        if (!field || isShortDescriptionField(field)) {
            return false;
        }

        var id = (field.id || '').toLowerCase();
        var name = (field.name || '').toLowerCase();

        return id.indexOf('form_step1_description_') !== -1
            || id.indexOf('description_') !== -1
            || name.indexOf('[description]') !== -1
            || (field.classList && field.classList.contains('js-locale-input'));
    }

    function getFieldLanguageId(field) {
        if (!field) {
            return '';
        }

        var id = field.id || '';
        var name = field.name || '';
        var match = id.match(/_(\d+)$/);

        if (match) {
            return match[1];
        }

        match = name.match(/\[(\d+)\]/);
        if (match) {
            return match[1];
        }

        return '';
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

                if (langId && generatedDescription) {
                    var translateBody = new URLSearchParams();
                    translateBody.append('source_text', generatedDescription);
                    translateBody.append('translate_to', 'English');

                    return fetch(config.ajaxUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: translateBody.toString()
                    })
                        .then(function (response) {
                            return response.text().then(function (text) {
                                var translationData = null;

                                try {
                                    translationData = JSON.parse(text);
                                } catch (error) {
                                    return null;
                                }

                                if (!translationData || !translationData.success) {
                                    return null;
                                }

                                return translationData;
                            });
                        })
                        .then(function (translationData) {
                            var translatedDescription = translationData && translationData.description ? translationData.description : generatedDescription;
                            var translatedShortDescription = extractFirstParagraphHtml(translatedDescription);

                            findAllDescriptionFields().forEach(function (field) {
                                if (field === textarea) {
                                    return;
                                }

                                var fieldLangId = getFieldLanguageId(field);
                                if (fieldLangId && langId && fieldLangId === langId) {
                                    return;
                                }

                                updateEditor(field, translatedDescription);
                            });

                            findAllShortDescriptionFields().forEach(function (field) {
                                if (field === shortDescriptionField) {
                                    return;
                                }

                                var fieldLangId = getFieldLanguageId(field);
                                if (fieldLangId && langId && fieldLangId === langId) {
                                    return;
                                }

                                updateEditor(field, translatedShortDescription);
                            });
                        });
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
        if (!textarea || textarea.dataset.internautenAiBound === '1' || !isDetailedDescriptionField(textarea)) {
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
            'textarea[id^="description_"]',
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
