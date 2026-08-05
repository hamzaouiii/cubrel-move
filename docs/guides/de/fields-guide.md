# Felder

Felder sind das, woraus ein Datensatz besteht. Der Betrag einer Verkaufschance, die E-Mail-Adresse eines Kontakts, die Priorität eines Tickets, jedes davon ist ein Feld. Dieser Leitfaden beschreibt die verschiedenen Feldtypen, die Cubrel unterstützt, was Sie bei jedem davon einstellen können, und wie Sie Felder erstellen, bearbeiten und entfernen.

Wie Module und Felder zusammenspielen, steht im [Leitfaden zu Modulen](modules.md).

## Die Felder, die jedes Modul bereits hat

Jedes Modul, mitgeliefert oder benutzerdefiniert, kommt mit fünf Feldern, die Sie nie selbst hinzufügen müssen: **Name**, **Beschreibung**, **Gehört zu**, **Erstellt am** und **Aktualisiert am**. Sie lassen sich in einem Layout ausblenden, wenn Sie sie nicht sehen möchten, gelöscht werden können sie aber nicht, sie existieren aus Konsistenzgründen auf jedem Datensatz.

Module mit Positionen (Angebote, Bestellungen, Rechnungen und jedes benutzerdefinierte Modul, für das Sie Positionen aktiviert haben) erhalten zusätzlich vier Felder, die Sie ebenfalls nicht selbst anlegen: **Zwischensumme**, **Rabattbetrag**, **Steuerbetrag** und **Gesamtbetrag**. Diese werden automatisch aus den Positionen des Datensatzes berechnet und sind deshalb immer schreibgeschützt.

## Feldtypen

Beim Anlegen eines Feldes wählen Sie einen dieser Typen:

| Typ | Was er speichert |
| --- | --- |
| **Text** | Eine einzelne Textzeile. |
| **Mehrzeiliger Text** | Ein mehrzeiliger Textblock. |
| **Zahl**, **Ganzzahl**, **Dezimalzahl** | Numerische Werte. |
| **Währung** | Ein Geldbetrag, formatiert nach der Währungseinstellung Ihres Unternehmens. |
| **Prozentsatz** | Eine Zahl zwischen 0 und 100. |
| **E-Mail** | Eine E-Mail-Adresse. |
| **Telefon** | Eine Telefonnummer. |
| **URL** | Eine Webadresse. |
| **Datum** | Ein Kalenderdatum. |
| **Datum & Uhrzeit** | Ein Kalenderdatum mit Uhrzeit. |
| **Checkbox** | Ein einfacher Ja/Nein-Schalter. |
| **Auswahl** | Eine einzelne Wahl aus einer einfachen, von Ihnen definierten Optionsliste. |
| **Status** | Eine einzelne Wahl aus einer Optionsliste, wobei jede Option eine eigene Farbe und optional ein Symbol hat, dargestellt als farbiges Abzeichen statt als reiner Text. |
| **Datensatzverweis** | Ein Verweis auf einen Datensatz eines anderen Moduls (zum Beispiel ein Feld an einem Ticket, das auf einen Kontakt zeigt). |
| **Adresse** | Eine strukturierte Adresse aus Straße, Postleitzahl, Ort, Bundesland/Region und Land. |
| **Bild** | Ein hochgeladenes Bild (JPEG, PNG, WEBP oder GIF, bis zu 2 MB). |

### Auswahl vs. Status

Die beiden sehen sich ähnlich, beide geben Ihnen ein Dropdown mit Optionen, aber Status ist der reichhaltigere der beiden Typen. Bei einem Status-Feld hat jede Option eine eigene Textfarbe, Hintergrundfarbe und optional ein Symbol, sodass der Wert als farbiges Abzeichen auf dem Datensatz erscheint. Bei einem Auswahl-Feld sind die Optionen einfacher Text ohne Farbe oder Symbol. Nutzen Sie Status für Dinge wie eine Vertriebsphase oder eine Prioritätsstufe, wo eine Farbcodierung auf einen Blick hilft, und Auswahl für eine einfache Optionsliste, bei der das nicht nötig ist.

### Datensatzverweis-Felder

Ein Datensatzverweis-Feld verweist auf ein anderes Modul, Sie legen beim Erstellen des Feldes fest, auf welches. Das ist etwas anderes als eine [Beziehung](relationships-guide.md): Eine Beziehung verbindet zwei Module generell und erscheint als Panel im Zugehörig-Tab, während ein Datensatzverweis-Feld ein einzelnes Feld auf einem Datensatz ist, das auf genau einen verknüpften Datensatz zeigt (zum Beispiel das Feld "Bezieht sich auf" einer Aufgabe).

### Adressfelder

Ein Adressfeld speichert fünf Angaben gemeinsam als ein Feld: Straße, Postleitzahl, Ort, Bundesland/Region und Land (das Land wird aus einer durchsuchbaren Länderliste gewählt). Auf der Datensatzseite wird es als mehrzeilige, formatierte Adresse mit einer Schaltfläche zum Kopieren in die Zwischenablage angezeigt, in Listen- und Tabellenansichten verdichtet es sich zu einer einzigen Zeile.

## Was Sie an einem Feld einstellen können

Jedes Feld hat ein paar Einstellungen, die Sie ein- oder ausschalten können, unabhängig vom Typ:

| Einstellung | Was sie bewirkt |
| --- | --- |
| **Pflichtfeld** | Der Datensatz kann erst gespeichert werden, wenn das Feld einen Wert hat. (Bei Checkbox-Feldern nicht verfügbar.) |
| **Schreibgeschützt** | Das Feld lässt sich im Datensatzformular nicht bearbeiten, nützlich für Felder, die auf anderem Weg befüllt werden sollen. |
| **Ausgeblendet** | Das Feld ist in Layouts nicht sichtbar, seine Daten bleiben aber erhalten. |
| **Durchsuchbar** | Das Feld wird bei der Suche innerhalb des Moduls einbezogen. |
| **Filterbar** | Das Feld lässt sich zum Filtern einer Listenansicht verwenden. |
| **Sortierbar** | Das Feld lässt sich zum Sortieren einer Listenspalte verwenden. |
| **Standardwert** | Ein Wert, der beim Anlegen eines neuen Datensatzes bereits vorausgefüllt ist. |

Bei textähnlichen Feldern (Text, Mehrzeiliger Text, E-Mail, Telefon, URL) lassen sich außerdem eine minimale/maximale Länge und ein eigenes Validierungsmuster festlegen, falls Sie Erwartungen an das Feld über den reinen Typ hinaus dokumentieren möchten.

## Felder erstellen und bearbeiten

Wo Sie Felder verwalten, hängt davon ab, ob das Modul brandneu ist oder bereits live läuft:

- **Beim Erstellen eines neuen Moduls** werden Felder im Schritt "Felder" des [Modul-Erstellers](modules.md) hinzugefügt. Diese werden nach der Veröffentlichung zu dauerhaften, fest eingebauten Feldern des Moduls.
- **Bei einem bereits laufenden Modul** verwalten Sie Felder unter **Einstellungen → Module → [Modul] → Felder**. Hier hinzugefügte Felder funktionieren genau wie jedes andere Feld, sie erscheinen in Layouts, sind durchsuchbar und filterbar, wenn Sie das so festlegen, und verhalten sich identisch zu einem Feld, das mit dem Modul mitgeliefert wurde.

Neue Feldnamen dürfen nicht mit den fünf universellen Feldern kollidieren, oder mit den Positionsfeldern, falls das Modul Positionen aktiviert hat.

### Was sich nach dem Anlegen nicht mehr ändern lässt

Der **Name** und der **Typ** eines Feldes sind ab dem Anlegen dauerhaft festgelegt, es gibt keine Möglichkeit, den internen Bezeichner eines Feldes umzubenennen oder es nachträglich zum Beispiel von Text in Zahl umzuwandeln. Brauchen Sie einen anderen Typ, legen Sie ein neues Feld an und entfernen das alte.

Alles andere lässt sich jederzeit ändern: die Bezeichnung, alle oben aufgeführten Einstellungen (Pflichtfeld, Schreibgeschützt, Ausgeblendet, Durchsuchbar, Filterbar, Sortierbar, Standardwert) und, bei Auswahl- und Status-Feldern, die Optionsliste selbst, einschließlich Farbe und Symbol einer Status-Option.

## Ein Feld löschen

Nur selbst hinzugefügte Felder lassen sich löschen. Felder, die mit einem Modul mitgeliefert wurden (die fünf universellen Felder und jedes andere eingebaute Feld), lassen sich nicht löschen, nur in Layouts ausblenden.

Bevor Sie ein benutzerdefiniertes Feld löschen, sagt Cubrel Ihnen, wie viele bestehende Datensätze aktuell Daten darin haben. Das Löschen des Feldes entfernt diese Daten nicht aus der Datenbank, bedeutet aber, dass sie nirgends mehr sichtbar oder bearbeitbar sind, das lässt sich nicht rückgängig machen. Jedes Layout, das auf das Feld verweist (Listen-, Datensatz- oder Verknüpfungs-Panel-Layouts), verliert diesen Verweis automatisch, sodass kein Layout auf ein nicht mehr existierendes Feld zeigt.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Kann ich eines der fünf universellen Felder entfernen? | Nein, blenden Sie es stattdessen in einem Layout aus. |
| Was unterscheidet Auswahl von Status? | Status-Optionen haben Farbe und Symbol und erscheinen als Abzeichen, Auswahl ist reiner Text. |
| Kann ich den Typ eines Feldes nachträglich ändern? | Nein, legen Sie stattdessen ein neues Feld an. |
| Kann ich die Bezeichnung eines Feldes nachträglich ändern? | Ja, jederzeit. |
| Kann ich ein eingebautes Feld löschen? | Nein, nur benutzerdefinierte Felder lassen sich löschen. |
| Was passiert mit den Daten eines Datensatzes, wenn ich sein benutzerdefiniertes Feld lösche? | Sie bleiben in der Datenbank, sind aber nicht mehr sichtbar oder bearbeitbar, das Löschen des Feldes lässt sich nicht rückgängig machen. |
| Gelten Schreibgeschützt/Ausgeblendet pro Benutzer oder pro Rolle? | Nein, sie gelten für jeden, der das Modul sehen kann. |
