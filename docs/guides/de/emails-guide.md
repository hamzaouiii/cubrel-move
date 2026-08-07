# E-Mail-Erfassung in Cubrel verstehen

In diesem Artikel geht es darum, was die E-Mail-Erfassung ist, wie Sie Ihre eigene Erfassungsadresse finden und nutzen, wie Administratoren gemeinsame Team-Adressen einrichten, und wie eine erfasste E-Mail mit dem Rest Ihrer CRM-Daten verknüpft wird.

## Was die E-Mail-Erfassung ist

Mit der E-Mail-Erfassung protokollieren Sie eine E-Mail an Ihren CRM-Datensätzen, ohne von Hand irgendetwas einzutippen. Jeder Benutzer erhält eine feste E-Mail-Adresse, setzen Sie sie in Kopie (BCC) auf jede E-Mail, die Sie aus Ihrem normalen Postfach senden (oder senden direkt an sie), protokolliert Cubrel automatisch eine Kopie dieser E-Mail und ordnet sie jedem Kontakt zu, dessen Adresse darin vorkommt.

Erfasste E-Mails landen im Modul **E-Mails**, direkt neben Aufgaben, Anrufen, Meetings und Notizen, sie erscheinen genauso im Aktivitäten-Verlauf eines Kontakts oder einer Verkaufschance, lassen sich wie jede andere Aktivität verknüpfen und trennen, und können eine Umwandlungsregel auslösen.

## Ihre persönliche Erfassungsadresse finden

Ihre Erfassungsadresse ist `ihrbenutzername@` gefolgt von der Cubrel-Domain Ihres Unternehmens, sie leitet sich direkt aus Ihrem Benutzernamen ab, es gibt also nichts Separates zu erzeugen oder zu merken. Sie finden sie, und können sie mit einem Klick kopieren, auf Ihrer **Profil**-Seite.

Ändern Sie irgendwann Ihren Benutzernamen, ändert sich Ihre Erfassungsadresse mit, die neue Adresse funktioniert sofort, und alles, was bereits über die alte erfasst wurde, bleibt davon unberührt.

## Sie nutzen

Setzen Sie Ihre Erfassungsadresse in Kopie (BCC) auf jede E-Mail, die Sie versenden, genau wie bei einem Kollegen. Pro E-Mail ist nichts einzustellen, jede an diese Adresse gesendete Nachricht wird automatisch protokolliert, egal ob Sie in Kopie stehen oder sie direkt an Sie adressiert ist.

Eine erfasste E-Mail behält ihren ursprünglichen Betreff, Absender, Empfänger und Inhalt, und wird mit dem tatsächlichen Sendezeitpunkt versehen, nicht mit dem Zeitpunkt, an dem Cubrel sie zufällig empfangen hat.

## Team- und gemeinsame Erfassungsadressen

Über persönliche Adressen hinaus kann ein Administrator zusätzliche Erfassungsadressen für einen gemeinsamen Zweck einrichten, zum Beispiel `leads@` für neue eingehende Anfragen, oder `support@` für ein gemeinsames Support-Postfach, unter **Einstellungen > E-Mail > Eingehende E-Mail**.

Eine Team-Adresse kann optional einen Besitzer haben (die Person, die für das gelten soll, was über sie hereinkommt), oder ohne Besitzer bleiben, über eine besitzerlose Adresse erfasste E-Mails werden trotzdem ganz normal protokolliert, nur ohne eine bestimmte Person als Besitzer. Das Löschen einer Team-Adresse entfernt nur die Adresse selbst, bereits über sie erfasste E-Mails bleiben genau dort, wo sie sind.

## Wie eine erfasste E-Mail mit Ihren Daten verknüpft wird

Jede Absender- und Empfängeradresse einer erfassten E-Mail wird mit Ihren Kontakten abgeglichen, jede Übereinstimmung verknüpft die E-Mail automatisch mit diesem Kontakt, über dasselbe Verknüpfungssystem, das überall sonst in Cubrel verwendet wird. Diese Verknüpfung sorgt dafür, dass eine erfasste E-Mail im Aktivitäten-Verlauf eines Kontakts (oder jedes anderen verknüpften Datensatzes) erscheint, ohne dass Sie zusätzlich etwas tun müssen.

## Automatisieren, was als Nächstes passiert

Jede erfasste E-Mail speichert, welche Adresse sie empfangen hat, Ihre persönliche, oder den Namen einer Team-Adresse. Da das ein reguläres Feld ist (genannt **Postfach**), lässt es sich als Bedingung in einer [Umwandlungsregel](/de/conversion-guide) verwenden, zum Beispiel eine Regel, die automatisch einen Interessenten aus allem erstellt, was über die Adresse `leads` erfasst wurde, ohne dass jemand das vorher prüfen muss.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Muss ich etwas einrichten, um eine Erfassungsadresse zu bekommen? | Nein, jeder Benutzer hat automatisch eine, basierend auf seinem Benutzernamen |
| Wo finde ich meine Erfassungsadresse? | Auf Ihrer Profil-Seite, mit einer Kopieren-Schaltfläche |
| Was mache ich damit? | In Kopie (BCC) setzen, oder direkt an sie senden, auf jede beliebige E-Mail |
| Behält die erfasste E-Mail Absender/Betreff/Inhalt bei? | Ja, genau wie versendet |
| Kann ich eine gemeinsame Adresse für ein Team-Postfach bekommen, nicht an eine Person gebunden? | Ja, ein Administrator erstellt sie unter Einstellungen > E-Mail > Eingehende E-Mail, Besitzer optional |
| Wie landet eine erfasste E-Mail im Verlauf eines Kontakts? | Automatisch, durch Abgleich der beteiligten E-Mail-Adressen |
| Können erfasste E-Mails Automatisierung auslösen? | Ja, über Umwandlungsregeln mit einer Bedingung auf das Postfach-Feld |
| Wenn ich meinen Benutzernamen ändere, funktioniert meine alte Erfassungsadresse dann nicht mehr? | Doch, sofort, die neue übernimmt direkt |
