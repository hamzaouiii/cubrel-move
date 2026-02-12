<?php

namespace App\Handlers\Modules;

use App\Models\Modules\ContactMessage;
use Illuminate\Database\Eloquent\Builder;

class CustomerInquriesModuleHandler extends BaseModuleHandler
{
  protected function query(array $params = []): Builder
  {
    $query = ContactMessage::query();

    // apply filters here if needed

    return $query;
  }
  protected string $model = ContactMessage::class;
}
