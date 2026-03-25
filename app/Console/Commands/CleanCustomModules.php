<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanCustomModules extends Command
{
  // The name of the command you'll type
  protected $signature = 'modules:clean-files';
  protected $description = 'Deletes all generated custom module models and handlers';

  public function handle()
  {
    $folders = [
      app_path('Models/Modules/Custom'),
      app_path('Handlers/Modules/Custom'),
    ];

    foreach ($folders as $folder) {
      if (File::exists($folder)) {
        // Delete all files inside the folder
        $files = File::files($folder);

        foreach ($files as $file) {
          // Safety check: don't delete .gitignore or README if you have them
          if ($file->getExtension() === 'php') {
            File::delete($file);
            $this->info("Deleted: " . $file->getFilename());
          }
        }

        $this->info("Cleaned: " . $folder);
      }
    }

    $this->info('Custom module files have been wiped clean.');
  }
}
