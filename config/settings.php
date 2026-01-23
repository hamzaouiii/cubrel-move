<?php
return [
  'system'   => [
    'label' =>  'settings.groups.system',
    'description' => 'settings.groups.description.system',
    'items' => [
      'currencies' => [
        'name' => 'Currencies',
        'slug' => 'currencies',
        'label' => 'settings.items.currencies',
        'path' => '/settings/system/currencies',
        'isActive' => 0,
        'icon' => 'fa-solid fa-coins'
      ],
      'locale' => [
        'name' => 'Locale',
        'slug' => 'locale',
        'label' => 'settings.items.locale',
        'path' => '/settings/system/locale',
        'isActive' => 1,
        'icon' => 'fa-solid fa-globe'
      ],
      'style' => [
        'name' => 'Style',
        'slug' => 'style',
        'label' => 'settings.items.style',
        'path' => '/settings/system/style',
        'isActive' => 1,
        'icon' => 'fa-solid fa-paint-brush'
      ],
      'system-settings' => [
        'name' => 'System Settings',
        'slug' => 'system-settings',
        'label' => 'settings.items.system_settings',
        'path' => '/settings/system/general',
        'isActive' => 0,
        'icon' => 'fa-solid fa-gear'
      ],
      'languages' => [
        'name' => 'Languages',
        'slug' => 'languages',
        'label' => 'settings.items.languages',
        'path' => '/settings/system/languages',
        'isActive' => 0,
        'icon' => 'fa-solid fa-language'
      ]
    ]
  ],
  'email'   => [
    'label' =>  'settings.groups.email',
    'description' => 'settings.groups.description.email',
    'items' => [
      'inbound-email' => [
        'name' => 'Inbound Email',
        'slug' => 'inbound-email',
        'label' => 'settings.items.inbound_email',
        'path' => '/settings/email/inbound',
        'isActive' => 0,
        'icon' => 'fa-solid fa-inbox'
      ],
      'email-queue' => [
        'name' => 'Email Queue',
        'slug' => 'email-queue',
        'label' => 'settings.items.email_queue',
        'path' => '/settings/email/queue',
        'isActive' => 0,
        'icon' => 'fa-solid fa-envelope-open-text'
      ],
      'system-email-settings' => [
        'name' => 'System Email Settings',
        'slug' => 'system-email-settings',
        'label' => 'settings.items.system_email_settings',
        'path' => '/settings/email/general',
        'isActive' => 0,
        'icon' => 'fa-solid fa-envelope-circle-check'
      ]
    ]
  ],
  'users'   => [
    'label' =>  'settings.groups.users',
    'description' => 'settings.groups.description.users',
    'items' => [
      'role-management' => [
        'name' => 'Role Management',
        'slug' => 'role-management',
        'label' => 'settings.items.role_management',
        'path' => '/settings/users/roles',
        'isActive' => 0,
        'icon' => 'fa-solid fa-user-shield'
      ],
      'create-user' => [
        'name' => 'Create User',
        'slug' => 'create-user',
        'label' => 'settings.items.create_user',
        'path' => '/settings/users/create',
        'isActive' => 0,
        'icon' => 'fa-solid fa-user-plus'
      ],
      'list-users' => [
        'name' => 'List Users',
        'slug' => 'list-users',
        'label' => 'settings.items.list_users',
        'path' => '/settings/users',
        'isActive' => 0,
        'icon' => 'fa-solid fa-users'
      ]
    ]
  ],
  'customisation'  => [
    'label' =>  'settings.groups.customisations',
    'description' => 'settings.groups.description.customisations',
    'items' => [
      'modulebuilder' => [
        'name' => 'Modulebuilder',
        'slug' => 'modulebuilder',
        'label' => 'settings.items.modulebuilder',
        'path' => '/settings/modulebuilder',
        'isActive' => 1,
        'icon' => 'fa-solid fa-plug-circle-plus'
      ],
      'modules' => [
        'name' => 'Modules',
        'slug' => 'modules',
        'label' => 'settings.items.modules',
        'path' => '/settings/modules',
        'isActive' => 1,
        'icon' => 'fa-solid fa-cubes'
      ],
      'dropdowns' => [
        'name' => 'Dropdowns',
        'slug' => 'dropdowns',
        'label' => 'settings.items.dropdowns',
        'path' => '/settings/dropdowns',
        'isActive' => 1,
        'icon' => 'fa-solid fa-list'
      ]
    ]
  ],
];
