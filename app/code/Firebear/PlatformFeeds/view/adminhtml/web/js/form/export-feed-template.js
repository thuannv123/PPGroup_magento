/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'jquery',
        'Magento_Ui/js/form/element/textarea',
        'Firebear_PlatformFeeds/js/lib/codemirror',
        'Firebear_PlatformFeeds/data/data',
        'Firebear_PlatformFeeds/js/lib/show-hint',
        'Firebear_PlatformFeeds/js/lib/xml-hint',
        'Firebear_PlatformFeeds/js/lib/xml'
    ],
    function ($, Element, CodeMirror, data) {
        'use strict';

        let editor;

        let editorInitialValue;

        let $textArea;

        let $templateSelector;

        let $modifierSelector;

        let $varHelperSelect;

        let $varHelperInput;

        let editorConfig = {
            'mode': 'xml',
            'lineNumbers': true,
            'extraKeys': {
                '\'<\'': completeAfter,
                '\'/\'': completeIfAfterLt,
                '\' \'': completeIfInTag,
                '\'=\'': completeIfInTag,
                'Ctrl-Space': "autocomplete"
            },
            'hintOptions': {
                'schemaInfo': {
                    '!top': ["top"],
                    '!attrs': {
                        'id': null,
                        'class': ['A', 'B', 'C']
                    },
                    'top': {
                        'attrs': {
                            'lang': ['en', 'de', 'fr', 'nl'],
                            'freeform': null
                        },
                    }
                }
            }
        };

        function completeAfter(cm, pred) {
            if (!pred || pred()) {
                setTimeout(function() {
                    if (!cm.state.completionActive) {
                        cm.showHint({completeSingle: false});
                    }
                }, 100);
            }

            return CodeMirror.Pass;
        }

        function completeIfAfterLt(cm) {
            return completeAfter(cm, function() {
                let cursor = cm.getCursor();
                return cm.getRange(CodeMirror.Pos(cursor.line, cursor.ch - 1), cursor) === "<";
            });
        }

        function completeIfInTag(cm) {
            return completeAfter(cm, function() {
                let token = cm.getTokenAt(cm.getCursor());
                if (token.type === "string"
                    && (!/['"]/.test(token.string.charAt(token.string.length - 1))
                        || token.string.length === 1
                    )
                ) {
                    return false;
                }

                let inner = CodeMirror.innerMode(cm.getMode(), token.state).state;
                return inner.tagName;
            });
        }

        return Element.extend(
            {
                defaults: {
                    getAttributesAjaxUrl: '',
                    valuesForOptions: [],
                    imports: {
                        toggleVisibility: '${$.parentName}.import_source:value'
                    },
                    isShown: false,
                    inverseVisibility: false,
                    visible: false
                },

                toggleVisibility: function(selected) {
                    this.isShown = selected in this.valuesForOptions;
                    this.visible(this.inverseVisibility
                        ? !this.isShown
                        : this.isShown);
                },

                afterRender: function(element) {
                    try {
                        $textArea = $(element);

                        this.initializeEditor();
                        this.initializeTemplateSelector();
                        this.initializeModifierSelector();

                        this.initializeVarHelper()
                    } catch (e) {
                        console.error(e);
                    }
                },

                initializeEditor: function() {
                    editorInitialValue = $textArea.val();

                    editor = CodeMirror.fromTextArea($textArea[0], editorConfig);
                    editor.on("change", this.saveEditorValue);
                },

                saveEditorValue: function() {
                    $textArea.val(editor.getValue()).change();
                },

                initializeTemplateSelector: function() {
                    data.templates.default = this.getDefaultTemplate;

                    $templateSelector = $('#feed_template_selector');
                    $templateSelector.on('change', this.setTemplate);
                },

                getDefaultTemplate: function() {
                    return editorInitialValue;
                },

                setTemplate: function() {
                    let selected = $templateSelector.val();
                    if (data.templates[selected]) {
                        editor.setValue(data.templates[selected]());
                    }
                },

                initializeModifierSelector: function() {
                    $modifierSelector = $('#feed_template_modifiers_helper');
                    $.each(data.modifiers, function(name, config) {
                        let $option = $("<option></option>")
                            .attr('value', config.value)
                            .attr('data-tooltip', config.tooltip)
                            .text(config.label);

                        $modifierSelector.append($option);
                    });

                    $modifierSelector.find('option')
                        .on('mouseenter', $.proxy(this.showTooltip, this))
                        .on('mouseleave', $.proxy(this.hideTooltip, this));

                    $modifierSelector.on('change', $.proxy(this.updateModifiers, this));
                },

                updateModifiers: function() {
                    let modifiers = $modifierSelector.val();
                    if (!modifiers || !modifiers.length) {
                        return;
                    }

                    let selectedVar = $varHelperSelect.val();
                    if (selectedVar === 'default') {
                        return;
                    }

                    let template = selectedVar.slice(0, -2) + ' | ' + modifiers.join(' | ') + ' }}';
                    this.setVarHelperInputValue(template);
                },

                initializeVarHelper: function() {
                    $varHelperInput = $('#feed_template_var_code');
                    $varHelperSelect = $('#feed_template_var_helper');
                    $varHelperSelect.on('change', $.proxy(this.onVarHelperSelectChanged, this));

                    $.ajax({
                        type: "POST",
                        data: {form_key: window.FORM_KEY},
                        url: this.getAttributesAjaxUrl,
                        dataType: 'json',
                        success: $.proxy(this.renderVarHelperSelect, this)
                    });
                },

                onVarHelperSelectChanged: function() {
                    this.updateHelperInput();
                    this.updateModifiers();
                },

                renderVarHelperSelect: function(attributes) {
                    $.each(attributes, function(key, attribute) {
                        let $option = $("<option></option>")
                            .attr("value", attribute.value)
                            .attr('data-tooltip', attribute.tooltip)
                            .text(attribute.label);

                        $varHelperSelect.append($option);
                    });

                    $varHelperSelect.find('option')
                        .on('mouseenter', $.proxy(this.showTooltip, this))
                        .on('mouseleave', $.proxy(this.hideTooltip, this));
                },

                showTooltip: function(event) {
                    let text = $(event.currentTarget).data('tooltip');
                    if (!text) {
                        return;
                    }

                    let $tooltip = $('.firebear-tooltip');
                    $tooltip.html(text);
                    let height = $tooltip.height();
                    $tooltip.css({
                        top: $(event.currentTarget).parent()[0].offsetTop - height,
                        left: $(event.currentTarget).parent()[0].offsetLeft
                    });

                    if (!$tooltip.is(':visible')) {
                        $tooltip.fadeIn();
                    }
                },

                hideTooltip: function(e) {
                    $('.firebear-tooltip').hide();
                },

                updateHelperInput: function() {
                    let selected = $varHelperSelect.val();
                    if (selected === 'default') {
                        return;
                    }

                    this.setVarHelperInputValue(selected);
                },

                setVarHelperInputValue(value) {
                    $varHelperInput.val(value);
                }
            }
        );
    }
);
