<?php

namespace DigitalDyve\ConditionalDisplay\Fields\Partials;

use Carbon_Fields\Field\Field;

class CodeEditor
{
    public function get(string $handle, string $name): Field
    {
        return Field::make('textarea', "code_editor_$handle", $name)
            ->set_classes('cf-code-editor');
    }
}
