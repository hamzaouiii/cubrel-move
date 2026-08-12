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
        'provider_message_id',
        'mailbox',
        'owner_id',
    ];

    protected $moduleCasts = [
        'to_addresses' => 'array',
        'cc_addresses' => 'array',
        'sent_at' => 'datetime',
    ];

    public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label'    => $this->name,
            'sublabel' => $this->from_address,
        ]);
    }

    // emails table has no description column, unlike other modules
    protected function searchableFields(): array
    {
        return array_diff(parent::searchableFields(), ['description']);
    }
}
