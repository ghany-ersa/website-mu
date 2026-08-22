<?php

// Whitelisted brand tokens for Organization::fontFamily()/borderRadius() and the two
// document scaffolds that render them (organizations/pages/_document.blade.php and
// templates/preview.blade.php — kept in sync deliberately, see that file's header comment).
// Adding an option here makes it selectable from the brand settings form and renderable
// everywhere without touching controller/model code.
return [

    'fonts' => [
        'Plus Jakarta Sans' => [
            'stack' => '"Plus Jakarta Sans", sans-serif',
            'google' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800',
        ],
        'Inter' => [
            'stack' => '"Inter", sans-serif',
            'google' => 'Inter:wght@400;500;600;700;800',
        ],
        'Poppins' => [
            'stack' => '"Poppins", sans-serif',
            'google' => 'Poppins:wght@400;500;600;700;800',
        ],
        'Lora' => [
            'stack' => '"Lora", serif',
            'google' => 'Lora:wght@400;500;600;700',
        ],
    ],

    'radii' => [
        'sharp' => '0.375rem',
        'soft' => '1rem',
        'full' => '1.5rem',
    ],

];
