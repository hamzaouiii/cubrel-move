<?php

return [
    'accounts' => 
    [
      'label'    => 'Firmen',
    ],
    'contacts' =>  
    [
      'label'    => 'Kontakte',
    ],
    
    'leads' => [
        'label'    => 'Interessenten',
        
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

        'actions' => [
            'cancel'          => 'Abbrechen',
            'edit'            => 'Bearbeiten',
            'save'            => 'Speichern',
            'share'           => 'Teilen',
            'export'            => 'Exportieren',
            'placeholder'       => 'Irgendetwas hier',
            'bulk_action'       => 'Sammelaktion',
            'delete'            => 'Löschen',
            'create'          => 'Erstellen',
            'search_placeholder' => 'In dieser Liste suchen'
        ],

      ],

    'invoices' => [
    'label'    => 'Rechnungen',
      ],
    'quotes'   => [
    'label'    => 'Angebote',
     ],
    'cases'    => [
    'label'    => 'Tickets',
      ],
    'emails'   => [
    'label'    => 'E-Mails',
      ],
    'inquiries' => 
    [
    'label'    => 'Anfragen',
    ],
    'money' => [
    'label'    => 'Geld',
]
];
