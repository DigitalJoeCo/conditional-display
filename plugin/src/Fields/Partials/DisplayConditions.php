<?php

namespace DigitalDyve\ConditionalDisplay\Fields\Partials;

use Carbon_Fields\Field\Field;
use DigitalDyve\ConditionalDisplay\Support\ConditionFieldFactory;

class DisplayConditions
{
    private ConditionFieldFactory $conditionFieldFactory;

    public function __construct()
    {
        $this->conditionFieldFactory = new ConditionFieldFactory();
    }

    /**
     * @return Field[]
     **/
    public function get(): Field
    {
        return Field::make('complex', 'display_conditions', __('Display Conditions'))
            ->setup_labels([
                'plural_name' => 'Or',
                'singular_name' => 'Or',
            ])
            ->add_fields('condition_group', [
                Field::make('complex', 'display_conditions_group', __('Display Conditions Group'))
                    ->setup_labels([
                        'plural_name' => 'And',
                        'singular_name' => 'And',
                    ])
                    ->add_fields('condition', $this->conditionFieldFactory->createFields()),
            ]);
    }
}
