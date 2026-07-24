<?php

return [
    // Gemeinsame Benachrichtigungs-Elemente (vendor/notifications/email.blade.php)
    'hello' => 'Hallo!',
    'whoops' => 'Hoppla!',
    'regards' => 'Mit freundlichen Grüßen,',
    'trouble_link' => 'Falls der Button „:actionText" nicht funktioniert, kopiere die folgende URL in deinen Browser:',

    // Passwort zurücksetzen
    'reset' => [
        'subject' => 'Passwort zurücksetzen',
        'intro' => 'Du erhältst diese E-Mail, weil wir eine Anfrage zum Zurücksetzen des Passworts für dein Konto erhalten haben.',
        'action' => 'Passwort zurücksetzen',
        'expires' => 'Dieser Link zum Zurücksetzen des Passworts läuft in :count Minuten ab.',
        'no_action' => 'Falls du keine Zurücksetzung des Passworts angefordert hast, ist keine weitere Aktion erforderlich.',
    ],

    'set_password' => [
        'subject' => 'Lege dein Passwort fest',
        'intro' => 'Für dich wurde ein Konto erstellt. Klicke auf den Button unten, um dein Passwort festzulegen und loszulegen.',
        'action' => 'Passwort festlegen',
        'expires' => 'Dieser Link läuft in :count Minuten ab.',
        'no_action' => 'Falls du diese E-Mail nicht erwartet hast, kannst du sie ignorieren.',
    ],

    'invitation' => [
        'subject' => 'Du wurdest zu :app eingeladen',
        'title' => 'Du wurdest zu :app eingeladen',
        'heading' => 'Du wurdest eingeladen',
        'body' => 'Du wurdest zu :app eingeladen. Klick auf den Button unten, um dein Konto einzurichten und loszulegen.',
        'cta' => 'Einladung annehmen',
        'expires' => 'Dieser Link läuft am :date ab.',
        'fallback' => 'Wenn die Schaltfläche nicht funktioniert, kopiere diese URL in deinen Browser:',
        'disclaimer' => 'Wenn du diese Einladung nicht erwartet hast, kannst du diese E-Mail ignorieren.',
    ],

    'setup' => [
        'subject' => ':app-Instanz einrichten',
        'title' => ':app-Instanz einrichten',
        'heading' => 'Willkommen bei :app ',
        'body' => 'Schön, dass du dich für :app entschieden hast. Klicke auf den Button unten, um dein Super-Admin-Konto zu erstellen und die Einrichtung abzuschließen.',
        'cta' => 'Einrichtung abschließen',
        'expires' => 'Dieser Link läuft am :date ab.',
        'fallback' => 'Wenn die Schaltfläche nicht funktioniert, kopiere diese URL in deinen Browser:',
        'disclaimer' => 'Wenn du diese E-Mail nicht erwartet hast, kannst du sie ignorieren.',
    ],

    'contact_admin' => [
        'subject' => ':app — Neue Kontaktformular-Anfrage',
        'heading' => 'Neue Kontaktformular-Anfrage',
        'label_name' => 'Name',
        'label_email' => 'E-Mail',
        'label_phone' => 'Telefon',
        'label_message' => 'Nachricht',
        'sent_on' => 'Gesendet am :date',
    ],

    'contact_confirmation' => [
        'subject' => 'Bestätigung: Deine Nachricht wurde erhalten',
        'heading' => 'Vielen Dank für deine Nachricht',
        'greeting' => 'Hallo :name,',
        'body' => 'Wir haben deine Nachricht erhalten und melden uns so bald wie möglich bei dir.',
        'label' => 'Deine Nachricht:',
        'regards' => 'Mit freundlichen Grüßen,',
    ],

    'common' => [
        'all_rights_reserved' => 'Alle Rechte vorbehalten.',
    ],

    'notifications' => [
        'view_action' => 'Ansehen',
        'record_assigned' => [
            'subject' => ':module wurde dir zugewiesen',
            'body' => '**:user** hat dir **:module** **„:record"** zugewiesen.',
        ],
        'meeting_invite' => [
            'subject' => 'Du wurdest zu einem Termin eingeladen',
            'body' => '**:user** hat dich zu **:meeting** eingeladen.',
        ],
        'task_due_soon' => [
            'subject' => 'Aufgabe bald fällig',
            'body' => '**:task** ist fällig **:due**.',
        ],
        'invite_accepted' => [
            'subject' => 'Deine Einladung wurde angenommen',
            'body' => '**:user** hat die Einladung angenommen und ein Konto erstellt.',
        ],
        'invite_expired' => [
            'subject' => 'Deine Einladung ist abgelaufen',
            'body' => 'Die an **:email** gesendete Einladung ist abgelaufen.',
        ],
        'record_activity' => [
            'subject' => 'Aktivität bei „:module"',
            'body' => [
                'updated' => '**:user** hat **:module** **„:record"** aktualisiert.',
                'deleted' => '**:user** hat **:module** **„:record"** gelöscht.',
                'linked' => '**:user** hat eine neue Aktivität mit **:module** **„:record"** verknüpft.',
            ],
        ],
        'impersonated' => [
            'subject' => 'Auf dein Konto wurde zugegriffen',
            'body' => '**:user** hat dein Konto am **:time** verwendet.',
        ],
    ],
];
