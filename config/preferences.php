<?php


return [
  'tabs' => [
    'general' => [
      'label' => 'globals.preferences.tabs.general',
      'fields' => [
        'app_locale' => [
          'type' => 'lang_switcher',
          'label' => 'settings.fields.app_locale',
          'validation' => 'nullable|string|in:en,de',
        ],
        'date_format' => [
          'type' => 'date',
          'label' => 'settings.fields.date_format',
          'validation' => 'nullable|string',
        ],
        'datetime_format' => [
          'type' => 'datetime',
          'label' => 'settings.fields.datetime_format',
          'validation' => 'nullable|string',
        ],
      ],
    ],
    'appearance' => [
      'label' => 'globals.preferences.tabs.appearance',
      'fields' => [
        'theme' => [
          'type' => 'theme_switcher',
          'label' => 'settings.fields.theme',
          'validation' => 'nullable|string|in:light,dark',
        ],
      ],
    ],
    'color-branding' => [
      'label' => 'globals.preferences.tabs.color_branding',
      'fields' => [
        'primary_color' => [
          'type' => 'color',
          'label' => 'settings.fields.primary_color',
          'validation' => 'nullable|string',
        ],
        'secondary_color' => [
          'type' => 'color',
          'label' => 'settings.fields.secondary_color',
          'validation' => 'nullable|string',
        ],
        'success_color' => [
          'type' => 'color',
          'label' => 'settings.fields.success_color',
          'validation' => 'nullable|string',
        ],
        'danger_color' => [
          'type' => 'color',
          'label' => 'settings.fields.danger_color',
          'validation' => 'nullable|string',
        ],
             'use_individual_module_colors' => [
          'type' => 'bool',
          'label' => 'settings.fields.use_individual_module_colors',
          'validation' => 'nullable|boolean',
        ],
      ],
    ],
    'lists' => [
      'label' => 'globals.preferences.tabs.lists',
      'fields' => [
        'related_panel_limit' => [
          'type' => 'int',
          'label' => 'settings.fields.related_panel_limit',
          'validation' => 'nullable|integer',
        ],
        'list_view_limit' => [
          'type' => 'int',
          'label' => 'settings.fields.list_view_limit',
          'validation' => 'nullable|integer',
        ],
        'linking_panel_limit' => [
          'type' => 'int',
          'label' => 'settings.fields.linking_panel_limit',
          'validation' => 'nullable|integer',
        ],
      ],
    ],
    'notifications' => [
      'label' => 'globals.preferences.tabs.notifications',
      'fields' => [
        'notify_email_record_assigned' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_record_assigned',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_meeting_invite' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_meeting_invite',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_task_due_soon' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_task_due_soon',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_invite_accepted' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_invite_accepted',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_invite_expired' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_invite_expired',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_record_activity' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_record_activity',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_impersonated' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_impersonated',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_record_converted' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_record_converted',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_transformation_triggered' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_transformation_triggered',
          'validation' => 'nullable|boolean',
        ],
        'notify_email_transformation_automation_failed' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_email_transformation_automation_failed',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_record_assigned' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_record_assigned',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_meeting_invite' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_meeting_invite',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_task_due_soon' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_task_due_soon',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_invite_accepted' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_invite_accepted',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_invite_expired' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_invite_expired',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_record_activity' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_record_activity',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_impersonated' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_impersonated',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_record_converted' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_record_converted',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_transformation_triggered' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_transformation_triggered',
          'validation' => 'nullable|boolean',
        ],
        'notify_inapp_transformation_automation_failed' => [
          'type' => 'bool',
          'label' => 'settings.fields.notify_inapp_transformation_automation_failed',
          'validation' => 'nullable|boolean',
        ],
      ],
    ],
  ],

  'theme_options' => [
    ['label' => 'Light', 'value' => 'light'],
    ['label' => 'Dark', 'value' => 'dark'],
  ],
];
