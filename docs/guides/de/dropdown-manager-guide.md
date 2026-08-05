# Dropdown-Listen in Cubrel verwalten

In diesem Artikel geht es darum, was eine Dropdown-Liste ist, wie Sie eine aufbauen, und welche zusätzlichen Gestaltungsoptionen für Listen vom Typ Status verfügbar sind.

## Was eine Dropdown-Liste ist

Eine Dropdown-Liste ist eine wiederverwendbare Menge von Optionen, jede mit einer Bezeichnung und einem Wert, die die Auswahl- und Status-Felder eines Moduls antreibt. Manche Listen werden mit Cubrel mitgeliefert (der Typ einer Verkaufschance, der Status einer Bestellung, die Priorität eines Tickets und so weiter), eigene erstellen Sie unter **Einstellungen > Dropdowns**.

Eine Liste kann an ein bestimmtes Feld gebunden sein, oder gemeinsam über mehrere Felder hinweg genutzt werden.

## Eine Liste erstellen

Vergeben Sie unter **Einstellungen > Dropdowns > Erstellen** einen Namen für die Liste (Cubrel erzeugt daraus automatisch den internen Schlüssel) und fügen Sie Optionen nacheinander hinzu, jede mit einer Bezeichnung. Neue Listen starten als einfache Optionslisten, ohne Farb- oder Symbolgestaltung.

## Eine Liste bearbeiten

Öffnen Sie eine beliebige Liste aus der Dropdown-Tabelle, um sie umzubenennen, Optionen hinzuzufügen oder zu entfernen, oder (bei Status-Listen) sie neu zu gestalten und anzuordnen. Speichern Sie über die Schaltfläche, oder mit **Strg+S**.

::: warning
Das Löschen einer Option entfernt sie sofort aus der Liste, Cubrel prüft dabei nicht vorher, ob bestehende Datensätze diesen Wert noch verwenden. Das Entfernen einer Option wirkt sich nur auf das aus, was künftig verfügbar ist, Datensätze, die bereits den alten Wert haben, behalten ihn.
:::

## Status-Listen: Farbe, Symbol und Reihenfolge

Eine von einem Status-Feld verwendete Liste erhält zusätzliche Steuerungsmöglichkeiten. Klappen Sie eine Option auf (das Stiftsymbol), um Folgendes festzulegen:

- **Farbe** und **Hintergrundfarbe**, über eine Farbauswahl.
- **Symbol**, über eine Symbolauswahl.

Eine Live-Vorschau zeigt genau, wie das Abzeichen aussehen wird, während Sie es anpassen. Status-Optionen lassen sich außerdem per Ziehen **neu anordnen**, das steuert die Reihenfolge, in der sie in Dropdowns und Status-Auswahlen in der gesamten Anwendung erscheinen. Einfache (Nicht-Status-)Listen haben keine Farb-/Symbolsteuerung und kein Ziehen zum Neuanordnen, ihre Reihenfolge entspricht schlicht der Reihenfolge, in der Sie sie hinzugefügt haben.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wo verwalte ich Dropdown-Listen? | **Einstellungen > Dropdowns** |
| Kann eine Liste über mehrere Felder hinweg wiederverwendet werden? | Ja, oder an ein bestimmtes Feld gebunden |
| Unterstützen alle Listen Farben und Symbole? | Nein, nur Listen, die von Status-Feldern verwendet werden |
| Kann ich Optionen neu anordnen? | Ja, bei Status-Listen, per Ziehen; einfache Listen behalten die Hinzufüge-Reihenfolge |
| Was passiert, wenn ich eine bereits auf Datensätzen verwendete Option lösche? | Bestehende Datensätze behalten ihren Wert, er lässt sich künftig nur nicht mehr neu auswählen |
| Sehen mitgelieferte Standardlisten (wie der Typ einer Verkaufschance) anders aus als eigene? | Nein, beide funktionieren nach dem Erstellen genau gleich |
