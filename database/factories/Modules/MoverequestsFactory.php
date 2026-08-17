<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MoverequestsFactory extends Factory
{
  public function definition(): array
  {
    $faker = \Faker\Factory::create('de_DE');

    $objekttyp = $faker->randomElement(['haus', 'wohnung', 'wohnung', 'studio']);
    $status = $faker->randomElement([...array_fill(0, 5, 'neu'), ...array_fill(0, 4, 'geprueft'), ...array_fill(0, 3, 'angebot_erstellt'), ...array_fill(0, 3, 'konvertiert'), ...array_fill(0, 3, 'verloren')]);

    $zimmeranzahl = match ($objekttyp) {
      'haus' => $faker->numberBetween(4, 8),
      'wohnung' => $faker->numberBetween(2, 5),
      'studio' => 1,
    };

    $wohnflaeche = match ($objekttyp) {
      'haus' => $faker->randomFloat(1, 90, 240),
      'wohnung' => $faker->randomFloat(1, 40, 130),
      'studio' => $faker->randomFloat(1, 18, 35),
    };

    $etagenanzahl = $objekttyp === 'haus' ? $faker->numberBetween(1, 3) : $faker->numberBetween(1, 8);
    $stockwerk = $objekttyp === 'haus' ? 0 : $faker->numberBetween(0, $etagenanzahl);

    $zieladresse = [
      'street' => $faker->streetAddress(),
      'postal_code' => $faker->postcode(),
      'city' => $faker->city(),
      'state' => null,
      'country' => 'Deutschland',
    ];

    $entfernungKm = $faker->randomFloat(1, 2, 380);
    $volumen = round($wohnflaeche * $faker->randomFloat(2, 0.28, 0.42), 1);
    $preisVon = round($volumen * $faker->randomFloat(2, 40, 55), 2);
    $preisBis = round($preisVon + $faker->randomFloat(2, 150, 900), 2);

    $angebotenerPreis = in_array($status, ['angebot_erstellt', 'konvertiert', 'verloren'], true)
      ? round($faker->randomFloat(2, $preisVon, $preisBis + 200), 2)
      : null;

    $ablehnungsgruende = [
      'Kunde hat sich für ein günstigeres Angebot entschieden.',
      'Termin konnte nicht eingehalten werden.',
      'Umzug wurde vom Kunden verschoben, kein neuer Kontakt.',
      'Zu kurzfristige Anfrage, keine Kapazität.',
      'Kunde hat nicht mehr reagiert.',
    ];

    return [
      'id' => (string) Str::uuid(),

      'name' => 'Anfrage ' . $zieladresse['city'] . ' (' . $faker->numberBetween(1000, 9999) . ')',
      'description' => $faker->optional(0.3)->realText(100),

      'objekttyp' => $objekttyp,
      'zimmeranzahl' => $zimmeranzahl,
      'wohnflaeche' => $wohnflaeche,
      'stockwerk' => $stockwerk,
      'etagenanzahl' => $etagenanzahl,
      'aufzug_vorhanden' => $stockwerk > 0 ? $faker->boolean(50) : false,

      'abholadresse' => [
        'street' => $faker->streetAddress(),
        'postal_code' => $faker->postcode(),
        'city' => $faker->city(),
        'state' => null,
        'country' => 'Deutschland',
      ],
      'zieladresse' => $zieladresse,

      'entfernung_km' => $entfernungKm,
      'geschaetztes_volumen_m3' => $volumen,
      'geschaetzter_preis_von' => $preisVon,
      'geschaetzter_preis_bis' => $preisBis,

      'langer_tragweg' => $faker->boolean(20),
      'zerbrechliche_gegenstaende' => $faker->boolean(40),
      'demontage' => $faker->boolean(30),
      'montage' => $faker->boolean(30),

      'wunschtermin' => \Carbon\Carbon::instance($faker->dateTimeBetween('-2 weeks', '+4 months')),
      'quelle' => $faker->randomElement(['widget', 'widget', 'telefon', 'empfehlung', 'laufkundschaft']),

      'angebotener_preis' => $angebotenerPreis,
      'ablehnungsgrund' => $status === 'verloren' ? $faker->randomElement($ablehnungsgruende) : null,

      'status' => $status,

      'created_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-6 months', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-1 month', 'now'))->utc(),
    ];
  }
}
