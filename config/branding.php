<?php

// Whitelisted brand tokens for Organization::fontFamily()/borderRadius() and the two
// document scaffolds that render them (organizations/pages/_document.blade.php and
// templates/preview.blade.php - kept in sync deliberately, see that file's header comment).
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

    /*
     * Font pairings a template can opt into on top of the body font above, exposed to sections
     * as the --font-headline / --font-body CSS custom properties (see
     * organizations/pages/_document.blade.php). A template declares one via
     * structure['brand']['font_pair']; organizations inherit whatever their template set.
     *
     * The default pairing points both roles at the document's own body font, so every existing
     * template renders exactly as before - only a template that names a pairing gets the split.
     */
    'font_pairs' => [

        // Inter headlines over Newsreader serif-italic body copy: the editorial look of the
        // SD Muhammadiyah Ambulu site, where quotes and lead paragraphs are set in italic serif.
        'sekolahmu' => [
            'headline' => '"Inter", sans-serif',
            'body' => '"Newsreader", serif',
            'google' => ['Inter:wght@400;500;600;700;800', 'Newsreader:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600'],
        ],
    ],

    'radii' => [
        'sharp' => '0.375rem',
        'soft' => '1rem',
        'full' => '1.5rem',
    ],

];
