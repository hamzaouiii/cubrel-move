# REST-API

Cubrel stellt eine REST-API bereit, damit ein externes System, eine Partner-Integration, ein Skript, eine Datenpipeline, Ihre Datensätze lesen und schreiben kann, ohne dass jemand sich durch die Anwendung klickt. Dieser Leitfaden beschreibt, wie Sie an einen Token kommen, wie die API aufgebaut ist, und welche Regeln sie durchsetzt.

## Einen API-Token erhalten

Der API-Zugriff läuft über Tokens, nicht über Benutzername/Passwort. Ein Administrator erstellt Tokens unter **Einstellungen → API-Tokens → Neuer Token**:

1. Geben Sie dem Token einen Namen (zu Ihrer eigenen Orientierung, "Zapier-Integration", "Nächtliches Exportskript").
2. Wählen Sie den Benutzer, in dessen Namen der Token handelt. Jede mit diesem Token gestellte Anfrage wird diesem Benutzer zugeschrieben (Datensätze, die er erstellt, zeigen zum Beispiel diesen Benutzer als Besitzer).
3. Gewähren Sie entweder **Vollzugriff** (jedes Modul, jede Aktion), oder lassen Sie das Häkchen weg und wählen einzelne **Lese-**/**Schreib-**/**Löschrechte** pro Modul. Manche Module bieten nur Lesezugriff an, siehe [Module, die die API nicht erreicht](#module-die-die-api-nicht-erreicht) unten.
4. Speichern. Der Klartext-Token wird **genau einmal** angezeigt, direkt nach dem Erstellen. Kopieren Sie ihn an einen sicheren Ort, Cubrel speichert nur einen Hash davon, verlieren Sie ihn, müssen Sie ihn widerrufen und einen neuen erstellen.

Widerrufen Sie einen Token jederzeit aus derselben Liste **API-Tokens**. Ein widerrufener Token funktioniert sofort nicht mehr.

## Anfragen authentifizieren

Senden Sie den Token als Bearer-Token bei jeder Anfrage:

```
Authorization: Bearer <ihr-token>
```

Es gibt keine Sitzung, keine Cookies, kein CSRF-Token, um das Sie sich kümmern müssten, dieser Header ist der gesamte Authentifizierungsmechanismus.

::: warning
Senden Sie immer auch einen `Accept: application/json`-Header. Ohne ihn kommt eine Anfrage mit fehlendem oder ungültigem Token nicht als sauberes 401 zurück, sondern leitet stattdessen auf die Anmeldeseite um, was verwirrend ist, wenn Sie nicht damit rechnen.
:::

## Basis-URL und Endpunkte

Jeder Endpunkt liegt unter `/api/v1/{module}`, wobei `{module}` der Slug eines Moduls ist (`leads`, `contacts`, `deals`, `accounts` und so weiter, derselbe Slug, den Sie in der URL des Moduls innerhalb der Anwendung sehen).

| Methode | Pfad | Aktion |
| --- | --- | --- |
| `GET` | `/api/v1/{module}` | Datensätze auflisten |
| `GET` | `/api/v1/{module}/{id}` | Einen Datensatz abrufen |
| `POST` | `/api/v1/{module}` | Einen Datensatz erstellen |
| `PUT` / `PATCH` | `/api/v1/{module}/{id}` | Einen Datensatz aktualisieren |
| `DELETE` | `/api/v1/{module}/{id}` | Einen Datensatz löschen |

### Datensätze auflisten

```
GET /api/v1/leads?per_page=25&sort=name&direction=asc&search=acme
```

| Parameter | Bedeutung |
| --- | --- |
| `per_page` | Datensätze pro Seite. Standardmäßig die Seitengröße Ihrer Listenansicht (meist 25). |
| `search` | Gleicht mit den durchsuchbaren Feldern des Moduls ab. |
| `sort` | Ein Feldname zum Sortieren. **Funktioniert nur für die regulären, beschreibbaren Felder des Moduls**, Sie können zum Beispiel nicht nach `id` oder `created_at` sortieren. Ein nicht erkannter Wert wird still ignoriert statt einen Fehler zu werfen, die Liste fällt in diesem Fall auf eine nicht näher festgelegte Reihenfolge zurück (nicht auf die Standardreihenfolge "neueste zuerst"). |
| `direction` | `asc` oder `desc`. Wird ignoriert, wenn `sort` nicht ebenfalls gesetzt ist. |
| `filter` | Der **Slug oder die ID eines bereits in der App für dieses Modul gespeicherten Listenfilters** (zum Beispiel bei Verkaufschancen Ihr gespeicherter Filter "Dieses Quartal offen"), kein roher Filterausdruck. |

Eine Listenantwort sieht so aus:

```json
{
  "data": [
    { "id": "...", "name": "Acme Corp", "email": "hello@acme.com", "...": "..." }
  ],
  "meta": {
    "total": 128,
    "per_page": 25,
    "current_page": 1,
    "last_page": 6
  },
  "links": {
    "next": "https://yourapp.com/api/v1/leads?page=2",
    "prev": null
  }
}
```

### Abrufen, Erstellen, Aktualisieren, Löschen

`GET`-/`POST`-/`PUT`-/`PATCH`-Antworten verpacken einen einzelnen Datensatz in `{ "data": {...} }`.
`DELETE` gibt bei Erfolg einen leeren `204 No Content`-Rumpf zurück.

`PUT`/`PATCH` sind **partielle Änderungen**, senden Sie nur die Felder, die Sie ändern möchten. Weggelassene Felder behalten ihren bestehenden Wert.

```
POST /api/v1/leads
Content-Type: application/json

{ "name": "Jane Doe", "email": "jane@example.com" }
```

Ein paar Felder werden nie von Ihnen akzeptiert, selbst wenn Sie sie senden, sie werden immer von Cubrel selbst gesetzt: **`owner_id`** (standardmäßig der Benutzer des Tokens), **`created_by`**/**`updated_by`**, **`created_at`**/**`updated_at`**, sowie jedes in der Feldkonfiguration des Moduls als schreibgeschützt oder berechnet markierte Feld. Sie zu senden ist kein Fehler, sie werden einfach ignoriert.

### Benutzerdefinierte Felder

Jedes benutzerdefinierte Feld eines Moduls ist einfach ein weiterer Schlüssel im selben flachen JSON-Objekt, sowohl beim Senden als auch beim Zurücklesen. Es gibt keinen separaten `custom_fields`-Wrapper, den Sie kennen müssten:

```json
{ "name": "Jane Doe", "referral_source": "conference" }
```

`referral_source` ist hier ein benutzerdefiniertes Feld, das am Modul Interessenten unter **Einstellungen → Module** definiert wurde, aus Sicht der API verhält es sich genau wie ein eingebautes Feld.

## Berechtigungen

Jeder Token hat eine bestimmte Menge an `Modul:Aktion`-Berechtigungen (oder Vollzugriff), festgelegt bei seiner Erstellung. Braucht eine Anfrage eine Berechtigung, die der Token nicht hat, erhalten Sie ein `403`. Das gilt pro Modul und pro Aktion, ein Token mit `leads:read`, aber ohne `leads:write`, kann Interessenten auflisten und abrufen, aber nicht erstellen, aktualisieren oder löschen.

### Module, die die API nicht erreicht

- Manche Module sind vollständig ausgeschlossen und erscheinen nie über die API, eine Anfrage an eines davon liefert immer 404, unabhängig davon, was der Token sonst darf.
- Manche Module sind **über die API nur lesbar**, Schreib-/Löschanfragen an sie scheitern immer mit `403`, selbst bei einem Token mit Vollzugriff. Das sind System-Kataloge, für die eine Partner-Integration über diese API keinen legitimen Grund hat, sie zu ändern.

Beide Listen sind eine Konfiguration der Arbeitsumgebung, kein Token-Recht kann sie überschreiben, wenden Sie sich an Ihren Administrator, falls ein von Ihnen benötigtes Modul nicht verfügbar ist.

## Ratenbegrenzung

Anfragen sind auf **60 pro Minute und Token** begrenzt (nicht authentifizierte Anfragen stattdessen pro IP-Adresse). Jede Antwort enthält die Header `X-RateLimit-Limit` und `X-RateLimit-Remaining`, damit Sie sehen, wie viel Spielraum Ihnen noch bleibt. Wird das Limit überschritten, kommt ein `429` zurück.

## Fehler

Jede Fehlerantwort ist JSON, in dieser Form:

```json
{ "message": "Für Menschen lesbare Zusammenfassung" }
```

Validierungsfehler (`422`) enthalten zusätzlich eine Aufschlüsselung pro Feld:

```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

| Status | Bedeutung |
| --- | --- |
| `401` | Fehlender oder ungültiger Token. |
| `403` | Der Token hat nicht die für dieses Modul/diese Aktion erforderliche Berechtigung, oder das Modul ist über die API nur lesbar/ausgeschlossen. |
| `404` | Das Modul oder der Datensatz existiert nicht (oder das Modul ist über die API nicht erreichbar). |
| `422` | Der Anfragerumpf hat die Validierung nicht bestanden. |
| `429` | Sie haben die Ratenbegrenzung erreicht. |

## Was in einer Antwort zurückkommt

Antworten enthalten nie etwas, das wie ein Passwort, Token oder Geheimnis aussieht, unabhängig vom Modul. Eine kleine Anzahl bestimmter Felder (zum Beispiel die gespeicherten Präferenzen eines Benutzers) wird außerdem immer aus der Antwort dieses Moduls entfernt, unabhängig davon, was Ihr Token sonst sehen darf.
