<?php


$layouts = DB::table('layouts')->select(['type', 'definition'])->where('module_name', 'Orders')->get()->mapWithKeys(function ($item) {
  return [$item->type => json_decode($item->definition, true)];
})->toArray();

file_put_contents(config_path('orders.php'), "<?php\n\nreturn " . var_export($layouts, true) . ";\n");
