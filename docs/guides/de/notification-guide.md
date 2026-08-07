# Benachrichtigungen

In diesem Artikel geht es darum, wo Benachrichtigungen in Cubrel zu finden sind, welche Aktionen sie auslösen, und wie Sie steuern, welche davon zusätzlich per E-Mail an Sie gehen.

## Wo Sie sie finden

Ein Glockensymbol sitzt in der Kopfleiste, neben Ihrem Profilmenü. Ein kleines rotes Abzeichen darauf zeigt, wie viele Benachrichtigungen Sie noch nicht gelesen haben. Ein Klick darauf öffnet ein Dropdown mit Ihren aktuellsten Benachrichtigungen, neueste zuerst, jede mit einem Symbol, einer kurzen Beschreibung und der Angabe, wie lange es her ist.

Neue Benachrichtigungen treffen live ein, während Sie die Anwendung geöffnet haben, das Abzeichen an der Glocke aktualisiert sich in dem Moment, in dem etwas passiert, ohne dass Sie neu laden müssen, und zusätzlich erscheint für ein paar Sekunden ein kleines Popup unten links im Bildschirm, sodass Sie es auch bemerken, ohne gerade auf die Glocke zu schauen.

## Was eine Benachrichtigung auslöst

| Ereignis | Wann Sie benachrichtigt werden |
| --- | --- |
| **Ein Datensatz wird Ihnen zugewiesen** | Jemand ändert das Feld Gehört zu eines Datensatzes auf Sie (in jedem Modul, Interessenten, Verkaufschancen, Firmen usw.), oder legt einen neuen Datensatz mit Ihnen als Besitzer an. |
| **Sie werden zu einem Meeting eingeladen** | Jemand fügt Sie als Teilnehmer zu einem Meeting-Datensatz hinzu. |
| **Eine Aufgabe ist bald fällig** | Eine Ihrer Aufgaben ist innerhalb der nächsten 24 Stunden fällig und noch nicht als abgeschlossen markiert. Wird automatisch stündlich geprüft. |
| **Eine von Ihnen gesendete Einladung wurde angenommen** | Jemand, den Sie eingeladen haben, schließt das Anlegen seines Kontos ab. |
| **Eine von Ihnen gesendete Einladung ist abgelaufen** | Eine von Ihnen gesendete Einladung bleibt über ihr Ablaufdatum hinaus unbeantwortet. Wird automatisch stündlich geprüft. |
| **Aktivität an einem Datensatz, der Ihnen gehört** | Jemand anderes bearbeitet, löscht oder verknüpft eine neue Aktivität (eine Aufgabe, einen Anruf, ein Meeting oder eine Notiz) mit einem Datensatz, der Ihnen gehört. Über Ihre eigenen Änderungen an Ihren eigenen Datensätzen werden Sie nicht benachrichtigt. |
| **Auf Ihr Konto wurde zugegriffen** | Ein Super-Admin imitiert Ihr Konto (meldet sich als Sie an), um bei einem Problem zu helfen. |
| **Ein Datensatz, der Ihnen gehört, wurde umgewandelt** | Jemand führt eine Umwandlungsregel aus (siehe [Umwandlungsregeln](conversion-guide.md)) an einem Datensatz, der Ihnen gehört, manuell oder automatisch, und macht daraus einen neuen Datensatz in einem anderen Modul. Führen Sie es selbst aus, werden Sie nicht benachrichtigt. |
| **Ihre Änderung hat eine automatische Umwandlung ausgelöst** | Sie bearbeiten einen Datensatz so, dass die Bedingungen einer automatischen Umwandlungsregel erfüllt sind, und sie läuft von selbst. Da das im Hintergrund unbemerkt passiert, ist dies Ihr Weg, davon zu erfahren. |

Ein Klick auf eine Benachrichtigung führt Sie (wo zutreffend) direkt zum betreffenden Datensatz oder zur betreffenden Seite und markiert sie als gelesen. "Alle als gelesen markieren" oben im Dropdown löscht alle ungelesenen Benachrichtigungen auf einmal. Das Popup unten links funktioniert genauso, ein Klick springt zum Datensatz und markiert es als gelesen, oder fahren Sie ein paar Sekunden mit der Maus darüber, markiert es sich von selbst als gelesen, ohne irgendwohin zu navigieren.

## Benachrichtigungskanäle

Jeder der neun Ereignistypen hat zwei unabhängige Ein-/Aus-Schalter: ob er **in der Anwendung** erscheint (Glocke und Popup), und ob er zusätzlich **per E-Mail** an Sie geht. Einen Typ in der Anwendung auszuschalten, blendet ihn nicht nur aus, er wird dort gar nicht mehr zugestellt, genauso wie das Ausschalten von E-Mail den Versand komplett stoppt.

Gehen Sie zu **Präferenzen > Benachrichtigungen**, um beide Schalter für jeden Ereignistyp nebeneinander zu sehen. Standardmäßig:

- **Standardmäßig per E-Mail**: Zugriff auf Ihr Konto (Impersonation), eine von Ihnen gesendete Einladung wurde angenommen, eine von Ihnen gesendete Einladung ist abgelaufen.
- **E-Mail standardmäßig aus**: Datensatz zugewiesen, Meeting-Einladung, Aufgabe bald fällig, Aktivität an Ihren Datensätzen, ein Ihnen gehörender Datensatz wurde umgewandelt, Ihre Änderung hat eine automatische Umwandlung ausgelöst.
- **In der Anwendung standardmäßig an**: alle neun Typen.

Schalten Sie einzelne davon an oder aus, je nachdem, wie eng Sie mitverfolgen möchten, gehören Ihnen zum Beispiel viele Datensätze und Sie möchten kein Postfach voller "Aktivität an Ihrem Datensatz"-E-Mails, lassen Sie diese aus und schauen bei Gelegenheit einfach nach der Glocke. Möchten Sie dagegen nie eine Aufgabenfrist verpassen, selbst wenn Sie nicht angemeldet sind, schalten Sie E-Mail für "Aufgabe bald fällig" ein.

Diese Einstellungen sind persönlich für Ihr Konto, ändern Sie sie für sich selbst, ändert das nicht, was jemand anderes sieht oder erhält. Lassen Sie einen Schalter genau so, wie er angezeigt wird, folgt er stattdessen dem Standardwert Ihres Unternehmens, den ein Administrator getrennt steuert (Einstellungen > Benachrichtigungen), Ihr Unternehmen kann so sinnvolle Standardwerte für alle setzen, und Sie können trotzdem jeden davon für sich selbst feinjustieren.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wo erscheinen Benachrichtigungen? | Das Glockensymbol in der Kopfleiste, auf jeder Seite, plus ein Popup unten links, sobald sie passieren |
| Muss ich Benachrichtigungen erst einschalten? | Nein, alle neun Typen sind standardmäßig in der Anwendung an, Sie können jeden davon ausschalten |
| Wie oft aktualisiert sich die Anzahl ungelesener Benachrichtigungen? | Sofort, in dem Moment, in dem etwas passiert |
| Kann ich diese auch per E-Mail bekommen? | Ja, pro Typ, unter Präferenzen > Benachrichtigungen |
| Welche gehen standardmäßig per E-Mail? | Impersonation, Einladung angenommen, Einladung abgelaufen |
| Kann mein Unternehmen andere Standardwerte festlegen? | Ja, ein Administrator setzt unternehmensweite Standardwerte unter Einstellungen > Benachrichtigungen, Ihre persönlichen Präferenzen überschreiben diese für Ihr eigenes Konto |
| Werde ich über meine eigenen Aktionen benachrichtigt? | Nein, Aktionen an Ihren eigenen Datensätzen benachrichtigen Sie nie |
| Wie leere ich meine Benachrichtigungen? | Auf eine klicken (in Glocke oder Popup), um sie als gelesen zu markieren, kurz über ein Popup fahren, oder "Alle als gelesen markieren" für alles auf einmal |
