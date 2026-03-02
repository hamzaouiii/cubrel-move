<?php


$dropdown_lists = DB::table('dropdown_lists')->select(['key', 'values'])->get()->mapWithKeys(function ($item) {
  return [$item->key => json_decode($item->values, true)];
})->toArray();

file_put_contents(config_path('dropdown_lists.php'), "<?php\n\nreturn " . var_export($dropdown_lists, true) . ";\n");
