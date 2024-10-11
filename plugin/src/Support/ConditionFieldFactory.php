<?php

namespace DigitalDyve\ConditionalDisplay\Support;

use Carbon_Fields\Field\Field;
use DigitalDyve\ConditionalDisplay\Support\ConditionTypes;

class ConditionFieldFactory
{
    private ConditionTypes $conditionTypes;

    public function __construct() {
        $this->conditionTypes = ConditionTypes::getInstance();
    }

    public function createFields(string $condition = '', string $parentField = 'condition_type'): array
    {
        $fields = [];

        $conditions = $this->conditionTypes->getConditions($condition);

        if (! is_array($conditions)) {
            return [];
        }

        if (isset($conditions['sub_conditions'])) {
            $fieldName = str_replace('.', '_', $condition);
            $subField = Field::make('select', $fieldName, $conditions['name'])
                ->set_options(array_combine(
                    array_keys($conditions['sub_conditions']),
                    array_map(fn ($subValue) => $subValue['name'], $conditions['sub_conditions'])
                ));

            $value = explode('.', $condition);
            $value = end($value);
            $subField->set_conditional_logic([
                [
                    'field' => $parentField,
                    'value' => $value,
                ],
            ]);

            $fields[] = $subField;

            foreach ($conditions['sub_conditions'] as $subKey => $subValue) {
                if (isset($subValue['sub_conditions'])) {
                    $subSubFields = $this->createFields($condition . '.' . $subKey, $fieldName);
                    $fields = array_merge($fields, $subSubFields);
                }
            }
        } else {
            foreach ($conditions as $condition => $innerFields) {
                if (! isset($innerFields['sub_conditions'])) {
                    continue;
                } 

                $fields = array_merge($fields, $this->createFields($condition));
            }

            $field = Field::make('select', 'condition_type', 'Condition Type')
                ->set_options(array_combine(
                    array_keys($conditions),
                    array_map(fn ($value) => $value['name'], $conditions)
                ));

            array_unshift($fields, $field);
        }

        return $fields;
    }

    private function getoptions(string $type): array
    {
        // Return the options for the type
        // This can be a dynamic function that returns the options based on the type
        return [];
    }
}
