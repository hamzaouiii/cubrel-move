# Umwandlungsregeln in Cubrel verstehen

In diesem Artikel geht es darum, was eine Umwandlungsregel ist, wie Sie eine einrichten, worin sich das manuelle Umwandeln eines Datensatzes vom automatischen Umwandeln durch Cubrel unterscheidet, und was mit den beiden Datensätzen passiert, sobald sie verknüpft sind.

## Was eine Umwandlungsregel ist

Eine Umwandlungsregel sagt Cubrel, wie aus einem Datensatz eines Moduls ein Datensatz in einem anderen Modul entstehen soll, zum Beispiel "erstelle eine Rechnung aus einem Angebot". Es ist ein wiederverwendbares Rezept, keine einmalige Aktion: Einmal festgelegt, lässt sich jeder Datensatz im Quellmodul auf dieselbe Weise umwandeln, entweder per Knopfdruck oder automatisch, sobald von Ihnen gewählte Bedingungen erfüllt sind.

Das Umwandeln eines Datensatzes verändert oder entfernt das Original nie. Ein Angebot bleibt genau so, wie es war, eine neue Rechnung wird einfach zusätzlich erstellt und mit ihm verknüpft.

## Eine Umwandlungsregel erstellen

Gehen Sie zu **Einstellungen > Automatisierung > Umwandlungsregeln > Neue Umwandlungsregel** und dann:

1. Vergeben Sie einen **Namen**, und wählen Sie das **Quellmodul** (woraus umgewandelt wird) und das **Zielmodul** (was dabei entsteht). Das kann jedes aktive Modul außer Benutzer und Benutzereinladungen sein. Nach dem Speichern lassen sich diese beiden Module nicht mehr ändern, löschen und neu anlegen ist dann der einzige Weg.
2. Füllen Sie den Tab **Einrichtung** aus (siehe unten).
3. Füllen Sie den Tab **Zuordnung** aus (siehe unten).
4. Speichern.

Cubrel prüft dabei ein paar Dinge, bevor es das Speichern erlaubt, damit Sie keine Regel anlegen können, die später fehlschlägt:

- Jedes im Zielmodul tatsächlich erforderliche Feld muss eine Zuordnung haben. Cubrel legt dafür automatisch eine leere Zeile an (und, wo möglich, gleich einen sinnvollen Standardwert, siehe unten), damit Sie sich nicht merken müssen, welche Felder erforderlich sind.
- Eine Zuordnung "aus einem Feld" bietet nur Quellfelder desselben Typs wie das Zielfeld an (und bei einem Datensatzverweis-Feld nur solche, die auf dasselbe verknüpfte Modul zeigen), sodass Sie nicht versehentlich ein Textfeld mit einem Zahlenfeld verbinden.
- Der einzeilige Ausdrucks-Editor wird nur für textähnliche Zielfelder angeboten (Text, Mehrzeiliger Text, E-Mail, Telefon, URL), bei einem Zahlen- oder Datumsfeld würde er keinen Sinn ergeben.

### Tab "Einrichtung"

- **Automatisch** ist standardmäßig ausgeschaltet. Eine Umwandlungsregel lässt sich unabhängig davon *immer* manuell ausführen, das Einschalten erlaubt Cubrel zusätzlich, sie von selbst auszuführen, sobald die Bedingungen eines Datensatzes zutreffen, ohne dass jemand klicken muss.
- **Bedingungen** werden nur angezeigt, sobald Automatisch eingeschaltet ist, da sie ausschließlich die automatische Ausführung steuern, nie die manuelle. Fügen Sie eine oder mehrere Zeilen der Form `Feld / Operator / Wert` hinzu (derselbe Bedingungs-Editor, den auch Listenfilter verwenden) und legen Sie fest, ob **alle** oder **mindestens eine** davon zutreffen muss. Eine Regel lässt sich nicht mit eingeschaltetem Automatisch und ohne Bedingungen speichern, eine automatische Regel ohne etwas zu prüfen würde nie tatsächlich laufen.
- **Die beiden Datensätze verknüpfen** ist standardmäßig eingeschaltet. Verknüpft den Quelldatensatz mit dem neu erstellten, sodass beide im Zugehörig-Tab des jeweils anderen erscheinen, mit der Verbindung "Erstellt aus"/"Umgewandelt in". Schalten Sie zusätzlich Automatisch ein, warnt Cubrel Sie: Anders als beim manuellen Ausführen gibt es beim automatischen keinen Bestätigungsschritt, ist die zugrunde liegende Beziehung strikt Eins-zu-Eins, ersetzt jeder automatische Lauf stillschweigend den zuvor verknüpften Datensatz.

### Tab "Zuordnung"

- **Feldzuordnungen**: Für jedes Feld am Zieldatensatz legen Sie fest, woher sein Wert kommt, ein Feld am Quelldatensatz, ein fester statischer Wert, oder ein kleiner Ausdruck (fester Text kombiniert mit einem Quellfeld und/oder einem Hilfswert wie dem heutigen Datum). Ein Zielfeld lässt sich nur einmal zuordnen. Erforderliche Zielfelder sind bereits vorangelegt, und die beiden häufigsten erhalten automatisch einen sinnvollen Standard: **Besitzer** wird standardmäßig auf "Aktueller Benutzer" gesetzt, **Name** auf den Namen des Quelldatensatzes, beides lässt sich weiterhin ändern.
  - Für einen statischen Wert an einem Datensatzverweis-Feld (etwa ein fester Besitzer oder ein fester verknüpfter Datensatz) können Sie über eine Suche den tatsächlichen Datensatz auswählen, statt eine ID einzutippen, oder "Aktueller Benutzer" als Abkürzung für die Person wählen, die die Umwandlung ausführt.
- **Zu kopierende Beziehungen**: Welche Beziehungen des Quelldatensatzes (Positionen, Notizen, Anhänge und so weiter) ebenfalls auf den neuen Datensatz kopiert werden. Mit "Alle auswählen"/"Alle abwählen" schalten Sie alle Optionen auf einmal um.

## Eine Umwandlung manuell ausführen

Jede aktivierte Umwandlungsregel erscheint im Aktionsmenü eines Datensatzes unter **Umwandeln**, unabhängig davon, ob Automatisch eingeschaltet ist. Wählen Sie eine aus, passiert Folgendes:

1. Cubrel prüft, ob dabei eine bestehende Verknüpfung ersetzt würde (nur relevant bei einer Eins-zu-Eins-Beziehung). Falls ja, werden Sie vor dem Fortfahren um Bestätigung gebeten, oder Sie erstellen den neuen Datensatz ohne Verknüpfung.
2. Der neue Datensatz wird gemäß den Feldzuordnungen und kopierten Beziehungen der Regel erstellt.
3. Eine Bestätigung mit einem Link direkt zum neuen Datensatz erscheint, Sie bleiben dabei auf dem Datensatz, von dem aus Sie umgewandelt haben, nichts navigiert Sie weg.

## Automatische Umwandlungen

Ist Automatisch eingeschaltet, prüft Cubrel die Bedingungen eines Datensatzes bei jedem Speichern und führt die Umwandlung genau in dem Moment aus, in dem sie zutreffen, ohne Knopf, ohne Bestätigung. Ein paar Dinge sind dabei wichtig zu wissen:

- Sie greift nur, wenn ein Speichervorgang tatsächlich eines der Bedingungsfelder ändert, nicht bei jedem beliebigen Speichern eines Datensatzes, der bereits zutrifft.
- Wird ein Bedingungsfeld von zutreffend weg und wieder zurück geändert, greift die Regel erneut und erstellt einen zweiten Datensatz, das ist aktuell so gewollt: ein einfacher, vorhersehbarer Auslöser, keine vollständige Workflow-Engine, die sich merkt, ob sie für einen bestimmten Datensatz bereits gelaufen ist.
- Da niemand zur Bestätigung eingebunden ist, kann eine Eins-zu-Eins-Einstellung von "Die beiden Datensätze verknüpfen" einen Datensatz bei jedem automatischen Lauf stillschweigend neu verknüpfen (siehe die Warnung unter Einrichtung oben), schalten Sie das Verknüpfen aus, oder nutzen Sie einen anderen Beziehungstyp, falls das nicht gewünscht ist.

## Aktivieren, deaktivieren und löschen

In der Liste der Umwandlungsregeln lässt sich jede Regel mit einem Klick aktivieren/deaktivieren (eine deaktivierte Regel lässt sich weder manuell noch automatisch ausführen) oder zum Bearbeiten öffnen. Das Löschen einer Regel entfernt nur das Rezept selbst, bereits erstellte Datensätze oder Verknüpfungen bleiben unangetastet. Wurde die Regel schon einmal tatsächlich verwendet, sehen Sie vor dem Löschen eine deutlichere Warnung, da sich diese Historie danach nicht wiederherstellen lässt.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Verändert das Umwandeln eines Datensatzes das Original? | Nein, der Quelldatensatz bleibt unangetastet |
| Kann ich eine Umwandlungsregel von Hand ausführen? | Ja, immer, solange sie aktiviert ist, Automatisch hat darauf keinen Einfluss |
| Wirken sich Bedingungen auf die manuelle Aktion "Umwandeln" aus? | Nein, nur auf den automatischen Lauf |
| Kann eine Regel automatisch sein und keine Bedingungen haben? | Nein, das wird beim Speichern verhindert |
| Was passiert, wenn ich bei einer Eins-zu-Eins-Beziehung sowohl Automatisch als auch "Die beiden Datensätze verknüpfen" einschalte? | Jeder automatische Lauf kann die bestehende Verknüpfung stillschweigend ersetzen, ohne Bestätigung |
| Kann ich Quell-/Zielmodul nach dem Erstellen einer Regel ändern? | Nein, löschen und neu anlegen ist der einzige Weg |
| Können Benutzer oder Benutzereinladungen Quell- oder Zielmodul sein? | Nein, sie sind aus der Modulauswahl ausgeschlossen |
| Kann ich eine Regel speichern, die ein erforderliches Zielfeld ohne Zuordnung lässt? | Nein, Cubrel blockiert das Speichern, bis jedes erforderliche Feld eine Zuordnung hat |
| Löscht das Löschen einer Regel auch bereits erstellte Datensätze oder Verknüpfungen? | Nein, nur die Regel selbst wird entfernt |
