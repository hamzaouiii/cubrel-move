# Einen Benutzer in Cubrel imitieren

In diesem Artikel geht es darum, wer wen imitieren darf, wie Sie eine Sitzung starten und beenden, und wo jede Impersonation-Sitzung protokolliert wird.

## Wofür Impersonation da ist

Mit Impersonation kann sich ein Super-Admin *als* ein anderer Benutzer anmelden und genau das sehen, was diese Person sieht, nützlich, um ein Problem aus deren eigener Sicht nachzuvollziehen, ohne dass sie den Bildschirm teilen muss. Es ist eingeschränkt und wird vollständig protokolliert, siehe [Wer imitieren darf](#wer-imitieren-darf) und [Impersonation ist immer transparent, nie verborgen](audit-trail-guide.md#impersonation-ist-immer-transparent-nie-verborgen) im Leitfaden zum Audit-Verlauf.

## Wer imitieren darf

Nur **Super-Admins** können eine Impersonation-Sitzung starten, und nur bei regulären Benutzern:

- Sie können sich nicht selbst imitieren.
- Sie können keinen anderen Administrator oder Super-Admin imitieren.
- Sie können keinen Benutzer imitieren, dessen Status nicht **Aktiv** ist.

## Eine Sitzung starten

Öffnen Sie den Datensatz der Zielperson (**Benutzer**) und wählen Sie im Aktionsmenü **Anmelden als**. Sie landen auf deren Dashboard und sehen die Anwendung genau so, wie diese Person sie sehen würde. Die Person erhält außerdem eine Benachrichtigung, dass sie gerade imitiert wird, das passiert nie unbemerkt.

## Während der Impersonation

Ein gelbes Banner bleibt die ganze Zeit unten am Bildschirmrand fixiert und zeigt, wer wen gerade imitiert, sodass nie unklar ist, welches Konto tatsächlich handelt.

## Eine Sitzung beenden

Klicken Sie auf die Beenden-Schaltfläche im Banner, um zu Ihrem eigenen Konto zurückzukehren. Bleibt eine Sitzung aus irgendeinem Grund offen (zum Beispiel schließt sich der Browser, ohne dass beendet wurde), schließt Cubrel sie automatisch, sobald die zugrunde liegende Sitzung abläuft, sodass keine Sitzung unbegrenzt offen bleibt.

## Wo Sitzungen protokolliert werden

**Einstellungen > Impersonation-Sitzungen** listet jede jemals stattgefundene Sitzung: wer wen imitiert hat, von welcher IP-Adresse aus, wann sie begann, wann sie endete (oder ob sie noch läuft), und wie lange sie dauerte.

Jede während der Impersonation ausgeführte Aktion erscheint außerdem im eigenen Audit-Verlauf dieses Datensatzes, gekennzeichnet mit einem Abzeichen, das den tatsächlichen Super-Admin dahinter nennt, sodass der Verlauf eines Datensatzes immer zeigt, wer wirklich am Werk war.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wer kann jemanden imitieren? | Nur Super-Admins |
| Wer kann imitiert werden? | Jeder aktive, nicht-administrative Benutzer |
| Wie starte ich eine Impersonation? | Aktionsmenü des Datensatzes > **Anmelden als** |
| Wird die imitierte Person benachrichtigt? | Ja, immer |
| Wie erkenne ich, dass ich gerade imitiere? | Ein dauerhaftes Banner am unteren Bildschirmrand |
| Wie beende ich es? | Klick auf Beenden im Banner |
| Was, wenn ich vergesse zu beenden? | Die Sitzung schließt sich automatisch, sobald sie abläuft |
| Wo kann ich vergangene Sitzungen einsehen? | **Einstellungen > Impersonation-Sitzungen** |
| Erscheinen imitierte Aktionen im Audit-Verlauf? | Ja, gekennzeichnet mit dem tatsächlichen Imitierenden |
