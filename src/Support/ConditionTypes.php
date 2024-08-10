<?php

namespace DigitalDyve\ConditionalDisplay\Support;

use DigitalDyve\ConditionalDisplay\Contracts\Singleton;

class ConditionTypes
{
    use Singleton;

    private array $types = [];

    private function __construct()
    {
        $this->types = [
            'any' => [
                'name' => 'Display Everywhere',
                'tester' => fn () => true,
            ],
            'post' => [
                'name' => 'Post',
                'sub_conditions' => [
                    'any' => [
                        'name' => 'Any',
                        'tester' => fn () => is_singular(),
                    ],
                    'post_type' => [
                        'name' => 'Post Type',
                        'sub_conditions' => $this->getPostTypesForPosts(),
                    ],
                    'post_status' => [
                        'name' => 'Post Status',
                        'sub_conditions' => $this->getPostStatuses(),
                    ],
                    'post_format' => [
                        'name' => 'Post Format',
                        'sub_conditions' => $this->getPostFormats(),
                    ],
                    'page' => [
                        'name' => 'Page',
                        'sub_conditions' => [
                            'front_page' => [
                                'name' => 'Front Page',
                                'tester' => fn () => is_front_page(),
                            ],
                            'blog_page' => [
                                'name' => 'Blog Page',
                                'tester' => fn () => is_home(),
                            ],
                            'page_template' => [
                                'name' => 'Page Template',
                                'sub_conditions' => $this->getPageTemplates(),
                            ],
                        ],
                    ],
                    'author_single' => [
                        'name' => 'Author Single',
                        'sub_conditions' => $this->getAuthors(),
                    ],
                    // 'date_single' => [
                    //     'name' => 'Date Single',
                    //     'tester' => fn () => is_single(),
                    // ],
                ],
            ],
            'archive' => [
                'name' => 'Archive',
                'sub_conditions' => [
                    'any' => [
                        'name' => 'Any',
                        'tester' => fn () => is_archive(),
                    ],
                    'post_type' => [
                        'name' => 'Post Type',
                        'sub_conditions' => $this->getPostTypesForArchives(),
                    ],
                    'taxonomy' => [
                        'name' => 'Taxonomy',
                        'sub_conditions' => $this->getTaxonomies(),
                    ],
                    // 'date_archive' => [
                    //     'name' => 'Date Archive',
                    //     'tester' => fn () => is_date(),
                    // ],
                    'author_archive' => [
                        'name' => 'Author Archive',
                        'sub_conditions' => $this->getAuthors(),
                    ],
                ],
            ],
            'special' => [
                'name' => 'Special Pages',
                'sub_conditions' => [
                    '404' => [
                        'name' => '404 Page',
                        'tester' => fn () => is_404(),
                    ],
                    'search_results' => [
                        'name' => 'Search Results Page',
                        'tester' => fn () => is_search(),
                    ],
                ],
            ],
            'comment_status' => [
                'name' => 'Comment Status',
                'sub_conditions' => [
                    'open' => [
                        'name' => 'Open',
                        'tester' => fn () => comments_open(),
                    ],
                    'closed' => [
                        'name' => 'Closed',
                        'tester' => fn () => ! comments_open(),
                    ],
                ],
            ],
        ];
    }

    public function getConditions(string $condition = ''): array|false
    {
        $conditions = $this->types;
        $conditionParts = array_filter(explode('.', $condition));

        while (! empty($conditionParts)) {
            $part = array_shift($conditionParts);

            if (isset($conditions[$part])) {
                $conditions = $conditions[$part];
            } elseif (isset($conditions['sub_conditions'][$part])) {
                $conditions = $conditions['sub_conditions'][$part];
            } else {
                return false;
            }
        }

        return $conditions;
    }

    private function getPostTypes(): array
    {
        static $postTypes;
        return $postTypes ??= get_post_types(['public' => true], 'objects');
    }

    private function getPostTypesForPosts(): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => true,
        ]] + array_map(fn ($postType) => [
            'name' => $postType->label . ' Single',
            'tester' => fn () => get_post_type($postType->name),
        ], $this->getPostTypes());
    }

    private function getPostTypesForArchives(): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => is_post_type_archive(),
        ]] + array_map(fn ($postType) => [
            'name' => $postType->label . ' Archive',
            'tester' => fn () => is_post_type_archive($postType->name),
        ], $this->getPostTypes());
    }

    private function getTaxonomies(): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => is_tax(),
        ]] + array_map(fn ($taxonomy) => [
            'name' => get_taxonomy($taxonomy)->label,
            'tester' => fn () => is_tax($taxonomy),
            'sub_conditions' => $this->getTerms($taxonomy),
        ], get_taxonomies(['public' => true, 'show_ui' => true]));
    }

    private function getTerms(string $taxonomy): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => is_tax($taxonomy),
        ]] + array_map(fn ($term) => [
            'name' => $term->name,
            'tester' => fn () => is_tax($taxonomy, $term->slug),
        ], get_terms($taxonomy));
    }

    private function getPageTemplates(): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => is_page_template(),
        ]] + array_map(
            fn ($template, $name) => [
                'name' => $name,
                'tester' => fn () => get_page_template_slug() === $template,
            ],
            array_keys($pageTemplates = wp_get_theme()->get_page_templates()),
            array_values($pageTemplates)
        );
    }

    private function getPostStatuses(): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => true,
        ]] + array_map(fn ($postStatus) => [
            'name' => $postStatus->label,
            'tester' => fn () => get_post_status() === $postStatus->name,
        ], get_post_stati(['show_in_admin_status_list' => true], 'objects'));
    }

    private function getPostFormats(): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => true,
        ]] + array_map(
            fn ($format, $name) => [
                'name' => $name,
                'tester' => fn () => get_post_format() === $format,
            ], 
            array_keys($postFormats = get_post_format_strings()),
            array_values($postFormats)
        );
    }

    private function getAuthors(): array
    {
        return ['any' => [
            'name' => 'Any',
            'tester' => fn () => true,
        ]] + array_map(fn ($author) => [
            'name' => $author->display_name,
            'tester' => fn () => is_author($author->ID),
        ], get_users(['role__in' => ['author', 'contributor', 'editor', 'administrator']]));
    }
}
