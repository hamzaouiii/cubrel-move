# Aktivitäten in Cubrel verstehen

In diesem Artikel geht es darum, was Aufgaben, Anrufe, Meetings und Notizen gemeinsam haben, wo ihr gemeinsamer Verlauf auf anderen Datensätzen erscheint, wie Sie sie von dort aus hinzufügen und abschließen, und was Meetings von den übrigen unterscheidet.

## Was eine Aktivität ist

**Aufgaben**, **Anrufe**, **Meetings** und **Notizen** sind Module wie jedes andere, jedes hat seine eigene Listenansicht und Datensatzseite, aber sie teilen sich außerdem eine zweite Aufgabe: das Erscheinen im **Aktivitäten**-Verlauf der Datensätze, auf die sie sich beziehen. Cubrel nennt dieses Paar von Verhaltensweisen "Aktivität" und "hat Aktivitäten":

- Ein **Aktivitätsmodul** (Aufgaben, Anrufe, Meetings, Notizen) ist etwas, das Sie in Bezug auf andere Datensätze *tun*, einen Interessenten anrufen, ein Meeting mit einer Firma ansetzen, eine Notiz an einer Verkaufschance hinterlassen.
- Ein **Modul mit Aktivitäten** (Interessenten, Firmen, Kontakte, Verkaufschancen, Tickets, Angebote, Bestellungen, Rechnungen) ist ein Datensatz, der diese Aktivitäten *sammelt* und dafür ein Verlaufs-Panel erhält.

## Der Aktivitäten-Verlauf

Öffnen Sie einen Datensatz aus einem Modul mit Aktivitäten, sehen Sie unterhalb der Datensatz-Kopfzeile ein einklappbares **Aktivitäten**-Panel. Es hat drei Tabs:

- **Alle**: alles zusammen, Aktivitäten und Feldänderungen.
- **Aktivität**: nur die verknüpften Aufgaben, Anrufe, Meetings und Notizen.
- **Änderungen**: nur die Feldänderungshistorie (dieselbe Historie, die Sie über "Verlauf anzeigen" sehen würden, siehe den [Leitfaden zum Audit-Verlauf](audit-trail-guide.md)).

Welchen Tab Sie zuletzt geöffnet hatten, wird sich beim nächsten Öffnen des Panels gemerkt.

Einträge werden neueste zuerst auf einem verbundenen Zeitstrahl angezeigt, jeder mit einem Symbol für seinen Typ und einer relativen Zeitangabe ("vor 2 Std.", "Heute 14:30", "Gestern 09:12", oder ein einfaches Datum, sobald es länger zurückliegt).

## Eine Aktivität zu einem Datensatz hinzufügen

Klicken Sie oben im Panel auf **Hinzufügen**, um ein Dropdown mit jeder verfügbaren Aktivitätsart zu öffnen (Aufgabe, Anruf, Meeting, Notiz). Wählen Sie eine aus, öffnet sich das normale Erstellen-Formular dieses Moduls, füllen Sie es aus und speichern, es wird automatisch mit dem Datensatz verknüpft, von dem aus Sie gestartet sind. Kein separater Verknüpfungsschritt nötig.

## Eine Aufgabe direkt aus dem Verlauf abschließen

Ein Aufgaben-Eintrag im Verlauf zeigt eine aktive Checkbox. Sie abzuhaken aktualisiert die Aufgabe sofort, ohne die Seite zu verlassen oder die Aufgabe selbst zu öffnen.

## Eine Aktivität mit mehr als einem Datensatz verknüpfen

Eine Aktivität ist nicht auf einen einzigen übergeordneten Datensatz beschränkt. Ein Meeting zu einer Verkaufschance kann zum Beispiel sowohl mit der Verkaufschance selbst *als auch* mit der Firma verknüpft werden, zu der sie gehört, und erscheint dann in beiden Verläufen. Weitere Verknüpfungen fügen Sie genauso hinzu wie bei jeder anderen Beziehung, über den **Zugehörig**-Tab des Datensatzes.

Standardmäßig erscheinen diese Aktivitäts-Verknüpfungen nicht zusätzlich als eigenes Panel im Zugehörig-Tab, da der Verlauf sie bereits abdeckt, das wäre dieselbe Information doppelt gezeigt. Möchten Sie sie dort trotzdem sehen, kann ein Administrator dieses Panel über die Layout-Einstellungen des Moduls wieder hinzufügen.

## Meetings: ein Sonderfall

Alles oben Beschriebene funktioniert für Aufgaben, Anrufe, Meetings und Notizen gleich. Meetings haben zusätzlich eine eigene **Teilnehmer**-Liste, die verfolgt, *wer* kommt und wie diese Person geantwortet hat, zusätzlich zu (nicht anstelle von) der allgemeinen Verknüpfung von oben.

### Jedes Meeting beginnt mit einem Organisator

In dem Moment, in dem Sie ein Meeting erstellen, wird sein Besitzer automatisch als Teilnehmer mit der Rolle **Organisator** hinzugefügt, bereits als **Angenommen** markiert. Nur eine Person kann jeweils Organisator sein, wählen Sie eine neue, rutscht die vorherige automatisch zu **Erforderlich** herunter.

### Teilnehmer hinzufügen

Nutzen Sie auf der Datensatzseite eines Meetings **Teilnehmer hinzufügen**. Sie können hinzufügen:

- **Interne** Teilnehmer: aus Ihrem Team suchen und auswählen, oder aus Kontakten oder Interessenten. Name und E-Mail-Adresse stammen dabei vom verknüpften Datensatz.
- **Externe Gäste**: jeder außerhalb Ihres Unternehmens. Name, E-Mail und Rolle geben Sie direkt ein, die E-Mail-Adresse ist erforderlich, da sie der einzige Weg ist, auf dem Cubrel diese Person erreichen kann.

Sie können beides in derselben Runde mischen, bevor Sie speichern, jeder erhält seine eigene **Rolle**: Organisator, Erforderlich oder Optional.

### Antworten und Anwesenheit verfolgen

Jeder Teilnehmer trägt zwei unabhängige Status:

| Rückmeldung | Anwesenheit |
| --- | --- |
| Eingeladen | *(nicht erfasst)* |
| Angenommen | Anwesend |
| Abgelehnt | Nicht erschienen |
| Vorläufig | |

Die Rückmeldung zeigt, ob jemand kommt, die Anwesenheit (nach dem Meeting ausgefüllt) zeigt, ob die Person tatsächlich da war. Beide erscheinen als farbige Abzeichen in der Teilnehmerliste, fahren Sie über eine Zeile, um eines der beiden zu bearbeiten. Sind Sie fertig, setzt **Alle als anwesend markieren** mit einem Klick jeden, dessen Anwesenheit noch nicht erfasst wurde, auf Anwesend, sodass Sie sich nach einem gut besuchten Meeting nicht durch die Liste klicken müssen.

Teilnehmer werden zuerst nach Organisator, dann Erforderlich, dann Optional sortiert. Interne Teilnehmer zeigen die Farbe des Moduls, aus dem sie stammen (Kontakt, Interessent oder Benutzer), externe Gäste erscheinen neutral grau.

## Was automatisch protokolliert wird

Alles im Aktivitäten-Verlauf, sowie jeder hinzugefügte, entfernte oder in einer Sammelaktion als anwesend markierte Teilnehmer eines Meetings, wird genauso erfasst wie jede andere Änderung in Cubrel, ohne dass etwas dafür eingerichtet werden muss. Siehe den [Leitfaden zum Audit-Verlauf](audit-trail-guide.md) für das, was dabei genau abgedeckt wird.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Was zählt als Aktivität? | Aufgaben, Anrufe, Meetings und Notizen |
| Wo sehe ich die Aktivitäten eines Datensatzes? | Im Aktivitäten-Panel unterhalb der Datensatz-Kopfzeile |
| Wie füge ich eine hinzu? | "Hinzufügen" im Panel → Art wählen → ausfüllen und speichern |
| Kann ich eine Aufgabe abschließen, ohne sie zu öffnen? | Ja, direkt im Verlauf abhaken |
| Kann sich eine Aktivität auf mehr als einen Datensatz beziehen? | Ja, verknüpfen Sie sie mit so vielen wie zutreffend |
| Was ist bei Meetings anders? | Sie haben zusätzlich eine eigene Teilnehmerliste mit Rückmeldung und Anwesenheit |
| Wer wird einem Meeting automatisch hinzugefügt? | Sein Besitzer, als Organisator |
| Kann ich Personen außerhalb meines Unternehmens hinzufügen? | Ja, als externe Gäste, mit Name und E-Mail |
| Wird das protokolliert? | Ja, automatisch, wie alles andere in Cubrel |
