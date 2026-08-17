<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Moverequests extends BaseModule
{
    protected $table = 'moverequests';

    protected $guarded = [];

    protected $moduleCasts = [
        'zimmeranzahl' => 'integer',
        'wohnflaeche' => 'decimal:2',
        'stockwerk' => 'integer',
        'etagenanzahl' => 'integer',
        'aufzug_vorhanden' => 'boolean',
        'abholadresse' => 'array',
        'zieladresse' => 'array',
        'entfernung_km' => 'decimal:2',
        'geschaetztes_volumen_m3' => 'decimal:2',
        'geschaetzter_preis_von' => 'decimal:2',
        'geschaetzter_preis_bis' => 'decimal:2',
        'langer_tragweg' => 'boolean',
        'zerbrechliche_gegenstaende' => 'boolean',
        'demontage' => 'boolean',
        'montage' => 'boolean',
        'wunschtermin' => 'date',
        'angebotener_preis' => 'decimal:2',
    ];
}
