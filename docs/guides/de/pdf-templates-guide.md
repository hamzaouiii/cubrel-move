# PDF-Vorlagen in Cubrel erstellen

In diesem Artikel geht es darum, was eine PDF-Vorlage ist, wie Sie eine aus Abschnitten und Feldern aufbauen, und wie Sie aus einem Datensatz tatsächlich ein PDF erzeugen, sobald eine Vorlage existiert.

## Was eine PDF-Vorlage ist

Eine PDF-Vorlage gehört zu einem Modul (zum Beispiel Angebote oder Rechnungen) und legt ein Layout fest, das Cubrel nutzt, um einen beliebigen Datensatz dieses Moduls in ein PDF zu verwandeln, stellen Sie es sich als ein Dokumentendesign vor, das Sie einmal bauen und für jeden Datensatz wiederverwenden. Ein Modul kann mehrere Vorlagen haben (etwa eine für Angebote und eine kürzere für eine Bestellbestätigung), eine davon als **Standard** markiert.

## Eine Vorlage aufbauen

Erstellen Sie unter **Einstellungen > PDF-Vorlagen** eine neue Vorlage und wählen Sie das Modul, zu dem sie gehört. Der Editor funktioniert per Drag-and-drop: Jede Vorlage hat eine feste **Kopfzeile** und **Fußzeile**, den Hauptteil bauen Sie, indem Sie Abschnitte hineinziehen:

- **Felder**: eine beschriftete Gruppe von Feldern, halb- oder vollbreit angeordnet.
- **Text**: ein freier Textblock für Dinge, die an kein Feld gebunden sind.
- **Trennlinie**: eine horizontale Linie, um Abschnitte optisch zu trennen.
- **Positionen**: eine Tabelle der Positionen des Datensatzes. Name, Position und Gesamtbetrag werden immer angezeigt, andere Spalten sind optional (halten Sie sich an etwa 8 Spalten oder weniger, damit die Seite nicht überläuft).
- **Beziehung**: Daten aus einem verknüpften Datensatz (zum Beispiel die Adresse der verknüpften Firma auf einem Angebot).

Kopf- und Fußzeile haben eigene Bausteine: Ihr Firmenlogo, Ihre Firmendaten, der Dokumenttitel, Seitenzahlen, das Datum, und eine einzeilige Firmeninfo-Zeile.

## Felder hinzufügen

Ziehen Sie ein Feld aus dem Panel **Verfügbare Felder** links in einen Abschnitt, Felder aus verknüpften Modulen sind dort ebenfalls aufgeführt und lassen sich aufklappen. Einmal platziert, kann bei einem Feld die Bezeichnung ein- oder ausgeblendet werden, dazu ein Anzeigestil, Titel, Untertitel, fett, klein, Bezeichnung, Status, Adresse oder gedämpft, der steuert, wie es auf der Seite aussieht.

## Vorschau vor dem Speichern

Klicken Sie jederzeit auf **Vorschau**, um ungefähr zu sehen, wie das aktuelle Layout gerendert wird, mit Platzhalter-Beispieldaten statt einem echten Datensatz, sodass Sie das Design prüfen können, ohne einen tatsächlichen Datensatz zum Testen zu brauchen.

## Ein PDF aus einem Datensatz erzeugen

Öffnen Sie einen Datensatz und klicken Sie auf das PDF-Symbol. Hat das Modul nur eine Vorlage, erzeugt Cubrel das PDF sofort. Gibt es mehr als eine, sehen Sie eine Auswahl (die Standardvorlage ist markiert), um zu wählen, welche verwendet werden soll.

## Branding

Name, Adresse, Telefon, E-Mail, Website und Logo Ihres Unternehmens stammen aus Ihren globalen Unternehmenseinstellungen und werden automatisch in Kopf-/Fußzeile jeder Vorlage übernommen, aktuell gibt es kein Logo oder Farbschema pro Vorlage.

## Bekannte Einschränkungen

- Kein Bild-Upload über das gemeinsame Firmenlogo hinaus.
- Keine manuelle Steuerung von Seitenumbrüchen innerhalb eines Abschnitts.
- Die PDF-Erzeugung läuft synchron (Sie warten, während es erstellt wird), sehr große Stapel von PDFs auf einmal werden deshalb nicht empfohlen.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wo verwalte ich Vorlagen? | **Einstellungen > PDF-Vorlagen** |
| Kann ein Modul mehrere Vorlagen haben? | Ja, eine davon als Standard markiert |
| Wie füge ich den Wert eines Feldes ein? | Aus **Verfügbare Felder** in einen Abschnitt ziehen |
| Kann ich vor dem Speichern eine Vorschau sehen? | Ja, mit Platzhalter-Beispieldaten |
| Wie erzeuge ich ein PDF aus einem Datensatz? | Datensatz öffnen und auf das PDF-Symbol klicken |
| Was, wenn ein Modul mehrere Vorlagen hat? | Sie sehen eine Auswahl, die Standardvorlage ist markiert |
| Kann ich pro Vorlage ein anderes Logo festlegen? | Nein, Branding kommt global aus den Unternehmenseinstellungen |
