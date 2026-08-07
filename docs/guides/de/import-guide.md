# Datensätze in Cubrel importieren

In diesem Artikel geht es darum, wie Sie mit dem Import-Assistenten externe Daten in Cubrel einspielen, welche Dateiformate akzeptiert werden, wie Sie Ihre Spalten den Feldern von Cubrel zuordnen, und was passiert, wenn eine Zeile nicht ganz passt.

## Was der Import-Assistent ist

Der Import-Assistent erstellt oder aktualisiert viele Datensätze auf einmal aus einer CSV- oder JSON-Datei, statt sie einzeln einzugeben. Er führt Sie durch das Hochladen einer Datei, das Zuordnen ihrer Spalten zu den passenden Feldern, und am Ende eine Übersicht darüber, was passiert ist.

## Wo Sie ihn finden

Öffnen Sie die Listenansicht eines beliebigen Moduls (Kontakte, Verkaufschancen, welches Modul auch immer Sie importieren möchten) und klicken Sie auf den kleinen Pfeil neben **Erstellen**. **Importieren** ist eine der Optionen in diesem Menü, neben Exportieren und den übrigen Listenaktionen.

## Unterstützte Dateien

- **CSV**, mit Komma oder Semikolon getrennt. Cubrel erkennt automatisch, welches Trennzeichen Ihre Datei verwendet, Sie können das beim Zuordnen aber überschreiben, falls die Erkennung falsch liegt.
- **JSON**, als Liste von Datensätzen, zum Beispiel `[{"name": "Acme Corp", "email": "info@acme.com"}, ...]`. Ein einzelnes JSON-Objekt wird nicht akzeptiert, es muss eine Liste sein, auch wenn sie nur einen Datensatz enthält.
- Die Dateiendung muss tatsächlich `.csv` oder `.json` sein, eine Datei umzubenennen ändert nicht, was wirklich darin steht.
- Bis zu **10 MB** und **50.000 Zeilen** pro Datei. Größere Exporte sollten in kleinere Stapel aufgeteilt werden.

## Schritt für Schritt

### 1. Hochladen

Ziehen Sie Ihre Datei per Drag & Drop in den Assistenten, oder klicken Sie darauf, um stattdessen eine Datei auszuwählen.

### 2. Spalten zuordnen

Sie sehen jede in Ihrer Datei erkannte Spalte samt eines Beispielwerts aus der ersten Zeile, daneben ein Dropdown, in dem Sie festlegen, welches Feld in Cubrel damit befüllt werden soll, oder **"Nicht importieren"**, um die Spalte ganz zu überspringen. Jedes in Cubrel erforderliche Feld muss einer Spalte zugeordnet sein, bevor Sie fortfahren können.

### 3. Duplikate vermeiden (optional)

Soll der Import bestehende Datensätze aktualisieren statt Duplikate anzulegen, wählen Sie ein Feld unter **"Übereinstimmung mit bestehenden Datensätzen anhand von"**, E-Mail ist eine gängige Wahl. Jede Zeile, deren Wert mit einem bestehenden Datensatz übereinstimmt, aktualisiert diesen statt einen neuen anzulegen. Lassen Sie dies leer, wird jede Zeile Ihrer Datei zu einem brandneuen Datensatz.

### 4. Bestätigen und starten

Prüfen Sie die Zusammenfassung, wie viele Spalten zugeordnet sind und wonach abgeglichen wird, und starten Sie dann den Import.

- **Kleine Dateien (200 Zeilen oder weniger)** sind sofort fertig, Sie landen direkt bei den Ergebnissen.
- **Größere Dateien** laufen im Hintergrund mit einem Fortschrittsbalken. Sie können den Assistenten erst schließen, wenn der Vorgang abgeschlossen ist, lassen Sie den Tab also offen, während er läuft.

### 5. Ergebnisse prüfen

Sie sehen, wie viele Datensätze erstellt, wie viele aktualisiert und wie viele übersprungen wurden. Wurde etwas übersprungen, steht der Grund direkt daneben, zum Beispiel ein leeres Pflichtfeld oder ein Wert, der zu nichts Erwartetem passte, ohne separaten Download.

Sobald Sie den Assistenten schließen, aktualisiert sich Ihre Liste automatisch, sodass neu importierte Datensätze sofort erscheinen.

## Ihre Spalten passend machen

- **Auswahl- und Status-Felder** (Dinge wie Vertriebsphase oder Interessenten-Status) verstehen den auf dem Bildschirm sichtbaren Text, in jeder von Cubrel unterstützten Sprache, nicht nur den internen Rohwert. Eine Spalte voller "Gewonnen" funktioniert also genauso wie die entsprechende Übersetzung in einer anderen Sprache.
- **Ja/Nein-Felder** akzeptieren gängige Wörter wie `yes`, `no`, `true`, `false`, `1`, `0`, sowie Cubrels eigene Ja/Nein-Formulierung in jeder unterstützten Sprache.
- **Daten** werden flexibel gelesen (die gängigsten Formate funktionieren), im Zweifel ist ein klares Format wie `2026-07-15` aber immer sicher.
- **Zahlen** sollten reine Ziffern sein, entfernen Sie Währungssymbole wie `$` oder `€` vor dem Import, Kommas als Tausendertrennzeichen sind kein Problem.

## Was sich noch nicht importieren lässt

- **Verknüpfte Datensatzfelder**, Dinge wie die Firma einer Verkaufschance oder der Besitzer eines Datensatzes, da ein zuverlässiges Zuordnen eines reinen Textwerts zum richtigen verknüpften Datensatz noch nicht umgesetzt ist.
- **Adressfelder**, da sie aus mehreren Teilen bestehen (Straße, Ort, Bundesland/Region usw.), die sich nicht sauber aus einer einzelnen Spalte ableiten lassen.

Beide fehlen schlicht in der Liste der Felder, denen Sie zuordnen können, alles andere an einem Modul lässt sich importieren.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wo starte ich einen Import? | Listenansicht > Pfeil neben Erstellen > Importieren |
| Welche Dateien kann ich verwenden? | CSV (Komma oder Semikolon) oder JSON (eine Liste von Datensätzen) |
| Wie groß darf die Datei sein? | 10 MB, 50.000 Zeilen |
| Kann ich Duplikate vermeiden? | Ja, wählen Sie beim Zuordnen ein Abgleichsfeld wie E-Mail |
| Was passiert mit nicht zugeordneten Pflichtfeldern? | Der Import startet erst, wenn sie zugeordnet sind |
| Was passiert mit fehlerhaften Zeilen? | Sie werden mit Grund übersprungen, der Rest wird trotzdem importiert |
| Kann ich verknüpfte Datensätze oder Adressen importieren? | Noch nicht |
| Aktualisiert sich die Liste nach dem Import? | Ja, automatisch, sobald Sie den Assistenten schließen |
