<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Contact;
use Illuminate\Database\Eloquent\Builder;

class ContactsModuleHandler extends BaseModuleHandler
{
  protected function query(array $params = []): Builder
  {
    $query = Contact::query();

    // apply filters here if needed

    return $query;
  }
  protected string $model = Contact::class;
}
