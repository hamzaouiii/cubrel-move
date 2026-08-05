# Sammelaktionen und Export in Cubrel

In diesem Artikel geht es darum, wie Sie mehrere Datensätze auf einmal auswählen, welche drei Sammelaktionen in einer Listenansicht verfügbar sind, und wie der Export funktioniert.

## Datensätze auswählen

Setzen Sie Häkchen bei einzelnen Datensätzen in einer Liste, oder nutzen Sie das Häkchen in der Kopfzeile, um jeden Datensatz der aktuellen Seite auszuwählen. Sobald eine ganze Seite ausgewählt ist, erscheint der Link **Alle N Datensätze auswählen**, mit dem Sie die Auswahl auf jeden Datensatz erweitern, der Ihrer aktuellen Suche und Ihren Filtern entspricht, über alle Seiten hinweg, nicht nur die gerade angezeigte.

Solange "alle passenden" ausgewählt sind, können Sie trotzdem einzelne Datensätze abwählen, die Sie nicht einbeziehen möchten, sie werden einzeln ausgeschlossen, statt die gesamte Auswahl zu verlieren.

## Sammelbearbeitung

Aktualisiert ein Feld auf einmal über alle ausgewählten Datensätze hinweg: Wählen Sie das Feld (schreibgeschützte Felder werden nicht angeboten), geben Sie den neuen Wert ein und bestätigen Sie. Cubrel zeigt Ihnen vor der Anwendung genau, wie viele Datensätze betroffen sind, und validiert den Wert genauso wie bei einer normalen Bearbeitung (ein Pflichtfeld lässt sich weiterhin nicht leer lassen).

## Sammellöschung

Löscht jeden ausgewählten Datensatz in einem Schritt. Sie sehen vorher eine Bestätigung mit der Anzahl der Datensätze, bevor etwas passiert.

::: warning
Das Löschen ist dauerhaft. Nach der Bestätigung gibt es kein Rückgängig und keine Wiederherstellung, prüfen Sie Ihre Auswahl also vorher sorgfältig, besonders wenn Sie "alle passenden auswählen" statt einer Auswahl von Hand verwendet haben.
:::

## Export

Exportieren Sie Ihre Auswahl, oder jeden Datensatz, der Ihrem aktuellen Filter entspricht, als **CSV** oder **JSON**. Es gibt keine Spaltenauswahl, Exporte enthalten jedes sichtbare Feld des Moduls. Der Export eines einzelnen Datensatzes umfasst auch seine Positionen, ein Sammelexport mehrerer Datensätze dagegen nicht.

Der Export läuft sofort und lädt als Datei herunter, es gibt kein Warten auf eine E-Mail oder einen Hintergrundauftrag, selbst bei einer großen Auswahl.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Kann ich Datensätze über mehrere Seiten hinweg auswählen? | Ja, über **Alle N Datensätze auswählen**, basierend auf Ihrem aktuellen Filter |
| Kann ich einzelne Datensätze aus einer "alle passenden"-Auswahl ausschließen? | Ja, wählen Sie sie einzeln ab |
| Welche Sammelaktionen gibt es? | Sammelbearbeitung, Sammellöschung, Export |
| Kann die Sammelbearbeitung mehrere Felder auf einmal ändern? | Nein, ein Feld pro Sammelbearbeitung |
| Ist die Sammellöschung rückgängig zu machen? | Nein, sie ist endgültig |
| Welche Exportformate werden unterstützt? | CSV und JSON |
| Kann ich auswählen, welche Spalten exportiert werden? | Nein, alle sichtbaren Felder sind enthalten |
| Läuft der Export sofort oder in einer Warteschlange? | Sofortiger Download, keine Warteschlange, keine E-Mail |
