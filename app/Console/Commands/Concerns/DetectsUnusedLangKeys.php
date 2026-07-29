<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait DetectsUnusedLangKeys
{
  /**
   * Namespaces that Laravel (or another framework piece) resolves by
   * convention rather than through an explicit __()/$t() call, so a static
   * scan will always report them as unused even though they're live.
   */
  protected function excludedNamespaces(): array
  {
    return [
      // Laravel's validator auto-resolves these by rule name.
      'validation',
    ];
  }

  /**
   * Returns [unusedKeys (list), dynamicPrefixes (prefix => true)].
   */
  protected function findUnusedLangKeys(string $locale, array $paths): array
  {
    $definedKeys = $this->loadDefinedKeys(lang_path($locale));
    $definedKeySet = array_flip($definedKeys);

    $files = $this->collectFiles($paths);

    $dynamicPrefixes = $this->extractDynamicPrefixes($files);
    $usedKeys = $this->extractLiteralKeyUsages($files, $definedKeySet);
    $excludedNamespaces = $this->excludedNamespaces();

    $unused = [];

    foreach ($definedKeys as $key) {
      if (isset($usedKeys[$key])) {
        continue;
      }

      if ($this->matchesDynamicPrefix($key, $dynamicPrefixes)) {
        continue;
      }

      if ($this->matchesDynamicPrefix($key, array_fill_keys($excludedNamespaces, true))) {
        continue;
      }

      $unused[] = $key;
    }

    sort($unused);

    return [$unused, $dynamicPrefixes];
  }

  protected function loadDefinedKeys(string $localePath): array
  {
    $keys = [];

    foreach (File::allFiles($localePath) as $file) {
      if ($file->getExtension() !== 'php') {
        continue;
      }

      $namespace = str_replace(
        ['/', '\\'],
        '.',
        substr($file->getRelativePathname(), 0, -4)
      );

      $flattened = $this->flatten(require $file->getPathname());

      foreach (array_keys($flattened) as $key) {
        $keys[] = "{$namespace}.{$key}";
      }
    }

    return $keys;
  }

  protected function flatten(array $array, string $prefix = ''): array
  {
    $result = [];

    foreach ($array as $key => $value) {
      $newKey = $prefix === '' ? $key : $prefix . '.' . $key;

      if (is_array($value)) {
        $result += $this->flatten($value, $newKey);
      } else {
        $result[$newKey] = $value;
      }
    }

    return $result;
  }

  protected function collectFiles(array $paths): array
  {
    $extensions = ['php', 'vue', 'js'];
    $files = [];

    foreach ($paths as $path) {
      $fullPath = base_path($path);

      if (!File::exists($fullPath)) {
        continue;
      }

      foreach (File::allFiles($fullPath) as $file) {
        if (in_array($file->getExtension(), $extensions)) {
          $files[] = $file->getPathname();
        }
      }
    }

    return $files;
  }

  /**
   * Looks for interpolated/concatenated strings that look like translation
   * keys, e.g. __("modules.{$type}.label") or `settings.modules.${key}_hint`.
   * Two passes: a precise one restricted to the actual translation-call
   * patterns per file type (__() in PHP, $t()/t() in Vue/JS), and a looser
   * one that catches keys built as data first and resolved elsewhere (e.g.
   * a computed hint-key passed to $t() later). Returns the static prefix
   * before the variable part, so keys under that prefix can be excluded
   * since they may be used dynamically.
   */
  protected function extractDynamicPrefixes(array $files): array
  {
    $dynamicPrefixes = [];

    // Any quoted/backtick string containing an interpolation whose static
    // lead-in looks like a dotted key path (e.g. "settings.modules.").
    $looseKeyPattern = '/([\'"`])((?:[a-z0-9_]+\.)+[a-z0-9_]*(?:\$\{?[^\'"`]*|\{\$[^\'"`]*)?)\1/i';

    foreach ($files as $path) {
      $content = File::get($path);
      $extension = pathinfo($path, PATHINFO_EXTENSION);
      $callPattern = $this->callPatternFor($extension);

      $keys = [];

      if ($callPattern && preg_match_all($callPattern, $content, $matches)) {
        $keys = array_merge($keys, $matches[2]);
      }

      if (preg_match_all($looseKeyPattern, $content, $matches)) {
        $keys = array_merge($keys, $matches[2]);
      }

      foreach ($keys as $key) {
        if (!preg_match('/[\$\{]/', $key)) {
          continue;
        }

        $prefix = rtrim(preg_split('/[\$\{]/', $key)[0], '.');

        if ($prefix !== '') {
          $dynamicPrefixes[$prefix] = true;
        }
      }
    }

    return $dynamicPrefixes;
  }

  /**
   * Some modules don't pass a translation key straight into __()/$t()/t() —
   * they store the key string in config/data (e.g. dropdown option labels)
   * and resolve it dynamically elsewhere. So instead of only matching
   * translation-function calls, treat any quoted string literal that
   * exactly equals a defined key as a usage, wherever it appears.
   */
  protected function extractLiteralKeyUsages(array $files, array $definedKeySet): array
  {
    $usedKeys = [];
    $pattern = '/([\'"`])((?:\\\\.|(?!\1).)*)\1/';

    foreach ($files as $path) {
      $content = File::get($path);

      if (!preg_match_all($pattern, $content, $matches)) {
        continue;
      }

      foreach ($matches[2] as $literal) {
        if (isset($definedKeySet[$literal])) {
          $usedKeys[$literal] = true;
        }
      }
    }

    return $usedKeys;
  }

  /**
   * Which translation-function names to look for, per file type.
   * Backend only ever uses __(); Vue/JS use $t() in templates and the
   * `const t = proxy.$t` alias in <script>.
   */
  protected function callPatternFor(string $extension): ?string
  {
    return match ($extension) {
      'php' => '/\b__\(\s*([\'"`])((?:(?!\1).)*)\1/',
      'vue', 'js' => '/(?:\$t|\bt)\(\s*([\'"`])((?:(?!\1).)*)\1/',
      default => null,
    };
  }

  protected function matchesDynamicPrefix(string $key, array $dynamicPrefixes): bool
  {
    foreach (array_keys($dynamicPrefixes) as $prefix) {
      if ($key === $prefix || str_starts_with($key, $prefix . '.')) {
        return true;
      }
    }

    return false;
  }
}
