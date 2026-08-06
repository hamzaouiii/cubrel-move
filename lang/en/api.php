<?php

return [

    'errors' => [
        'unauthenticated' => 'Unauthenticated.',
        'not_found' => 'Resource not found.',
        'forbidden_read_only_module' => 'Module :module is read-only via the API.',
        'forbidden_missing_ability' => 'Token lacks the :module::verb ability.',
        'too_many_requests' => 'Too many requests. Please try again shortly.',
        'generic' => 'HTTP :status error.',
    ],

];
