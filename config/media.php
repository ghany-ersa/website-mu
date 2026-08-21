<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk that organization media uploads (logos, banners,
    | gallery photos, etc.) are stored on. Defaults to Cloudflare R2 ('r2');
    | tests pin this to 'public' via phpunit.xml so they never touch R2.
    |
    */

    'disk' => env('MEDIA_DISK', 'r2'),

];
