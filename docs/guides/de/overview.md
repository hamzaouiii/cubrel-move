# Der Cubrel-Leitfaden

Dieser Artikel ist ein Rundgang durch Cubrel aus Sicht eines Benutzers: was Sie damit im Alltag tun können, von der ersten Anmeldung bis zum Betrieb eines etablierten Teams. Er richtet sich an jeden, der Cubrel nutzt, nicht nur an Administratoren, auch wenn ein paar Abschnitte (entsprechend gekennzeichnet) nur mit Administratorzugriff gelten. Gibt es zu einem Thema bereits einen eigenen ausführlichen Leitfaden, liefert dieser Artikel die Kurzversion und verlinkt zur Vertiefung.

## Erste Schritte

Beim allerersten Öffnen einer brandneuen Cubrel-Instanz gibt es eine kurze, einmalige Einrichtung: Die Person, die Cubrel installiert, erhält einen Link per E-Mail, um das erste Konto anzulegen, das zum ersten Administrator wird.

Von dort aus durchläuft jede neue Instanz direkt nach dieser ersten Anmeldung einen kurzen Einrichtungsassistenten:

1. **Ihr Unternehmen**: Name, Logo, Adresse, Telefon, E-Mail, Website. Das sind die Angaben, die später auf erzeugten PDFs erscheinen.
2. **Beispieldaten** (optional): Cubrel kann einige Beispieldatensätze anlegen, damit Sie sich umsehen und sehen können, wie alles zusammenpasst, bevor Sie Ihre echten Daten hinzufügen.
3. **Ihr Team einladen**: die Personen hinzufügen, die Cubrel mit Ihnen gemeinsam nutzen werden. Sie können das überspringen und Personen später einladen.

Danach landen Sie auf Ihrem Dashboard, und Cubrel verhält sich von da an für jeden, der sich anmeldet, gleich.

## Module: woraus Cubrel aufgebaut ist

Alles in Cubrel, Interessenten, Firmen, Kontakte, Verkaufschancen, Angebote, Bestellungen, Rechnungen, Produkte, Tickets, ist ein **Modul**: ein Datensatztyp mit eigenem Platz in der Seitenleiste, eigener Liste von Einträgen und eigener Datensatzseite. Cubrel bringt all das bereits mit, organisiert in ein paar Gruppen (Vertrieb, Umsatz, Kundenservice), plus Systembereiche wie Benutzer und Einstellungen.

Erfasst Ihr Unternehmen etwas, wofür Cubrel noch kein Modul hat, Ausrüstung, Projekte, Garantien, was auch immer es ist, kann ein Administrator dafür ohne eine Zeile Code ein neues Modul bauen, mit dem **Modul-Ersteller**. Einmal gebaut, funktioniert ein benutzerdefiniertes Modul genau wie ein mitgeliefertes: dieselben Datensatzseiten, dieselben Layouts, dieselben Berechtigungen. Den vollständigen Ablauf zum Erstellen und Pflegen eines Moduls finden Sie im [Leitfaden zu Modulen](modules.md).

## Felder: woraus ein Datensatz besteht

Jeder Datensatz besteht aus **Feldern**, einzelnen Informationen wie einem Namen, einer E-Mail-Adresse, einem Datum oder einem Betrag. Cubrel unterstützt eine breite Palette an Feldtypen, sodass für jede Art von Daten die passende Eingabe verwendet wird:

- Einfacher Text und längere Textnotizen
- E-Mail, Telefon und Webadresse, jeweils mit eigener Validierung, damit offensichtlich fehlerhafte Eingaben auffallen
- Dropdown-Felder ("Auswahl") und farbige Status-Felder
- Ja/Nein-Checkboxen
- Daten und Datum mit Uhrzeit
- Ganzzahlen, Dezimalzahlen, Prozentsätze und Währungsbeträge
- Mehrteilige Adressen (Straße, Ort, Postleitzahl, Land und so weiter)
- Verweise auf einen Datensatz eines anderen Moduls
- Bild-Uploads

Jedes Modul kommt automatisch mit einer Handvoll Felder, Name, Beschreibung, Gehört zu, Erstellt am und Aktualisiert am, ein Administrator kann jederzeit so viele zusätzliche Felder hinzufügen, wie das Modul braucht, ohne Ausfallzeit oder technischen Aufwand. Nachträglich zu einem bereits bestehenden Modul hinzugefügte Felder heißen **benutzerdefinierte Felder**, aus Ihrer Sicht sehen und verhalten sie sich aber genau wie jedes andere Feld am Datensatz.

## Layouts: wie Bildschirme angeordnet sind

Welche Felder Sie tatsächlich sehen, und wo, wird von **Layouts** gesteuert, jedes Modul hat mehrere davon, jedes für einen anderen Bildschirm:

- Die **Listenansicht**: welche Spalten in der Tabelle erscheinen, wenn Sie ein Modul öffnen.
- Die **Datensatzseite**: wie Felder beim Öffnen eines Datensatzes in Abschnitte gruppiert sind.
- Die **Zugehörigen Panels**: welche Panels verknüpfter Datensätze im Zugehörig-Tab eines Datensatzes erscheinen, und in welcher Reihenfolge.
- Das **Verknüpfungsfenster**: welche zusätzlichen Spalten Ihnen helfen, Datensätze bei der Suche nach einem zu verknüpfenden auseinanderzuhalten.
- Die **Positionstabelle**: bei Modulen mit Positionen, welche Spalten in dieser Tabelle angezeigt werden.

Administratoren können jedes davon mit einem Drag-and-drop-Editor unter **Einstellungen → Module → [Modul] → Layouts** neu anordnen, ohne sonst etwas am Modul zu verändern.

## Mit Datensätzen arbeiten

### Erstellen, bearbeiten und löschen

Jedes Modul bietet Ihnen die vertrauten Aktionen: die Liste öffnen, einen neuen Datensatz erstellen, einen bestehenden öffnen, um ihn anzusehen oder zu bearbeiten, oder ihn zu löschen. Jede Änderung, die Sie vornehmen, erstellen, bearbeiten oder löschen, wird automatisch im Verlauf dieses Datensatzes festgehalten, siehe den [Leitfaden zum Audit-Verlauf](audit-trail-guide.md).

### Sammelaktionen

Sie müssen nicht immer nur einen Datensatz auf einmal bearbeiten. In jeder Listenansicht können Sie mehrere Datensätze auswählen, einzeln, oder "alles, was meinem aktuellen Filter entspricht", auch über mehrere Seiten hinweg, und:

- Sie alle auf einmal **löschen**.
- Ein Feld bei allen auf einmal in einem Schritt **aktualisieren** (zum Beispiel den Besitzer von 40 Interessenten auf einmal ändern).

Ist das Feld, das Sie in einer Sammelaktion bearbeiten, ein Pflichtfeld, prüft Cubrel vorab, dass der neue Wert keinen Datensatz leer lassen würde, bevor irgendetwas geändert wird.

### Exportieren

Sie können einen einzelnen Datensatz, oder eine Sammelauswahl, als **CSV** oder **JSON** aus der Listenansicht oder der Datensatzseite selbst exportieren. Der Export eines einzelnen Datensatzes umfasst auch seine Positionen (falls das Modul welche hat) als eigenen Abschnitt, Sammelexporte vieler Datensätze auf einmal tun das nicht, da Positionen jede Zeile unterschiedlich formen würden. Exportierte Werte sind so formatiert, wie sie auf einem erzeugten PDF erscheinen würden, was Sie herunterladen, entspricht also dem, was Sie gedruckt sehen würden.

## Beziehungen: Datensätze miteinander verbinden

Datensätze in verschiedenen Modulen lassen sich miteinander verknüpfen, eine Firma mit ihren Kontakten, eine Verkaufschance mit ihrer Firma, und so weiter. Sobald zwischen zwei Modulen eine Beziehung besteht, sehen Sie sie als Panel im **Zugehörig**-Tab jedes Datensatzes auf beiden Seiten.

Cubrel bringt bereits Beziehungen zwischen den Standardmodulen mit, und ein Administrator kann neue zwischen beliebigen zwei Modulen anlegen, auch zwischen selbst gebauten. Die verschiedenen Formen, die eine Beziehung annehmen kann (Eins-zu-Eins, Eins-zu-Viele, Viele-zu-Viele), und wie das Verknüpfen und Entfernen von Datensätzen funktioniert, stehen im [Leitfaden zu Beziehungen](relationships-guide.md).

## Positionen: aufgeschlüsselte Angebote, Bestellungen und Rechnungen

Angebote, Bestellungen und Rechnungen (sowie jedes ebenso eingerichtete benutzerdefinierte Modul) enthalten nicht nur reguläre Felder, sondern eine Liste bepreister **Positionen**, meist aus Ihrem Produktkatalog. Für jede Position berechnet Cubrel automatisch Zwischensumme, Rabatt und Steuer, und fasst alle Positionen im Gesamtbetrag, Rabatt, Steuer und der Zwischensumme des Datensatzes selbst zusammen, automatisch synchron gehalten, sobald sich eine Position ändert.

Welche Felder eines Produkts welche Positionsfelder automatisch befüllen (zum Beispiel sein Preis den Einzelpreis der Position), ist pro Modul konfigurierbar, ebenso welche Spalten in der Positionstabelle selbst erscheinen.

## Aktivitäten: ein Verlauf dessen, was passiert ist

Aufgaben, Anrufe, Meetings und Notizen sind Aktivitätsmodule: Eine erstellen und mit einem anderen Datensatz verknüpfen (einem Interessenten, einer Firma, einer Verkaufschance und so weiter) fügt sie dessen **Aktivitäten**-Verlauf hinzu, einer laufenden Historie direkt auf der Datensatzseite, neben der Feldänderungshistorie. Meetings verfolgen zusätzlich ihre eigene Teilnehmerliste, Rückmeldungsstatus und Anwesenheit. Das vollständige Bild steht im [Leitfaden zu Aktivitäten](activities-guide.md).

## Suche und Filter

### Alles finden, von überall

Die globale Suchleiste, von überall in Cubrel verfügbar, durchsucht alle Module gleichzeitig und führt Sie direkt zu einem passenden Datensatz.

### Eine Liste eingrenzen

Jede Listenansicht hat außerdem ihren eigenen Filter-Editor, mit dem Sie mehrere Bedingungen kombinieren (zum Beispiel "Status ist Offen" UND "Besitzer bin ich"), statt nur nach einem Stichwort zu suchen. Welche Bedingungen verfügbar sind, hängt vom Feldtyp ab, Textfelder unterstützen Dinge wie "enthält" oder "beginnt mit", Zahlen und Daten unterstützen "größer als", "vor/nach" oder "zwischen", und jedes Feld unterstützt "ist leer"/"ist nicht leer".

Einen häufig genutzten Filter können Sie speichern, statt ihn jedes Mal neu aufzubauen. Ein gespeicherter Filter kann privat bleiben, mit Ihrem ganzen Team geteilt werden, oder, bei ein paar mitgelieferten wie "Meine Datensätze", von Cubrel selbst bereitgestellt und immer verfügbar sein. Siehe den [Leitfaden zu Listenfiltern](list-filters-guide.md) für Details.

## Dashboard: Ihre Startseite

Die Seite, auf der Sie nach der Anmeldung landen, ist Ihr **Dashboard**, und es ist vollständig persönlich. Cubrel startet für jeden mit einer sinnvollen Standardanordnung, Sie können sie aber umgestalten, Widgets hinzufügen, nicht benötigte entfernen und in der Größe anpassen, Ihre Anordnung wird sich von da an merken, getrennt von der jedes anderen.

Verfügbare Widgets umfassen Kennzahlen, Aufschlüsselungen, Zeitverlaufsdiagramme, Datensatzlisten und Personen-/Team-Widgets, jedes konfigurierbar, aus welchem Modul, welchen Feldern und Filtern es schöpft, zum Beispiel "offene Verkaufschancen nach Vertriebsphase" oder "diesen Monat fällige Rechnungen".

## PDF-Dokumente

Jeder Datensatz aus einem dafür eingerichteten Modul, meist Angebote, Bestellungen und Rechnungen, lässt sich als ansprechendes PDF erzeugen, mit dem Branding Ihres Unternehmens (Logo, Name, Adresse) automatisch übernommen. Hat ein Modul mehr als eine PDF-Vorlage, werden Sie beim Erzeugen gefragt, welche verwendet werden soll, hat es nur eine, erzeugt und lädt Cubrel sie sofort herunter.

**Administratoren** gestalten diese Vorlagen unter **Einstellungen → PDF-Vorlagen**, mit einem abschnittsbasierten Editor (Kopfzeile, Fußzeile, Feldzeilen, Notizen, Tabellen zu verknüpften Datensätzen, eine Positionstabelle mit eigenen Summen) und einer Live-Vorschau, die mit Beispieldaten genau zeigt, wie ein erzeugtes PDF aussehen wird, bevor es je an einem echten Datensatz verwendet wird.

## Einstellungen (Administratoren)

Einstellungen bündeln alles, was steuert, wie sich Cubrel für Ihre gesamte Organisation verhält:

- **System**: Sprache, Datums-/Uhrzeit-/Zahlenformatierung, Zeitzone und die Standardwährung Ihrer Organisation, einschließlich der [Datenaufbewahrung](data-retention-guide.md).
- **Stil**: Ihre Markenfarben, Eckenrundung, und ob jedes Modul seine eigene Akzentfarbe behält oder eine gemeinsame nutzt.
- **Unternehmen**: Name, Adresse, Logo und Kontaktdaten Ihrer Organisation.
- **Feldverwaltung**: Felder an jedem Modul hinzufügen, bearbeiten oder entfernen.
- **Dropdown-Verwaltung**: die wiederverwendbaren Optionslisten hinter Ihren Auswahl- und Status-Feldern verwalten, sodass eine Änderung an einer Liste an einer Stelle jedes Feld aktualisiert, das sie nutzt.
- **PDF-Vorlagen**: oben beschrieben.
- **Module**: der Modul-Ersteller und die Modulverwaltung, weiter oben beschrieben.
- **Audit-Verlauf**: unten beschrieben.

## Berechtigungen, Rollen und Besitz

Cubrel hält den Zugriff einfach, mit zwei Stufen über einem regulären Benutzer:

- **Reguläre Benutzer** sehen jedes Modul, das für alle aktiviert ist, und haben vollen Erstellen-/Bearbeiten-/Löschzugriff auf Datensätze darin, einschließlich Verknüpfen und Entfernen zugehöriger Datensätze. Einstellungen, Benutzer und andere reine Administratorbereiche bleiben ihnen komplett verborgen.
- **Administratoren** erhalten zusätzlich Zugriff auf Einstellungen, Module, Felder, Layouts, Dropdowns, Unternehmensdaten, PDF-Vorlagen und Benutzerverwaltung.
- **Super-Admins** können alles, was ein Administrator kann, plus eine zusätzliche Sache: sich *als* ein anderer Benutzer anmelden (Impersonation), um bei einem Problem genau aus dessen Sicht zu helfen. Das ist danach immer vollständig sichtbar, nie verborgen, siehe [den Leitfaden zum Audit-Verlauf](audit-trail-guide.md#impersonation-ist-immer-transparent-nie-verborgen).

Die meisten Module verfolgen außerdem einen **Besitzer**, den Benutzer, dem ein Datensatz zugewiesen ist, das ist die Grundlage für "Meine Datensätze"-Filter und besitzerbezogene Dashboard-Widgets.

## Benutzer und Einladungen (Administratoren)

Administratoren verwalten das Team unter **Einstellungen → Benutzer**: Konten direkt anlegen, oder Personen per E-Mail einladen, einzeln oder mehrere auf einmal. Eine eingeladene Person erhält einen Link, um ihr eigenes Passwort zu setzen und ihr Konto anzulegen, Administratoren können eine offene Einladung jederzeit erneut senden oder zurückziehen. Jeder Benutzer hat außerdem seine eigene Profilseite, um Name, Kontaktdaten, Titel und Avatar selbst zu aktualisieren.

## Anmelden und angemeldet bleiben

Die Anmeldung läuft über die übliche E-Mail-und-Passwort-Anmeldung, bei Bedarf mit einem "Passwort vergessen"-Ablauf. Ein paar Dinge sind gut zu wissen:

- **"Angemeldet bleiben"** auf dem Anmeldebildschirm hält Sie auf diesem Gerät für sehr lange angemeldet (etwa ein Jahr), statt der normalen mehrstündigen Sitzung, praktisch auf Ihrem eigenen Computer, auf einem gemeinsam genutzten besser vermeiden.
- **Solange Sie Cubrel aktiv nutzen**, läuft Ihre Sitzung schlicht nicht ab, egal wie lange Sie den Tab schon offen haben.
- **Werden Sie mitten in einer Bearbeitung abgemeldet**, wirft Cubrel nicht weg, was Sie gerade eingegeben hatten, Sie landen nach der erneuten Anmeldung direkt wieder dort.

Die vollständigen Details dazu, wie das über mehrere Geräte und Browser-Tabs hinweg funktioniert, stehen im [Leitfaden zu Sitzungen](session-timeout-guide.md).

## Audit-Verlauf: ein Protokoll jeder Änderung

Jede Erstellung, Änderung und Löschung an jedem Datensatz, und jedes Verknüpfen oder Entfernen zwischen Datensätzen, wird automatisch protokolliert, ohne dass etwas dafür eingerichtet werden muss. Sie sehen den eigenen Verlauf jedes Datensatzes über sein Aktionsmenü ("Verlauf anzeigen"), Administratoren sehen das vollständige Bild über die ganze Organisation unter **Einstellungen → Audit-Verlauf**. Was dabei genau erfasst wird, steht im [Leitfaden zum Audit-Verlauf](audit-trail-guide.md), ein schnelles Nachschlagewerk zu allem in diesem Leitfaden bietet die [Cubrel-Terminologie](terminology.md).

## Wie es weitergeht

- Neu bei Cubrels Vokabular? Starten Sie mit der [Cubrel-Terminologie](terminology.md).
- Bauen Sie Ihr eigenes Modul? Siehe den [Leitfaden zu Modulen](modules.md).
- Module miteinander verbinden? Siehe den [Leitfaden zu Beziehungen](relationships-guide.md).
- Aufgaben, Anrufe, Meetings und Notizen an einem Datensatz protokollieren? Siehe den [Leitfaden zu Aktivitäten](activities-guide.md).
- Neugierig, was automatisch verfolgt wird? Siehe den [Leitfaden zum Audit-Verlauf](audit-trail-guide.md).
- Fragen Sie sich, wie sich Anmeldung und Sitzungen verhalten? Siehe den [Leitfaden zu Sitzungen](session-timeout-guide.md).
