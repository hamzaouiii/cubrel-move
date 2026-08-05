# Layouts

Die Felder eines Moduls müssen irgendwo angeordnet werden, in seiner Listenansicht, auf seiner Datensatzseite, in den Panels für zugehörige Datensätze. Diese Anordnung ist ein **Layout**. Dieser Leitfaden beschreibt die verschiedenen Layouts eines Moduls, was jedes davon steuert, und wie Sie sie bearbeiten.

Was ein Feld ist und wie Sie eines erstellen, steht im [Leitfaden zu Feldern](fields-guide.md).

## Wo Sie sie finden

Layouts werden unter **Einstellungen → Module → [Modul] → Layouts** bearbeitet, das öffnet eine kleine Übersicht mit Links zu jedem der unten beschriebenen Layout-Typen. Jedes Modul hat seinen eigenen Satz, es gibt genau ein Layout pro Typ und Modul, gemeinsam genutzt von allen, die das Modul sehen können (Layouts sind nicht pro Benutzer oder pro Rolle).

Jeder Editor funktioniert nach demselben Grundprinzip: eine Seitenleiste "Verfügbare Felder" (oder "Verfügbare Beziehungen") auf der einen Seite, das Layout selbst auf der anderen. Ziehen Sie ein Element hinein, um es hinzuzufügen, wieder heraus, um es zu entfernen, und innerhalb des Layouts, um die Reihenfolge zu ändern. Änderungen werden erst gespeichert, wenn Sie auf **Layout speichern** klicken, eine **Zurücksetzen**-Schaltfläche stellt die zuletzt gespeicherte Version wieder her.

## Die Layout-Typen

### Liste

Steuert die Spalten, die in der Listen-/Tabellenansicht des Moduls angezeigt werden. Hier gibt es keine Gruppierung, nur eine flache, geordnete Spaltenmenge.

### Datensatz

Steuert das Layout der Datensatz-Detailseite. Felder sind in benannte **Abschnitte** gruppiert, ein Abschnitt ist eine beschriftete Gruppe von Feldern, die zusammen angezeigt werden, wie eine Karte. Sie können Abschnitte hinzufügen, umbenennen, neu anordnen und entfernen, und Felder zwischen ihnen verschieben. Jedes Feld kann im gesamten Layout nur einmal vorkommen, sobald Sie ein Feld platziert haben, fällt es aus der Liste "verfügbar", bis Sie es wieder entfernen.

Zwei besondere Abschnitte sind bei den Modulen verfügbar, auf die sie zutreffen, jeder davon lässt sich nur einmal hinzufügen:

- **Positionen**: Bei Modulen mit aktivierten Positionen (Angebote, Bestellungen, Rechnungen, oder jedes benutzerdefinierte Modul, für das Sie das eingeschaltet haben) bezieht dieser Abschnitt seine eigene Feldliste aus der Positionsstruktur statt aus den regulären Feldern des Moduls.
- **Teilnehmer**: Bei Meetings wird dieser Abschnitt automatisch erzeugt, ohne eigene konfigurierbare Felder.

Ein Datensatz-Layout braucht immer mindestens einen Abschnitt, der letzte verbleibende Abschnitt lässt sich nicht entfernen.

### Zugehörige Panels

Steuert, welche [Beziehungen](relationships-guide.md) als Panels im Zugehörig-Tab der Datensatzseite erscheinen, und in welcher Reihenfolge. Beziehungen werden in bis zu zwei Spalten (nebeneinander) angeordnet, jede mit einer geordneten Liste von Beziehungs-Panels.

Für jede Beziehung im Layout können Sie außerdem eine Handvoll zusätzlicher Felder aus dem *verknüpften* Modul wählen, die in der Kopfzeile dieses Panels angezeigt werden, zum Beispiel die E-Mail-Adresse und Telefonnummer eines verknüpften Kontakts direkt im Verkaufschancen-Panel, ohne den Kontakt öffnen zu müssen.

Hat ein Modul noch keine Beziehungen definiert, zeigt dieser Editor statt der üblichen Drag-and-drop-Ansicht einen Hinweis zum Erstellen einer Beziehung, es gibt nichts anzuordnen, solange nicht mindestens eine Beziehung existiert.

### Verknüpfungs-Panel

Steuert die Spalten im Such-Fenster, das erscheint, wenn Sie einen bestehenden Datensatz in eine Beziehung verknüpfen (siehe [Anpassen, was angezeigt wird und wonach die Suche sucht](relationships-guide.md#anpassen-was-angezeigt-wird-und-wonach-die-suche-sucht) im Leitfaden zu Beziehungen). Es funktioniert wie das Listen-Layout, mit einer Ergänzung: Jede Spalte hat eine eigene **Sortierbar**-Checkbox, mit der Sie festlegen, welche der sichtbaren Spalten sich zum Sortieren dieses Suchfensters eignen.

### Positionszuordnung

Nur relevant bei Modulen mit aktivierten Positionen. Steuert, welche Felder im Erstellen-/Bearbeiten-Formular für eine Position erscheinen, in welcher Reihenfolge, und für jedes davon, ob es manuell ausgefüllt oder aus einem Feld des Positions-Quellmoduls **automatisch befüllt** wird (meist Produkte). Zum Beispiel kann sich der Einzelpreis einer Position in dem Moment automatisch aus dem Preis des verknüpften Produkts befüllen, in dem es ausgewählt wird, sodass Sie ihn nicht erneut eintippen müssen.

## Aus einem Modul entfernte Felder räumen ihre Layouts automatisch auf

Löschen Sie ein benutzerdefiniertes Feld (siehe [Ein Feld löschen](fields-guide.md#ein-feld-löschen) im Leitfaden zu Feldern), entfernt Cubrel es automatisch aus jedem Listen-, Datensatz- oder Verknüpfungs-Panel-Layout, in dem es vorkam, es bleibt kein Layout zurück, das still auf ein nicht mehr existierendes Feld zeigt.

## Eine verwandte, aber eigenständige Funktion: PDF-Vorlagen

Rechnungen, Angebote und Bestellungen können außerdem ein PDF-Layout erhalten, das beim Erzeugen eines PDFs für diesen Datensatz verwendet wird, das wird jedoch getrennt konfiguriert, unter **Einstellungen → PDF-Vorlagen**, nicht über den Layouts-Tab eines Moduls.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wie viele Layouts hat ein Modul? | Eines pro Typ: Liste, Datensatz, Zugehörige Panels, Verknüpfungs-Panel, und (bei Positionen) Positionszuordnung. |
| Unterscheiden sich Layouts je Rolle oder Benutzer? | Nein, ein Layout pro Modul und Typ, gemeinsam genutzt von allen. |
| Kann ich Felder in Abschnitte gruppieren? | Ja, im Datensatz-Layout. Die anderen Layouts sind flache Listen. |
| Wie viele Spalten kann ein Zugehörige-Panels-Layout haben? | Bis zu zwei. |
| Was passiert mit einem Layout, wenn ich ein darin verwendetes Feld lösche? | Das Feld wird automatisch aus dem Layout entfernt. |
| Kann ich Spaltenbreiten steuern? | Nein, Layouts steuern, welche Felder/Beziehungen erscheinen und in welcher Reihenfolge, nicht deren Größe. |
