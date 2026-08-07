# Datenaufbewahrung in Cubrel verstehen

Cubrel räumt hinter sich selbst auf. Alte Benachrichtigungen, veraltete Audit-Verlaufseinträge, abgelaufene Einladungen, solche Dinge sammeln sich nicht endlos an. Dieser Artikel beschreibt, was automatisch aufgeräumt wird, wie lange jede Art von Daten standardmäßig aufbewahrt wird, und wo Sie diese Fristen ändern.

## Wo Sie es finden

Administratoren finden unter **Einstellungen > System > Datenaufbewahrung** jede Aufbewahrungsfrist an einem Ort. Nichts hier braucht ein neues Deployment oder einen Neustart, eine Änderung wirkt sich beim nächsten Lauf des jeweiligen Aufräumauftrags aus.

## Was aufgeräumt wird

Sieben Arten von Daten haben ihre eigene konfigurierbare Aufbewahrungsfrist, jeweils in Tagen gemessen ab dem Erstellungsdatum des Eintrags (bei Einladungen ab dem Zeitpunkt, an dem sie abgeschlossen wurden):

| Einstellung | Was sie betrifft | Standard |
| --- | --- | --- |
| Aufbewahrung von Benachrichtigungen | In-App-Benachrichtigungen | 180 Tage |
| Aufbewahrung des Audit-Verlaufs | Jede protokollierte Erstellung, Änderung und Löschung in der gesamten Anwendung | 730 Tage |
| Aufbewahrung von Benutzereinladungen | Bereits angenommene oder zurückgezogene Einladungen | 365 Tage |
| Aufbewahrung fehlgeschlagener Aufträge | Fehlgeschlagene Hintergrundaufträge, die zur Fehlersuche protokolliert wurden | 30 Tage |
| Aufbewahrung von Einrichtungs-Tokens | Einmalige Tokens aus der Ersteinrichtung eines Kontos | 90 Tage |
| Aufbewahrung des Importverlaufs | Abgeschlossene oder fehlgeschlagene Importläufe, einschließlich der hochgeladenen Datei | 90 Tage |
| Aufbewahrung verlassener Entwurfsmodule | In der Modul-Ersteller unfertig zurückgelassene benutzerdefinierte Module | 7 Tage |

Das Löschen eines Datensatzes oder das Ersetzen des Werts eines Bildfeldes räumt außerdem sofort die dahinterliegende Datei auf, das läuft nicht nach einer Frist, sondern in dem Moment, in dem sie nicht mehr gebraucht wird.

## Eine Frist auf 0 oder eine sehr niedrige Zahl setzen

Eine kürzere Frist bedeutet, dass Cubrel diese Daten früher vergisst. Es gibt keinen Schalter, um eine Kategorie ganz abzuschalten, die niedrigste sinnvolle Einstellung ist die Anzahl an Tagen, mit der Sie sich wohlfühlen. Beim Audit-Verlauf lohnt es sich besonders, vor dem Verkürzen nachzudenken, ist ein Eintrag einmal aus der Frist gefallen, ist diese Historie endgültig weg.

## Entwurfsmodule lassen sich auch manuell verwerfen

Sie müssen nicht auf das Ablaufen der Aufbewahrungsfrist warten, um ein verlassenes Entwurfsmodul loszuwerden. Ein Administrator kann im Modul-Ersteller bei einem unfertigen Modul auf **Entwurf verwerfen** klicken, um es sofort zu löschen, samt aller nicht gespeicherten Felder. Die 7-Tage-Frist ist nur das Sicherheitsnetz für Entwürfe, die niemand mehr aufgeräumt hat.

## Was nicht auf einer konfigurierbaren Frist läuft

Ein paar Aufräumaufgaben laufen nach einem festen Zeitplan statt nach einer Einstellung, die Sie ändern können: verwaiste Bilduploads werden wöchentlich aufgeräumt, und Impersonation-Sitzungen, die offen hängen geblieben sind (etwa weil ein Browser-Tab mitten in der Sitzung geschlossen wurde), werden stündlich bereinigt. Das sind keine Benutzerdaten im selben Sinn wie die Tabelle oben, es gibt also nichts zu konfigurieren.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wo ändere ich Aufbewahrungsfristen? | **Einstellungen > System > Datenaufbewahrung** |
| Brauchen Änderungen ein neues Deployment? | Nein, sie gelten beim nächsten Lauf des Aufräumauftrags |
| Was passiert, wenn Daten ihre Aufbewahrungsfrist überschreiten? | Sie werden endgültig gelöscht, es gibt keine Wiederherstellung |
| Kann ich die Aufbewahrung für eine Kategorie abschalten? | Nein, aber Sie können eine lange Frist einstellen |
| Was räumt sich sofort statt nach einem Zeitplan auf? | Dateien hinter einem gelöschten Datensatz oder einem ersetzten Bildfeld |
| Kann ich ein verlassenes Entwurfsmodul selbst verwerfen? | Ja, mit **Entwurf verwerfen** im Modul-Ersteller, ohne auf die Aufbewahrungsfrist zu warten |
