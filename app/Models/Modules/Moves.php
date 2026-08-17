<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Moves extends BaseModule
{
    protected $table = 'moves';

    protected $guarded = [];

    protected $moduleCasts = [
        'umzugstermin' => 'date',
        'abholadresse' => 'array',
        'zieladresse' => 'array',
        'entfernung_km' => 'decimal:2',
        'anzahl_umzugshelfer' => 'integer',
        'endgueltiges_volumen_m3' => 'decimal:2',
        'endpreis' => 'decimal:2',
        'langer_tragweg' => 'boolean',
        'zerbrechliche_gegenstaende' => 'boolean',
        'demontage' => 'boolean',
        'montage' => 'boolean',
    ];
}
