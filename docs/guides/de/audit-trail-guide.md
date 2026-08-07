# Den Audit-Verlauf in Cubrel verstehen

In diesem Artikel geht es darum, was der Audit-Verlauf von Cubrel tatsächlich aufzeichnet, wo Sie ihn finden, was Impersonation-Transparenz in der Praxis bedeutet, und was heute noch außen vor bleibt.

## Was der Audit-Verlauf ist

Jedes Mal, wenn irgendwo in Cubrel ein Datensatz erstellt, geändert oder gelöscht wird, wird diese Änderung automatisch protokolliert, ohne dass etwas dafür eingerichtet werden muss. Jeder Eintrag erfasst, **wer** die Änderung vorgenommen hat, **was** sich geändert hat, und **wann**, sodass Sie immer nachvollziehen können, wie Ihre Daten zu ihrem aktuellen Stand gekommen sind.

## Wo Sie ihn finden

Es gibt zwei Wege, sich diesen Verlauf anzusehen, je nachdem, ob Sie einen einzelnen Datensatz oder alles auf einmal betrachten möchten.

### An einem einzelnen Datensatz

Öffnen Sie einen beliebigen Datensatz und wählen Sie im Aktionsmenü (das kleine Dropdown neben Bearbeiten/Speichern) **"Verlauf anzeigen"**. Das öffnet ein Fenster mit jeder jemals an diesem Datensatz vorgenommenen Änderung, neueste zuerst, jedes geänderte Feld mit altem und neuem Wert nebeneinander.

### Über Ihre gesamte Organisation hinweg

Administratoren finden unter **Einstellungen > Audit-Verlauf** das vollständige, ungefilterte Bild, jede Änderung an jedem Datensatz, über jedes Modul hinweg, in einer durchsuchbaren Liste. Sie lässt sich nach Modul, nach Benutzer, nach Aktionsart (erstellt/geändert/gelöscht) und nach Zeitraum filtern.

## Was jeder Eintrag zeigt

- **Wann** es passiert ist, in Ihrem eingestellten Datums-/Uhrzeitformat.
- **Wer** es getan hat.
- **Was** sich geändert hat, mit denselben Feldnamen und Bezeichnungen, die Sie auch am Datensatz selbst sehen, nicht den internen Rohnamen.
- Bei Dropdown-Feldern die tatsächliche Bezeichnung der Option (z. B. "Verloren"), nicht den internen Wert dahinter.
- Bei Feldern, die auf einen anderen Datensatz verweisen (wie der Besitzer eines Datensatzes), den Namen dieses anderen Datensatzes, nicht seine interne ID.

## Impersonation ist immer transparent, nie verborgen

Gelegentlich muss sich ein Super-Admin *als* ein anderer Benutzer anmelden, zum Beispiel um bei einem Problem genau aus dessen Sicht zu helfen. Passiert das und wird während dieser Sitzung eine Änderung vorgenommen, zeigt der Audit-Verlauf das immer unmissverständlich: Der Eintrag ist direkt neben der Aktion mit einem kleinen Abzeichen **"als [Name des Super-Admins]"** markiert. Niemand, der den Verlauf eines Datensatzes ansieht, muss sich je fragen, ob die angezeigte Person die Änderung wirklich vorgenommen hat. Siehe den [Leitfaden zu Impersonation](impersonation-guide.md) dafür, wer imitieren darf und wie eine Sitzung gestartet und beendet wird.

Getrennt vom Audit-Verlauf listet **Einstellungen > Impersonation-Sitzungen** (für alle Administratoren sichtbar) jede Impersonation-Sitzung für sich: wer sich als wer angemeldet hat, von welcher IP-Adresse, wann sie begann und endete, und wie lange sie dauerte. Eine laufende Sitzung (die noch nicht beendet ist) ist eindeutig als solche gekennzeichnet.

## Ein paar Dinge bleiben bewusst außen vor

- **Automatisch berechnete Summen werden nicht als Änderung protokolliert.** Felder wie Gesamtbetrag, Zwischensumme, Steuer und Rabattbetrag werden automatisch neu berechnet, sobald sich Positionen ändern, niemand hat sie direkt eingetippt, deshalb bleiben sie außen vor, damit sich der Verlauf auf Änderungen konzentriert, die eine Person tatsächlich vorgenommen hat.
- **Große Sammelbearbeitungen werden zusammengefasst, nicht einzeln aufgeführt.** Bearbeiten Sie in einer Sammelaktion Datensätze, die Sie ausdrücklich ausgewählt haben, lässt sich dieser Vorgang weiterhin bis zum eigenen Verlauf jedes einzelnen Datensatzes zurückverfolgen. Nutzen Sie stattdessen "alle, die diesem Filter entsprechen auswählen" ohne bestimmte Datensätze auszuwählen, erscheint dieser Vorgang nur als ein einziger Sammeleintrag im globalen Audit-Verlauf, nicht im Verlauf eines einzelnen Datensatzes.

## Wenn ein Datensatz gelöscht wird

Der Audit-Verlauf behält fest, *was* gelöscht wurde (seinen Namen, damit der Eintrag auch nach dem Verschwinden des Datensatzes selbst aussagekräftig bleibt), zusammen mit wer ihn gelöscht hat und wann. Was er noch nicht kann, ist den Datensatz daraus wiederherzustellen. Das Wiederherstellen gelöschter Datensätze aus diesem Verlauf ist geplant, aber heute noch nicht verfügbar, einmal gelöscht, ist ein Datensatz endgültig weg.

## Wer was sehen kann

- **Der eigene Verlauf eines Datensatzes** ist für jeden sichtbar, der den Datensatz selbst bereits öffnen kann, genau wie beim Ansehen des Datensatzes selbst.
- **Der vollständige Audit-Verlauf** und die Seite **Impersonation-Sitzungen**, die ungefilterte Sicht über alles hinweg, liegen unter Einstellungen und sind auf Administratoren beschränkt.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Muss ich das erst einschalten? | Nein, jedes Modul wird automatisch verfolgt. |
| Wo sehe ich den Verlauf eines Datensatzes? | Sein Aktionsmenü > "Verlauf anzeigen" |
| Wo sehe ich alles? | Einstellungen > Audit-Verlauf (Administratoren) |
| Wo sehe ich die Impersonation-Sitzungen selbst? | Einstellungen > Impersonation-Sitzungen (Administratoren) |
| Wird Impersonation je verborgen? | Nein, sie ist immer gekennzeichnet, für jeden, der den Eintrag sehen kann |
| Werden neu berechnete Summen protokolliert? | Nein, nur Felder, die eine Person tatsächlich geändert hat |
| Kann ich einen gelöschten Datensatz von hier aus wiederherstellen? | Noch nicht, sein Name bleibt erhalten, der Datensatz selbst ist aber weg |
