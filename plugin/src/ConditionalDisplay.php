<?php

namespace DigitalDyve\ConditionalDisplay;

use Carbon_Fields\Field\Field;
use DigitalDyve\ConditionalDisplay\Contracts\Singleton;
use Carbon_Fields\Carbon_Fields;

class ConditionalDisplay
{
    use Singleton;

    public const FIELDS = [
        Fields\Scripts::class,
        Fields\Sidebars::class,
    ];

    public function init()
    {
        if (! class_exists('\Carbon_Fields\Carbon_Fields')) {
            if (is_admin()) {
                add_action('admin_notices', fn () => printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    __('Please install Carbon Fields.')
                ));
            }
            return;
        }

        add_action('after_setup_theme', fn () => ! Carbon_Fields::is_booted() && Carbon_Fields::boot());
        add_action('carbon_fields_register_fields', fn () => $this->registerFields());
        add_action('carbon_fields_field_activated', fn ($field) => $this->codeEditorJs($field));
        add_action('template_redirect', fn () => $this->renderFields(), PHP_INT_MAX);
    }    

    private function renderFields()
    {
        if (is_admin()) {
            return;
        }

        foreach (self::FIELDS as $field) {
            (new $field())->render();
        }
    }

    private function registerFields()
    {
        foreach (self::FIELDS as $field) {
            (new $field())->handle();
        }
    }

    private function codeEditorJs(Field $field): void
    {
        static $hasEnqueued = false;

        if (
            ! $field instanceof Field
            || str_starts_with($field->get_name(), 'code_editor_')
            || $hasEnqueued
        ) {
            return;
        }

        add_action('admin_enqueue_scripts', function () {
            $settings = wp_enqueue_code_editor([
                'type' => 'text/html',
                'codemirror' => array(
                    'mode' => 'htmlmixed',
                    'indentUnit' => 4,
                    'indentWithTabs' => true,
                    'inputStyle' => 'contenteditable',
                    'lineNumbers' => true,
                    'lineWrapping' => true,
                    'styleActiveLine' => true,
                    'continueComments' => true,
                    'extraKeys' => array(
                        'Ctrl-Space' => 'autocomplete',
                        'Ctrl-/' => 'toggleComment',
                        'Cmd-/' => 'toggleComment',
                        'Alt-F' => 'findPersistent',
                        'Ctrl-F' => 'findPersistent',
                        'Cmd-F' => 'findPersistent',
                    ),
                    'direction' => 'ltr',
                    'tabSize' => 4,
                    'theme' => 'default',
                ),
            ]);

            if (false === $settings) {
                add_action('admin_notices', fn () => printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    __('The code editor could not be initialized.')
                ));
                return;
            }

            $settings = wp_json_encode($settings);

            wp_add_inline_script('code-editor', "
                jQuery(document).ready(function($) {
                    const initializer = function (element) {
                        wp.codeEditor.initialize(element, {$settings});
                    };

                    const debounce = (func, delay) => {
                        let inDebounce;
                        return function() {
                            const context = this;
                            const args = arguments;
                            clearTimeout(inDebounce);
                            inDebounce = setTimeout(() => func.apply(context, args), delay);
                        }
                    };

                    var observer = new MutationObserver(debounce(function (mutations) {
                        mutations.forEach(function (mutation) {
                            if (mutation.type !== 'childList') {
                                return;
                            }

                            mutation.addedNodes.forEach(function (node) {
                                if (node.nodeType !== 1) {
                                    return;
                                }

                                var codeEditor = node.querySelector('.cf-code-editor textarea');

                                if (! codeEditor) {
                                    return;
                                }

                                initializer(codeEditor);
                            });
                        });
                    }, 100));
                    
                    observer.observe(document.body, { childList: true, subtree: true });
                    document.querySelectorAll('.cf-code-editor textarea').forEach(initializer);
                });
            ");
        });

        $hasEnqueued = true;
    }
}
