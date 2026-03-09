<?php

namespace App\Contracts;

use App\Models\Module;

interface ModuleHandler
{
  /**
   * Return data required by the Admin/Modules/List Inertia page.
   *
   * @param array $params optional params (e.g. request filters, pagination)
   * @return array props to pass to Inertia
   */
  public function getListData(Module $module, array $params = []): array;
}
