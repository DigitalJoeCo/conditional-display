<?php

namespace DigitalDyve\ConditionalDisplay\Fields;

use Carbon_Fields\Field\Field;
use DigitalDyve\ConditionalDisplay\Contracts\Fields\InitializesConditionalFields;

class Sidebars
{
    use InitializesConditionalFields;

    public function handle(): void
    {
        $this->initFields(
            fn ($prefix): Field => Field::make('complex', "{$prefix}_conditional_display_sidebars_output", 'Sidebars')
                ->add_fields([
                    Field::make('text', "{$prefix}_conditional_display_sidebar_name", __('Sidebar Name'))
                        ->set_help_text(__('Enter the name of the sidebar to display')),
                ]),
            'Sidebars',
            'themes.php'
        );
    }

    public function render(): void
    {
        if (! current_theme_supports('widgets')) {
            return;
        }

        foreach ($this->getDisplayed('sidebars') as $sidebar) {
            $normalizedSidebar = $sidebar;

            // normalize the keys
            foreach (array_keys($sidebar) as $key) {
                $suffix = explode('_', $key);
                $suffix = end($suffix);
                $normalizedSidebar[$suffix] = $sidebar[$key];
                unset($normalizedSidebar[$key]);
            }

            // Register the sidebar
            register_sidebar([
                'name' => $normalizedSidebar['sidebar_name'],
                'id' => sanitize_title($normalizedSidebar['sidebar_name']),
            ]);

            do_action('dynamic_sidebar_before', function () use ($normalizedSidebar) {
                static $hasRendered = false;

                if ($hasRendered) {
                    return;
                }

                dynamic_sidebar($normalizedSidebar['sidebar_name']);
                $hasRendered = true;
            });
        }
    }
}
