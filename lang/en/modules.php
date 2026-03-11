<?php

return array(
  'of' => 'of',
  'overview' => 'Overview',
  'related' => 'Related',
  'defaults' =>
  array(
    'name' => 'Name',
    'description' => 'Description',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'no_data' => ' No data available',
  ),
  'actions' =>
  array(
    'share' => 'Share',
    'export' => 'export',
    'placeholder' => 'Something here...',
    'bulk_action' => 'Bulk Actions',
    'delete' => 'Delete',
    'create' => 'Create',
    'search_placeholder' => 'Search In This List',
    'cancel' => 'Cancel',
    'edit' => 'Edit',
    'save' => 'Save',
    'saving' => 'Saving...',
    'saved' => 'Saved',
    'edit_module' => 'Edit this module',
    'mass_delete' => 'Delete',
    'mass_update' => 'Mass Update',
    'updating' => 'Updating...',
    'save_success' => 'Record saved successfully',
    'update_success' => 'Record updated successfully',
    'create_success' => 'Record created successfully',
    'update_error' => 'An error occurred while updating the record',
    'create_error' => 'An error occurred while creating the record',
    'save_error' => 'An error occurred while saving the record',
    'delete_success' => 'Record deleted successfully',
    'delete_error' => 'An error occurred while deleting the record',
    'deleting' => 'Deleting...',
    'delete_title' => 'Confirm deleting record',
    'delete_confirm' => 'Are you sure you want to delete this record ?',
    'delete_yes' => 'Yes',
    'delete_no' => 'No',
    'no_data_entered' => 'You have enetered no new data, No record will be saved!',
    'link' => 'Link',
    'unlink' => 'Unlink',
    'unlink_yes' => 'Yes',
    'unlink_no' => 'No',
    'unlink_process' => 'Unlinking...',
    'unlink_success' => 'Record Unlinked successfully',
    'unlink_error' => 'An error occurred while linking the records',
    'unlink_confirm_title' => 'Confirm Unlinking record',
    'unlink_confirm' => 'Are you sure you want to unlink these records ?',
    'open_new_tab' => 'Open in a new Tab',
    'quick_edit' => 'Quick Edit',
    'load_more' => 'Load More',
    'view_all' => 'View All',
    'loading' => 'Loading...',
  ),
  'linking' => array(
    'link_existing_records'  => 'Link Existing Records',
    'save'    => 'Save',
    'close'   => 'Close',
    'search'  => 'Search',
    'showing_count' => 'Showing :count records',
    'success'   => 'Linking records finished successfully',
    'error_missing_context'   => 'Missing relationship context',
    'error_lodaing_related_records' => 'Failed loading available records',
    'info_linking'  => 'Linking Records'
  ),
  'delete' =>
  array(
    'confirm_delete' => 'Confirm Delete',
    'confirm_delete_message' => 'You are about to delete :count records, are you sure ?',
    'selected_count' => 'You have selected :count records.',
    'description' => 'Select records to delete',
    'clear_selection' => 'Clear selection',
    'select_all' => 'Select all :total records in the result set',
    'delete' => 'Delete',
  ),
  'accounts' =>
  array(
    'label' => 'Accounts',
    'single_label' => 'Account',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',
      'website' => 'Website',
      'email' => 'Email',
      'phone' => 'Phone',
      'billing_address' => 'Billing Address',
      'shipping_address' => 'Shipping Address',
      'city' => 'City',
      'description' => 'Description',
      'country' => 'Country',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'contacts' =>
  array(
    'label' => 'Contacts',
    'single_label' => 'Contact',
    'fields' =>
    array(
      'name' => 'Name',
      'first_name' => 'First Name',
      'last_name' => 'Last Name',
      'email' => 'Email',
      'phone' => 'Phone',
      'position' => 'Position',
      'notes' => 'Notes',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
      'description' => 'Description',
      'id' => 'ID',
    ),
  ),
  'leads' =>
  array(
    'label' => 'Leads',
    'single_label' => 'Lead',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',
      'first_name' => 'First Name',
      'last_name' => 'Last Name',
      'email' => 'Email',
      'phone' => 'Phone',
      'company' => 'Company',
      'street' => 'Street',
      'city' => 'City',
      'zip' => 'ZIP Code',
      'description' => 'Description',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
    'actions' =>
    array(
      'cancel' => 'Cancel',
      'edit' => 'Edit',
      'save' => 'Save',
      'share' => 'Share',
      'export' => 'Export',
      'placeholder' => 'Something else here',
      'bulk_action' => 'Bulk Action',
      'delete' => 'Delete',
      'create' => 'Create',
      'search_placeholder' => 'Search In This List',
    ),
  ),
  'invoices' =>
  array(
    'label' => 'Invoices',
    'single_label' => 'Invoice',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',

      'number' => 'Invoice Number',
      'status' => 'Status',
      'issue_date' => 'Issue Date',
      'due_date' => 'Due Date',
      'currency' => 'Currency',
      'subtotal' => 'Subtotal',
      'tax' => 'Tax',
      'total' => 'Total',
      'notes' => 'Notes',
      'description' => 'Description',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'quotes' =>
  array(
    'label' => 'Quotes',
    'single_label' => 'Quote',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',

      'number' => 'Quote Number',
      'status' => 'Status',
      'valid_until' => 'Valid Until',
      'currency' => 'Currency',
      'subtotal' => 'Subtotal',
      'tax' => 'Tax',
      'total' => 'Total',
      'description' => 'Description',
      'notes' => 'Notes',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'cases' =>
  array(
    'label' => 'Cases',
    'single_label' => 'Case',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',

      'subject' => 'Subject',
      'description' => 'Description',
      'status' => 'Status',
      'priority' => 'Priority',
      'opened_at' => 'Opened At',
      'closed_at' => 'Closed At',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'emails' =>
  array(
    'label' => 'Emails',
    'single_label' => 'Email',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',
      'to' => 'To',
      'sent' => 'Sent',
      'subject' => 'Subject',
      'mailable_class' => 'Mailable Class',
      'related_id' => 'Related ID',
      'status' => 'Status',
      'description' => 'Description',
      'error' => 'Error',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'inquiries' =>
  array(
    'label' => 'Inquiries',
    'single_label' => 'Inquiry',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',
      'email' => 'Email',
      'email_confirmation' => 'Email Confirmation',
      'phone' => 'Phone',
      'message' => 'Message',
      'status' => 'Status',
      'ip' => 'IP Address',
      'description' => 'Description',
      'user_agent' => 'User Agent',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'settings' =>
  array(
    'label' => 'Settings',
    'single_label' => 'Setting',
  ),
  'opportunities' =>
  array(
    'label' => 'Opportunities',
    'single_label' => 'Opportunity',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',

      'amount' => 'Amount',
      'currency' => 'Currency',
      'description' => 'Description',
      'sales_stage' => 'Sales Stage',
      'probability' => 'Probability',
      'expected_close_date' => 'Expected Close Date',
      'type' => 'Type',
      'assigned_user_id' => 'Assigned User',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'products' =>
  array(
    'label' => 'Products',
    'single_label' => 'Product',
    'fields' =>
    array(
      'id' => 'ID',
      'name' => 'Name',
      'sku' => 'SKU',
      'description' => 'Description',
      'category' => 'Category',
      'price' => 'Price',
      'currency' => 'Currency',
      'is_active' => 'Active',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'orders' =>
  array(
    'label' => 'Orders',
    'single_label' => 'Order',
    'fields' =>
    array(
      'name' => 'Name',
      'id' => 'ID',
      'order_number' => 'Order Number',

      'opportunity_id' => 'Opportunity',
      'description' => 'Description',
      'total_amount' => 'Total Amount',
      'currency' => 'Currency',
      'status' => 'Status',
      'order_date' => 'Order Date',
      'due_date' => 'Due Date',
      'assigned_user_id' => 'Assigned User',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ),
  ),
  'update' =>
  array(
    'description' => 'Wähle Datensätze zum Aktualisieren aus',
    'update' => 'Aktualisieren',
    'cancel' => 'Abbrechen',
    'clear_selection' => 'Auswahl löschen',
    'selected_count' => 'Du hast :count Datensätze ausgewählt.',
    'select_all' => 'Alle :total Datensätze im Ergebnis auswählen',
    'confirm_update' => 'Aktualisierungsbestätigung ',
    'confirm_update_message' => 'Du bist dabei, :count Datensätze zu aktualisieren. Bist du sicher?',
    'update_yes' => 'Ja',
    'update_no' => 'Nein',
  ),
);
