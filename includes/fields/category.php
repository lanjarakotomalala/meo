<?php

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key' => 'group_display_in_front',
        'fields' => [
            [
                'key' => 'field_display_in_front',
                'label' => 'Afficher en front',
                'name' => 'display_in_front',
                'type' => 'true_false',
                'default_value' => 1,
                'ui' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'taxonomy',
                    'operator' => '==',
                    'value' => 'product_cat',
                ],
            ],
        ],
        'hide_on_screen' => [],
        'active' => true,
        'description' => '',
    ]);
}
