<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
  public function definition(): array
  {
    $faker = \Faker\Factory::create('de_DE');

    $lang = $faker->boolean(50) ? 'de' : 'en';

    $data = [
      'de' => [
        'adjectives' => [
          'Digitales',
          'Intelligentes',
          'Automatisiertes',
          'Modulares',
          'Effizientes',
          'Zentrales',
          'Flexibles',
          'Modernes',
          'Skalierbares',
          'Integriertes'
        ],
        'domains' => [
          'Daten',
          'Dokumenten',
          'Lager',
          'Kunden',
          'Projekt',
          'Energie',
          'Workflow',
          'Sicherheits',
          'Analyse',
          'Monitoring'
        ],
        'types' => [
          'System',
          'Plattform',
          'Tool',
          'Software',
          'Modul',
          'Manager',
          'Suite',
          'Service'
        ]
      ],
      'en' => [
        'adjectives' => [
          'Smart',
          'Digital',
          'Advanced',
          'Integrated',
          'Modular',
          'Automated',
          'Efficient',
          'Flexible',
          'Scalable',
          'Modern'
        ],
        'domains' => [
          'Data',
          'Document',
          'Inventory',
          'Customer',
          'Project',
          'Energy',
          'Workflow',
          'Security',
          'Analytics',
          'Monitoring'
        ],
        'types' => [
          'System',
          'Platform',
          'Tool',
          'Software',
          'Module',
          'Manager',
          'Suite',
          'Service'
        ]
      ]
    ];

    $vocab = $data[$lang];

    $name =
      $faker->randomElement($vocab['adjectives']) . ' ' .
      $faker->randomElement($vocab['domains']) . ' ' .
      $faker->randomElement($vocab['types']);

    return [
      'id' => (string) \Illuminate\Support\Str::uuid(),

      'name' => $name,
      'sku' => strtoupper(\Illuminate\Support\Str::random(8)),
      'description' => $faker->realText(150),

      'category' => $faker->randomElement([
        'software',
        'hardware',
        'services',
        'consulting'
      ]),

      'price' => $faker->randomFloat(2, 50, 5000),
      'currency' => $faker->randomElement(['EUR', 'USD', 'GBP']),
      'is_active' => $faker->boolean(90),

      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
}
