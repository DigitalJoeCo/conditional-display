<?php

namespace DigitalDyve\ConditionalDisplay\Fields;

use Carbon_Fields\Field\Field;
use DigitalDyve\ConditionalDisplay\Contracts\Fields\InitializesConditionalFields;
use DigitalDyve\ConditionalDisplay\Fields\Partials\CodeEditor;

class Scripts
{
    use InitializesConditionalFields;

    public function handle(): void
    {
        $this->initFields(
            fn ($prefix): Field => Field::make('complex', "{$prefix}_conditional_display_scripts_output", 'Scripts')
                ->add_fields([
                    (new CodeEditor())->get('script', 'Script')
                        ->set_help_text(__('Enter the script HTML, be sure to include the script tags')),
                    Field::make('select', "{$prefix}_conditional_display_script_location", __('Script Location'))
                        ->set_options([
                            'head' => __('Header'),
                            'body_open' => __('After Body Open'),
                            'footer' => __('Before Body Close'),
                        ]),
                    Field::make('number', "{$prefix}_conditional_display_script_priority", __('Script Priority'))
                        ->set_default_value(10),
                ]),
            'Scripts',
            'themes.php'
        );
    }
    
    public function render(): void
    {
        foreach ($this->getDisplayed('scripts') as $script) {
            $normalizedScript = $script;

            // normalize the keys
            foreach (array_keys($script) as $key) {
                $suffix = explode('_', $key);
                $suffix = end($suffix);
                $normalizedScript[$suffix] = $script[$key];
                unset($normalizedScript[$key]);
            }

            add_action(
                "wp_{$normalizedScript['location']}",
                fn () => print $normalizedScript['script'],
                $normalizedScript['priority']
            );
        }
    }
}
