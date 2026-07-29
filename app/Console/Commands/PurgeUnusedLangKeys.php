<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DetectsUnusedLangKeys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class PurgeUnusedLangKeys extends Command
{
  use DetectsUnusedLangKeys;

  protected $signature = 'lang:purge-unused
                            {--locale=en : Locale used to detect unused keys}
                            {--paths=app,resources,routes,database,config : Comma-separated list of directories to scan for usages}
                            {--dry-run : List what would be removed without writing changes}
                            {--force : Skip the confirmation prompt}';

  protected $description = 'Delete translation keys that lang:unused reports as unused, from every locale';

  public function handle()
  {
    if (!class_exists(ParserFactory::class)) {
      $this->error('nikic/php-parser is not available. Run "composer install" without --no-dev.');
      return 1;
    }

    $locale = $this->option('locale');
    $localePath = lang_path($locale);

    if (!File::exists($localePath)) {
      $this->error("Locale folder [{$locale}] does not exist.");
      return 1;
    }

    $paths = array_map('trim', explode(',', $this->option('paths')));

    $this->info("Detecting unused keys against lang/{$locale}...");
    [$unused, ] = $this->findUnusedLangKeys($locale, $paths);

    if (empty($unused)) {
      $this->info('✓ No unused keys found. Nothing to purge.');
      return 0;
    }

    $this->warn(count($unused) . ' unused key(s) found:');
    foreach ($unused as $key) {
      $this->line("  - {$key}");
    }

    $namespaces = $this->mapNamespacesToFiles($localePath);
    $grouped = $this->groupKeysByFile($unused, $namespaces);

    if ($this->option('dry-run')) {
      $this->line("\nDry run: no files were changed.");
      return 0;
    }

    if (!$this->option('force') && !$this->confirm("\nRemove these keys from every locale's lang files?")) {
      $this->line('Aborted.');
      return 0;
    }

    $locales = File::directories(lang_path());
    $removedTotal = 0;
    $filesTouched = 0;

    foreach ($locales as $localeDir) {
      foreach ($grouped as $relativePath => $relativeKeys) {
        $filePath = $localeDir . DIRECTORY_SEPARATOR . $relativePath;

        if (!File::exists($filePath)) {
          continue;
        }

        $removedCount = $this->purgeKeysFromFile($filePath, $relativeKeys);

        if ($removedCount > 0) {
          $removedTotal += $removedCount;
          $filesTouched++;
          $this->line("  Removed {$removedCount} key(s) from " . $this->relativeToBase($filePath));
        }
      }
    }

    $this->info("\n✓ Removed {$removedTotal} key entr" . ($removedTotal === 1 ? 'y' : 'ies') . " across {$filesTouched} file(s).");
    return 0;
  }

  /**
   * Maps each namespace (e.g. "custom.modules.Opportunties") to the lang
   * file's path relative to a locale folder (e.g. "custom/modules/Opportunties.php").
   */
  private function mapNamespacesToFiles(string $localePath): array
  {
    $namespaces = [];

    foreach (File::allFiles($localePath) as $file) {
      if ($file->getExtension() !== 'php') {
        continue;
      }

      $namespace = str_replace(
        ['/', '\\'],
        '.',
        substr($file->getRelativePathname(), 0, -4)
      );

      $namespaces[$namespace] = $file->getRelativePathname();
    }

    // Longest namespace first, so nested namespaces (e.g. "custom.modules.Opportunties")
    // are matched before a shorter, unrelated prefix could be.
    uksort($namespaces, fn($a, $b) => strlen($b) <=> strlen($a));

    return $namespaces;
  }

  /**
   * Groups dotted keys by the file they belong to, keyed by the file's path
   * relative to a locale folder, with values being the key path within that
   * file (i.e. the namespace prefix stripped off).
   */
  private function groupKeysByFile(array $keys, array $namespaces): array
  {
    $grouped = [];

    foreach ($keys as $key) {
      foreach ($namespaces as $namespace => $relativePath) {
        if (!str_starts_with($key, $namespace . '.')) {
          continue;
        }

        $relativeKey = substr($key, strlen($namespace) + 1);
        $grouped[$relativePath][] = $relativeKey;
        break;
      }
    }

    return $grouped;
  }

  /**
   * Removes the given dotted keys from a lang file's returned array while
   * preserving the rest of the file's formatting and comments, using
   * php-parser's format-preserving printer. Returns the number of leaf
   * entries actually removed.
   */
  private function purgeKeysFromFile(string $filePath, array $relativeKeys): int
  {
    $code = File::get($filePath);

    $parser = (new ParserFactory())->createForNewestSupportedVersion();
    $oldStmts = $parser->parse($code);
    $oldTokens = $parser->getTokens();

    $traverser = new NodeTraverser();
    $traverser->addVisitor(new CloningVisitor());
    $newStmts = $traverser->traverse($oldStmts);

    $arrayNode = null;

    foreach ($newStmts as $stmt) {
      if ($stmt instanceof Node\Stmt\Return_ && $stmt->expr instanceof Node\Expr\Array_) {
        $arrayNode = $stmt->expr;
        break;
      }
    }

    if ($arrayNode === null) {
      return 0;
    }

    $unusedSet = array_flip($relativeKeys);
    $removed = 0;
    $this->pruneArray($arrayNode, '', $unusedSet, $removed);

    if ($removed === 0) {
      return 0;
    }

    $printer = new Standard();
    $newCode = $printer->printFormatPreserving($newStmts, $oldStmts, $oldTokens);

    File::put($filePath, $newCode);

    return $removed;
  }

  private function pruneArray(Node\Expr\Array_ $array, string $prefix, array $unusedSet, int &$removed): void
  {
    $items = [];

    foreach ($array->items as $item) {
      if ($item === null || !($item->key instanceof Node\Scalar\String_)) {
        $items[] = $item;
        continue;
      }

      $keyName = $item->key->value;
      $path = $prefix === '' ? $keyName : $prefix . '.' . $keyName;

      if (isset($unusedSet[$path])) {
        $removed++;
        continue;
      }

      if ($item->value instanceof Node\Expr\Array_) {
        $this->pruneArray($item->value, $path, $unusedSet, $removed);

        if (count($item->value->items) === 0) {
          continue;
        }
      }

      $items[] = $item;
    }

    $array->items = $items;
  }

  private function relativeToBase(string $path): string
  {
    return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
  }
}
