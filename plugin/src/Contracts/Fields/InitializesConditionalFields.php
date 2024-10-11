<?php

namespace DigitalDyve\ConditionalDisplay\Contracts\Fields;

use Carbon_Fields\Container;
use Carbon_Fields\Container\Container as AbstractContainer;
use Carbon_Fields\Field\Field;
use DigitalDyve\ConditionalDisplay\Fields\Partials\DisplayConditions;
use DigitalDyve\ConditionalDisplay\Contracts\Fields\DisplayConditionTypes;
use DigitalDyve\ConditionalDisplay\Support\ConditionTypes;

trait InitializesConditionalFields
{
    private AbstractContainer $post_meta_container;
    private AbstractContainer $term_meta_container;
    private AbstractContainer $user_meta_container;

    abstract public function handle(): void;

    abstract public function render(): void;

    /**
     * Initialize the fields
     *
     * @param callable $fields
     * @param string $pluralName
     * @return void
     */
    public function initFields(callable $get_fields, string $pluralName, string $pageParent): void
    {
        $this->registerOptionsPages(
            is_array($site_fields = $get_fields('site')) ? $site_fields : [$site_fields],
            'conditional_display_site_' . strtolower($pluralName),
            sprintf(__('Site %s'), ucfirst($pluralName)),
            $pageParent
        );

        if (is_multisite()) {
            $this->registerOptionsPages(
                is_array($network_fields = $get_fields('network')) ? $network_fields : [$network_fields],
                'conditional_display_network_' . strtolower($pluralName),
                sprintf(__('Network %s'), ucfirst($pluralName)),
                $pageParent
            );
        }

        $this->post_meta_container = Container::make('post_meta', sprintf(__('Post %s'), ucfirst($pluralName)))
            ->where('post_type', 'CUSTOM', function ($post_type) {
                if (
                    ! post_type_supports($post_type, 'editor')
                    || ! get_post_type_object($post_type)->public
                ) {
                    return false;
                }

                return true;
            })
            ->add_fields(is_array($post_fields = $get_fields('post')) 
                ? $post_fields 
                : [$post_fields]
            );

        $this->term_meta_container = Container::make('term_meta', sprintf(__('Term %s'), ucfirst($pluralName)))
            ->add_fields(is_array($term_fields = $get_fields('term')) 
                ? $term_fields 
                : [$term_fields]
            );

        $this->user_meta_container = Container::make('user_meta', sprintf(__('User %s'), ucfirst($pluralName)))
            ->add_fields(is_array($user_fields = $get_fields('user')) 
                ? $user_fields 
                : [$user_fields]
            );
    }

    /**
     * Initialize the fields
     *
     * @param Field[] $fields
     * @param string $pluralName
     * @return void
     */
    private function registerOptionsPages(
        array $fields,
        string $handle,
        string $pretty,
        string $pageParent,
    ): AbstractContainer {
        return Container::make('theme_options', $pretty)
            ->set_page_parent($pageParent)
            ->add_fields([
                Field::make('complex', $handle, $pretty)
                    ->set_layout('tabbed-horizontal')
                    ->add_fields('scripts', [
                        Field::make('text', "{$handle}_title", sprintf(__('%s Name'), $pretty)),
                        $this->getDisplayConditions(),
                        ...$fields
                    ])
                    ->set_header_template("
                        <% if ({$handle}_title) { %>
                        <%- {$handle}_title %>
                        <% } else { %>
                        {$pretty}
                        <% } %>
                    "),
            ]);
    }

    private function getDisplayConditions(): Field
    {
        static $displayConditions;
        return $displayConditions ??= (new DisplayConditions())->get();
    }

    public function getDisplayed(string $handle): array
    {
        $queried_object = get_queried_object();

        $outputs = match (true) {
            $queried_object instanceof \WP_Post => carbon_get_post_meta(
                $queried_object->ID,
                "post_conditional_display_{$handle}_output"
            ) ?? [],
            $queried_object instanceof \WP_Term => carbon_get_term_meta(
                $queried_object->term_id,
                "term_conditional_display_{$handle}_output"
            ) ?? [],
            $queried_object instanceof \WP_User => carbon_get_user_meta(
                $queried_object->ID,
                "user_conditional_display_{$handle}_output"
            ) ?? [],
            // TODO: 404 page?
            // TODO: date archive?
            // TODO: home page?
            // TODO: search results?
            default => [],
        };

        $conditionalOutputs = carbon_get_theme_option("conditional_display_site_{$handle}") ?? [];

        if (is_multisite()) {
            $conditionalOutputs += carbon_get_theme_option("conditional_display_network_{$handle}_output") ?? [];
        }

        $conditionTypes = ConditionTypes::getInstance()->getConditions();

        foreach ($conditionalOutputs as $conditionalOutput) {
            if (
                ! isset($conditionalOutput['_type']) 
                && $conditionalOutput['_type'] !== $handle
                || ! isset($conditionalOutput['display_conditions'])
            ) {
                continue;
            }

            $shouldDisplay = false;

            // OR CONDITIONS (any of them can be true)
            foreach ($conditionalOutput['display_conditions'] as $conditionGroup) {
                if (
                    ! isset($conditionGroup['_type'])
                    && $conditionGroup['_type'] !== 'condition_group'
                    || ! isset($conditionGroup['display_conditions_group'])
                ) {
                    continue;
                }

                // AND CONDITIONS (all of them must be true)
                try {
                    foreach ($conditionGroup['display_conditions_group'] as $condition) {
                        if (
                            ! isset($condition['_type'])
                            && $condition['_type'] !== 'condition'
                            || ! isset($condition['condition_type'])
                            || ! array_key_exists($condition['condition_type'], $conditionTypes)
                        ) {
                            throw new \Exception('Invalid condition');
                        }

                        $conditionType = $conditionTypes[$condition['condition_type']];

                        if (isset($conditionType['tester'])) {
                            if (!$conditionType['tester']()) {
                                throw new \Exception('Condition not met');
                            }
                        } elseif (isset($conditionType['sub_conditions'])) {
                            // Handle sub-conditions recursively
                            $subConditions = $conditionType['sub_conditions'];
                            foreach ($subConditions as $subCondition) {
                                if (isset($subCondition['tester'])) {
                                    if (!$subCondition['tester']()) {
                                        throw new \Exception('Sub-condition not met');
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception) {
                    continue;
                }

                $shouldDisplay = true;
                break;
            }

            if ($shouldDisplay) {
                $outputs = array_merge($outputs, $conditionalOutput["site_conditional_display_{$handle}_output"] ?? $conditionalOutput["network_conditional_display_{$handle}_output"] ?? []);
            }
        }

        return $outputs;
    }
}
