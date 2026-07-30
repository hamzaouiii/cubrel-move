<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Email extends BaseModule
{
    protected $table = 'emails';

    protected $fillable = [
        'name',
        'body',
        'from_address',
        'from_name',
        'to_addresses',
        'cc_addresses',
        'sent_at',
        'direction',
        'provider_message_id',
        'mailbox',
        'owner_id',
    ];

    protected $moduleCasts = [
        'to_addresses' => 'array',
        'cc_addresses' => 'array',
        'sent_at' => 'datetime',
    ];
}
