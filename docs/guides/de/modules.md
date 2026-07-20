# Module

Module sind das, woraus Cubrel aufgebaut ist. Verkaufschancen, Kontakte,
Firmen, Tickets, Angebote, Bestellungen, Rechnungen, Produkte, jedes davon
ist ein Modul: ein Datensatztyp mit eigener Listenansicht, eigener
Datensatzseite, eigenen Feldern und einem eigenen Platz in der Seitenleiste.
Wenn Sie etwas erfassen möchten, das Cubrel noch nicht abdeckt (Ausrüstung,
Projekte, Garantien, was auch immer Ihr Unternehmen betreibt), warten Sie
nicht auf ein neues Release, Sie erstellen sich das passende Modul einfach
selbst.

Dieser Leitfaden erklärt, woraus ein Modul besteht, wie Sie mit dem
Modul-Ersteller eines erstellen, und wie Sie es anschließend mit der
Modulverwaltung pflegen.

## Was ein Modul ist

Jedes Modul besteht aus denselben Bestandteilen, egal ob es mit Cubrel
ausgeliefert wurde oder Sie es selbst erstellt haben:

- **Attribute** — Name, Symbol, Farbe, Kategorie und ein paar
  Verhaltensschalter (weiter unten beschrieben).
- **Felder** — die Informationen, die ein Datensatz enthält (ein Textfeld,
  ein Datum, eine Auswahlliste, ein Verweis auf ein anderes Modul, und so
  weiter).
- **Layouts** — wie diese Felder in der Listenansicht, auf der
  Datensatzseite und in den Panels für zugehörige Datensätze angeordnet
  sind.
- **Beziehungen** — optionale Verknüpfungen zu anderen Modulen (eine
  Verkaufschance, die zu einer Firma gehört; eine Rechnung, die mit einem
  Kontakt verknüpft ist). Details dazu finden Sie im [Leitfaden zu
  Beziehungen](./relationships-guide.md).

Mitgelieferte Module (Interessenten, Firmen, Kontakte, Verkaufschancen,
Angebote, Bestellungen, Rechnungen, Produkte, Tickets, sowie Systembereiche
wie Einstellungen und Benutzer) sind bereits vorkonfiguriert.
Benutzerdefinierte Module sind solche, die ein Administrator über die
Oberfläche erstellt, einmal veröffentlicht, funktionieren sie genau wie ein
mitgeliefertes Modul: derselbe Platz in der Seitenleiste, dieselben
Datensatzseiten, dieselben Feld- und Layout-Editoren, dieselben
Berechtigungen. Ab diesem Zeitpunkt gibt es keinen funktionalen Unterschied
mehr zwischen einem "mitgelieferten" und einem "benutzerdefinierten" Modul.

## Modul-Attribute

Das sind die Einstellungen, die ein Modul beschreiben. Die meisten davon
legen Sie beim Erstellen eines Moduls fest, Sie können sie jedoch jederzeit
später über die Einstellungsseite des Moduls wieder ändern.

| Attribut | Was es steuert |
| --- | --- |
| **Anzeige Bezeichnung** | Der Pluralname, der in Menüs und Überschriften angezeigt wird (z. B. "Verkaufschancen"). |
| **Einzelanzeige Bezeichnung** | Der Name, der bei der Bezugnahme auf einen einzelnen Datensatz verwendet wird (z. B. "Verkaufschance"). |
| **Systembezeichnung (Slug)** | Der interne Bezeichner des Moduls, automatisch aus der Anzeige Bezeichnung generiert. Er erscheint in der URL und kann nach dem Erstellen des Moduls nicht mehr geändert werden. Eine Handvoll Wörter ist reserviert und kann nicht verwendet werden (`fields`, `modules`, `labels`, `settings`, `users`, `roles`, `permissions`, `relationships`, `layouts`, `dropdowns`). |
| **Beschreibung** | Eine kurze Erklärung, die anderen Administratoren in der Modulliste angezeigt wird, für reguläre Benutzer nicht sichtbar. |
| **Symbol & Farbe** | Wird in der Seitenleiste, in Seitenüberschriften und auf Datensatzkarten verwendet. |
| **Kategorie** | Ordnet das Modul einem Abschnitt der Seitenleiste zu (Vertrieb, Umsatz, Kundenservice usw.). |
| **Im Sidebar anzeigen** | Ob das Modul einen eigenen Eintrag in der Navigation erhält. |
| **Hat Positionen** | Macht aus dem Modul ein Angebots-/Bestellungs-/Rechnungs-artiges Modul, dessen Datensätze eine Liste bepreister Positionen enthalten, statt (oder zusätzlich zu) regulären Feldern. |
| **Positions-Quellmodul** | Nur relevant, wenn "Hat Positionen" aktiviert ist. Das ist das Katalogmodul (meist Produkte), aus dem die Positionen ausgewählt werden. Es kann nur geändert werden, solange das Modul noch keine Datensätze mit Positionen besitzt, sobald echte Angebote/Bestellungen/Rechnungen existieren, ist die Quelle festgelegt, da eine nachträgliche Änderung die Verknüpfung zwischen bestehenden Positionen und ihren Katalogeinträgen zerstören würde. |

Jedes Modul besitzt außerdem automatisch fünf Felder, die Sie nicht selbst
hinzufügen müssen: **Name**, **Beschreibung**, **Gehört zu** (Besitzer),
**Erstellt am** und **Aktualisiert am**. Diese existieren aus
Konsistenzgründen auf jedem Modul und können nicht entfernt werden, Sie
können sie jedoch in einem Layout ausblenden, falls Sie sie nicht anzeigen
möchten.

## Ein Modul erstellen: der Modul-Ersteller

Neue Module werden mit dem **Modul-Ersteller** erstellt, zu finden unter
**Einstellungen → Module → Erstellen**. Es handelt sich um einen kurzen
Assistenten:

### 1. Grundlagen

Füllen Sie die Attribute des Moduls aus, Anzeige Bezeichnung, Einzelanzeige
Bezeichnung, Symbol, Farbe, Kategorie, Beschreibung, Sichtbarkeit in der
Seitenleiste, und ob es Positionen hat. Die Systembezeichnung (Slug) wird
automatisch aus der Anzeige Bezeichnung erzeugt und aktualisiert sich live
während der Eingabe, bis Sie fortfahren.

Ihr Fortschritt wird währenddessen als privater Entwurf gespeichert, sodass
Sie jederzeit unterbrechen und später weitermachen können. Immer nur ein
Administrator kann an einem gegebenen Modul-Entwurf arbeiten, beginnt jemand
anderes mit demselben Modul, erhält diese Person einen eigenen, neuen
Entwurf, statt mit Ihrem zu kollidieren. Ein unangetasteter Entwurf gibt
sich nach ein paar Stunden Inaktivität automatisch wieder frei, sodass er
nicht für immer gesperrt bleibt, falls jemand ein Modul beginnt und nie
fertigstellt.

### 2. Felder

Fügen Sie die Felder hinzu, die Ihr Modul benötigt, Text, Zahlen, Daten,
Auswahllisten, Währungsbeträge, Verweise auf andere Module, und so weiter.
Felder, die Sie in diesem Schritt hinzufügen, werden zu dauerhaften,
fest eingebauten Feldern des Moduls, gleichrangig mit den Feldern jedes
mitgelieferten Moduls.

Feldnamen dürfen nicht mit den fünf automatischen Feldern kollidieren, die
jedes Modul bereits besitzt (Name, Beschreibung, Gehört zu, Erstellt am,
Aktualisiert am), oder mit den Positionsfeldern, falls für das Modul
Positionen aktiviert sind.

### 3. Veröffentlichen

Das Veröffentlichen ist der Schritt, der den Entwurf in ein echtes,
einsatzbereites Modul verwandelt. Sie sehen dabei einen kurzen
Fortschrittsbildschirm, während im Hintergrund das Modul eingerichtet, seine
Felder vorbereitet, sein Speicher angelegt und es abschließend aktiviert
wird. Das dauert in der Regel nur wenige Sekunden.

Schlägt ein Schritt dabei fehl, erhalten Sie die Wahl, dieselbe
Veröffentlichung erneut zu **wiederholen**, oder **abzubrechen und
aufzuräumen**, wodurch alles verworfen wird, was der Vorgang bereits
begonnen hatte, das Modul wird wieder in einen Entwurf zurückversetzt, und
die bereits definierten Felder bleiben dabei erhalten, sodass Sie das
Problem beheben und es erneut versuchen können, ohne von vorn zu beginnen.

Sobald die Veröffentlichung abgeschlossen ist, ist das Modul live: Es
erscheint in der Seitenleiste (falls Sie das so gewählt haben), taucht in
der Modulliste auf und verhält sich genau wie jedes andere Modul in Cubrel.

## Ein Modul pflegen: die Modulverwaltung

Sobald ein Modul existiert, mitgeliefert oder benutzerdefiniert, laufen
alltägliche Änderungen über die **Modulverwaltung**, unter **Einstellungen
→ Module**. Das ist eine Liste aller Module im System; das Öffnen eines
Moduls führt zu dessen Einstellungsseite mit folgenden Tabs:

- **Moduleinstellungen** — die oben beschriebenen Attribute (Name, Symbol,
  Farbe, Kategorie, Beschreibung, Sichtbarkeit in der Seitenleiste).
  Änderungen hier wirken sich sofort aus, ohne einen
  Veröffentlichungsschritt.
- **Felder** — Felder jederzeit hinzufügen, bearbeiten oder entfernen,
  nachdem das Modul live ist, ohne dass irgendetwas erneut veröffentlicht
  werden muss.
- **Layouts** — Felder in der Listenansicht, auf der Datensatzseite und in
  den zugehörigen Panels anordnen.
- **Beziehungen** — das Modul mit anderen verknüpfen.

Ein paar Dinge sind bewusst gesperrt, sobald ein Modul veröffentlicht wurde,
und lassen sich nicht mehr über die Modulverwaltung ändern: die
Systembezeichnung (Slug), ob das Modul Positionen hat, und (sobald
Positions-Datensätze existieren) sein Positions-Quellmodul. Alles andere,
Name, Symbol, Farbe, Kategorie, Beschreibung, Sichtbarkeit in der
Seitenleiste, Felder und Layouts, lässt sich jederzeit frei anpassen.

### Modul-Ersteller vs. Modulverwaltung, kurz gefasst

- **Modul-Ersteller** dient ausschließlich dem Erstellen eines
  brandneuen Moduls. Sie nutzen ihn einmal pro Modul, vom Entwurf bis zur
  Veröffentlichung.
- **Modulverwaltung** ist für alles Weitere zuständig, der Ort, an dem Sie
  tatsächlich im Alltag Zeit verbringen, um die Einstellungen, Felder und
  Layouts eines bestehenden Moduls zu bearbeiten.
