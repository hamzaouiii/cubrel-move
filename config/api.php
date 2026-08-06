<?php

return [

    /*
    |--------------------------------------------------------------------------
    | REST API - Excluded Modules
    |--------------------------------------------------------------------------
    |
    | Module slugs the API never exposes. 404 regardless of any
    | token's abilities.
    |
    */

    'excluded_modules' => ['settings', 'line_items', 'users', 'userinvites', 'pdf_templates'],

];
