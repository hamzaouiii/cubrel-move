# Benutzer, Einladungen und Passwörter in Cubrel verwalten

In diesem Artikel geht es darum, wie Personen Zugriff auf Ihre Cubrel-Organisation erhalten, den Unterschied zwischen dem direkten Anlegen und dem Einladen eines Benutzers, und wie Passwörter dabei gesetzt, zurückgesetzt oder wiederhergestellt werden.

## Das Modul Benutzer

**Unabhängig vom Zugriff auf Einstellungen erscheint jeder mit einem Cubrel-Konto unter Benutzer**, ein Datensatz pro Person, neben den Datensätzen jedes anderen Moduls: eine Listenansicht, eine Datensatzseite und eigene Felder (Name, Benutzername, E-Mail, Telefon, Titel, Avatar, Status und so weiter). Es verhält sich wie jedes Kernmodul, mit zwei Unterschieden, die daher kommen, dass es eine Person statt eines Geschäftsdatensatzes darstellt:

- Benutzer haben kein Feld **Gehört zu**, ein Benutzer kann sich nicht selbst gehören, so wie eine Verkaufschance oder ein Kontakt jemandem gehört.
- Das Aktionsmenü der Datensatzseite bietet benutzerspezifische Aktionen statt der üblichen: **Passwort-Zurücksetzen-E-Mail senden**, und, nur für Super-Admins, **Anmelden als** (Impersonation, siehe den [Leitfaden zu Impersonation](impersonation-guide.md)).

Jeder Benutzer hat einen **Status** (Aktiv oder Inaktiv) und ein Kennzeichen **Ist Administrator**. Der Status steuert, ob das Konto imitiert werden kann und als verfügbar erscheint, Administrator steuert den Zugriff auf Einstellungen, siehe [Administrator und Super-Admin](terminology.md#administrator) im Terminologie-Leitfaden für den Unterschied zwischen einem Administrator und einem Super-Admin.

## Zwei Wege, jemanden hinzuzufügen: direkt anlegen oder einladen

Beide finden sich unter **Benutzer** und führen zur selben Art von Konto, sie unterscheiden sich nur darin, wer das Passwort der Person setzt.

### Einen Benutzer direkt anlegen

Unter **Benutzer > Neu** füllt ein Administrator die Angaben der Person aus (Name, Benutzername, E-Mail, Titel, Administrator-Kennzeichen usw.) und speichert. Dieses Formular hat bewusst kein Passwortfeld, das Konto wird ohne nutzbares Passwort angelegt. Direkt nach dem Speichern werden Sie gefragt, ob dem neuen Benutzer ein Link zum Setzen des eigenen Passworts per E-Mail geschickt werden soll, das ist dieselbe Aktion **Passwort-setzen-E-Mail senden**, die unten beschrieben ist, hier nur automatisch im Moment des Anlegens angeboten. Sie können ablehnen und sie später vom Datensatz des Benutzers aus senden.

### Jemanden per E-Mail einladen

In der Liste **Benutzer** öffnet **Benutzer einladen** ein Fenster, in dem Sie eine E-Mail-Adresse nach der anderen sammeln (jede mit ihrem eigenen **Administrator**-Schalter) und alle zusammen versenden, bis zu 20 in einer einzigen Runde. Es gibt keinen getrennten Bildschirm für "eine hinzufügen" gegenüber "mehrere einladen", Sie fügen einfach so viele Zeilen hinzu, wie Sie brauchen, bevor Sie senden.

Jede Einladung verschickt eine E-Mail mit einem eindeutigen Anmeldelink. Öffnet die Person ihn, gelangt sie zu einem kurzen Formular (Vorname, Nachname, Benutzername, Passwort, Passwort bestätigen), ihre E-Mail-Adresse ist bereits durch die Einladung festgelegt und lässt sich dort nicht ändern. Das Absenden legt das Konto an und meldet die Person direkt an.

Ein paar Regeln gelten für Einladungen:

- **Nur eine offene Einladung pro E-Mail-Adresse gleichzeitig.** Laden Sie eine Adresse ein, die bereits eine offene Einladung hat, ersetzt das die alte, statt eine doppelte anzulegen. Das Einladen einer Adresse, die bereits ein Benutzerkonto hat, wird rundweg abgelehnt.
- **Einladungslinks laufen nach 7 Tagen ab.** Danach öffnet sich der Link zwar noch, zeigt aber "abgelaufen" statt des Anmeldeformulars.
- **Links sind nur einmal verwendbar.** Einmal angenommen, lässt sich derselbe Link nicht erneut nutzen.

### Einladungen verfolgen: Benutzer > Einladungen

**Benutzer > Einladungen** listet jede versendete Einladung, mit dem Status **Ausstehend**, **Angenommen**, **Abgelaufen** oder **Zurückgezogen**. Von dieser Liste aus können Sie:

- Eine ausstehende oder abgelaufene Einladung **erneut senden**, das stellt einen brandneuen Link aus (mit einem neuen 7-Tage-Fenster) und ersetzt den alten. Der alte Link funktioniert nicht mehr, sobald der neue versendet ist.
- Eine ausstehende Einladung **zurückziehen**, falls Sie die falsche Adresse eingeladen haben oder es sich anders überlegt haben. Der Link einer zurückgezogenen Einladung funktioniert sofort nicht mehr.
- Eine Einladung **löschen**, sobald sie nicht mehr ausstehend ist (angenommen, abgelaufen oder zurückgezogen), um sie aus der Liste zu entfernen. Ausstehende Einladungen müssen zuerst zurückgezogen werden.

## Passwortverwaltung

Cubrel zeigt oder verschickt niemals das tatsächliche Passwort einer Person, jeder der folgenden Wege funktioniert stattdessen über eine sichere, zeitlich begrenzte E-Mail mit Link.

### Ein Passwort zum ersten Mal setzen

Neue Benutzer setzen ihr eigenes Passwort selbst, es wird ihnen nie von einem Administrator zugewiesen:

- **Eingeladene Benutzer** setzen es beim Annehmen ihrer Einladung (das oben beschriebene Anmeldeformular).
- **Direkt angelegte Benutzer** setzen es, sobald sie zum ersten Mal dem Link aus **Passwort-setzen-E-Mail senden** folgen, den ein Administrator jederzeit (nicht nur direkt nach dem Anlegen) vom Datensatz des Benutzers aus über das Aktionsmenü auslösen kann.

### Ein vergessenes Passwort zurücksetzen

Jeder kann über den Link **Passwort vergessen?** auf der Anmeldeseite eine eigene Zurücksetzung anfordern, die Eingabe der E-Mail-Adresse verschickt einen Link zum Zurücksetzen, genau wie ein gewöhnlicher "Passwort vergessen"-Ablauf überall sonst funktioniert.

Ein Administrator kann dies auch stellvertretend auslösen: Öffnen Sie den Datensatz des Benutzers, öffnen Sie das Aktionsmenü, und wählen Sie **Passwort-Zurücksetzen-E-Mail senden**. Das ist derselbe zugrunde liegende Ablauf, nur von einem Administrator statt vom Benutzer selbst gestartet, nützlich, wenn jemand ausgesperrt ist und die Passwort-vergessen-Seite selbst nicht erreichen kann.

Sowohl der Zurücksetzen-Link als auch der Passwort-setzen-Link laufen **1 Stunde** nach dem Versand ab und lassen sich nur einmal verwenden. Hat ein Benutzer keine E-Mail-Adresse hinterlegt, lassen sich diese Aktionen nicht versenden, und Cubrel sagt Ihnen, warum, statt still zu scheitern.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wo verwalte ich Benutzer? | **Benutzer** (Liste, Anlegen, und die Datensatzseite jeder Person) |
| Wie füge ich jemanden hinzu, ohne eine E-Mail zu senden? | **Benutzer > Neu**, legt das Konto an, noch ohne gesetztes Passwort |
| Wie füge ich jemanden per E-Mail-Einladung hinzu? | Liste **Benutzer** > **Benutzer einladen**, bis zu 20 E-Mails pro Runde |
| Wo sehe ich den Status einer Einladung? | **Benutzer > Einladungen**, Ausstehend / Angenommen / Abgelaufen / Zurückgezogen |
| Kann ich eine abgelaufene Einladung erneut senden? | Ja, **Erneut senden** stellt einen frischen Link mit neuem 7-Tage-Fenster aus |
| Wie lange gelten Einladungs-/Zurücksetzen-/Passwort-setzen-Links? | Einladungen: 7 Tage. Zurücksetzen-/Passwort-setzen-Links: 1 Stunde. Alle nur einmal verwendbar |
| Sieht oder setzt ein Administrator jemals das Passwort einer Person? | Nein, jeder Weg verschickt der Person (oder der eingeladenen Person) einen Link, um es selbst zu setzen |
| Was, wenn jemand ausgesperrt ist? | Ein Administrator sendet vom Datensatz aus eine **Passwort-Zurücksetzen-E-Mail** |
