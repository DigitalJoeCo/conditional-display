<?php

namespace DigitalDyve\ConditionalDisplay\Contracts\Fields;

class DisplayConditionTypes
{
    private const CONDITIONS = [
        'post' => [
            'has_taxonomy' => 'Post has taxonomy',
            'has_term' => 'Post has term',
            'has_author' => 'Post has author',
            'has_date' => 'Post has date',
            'has_status' => 'Post has status',
            'has_format' => 'Post has format',
            'has_any_taxonomy' => 'Post has any taxonomy',
            'has_all_taxonomies' => 'Post has all taxonomies',
            'has_any_term' => 'Post has any term',
            'has_all_terms' => 'Post has all terms',
        ],
        'archive' => [
            'is_for_taxonomy' => 'Archive is for taxonomy',
            'is_for_term' => 'Archive is for term',
            'is_for_author' => 'Archive is for author',
            'is_for_date' => 'Archive is for date',
            'is_for_post_type' => 'Archive is for post type',
            'is_for_any_taxonomy' => 'Archive is for any taxonomy',
            'is_for_all_taxonomies' => 'Archive is for all taxonomies',
            'is_for_any_term' => 'Archive is for any term',
            'is_for_all_terms' => 'Archive is for all terms',
        ],
        'taxonomy' => [
            'is' => 'Taxonomy is',
            'has_parent' => 'Taxonomy has parent',
            'has_child' => 'Taxonomy has child',
            'is_any' => 'Taxonomy is any',
            'is_all' => 'Taxonomy is all',
        ],
        'term' => [
            'is' => 'Term is',
            'has_parent' => 'Term has parent',
            'has_child' => 'Term has child',
            'is_any' => 'Term is any',
            'is_all' => 'Term is all',
        ],
        'author' => [
            'is' => 'Author is',
            'has_role' => 'Author has role',
            'is_any' => 'Author is any',
            'is_all' => 'Author is all',
        ],
        'date' => [
            'is' => 'Date is',
            'is_before' => 'Date is before',
            'is_after' => 'Date is after',
            'is_any' => 'Date is any',
            'is_all' => 'Date is all',
        ],
        'user' => [
            'is_logged_in' => 'User is logged in',
            'has_role' => 'User has role',
            'is_any' => 'User is any',
            'is_all' => 'User is all',
        ],
        'page' => [
            'is' => 'Page is',
            'is_child_of' => 'Page is child of',
            'is_parent_of' => 'Page is parent of',
            'is_any' => 'Page is any',
            'is_all' => 'Page is all',
        ],
        '404' => [
            'is' => 'Is 404 page',
        ],
        'ping_status' => [
            'is' => 'Ping status is',
            'is_any' => 'Ping status is any',
            'is_all' => 'Ping status is all',
        ],
        'comment_status' => [
            'is' => 'Comment status is',
            'is_any' => 'Comment status is any',
            'is_all' => 'Comment status is all',
        ],
        'post_status' => [
            'is' => 'Post status is',
            'is_any' => 'Post status is any',
            'is_all' => 'Post status is all',
        ],
        'post_format' => [
            'is' => 'Post format is',
            'is_any' => 'Post format is any',
            'is_all' => 'Post format is all',
        ],
    ];

    public function getTypes(): array
    {
        return self::CONDITIONS;
    }

    public function getType($key)
    {
        return self::CONDITIONS[$key] ?? null;
    }
}

enum DisplayConditionTypes: string
{
    case everywhere = 'Everywhere';
    case archive = 'Archive';
    case post_type = 'Post Type';
    case template = 'Template';
    case taxonomy = 'Taxonomy';
    case term = 'Term';
    case user = 'User';
    case author = 'Author';
    case date = 'Date';
    case fourohfour = '404';
    case ping_status = 'Ping Status';
    case comment_status = 'Comment Status';
    case post_status = 'Post Status';
    case post_format = 'Post Format';
}
