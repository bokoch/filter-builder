<?php

use Mykolab\FilterBuilder\Pagination\Resolvers\SimplePaginationResolver;

return [
    'default_pagination_resolver' => SimplePaginationResolver::class,

    'per_page_default' => 10,

    'request_parameters' => [
        'current_page' => 'page',
        'per_page' => 'per_page',

        'search' => 'search',

        'order_by' => 'order_by',
        'order_direction' => 'order_direction',

        'range_suffix' => [
            'from' => '_from',
            'to' => '_to',
        ],
    ],

    'response_parameters' => [
        'current_page' => 'page',
    ],
];
