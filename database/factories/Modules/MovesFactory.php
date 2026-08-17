<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MovesFactory extends Factory
{
  public function definition(): array
  {
    $faker = \Faker\Factory::create('de_DE');

    $status = $faker->randomElement([...array_fill(0, 4, 'geplant'), ...array_fill(0, 2, 'in_durchfuehrung'), ...array_fill(0, 6, 'abgeschlossen'), ...array_fill(0, 1, 'storniert')]);

    $zahlungsstatus = match ($status) {
      'abgeschlossen' => $faker->randomElement(['bezahlt', 'bezahlt', 'bezahlt', 'anzahlung_erhalten']),
      'storniert' => 'unbezahlt',
      'in_durchfuehrung' => $faker->randomElement(['anzahlung_erhalten', 'bezahlt', 'unbezahlt']),
      default => $faker->randomElement(['unbezahlt', 'unbezahlt', 'anzahlung_erhalten']),
    };

    $umzugstermin = match ($status) {
      'abgeschlossen' => $faker->dateTimeBetween('-8 months', '-1 day'),
      'storniert' => $faker->dateTimeBetween('-4 months', '+2 months'),
      'in_durchfuehrung' => $faker->dateTimeBetween('-1 day', '+1 day'),
      default => $faker->dateTimeBetween('+1 day', '+3 months'),
    };

    $zieladresse = [
      'street' => $faker->streetAddress(),
      'postal_code' => $faker->postcode(),
      'city' => $faker->city(),
      'state' => null,
      'country' => 'Deutschland',
    ];

    $entfernungKm = $faker->randomFloat(1, 3, 420);
    $volumen = $faker->randomFloat(1, 8, 110);
    $preisProM3 = $faker->randomFloat(2, 45, 75);
    $endpreis = $status === 'storniert' ? 0 : round($volumen * $preisProM3 + $entfernungKm * 1.2, 2);

    $notizenOptionen = [
      'Kunde wünscht Anruf 30 Minuten vor Ankunft.',
      'Parkverbotszone muss vorab beantragt werden.',
      'Klavier muss über den Balkon transportiert werden.',
      'Zugang nur über Hinterhof möglich.',
      'Kunde ist sehr zeitkritisch, pünktliches Erscheinen wichtig.',
      null,
      null,
    ];

    return [
      'id' => (string) Str::uuid(),

      'name' => 'Umzug nach ' . $zieladresse['city'],
      'description' => $faker->optional(0.4)->realText(100),

      'umzugstermin' => \Carbon\Carbon::instance($umzugstermin),

      'abholadresse' => [
        'street' => $faker->streetAddress(),
        'postal_code' => $faker->postcode(),
        'city' => $faker->city(),
        'state' => null,
        'country' => 'Deutschland',
      ],
      'zieladresse' => $zieladresse,

      'entfernung_km' => $entfernungKm,
      'anzahl_umzugshelfer' => $faker->numberBetween(2, 6),
      'endgueltiges_volumen_m3' => $volumen,
      'endpreis' => $endpreis,
      'zahlungsstatus' => $zahlungsstatus,

      'langer_tragweg' => $faker->boolean(25),
      'zerbrechliche_gegenstaende' => $faker->boolean(45),
      'demontage' => $faker->boolean(35),
      'montage' => $faker->boolean(35),

      'notizen' => $faker->randomElement($notizenOptionen),

      'status' => $status,

      'created_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-10 months', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-1 month', 'now'))->utc(),
    ];
  }
}
