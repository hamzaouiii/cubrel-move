<?php

// Run with: php scripts/fa.php

// 1) Paths – adjust if your files are elsewhere
$cssAllPath    = __DIR__ . '/../storage/app/FA.css';
$cssBrandsPath = __DIR__ . '/../storage/app/solid.css';
$outPath       = __DIR__ . '/../storage/app/fa-icons-solid.json';

// 2) Read CSS files
$cssAll = file_get_contents($cssAllPath);
if ($cssAll === false) {
    throw new RuntimeException("Could not read all.min.css at $cssAllPath");
}

$cssBrands = file_get_contents($cssBrandsPath);
if ($cssBrands === false) {
    throw new RuntimeException("Could not read brands.min.css at $cssBrandsPath");
}

// 3) Helper: extract icon names from a CSS string
function extractIconNames(string $css): array
{
    $pattern = '/\.fa-([a-z0-9-]+):before\{content:"\\\\[a-f0-9]{1,6}"\}/i';
    preg_match_all($pattern, $css, $matches);

    // $matches[1] contains the icon names
    $names = array_unique($matches[1]);
    sort($names);

    return $names;
}

// 4) Extract all icon names and brand icon names
$allNames    = extractIconNames($cssAll);
$brandNames  = extractIconNames($cssBrands);

// 5) Remove brand icons from the full list → "solid-ish" icon names
$solidNames = array_values(array_diff($allNames, $brandNames));
sort($solidNames);

// 6) Build JSON structure
$icons = [];
foreach ($solidNames as $name) {
    $icons[] = [
        'name'  => $name,
        'style' => 'solid',
        'class' => 'fa-solid fa-' . $name,
    ];
}

// 7) Write JSON
if (! is_dir(dirname($outPath))) {
    mkdir(dirname($outPath), 0755, true);
}

file_put_contents(
    $outPath,
    json_encode($icons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo 'Found ' . count($allNames)   . " total icons in all.min.css\n";
echo 'Found ' . count($brandNames) . " brand icons in brands.min.css\n";
echo 'Generated ' . count($icons)  . " solid-only icons into $outPath\n";
