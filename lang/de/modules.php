<?php

return [
  'defaults' => [
    'name' => 'Name',
    'created_at' => 'Erstellt am',
    'updated_at' => 'Aktualisiert am',

  ],
  'of' => 'von',

  'actions' => [
    'share'              => 'Teilen',
    'export'             => 'Exportieren',
    'placeholder'        => 'Irgendetwas hier',
    'bulk_action'        => 'Sammelaktion',
    'delete'             => 'Löschen',
    'create'             => 'Erstellen',
    'search_placeholder' => 'In dieser Liste suchen',
    'cancel'             => 'Abbrechen',
    'edit'               => 'Bearbeiten',
    'save'               => 'Speichern',
    'saving'            => 'Wird gespeichert...',
    'saved'             => 'Gespeichert',
    'updating'          => 'Wird aktualisiert...',
    'save_success'      => 'Datensatz wurde erfolgreich gespeichert',
    'update_success'    => 'Datensatz wurde erfolgreich aktualisiert',
    'create_success'    => 'Datensatz wurde erfolgreich erstellt',
    'update_error'      => 'Beim Aktualisieren des Datensatzes ist ein Fehler aufgetreten',
    'create_error'      => 'Beim Erstellen des Datensatzes ist ein Fehler aufgetreten',
    'save_error'        => 'Beim Speichern des Datensatzes ist ein Fehler aufgetreten',
  ],

  'accounts' => [
    'label'  => 'Firmen',
    'fields' => [
      'id'               => 'ID',
      'name'             => 'Name',
      'website'          => 'Webseite',
      'email'            => 'E-Mail',
      'phone'            => 'Telefon',
      'billing_address'  => 'Rechnungsadresse',
      'shipping_address' => 'Lieferadresse',
      'city'             => 'Stadt',
      'country'          => 'Land',
      'created_at'       => 'Erstellt am',
      'updated_at'       => 'Aktualisiert am',
    ],
  ],

  'contacts' => [
    'label'  => 'Kontakte',
    'fields' => [
      'id'         => 'ID',
      'name'       => 'Name',
      'account_id' => 'Firma',
      'first_name' => 'Vorname',
      'last_name'  => 'Nachname',
      'email'      => 'E-Mail',
      'phone'      => 'Telefon',
      'position'   => 'Position',
      'notes'      => 'Notizen',
      'created_at' => 'Erstellt am',
      'updated_at' => 'Aktualisiert am',
    ],
  ],

  'leads' => [
    'label' => 'Interessenten',

    'fields' => [
      'id'          => 'ID',
      'name'        => 'Name',
      'first_name'  => 'Vorname',
      'last_name'   => 'Nachname',
      'email'       => 'E-Mail',
      'phone'       => 'Telefon',
      'company'     => 'Firma',
      'street'      => 'Straße',
      'city'        => 'Stadt',
      'zip'         => 'PLZ',
      'description' => 'Beschreibung',
      'created_at'  => 'Erstellt am',
      'updated_at'  => 'Aktualisiert am',
    ],

  ],

  'invoices' => [
    'label'  => 'Rechnungen',
    'fields' => [
      'id'          => 'ID',
      'name'        => 'Name',
      'account_id'  => 'Firma',
      'contact_id'  => 'Kontakt',
      'quote_id'    => 'Angebot',
      'number'      => 'Rechnungsnummer',
      'status'      => 'Status',
      'issue_date'  => 'Rechnungsdatum',
      'due_date'    => 'Fälligkeitsdatum',
      'currency'    => 'Währung',
      'subtotal'    => 'Zwischensumme',
      'tax'         => 'Steuer',
      'total'       => 'Gesamtbetrag',
      'notes'       => 'Notizen',
      'created_at'  => 'Erstellt am',
      'updated_at'  => 'Aktualisiert am',
    ],
  ],

  'quotes' => [
    'label'  => 'Angebote',
    'fields' => [
      'id'          => 'ID',
      'name'        => 'Name',
      'account_id'  => 'Firma',
      'contact_id'  => 'Kontakt',
      'number'      => 'Angebotsnummer',
      'status'      => 'Status',
      'valid_until' => 'Gültig bis',
      'currency'    => 'Währung',
      'subtotal'    => 'Zwischensumme',
      'tax'         => 'Steuer',
      'total'       => 'Gesamtbetrag',
      'notes'       => 'Notizen',
      'created_at'  => 'Erstellt am',
      'updated_at'  => 'Aktualisiert am',
    ],
  ],

  'cases' => [
    'label'  => 'Tickets',
    'fields' => [
      'id'          => 'ID',
      'name'        => 'Name',
      'account_id'  => 'Firma',
      'contact_id'  => 'Kontakt',
      'subject'     => 'Betreff',
      'description' => 'Beschreibung',
      'status'      => 'Status',
      'priority'    => 'Priorität',
      'opened_at'   => 'Geöffnet am',
      'closed_at'   => 'Geschlossen am',
      'created_at'  => 'Erstellt am',
      'updated_at'  => 'Aktualisiert am',
    ],
  ],

  'emails' => [
    'label'  => 'E-Mails',
    'fields' => [
      'id'             => 'ID',
      'name'           => 'Name',
      'to'             => 'An',
      'sent'           => 'Gesendet',
      'subject'        => 'Betreff',
      'mailable_class' => 'Mailable Klasse',
      'related_id'     => 'Zugehörige ID',
      'status'         => 'Status',
      'error'          => 'Fehler',
      'created_at'     => 'Erstellt am',
      'updated_at'     => 'Aktualisiert am',
    ],
  ],

  'inquiries' => [
    'label'  => 'Anfragen',
    'fields' => [
      'id'                 => 'ID',
      'name'               => 'Name',
      'email'              => 'E-Mail',
      'email_confirmation' => 'E-Mail Bestätigung',
      'phone'              => 'Telefon',
      'message'            => 'Nachricht',
      'status'             => 'Status',
      'ip'                 => 'IP Adresse',
      'user_agent'         => 'User Agent',
      'created_at'         => 'Erstellt am',
      'updated_at'         => 'Aktualisiert am',
    ],
  ],

  'money' => [
    'label'  => 'Geld',
    'fields' => [
      'id'          => 'ID',
      'name'        => 'Name',
      'description' => 'Beschreibung',
      'data'        => 'Daten',
      'created_at'  => 'Erstellt am',
      'updated_at'  => 'Aktualisiert am',
      'deleted_at'  => 'Gelöscht am',
    ],
  ],

  'books' => [
    'label'  => 'Bücher',
    'fields' => [
      'id'          => 'ID',
      'name'        => 'Name',
      'description' => 'Beschreibung',
      'data'        => 'Daten',
      'created_at'  => 'Erstellt am',
      'updated_at'  => 'Aktualisiert am',
      'deleted_at'  => 'Gelöscht am',
    ],
  ],

  'settings' => [
    'label' => 'Einstellungen',
  ],
];
