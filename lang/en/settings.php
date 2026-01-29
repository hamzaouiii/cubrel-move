<?php

return [
  'label' => 'Settings',
  'reset' => 'Reset',
  'cancel' => 'Cancel',
  'save' => 'Save',
  'create_new_module' => 'Create a new module',
  'next' => 'Next',
  'previous' => 'Previous',
  'saving'  => 'Saving...',
  'loading' => 'Loading...',
  'module_save_success' => 'Module saved successfuly',
  'module_update_success' => 'Module updated successfuly',
  'module_save_error' => 'An error occured while saving module',
  'select'  => 'Select...',
  'search_in_drop_down' => 'Search this list...',
  'dropdown_no_results' => 'No results',
  'setting_update_success' => 'The settings have been successfully updated',
  'setting_update_error' => 'An error occurred while updating the settings',
  'tabs' => [
    'module_settings' => 'Module Settings',
    'layouts' => 'Layouts',
    'fields' => 'Fields',
  ],
  'modules' => [
    'label' => 'Modules',
    'display_label' => 'Display Label',
    'name' => 'Name',
    'name_placeholder' => 'Module Name',
    'slug' => 'Slug',
    'icon' => 'Icon',
    'color' => 'Color',
    'show_in_sidebar' => 'Show In Sidebar',
    'description' => 'Descrption'
  ],
  // perhaps need to change this to system_fields not to conflict with settings.modules.fields
  'fields' => [
    'app_locale'                => 'Application Language',
    'show_language_switcher'    => 'Show Language Switcher',
    'border_radius'             => 'Border Radius',
    'enabled_languages'         => 'Enabled Languages',
    'secondary_color'           => 'Secondary Color',
    'theme'                     => 'Theme',
    'default_locale'            => 'Default Locale',
    'primary_color'             => 'Primary Color',
    'table_striped_rows'        => 'Striped Table Rows',
    'default_language'          => 'Default Language',
    'fallback_language'         => 'Fallback Language',
    'datetime_format'               => 'Datetime Format',
    'use_individual_module_colors' => 'Use Single Module Colors',
    'timezone'                  => 'Timezone',
    'first_day_of_week'         => 'First Day of Week',
  ],
  'groups' => [
    'email'          => 'Email Settings',
    'system'         => 'System Settings',
    'users'          => 'User Management',
    'customisations' => 'Customisations',
    'description' => [
      'email'          => 'Manage outbound and inbound emails. The email settings must be configured in order to enable users to send out email and newsletter campaigns.',
      'system'         => 'Configure the system-wide settings according to the specifications of your organization. Users can override some of the default locale settings within their user settings page.',
      'users'          => 'Create, edit, activate and deactivate users in AR-CRM. Create and manage teams and roles, including module- and field-level access.',
      'customisations' => 'Create and manage: Layouts, fields and custom modules'
    ]
  ],
  'items' => [
    'role_management'     => 'Role Management',
    'inbound_email'       => 'Inbound Email',
    'currencies'          => 'Currencies',
    'create_user'         => 'Create User',
    'fields'              => 'Fields',
    'email_queue'         => 'Email Queue',
    'locale'              => 'Locale Settings',
    'style'               => 'Style',
    'modules'             => 'Modules',
    'modulebuilder'       => 'Module Builder',
    'list_users'          => 'List Users',
    'system_settings'     => 'System Settings',
    'languages'           => 'Languages',
    'layouts'             => 'Layouts',
    'system_email_settings' => 'System Email Settings',
    'dropdowns'           => 'Dropdown Editor'
  ],
  'dropdown' => [
    'create'  => 'Create new dropdown list',
    'edit'  => 'Edit Dropdown list',
    'display_label'   => 'Display Label',
    'value'   => 'Value',
    'list_name' => 'List Name',
    'related_field' => 'Related Field'
  ]

];
