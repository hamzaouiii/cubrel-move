<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Account;
use Illuminate\Database\Eloquent\Builder;

class AccountsModuleHandler extends BaseModuleHandler
{
  protected string $model = Account::class;
  protected array $searchable = [
    'name',
    'website',
    'email',
    'phone',
    'billing_address',
    'shipping_address',
    'city',
    'country',
  ];


  protected function query(array $params = []): Builder
  {
    $query = Account::query();


    return $query;
  }
}
