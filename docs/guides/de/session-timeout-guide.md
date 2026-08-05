# Was mit Ihrer Sitzung in Cubrel passiert

In diesem Artikel geht es darum, wie Sitzungen in Cubrel funktionieren, was Sie sehen, wenn eine abläuft, und warum Ihre Arbeit so oder so sicher ist.

## Was eine Sitzung ist

Eine Sitzung ist schlicht Cubrels Art, sich zu merken, dass Sie es sind. Melden Sie sich an, startet Cubrel eine Sitzung und hält Sie angemeldet, während Sie zwischen Seiten wechseln, sodass Sie sich nicht bei jedem geöffneten Datensatz oder jeder Suche neu anmelden müssen.

## Über verschiedene Geräte oder Browser hinweg

Cubrel gleichzeitig auf Ihrem Tablet und Ihrem Laptop zu nutzen, ist zum Beispiel völlig unbedenklich. Jedes Gerät führt seine eigene, getrennte Sitzung, was auf dem einen passiert, hat also keine Auswirkung auf das andere. Läuft die Sitzung Ihres Laptops ab oder wird erneuert, bleibt Ihr Tablet genau, wie es war, und umgekehrt. Sie können sich auf jedem Gerät unabhängig an- und abmelden, ohne dass das Ihre Arbeit anderswo stört.

## Über Tabs im selben Browser hinweg

Tabs sind nicht so unabhängig wie Geräte. Jeder Tab, den Sie im selben Browser offen haben, teilt sich eine einzige Anmeldung, melden Sie sich in einem Tab ab, wird also auch jeder andere in diesem Browser offene Tab abgemeldet, selbst einer, den Sie seit einer Weile nicht angerührt haben. Gehen Sie danach zu einem dieser anderen Tabs zurück und versuchen etwas zu tun, werden Sie einfach erneut zur Anmeldung aufgefordert.

Das gilt nur innerhalb eines Browsers auf einem Gerät. Das Abmelden auf Ihrem Laptop berührt Ihr Telefon nicht, oder irgendein anderes Gerät, auf dem Sie angemeldet sind, das ist die oben beschriebene Unabhängigkeit. Es sind speziell mehrere Tabs, die sich denselben Browser teilen, die sich auch dieselbe Anmeldung teilen.

## Warum Sitzungen überhaupt ablaufen

Untätige Sitzungen automatisch abzumelden, ist die technische Schutzmaßnahme, die uns hilft, Ihre Daten im Einklang mit der DSGVO zu schützen. Nach einer Phase ohne Aktivität meldet Cubrel Sie also automatisch ab.

Aktuell ist dieses Untätigkeitsfenster standardmäßig auf **8 Stunden** eingestellt. Das reicht für einen vollen Arbeitstag, ohne dass Sie es je bemerken, solange Sie immer wieder zum Tab zurückkehren.

## Können Sie das Standardverhalten der Sitzung überschreiben?

Ja. Setzen Sie beim Anmelden das Häkchen bei **"Angemeldet bleiben"**, merkt sich Cubrel Sie auf diesem Gerät, getrennt von der standardmäßigen 8-Stunden-Sitzung. Statt nach einer Phase der Untätigkeit abgemeldet zu werden, bleiben Sie für sehr lange angemeldet, etwa 400 Tage. Sie können den Browser komplett schließen, Wochen später zurückkommen und landen direkt wieder in Cubrel, ohne sich erneut anzumelden.

#### Ein paar Hinweise

- **Es ist nicht wirklich dauerhaft.** Es ist ein sehr langes Fenster (etwa 400 Tage), das sich bei jeder Nutzung von Cubrel zurücksetzt, in der Praxis läuft es also selten von selbst ab.
- **Abmelden löscht es.** Melden Sie sich ausdrücklich ab, wird das gelöscht, und Sie brauchen beim nächsten Mal wieder Ihr Passwort, genau wie bei einer normalen Sitzung.
- **Nutzen Sie es nur auf Ihrem eigenen Gerät.** Da es Sie so lange angemeldet hält, könnte jeder, der diesen Browser danach benutzt, Cubrel als Sie öffnen. Verzichten Sie darauf auf gemeinsam genutzten oder öffentlichen Computern.

## Solange Sie arbeiten, bekommen Sie davon nichts mit

Solange Sie Cubrel in einem sichtbaren Browser-Tab geöffnet haben, meldet es sich im Hintergrund alle paar Minuten leise beim Server, Sie merken davon nichts. Solange der Tab geöffnet und auf Ihrem Bildschirm sichtbar bleibt, setzt das die 8-Stunden-Uhr immer wieder zurück, eine Sitzung, die Sie tatsächlich aktiv nutzen, läuft also praktisch nie ab, egal wie lange Sie sie schon offen haben. Das stoppt nur, wenn Sie zu einem anderen Tab wechseln oder das Fenster für längere Zeit minimieren, mit anderen Worten, wenn Sie sich wirklich entfernen.

## Wenn Sie nach einer Weile zurückkommen

Angenommen, Sie hatten Cubrel bei einem Datensatz geöffnet, waren eine Weile weg, und kommen zurück, um die Bearbeitung abzuschließen und auf Speichern zu klicken. Je nachdem, was in der Zwischenzeit passiert ist, kann eines von zwei Dingen passieren:

### 1. "Ihre Sitzung wurde erneuert, bitte speichern Sie erneut"

Gelegentlich kann eine Sicherheitsprüfung Ihrer Sitzung aus dem Takt geraten, obwohl Sie weiterhin vollständig angemeldet sind. Das hat nicht unbedingt damit zu tun, wie lange Sie weg waren, es kann passieren, wenn sich im Hintergrund etwas an Ihrer Sitzung ändert, zum Beispiel wenn ein Administrator Ihr Konto kurz für den Support genutzt hat, während Sie anderswo eine Seite geöffnet hatten. In diesem Fall:

- Sehen Sie ein kleines Hinweisbanner, dass Ihre Sitzung erneuert wurde.
- Bleiben Sie genau da, wo Sie waren, derselbe Datensatz, dieselben Änderungen noch in den Feldern, nichts wird geleert oder neu geladen.
- Klicken Sie einfach noch einmal auf Speichern, und es geht normal durch.

### 2. "Ihre Sitzung ist abgelaufen, bitte melden Sie sich erneut an"

Waren Sie lange genug weg, dass Sie tatsächlich abgemeldet wurden (über das oben beschriebene Untätigkeitsfenster hinaus), bringt Cubrel Sie zur Anmeldeseite. Das läuft ganz normal ab, zwei Dinge machen es dabei aber schmerzlos:

- Ihre nicht gespeicherte Änderung geht nicht verloren. Cubrel merkt sich, was Sie gerade eingegeben hatten.
- Nach dem erneuten Anmelden landen Sie direkt wieder auf genau dem Datensatz, an dem Sie waren, nicht auf dem Dashboard, und er öffnet sich bereits im Bearbeitungsmodus mit Ihrer Änderung wieder eingetragen, bereit zum Speichern.

Auch im Fall "Sie wurden abgemeldet" verlieren Sie also nichts von Ihrer Arbeit und müssen den Datensatz nicht erneut suchen. Einfach anmelden und dort weitermachen, wo Sie aufgehört haben.

## Was nicht abgedeckt ist

- Schließen Sie den Browser-Tab (oder Ihr Laptop stürzt ab usw.), **bevor** Sie überhaupt auf Speichern geklickt haben, ist diese nicht gespeicherte Änderung weg, genau wie bei jedem nicht gespeicherten Formular auf jeder Website. Die oben beschriebene Wiederherstellung greift erst in dem Moment, in dem Sie tatsächlich versuchen zu speichern.
- Warnt Sie Ihr Browser selbst mit "Sie haben ungespeicherte Änderungen, möchten Sie die Seite wirklich verlassen?", ist das eine separate, übliche Sicherheitsabfrage. Cubrel prüft damit nur noch einmal nach, bevor Sie mit noch ungespeicherten Änderungen wegnavigieren, das hat nichts mit dem Ablaufen Ihrer Sitzung zu tun.

## Kurz gefasst

| Situation | Was Sie sehen | Ihre Arbeit |
| --- | --- | --- |
| Cubrel aktiv genutzt (Tab offen, sichtbar) | Nichts, die Sitzung bleibt einfach bestehen | Nie gefährdet |
| Sitzung im Hintergrund erneuert (selten) | Kurzer Hinweis "bitte erneut speichern", gleiche Seite | Vollständig erhalten, weiterhin auf dem Bildschirm |
| Lange genug weg, um vollständig abgemeldet zu werden | Weiterleitung zur Anmeldung | Automatisch wiederhergestellt, sobald Sie sich erneut anmelden und zum Datensatz zurückkehren |
| Tab geschlossen / Browser abgestürzt vor dem Speichern | Entfällt | Verloren, wie bei jedem nicht gespeicherten Formular |
