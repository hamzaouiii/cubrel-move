<?php

return [

    'errors' => [
        'unauthenticated' => 'Nicht authentifiziert.',
        'not_found' => 'Ressource nicht gefunden.',
        'forbidden_read_only_module' => 'Modul :module ist über die API nur lesbar.',
        'forbidden_missing_ability' => 'Token besitzt nicht die Berechtigung :module::verb.',
        'relationship_conflict' => 'Diese Verknüpfung besteht bereits oder verletzt die Kardinalitätsregeln der Beziehung.',
        'too_many_requests' => 'Zu viele Anfragen. Bitte noch einmal versuchen.',
        'generic' => 'HTTP-:status-Fehler.',
    ],

];
