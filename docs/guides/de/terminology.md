# Cubrel-Terminologie

Diese Seite ist eine Referenz für die Begriffe, die Cubrel für seine eigenen
Bausteine verwendet. Manche davon (Modul, Feld, Layout) begegnen Ihnen
ständig; andere (Bezeichnung, Dropdown-Liste) sind eher im Hintergrund tätig
und tauchen erst auf, wenn Sie etwas anpassen. Wo es zu einem Thema bereits
einen ausführlichen Leitfaden gibt, verlinkt dieser Eintrag darauf, betrachten
Sie diese Seite als die Landkarte, nicht als das ganze Gebiet.

## Die Bausteine

### Modul

Ein Modul ist ein Datensatztyp, Verkaufschancen, Kontakte, Firmen, Tickets
und so weiter sind alles Module. Jedes hat seinen eigenen Platz in der
Seitenleiste, eine eigene Listenansicht, eine eigene Datensatzseite und einen
eigenen Satz an Feldern. Falls Cubrel noch kein Modul für etwas hat, das Ihr
Unternehmen abbilden möchte, können Sie selbst eines erstellen. Details dazu
finden Sie im [Leitfaden zu Modulen](modules.md), einschließlich der
Schritte zum Erstellen eines neuen Moduls.

### Kernmodul

Ein Modul, das bereits mit Cubrel ausgeliefert wird, etwa Interessenten,
Firmen, Kontakte, Verkaufschancen, Angebote, Bestellungen, Rechnungen,
Produkte und Tickets, sowie Systembereiche wie Benutzer. Kernmodule
verhalten sich einmal angelegt genau wie benutzerdefinierte Module, "Kern"
beschreibt lediglich ihre Herkunft, nicht ihre Funktionsweise.

### Benutzerdefiniertes Modul

Ein Modul, das Sie (oder ein anderer Administrator) selbst mit dem
Modul-Ersteller erstellt haben. Einmal angelegt, funktioniert ein
benutzerdefiniertes Modul genauso wie ein Kernmodul, dieselben
Datensatzseiten, dieselben Feld- und Layout-Editoren, dieselben
Berechtigungen.

### Feld

Eine einzelne Information, die ein Datensatz enthält, ein Textfeld, ein
Datum, eine Auswahlliste, ein Währungsbetrag, ein Verweis auf einen
Datensatz eines anderen Moduls und so weiter. Jedes Modul hat seinen eigenen
Satz an Feldern, dazu fünf, die jedes Modul automatisch erhält: Name,
Beschreibung, Gehört zu (der Besitzer), Erstellt am und Aktualisiert am.

### Kernfeld

Eines der Felder, mit denen ein Modul ursprünglich angelegt wurde, entweder
eines der fünf automatischen Felder, die jedes Modul besitzt, oder ein Feld,
das Sie beim Erstellen eines benutzerdefinierten Moduls im Modul-Ersteller
festgelegt haben. Kernfelder sind strukturell: Sie sind von Anfang an Teil
der Definition des Moduls.

### Benutzerdefiniertes Feld

Ein Feld, das *nachträglich* zu einem bereits bestehenden Modul hinzugefügt
wurde, über **Einstellungen > Module > [Modul] > Felder**, ohne dass sonst
etwas angepasst werden muss. Benutzerdefinierte Felder können jederzeit
hinzugefügt, bearbeitet oder entfernt werden. Auf der Datensatzseite sehen
und verhalten sie sich genauso wie jedes andere Feld, "benutzerdefiniert"
beschreibt nur, wann sie hinzugefügt wurden, nicht wie sie sich verhalten.

### Layout

Wie die Felder eines Moduls auf dem Bildschirm angeordnet sind. Ein Modul hat
mehrere Layouts, jedes für eine andere Ansicht:

- **Liste** — welche Spalten in der Listenansicht des Moduls angezeigt
  werden.
- **Datensatz** — wie Felder auf der Datensatzseite selbst in Abschnitte
  gruppiert sind.
- **Zugehörige Panels** — welche Beziehungs-Panels auf dem
  Zugehörig-Tab eines Datensatzes erscheinen, und in welcher Reihenfolge.
- **Verknüpfungs-Panel** — welche zusätzlichen Spalten im Suchfenster
  angezeigt werden, das beim Verknüpfen eines Datensatzes mit einem anderen
  erscheint, über den Namen hinaus.
- **Positions-Übersicht** — bei Modulen mit Positionen, welche Spalten in
  der Positionstabelle selbst angezeigt werden.

Layouts werden pro Modul unter **Einstellungen > Module > [Modul] >
Layouts** bearbeitet. Ist für eine Ansicht kein eigenes Layout konfiguriert,
greift Cubrel automatisch auf eine sinnvolle Standardvorgabe zurück.

### Bezeichnung

Der für Menschen lesbare Name, unter dem etwas angezeigt wird, im Gegensatz
zu seinem internen Namen. Wenn Sie beim Anlegen eines Feldes dessen
"Bezeichnung" festlegen, ist genau das der Text, den Benutzer tatsächlich in
Formularen, Spalten und auf Datensatzseiten sehen, sie kann jederzeit
geändert werden, ohne das zugrunde liegende Feld selbst zu beeinflussen.
Module, Felder und Beziehungen haben alle ihre eigene Bezeichnung, die sich
zudem je nach Sprache unterscheiden kann, falls Ihr Unternehmen mehrsprachig
arbeitet.

### Dropdown-Liste

Eine wiederverwendbare Optionsliste für ein Feld vom Typ "Auswahlliste", zum
Beispiel die Vertriebsphase einer Verkaufschance oder die Quelle eines
Interessenten. Dropdown-Listen werden zentral unter **Einstellungen >
Dropdown-Editor** verwaltet, dieselbe Liste kann in mehreren Feldern
wiederverwendet werden, sodass eine Änderung an einer Stelle alle Felder
aktualisiert, die diese Liste nutzen.

### Beziehung

Eine Verknüpfung zwischen zwei Modulen, die beschreibt, wie deren Datensätze
zusammenhängen, zum Beispiel "eine Firma hat viele Kontakte" oder "eine
Verkaufschance gehört zu einer Firma". Cubrel bringt bereits ein Set an
Beziehungen zwischen den Standardmodulen mit, und Sie können eigene
Beziehungen zwischen beliebigen zwei Modulen anlegen, auch zwischen selbst
erstellten Modulen.

### Positionen

Bei Modulen wie Angeboten, Bestellungen und Rechnungen bedeutet "hat
Positionen", dass Datensätze nicht nur reguläre Felder enthalten, sondern
zusätzlich eine Liste bepreister Posten (meist aus Ihrem Produkte-Modul
ausgewählt), deren Mengen und Preise automatisch in eine Zwischensumme, einen
Steuerbetrag, einen Rabattbetrag und einen Gesamtbetrag auf dem Datensatz
einfließen. Diese Funktion lässt sich auch für ein benutzerdefiniertes Modul
aktivieren, wobei Sie festlegen, aus welchem Modul die Positionen ausgewählt
werden.

### Dashboard

Die Startseite, die Sie nach der Anmeldung sehen, eine anpassbare
Zusammenstellung von Widgets, die Ihnen einen Überblick über Ihre Daten gibt
(zuletzt bearbeitete Datensätze, Zählungen und so weiter). Es gibt ein
unternehmensweites Standard-Dashboard, und jeder Benutzer kann zusätzlich
eine eigene, persönliche Version davon haben.

## Personen und Zugriff

### Administrator

Ein Benutzer mit Zugriff auf **Einstellungen**, also Module, Felder,
Layouts, Dropdown-Listen, Unternehmensdaten und alles Weitere, das auf
dieser Seite behandelt wird. Reguläre Benutzer ohne Administratorrechte
sehen nur das alltägliche CRM: ihre Module, Datensätze und persönlichen
Präferenzen.

### Super-Admin

Die höchste Zugriffsstufe in einer Cubrel-Instanz. Zusätzlich zu allem, was
ein Administrator kann, kann sich ein Super-Admin *als* ein anderer
Benutzer anmelden (Impersonation), um ein Problem genau aus dessen
Perspektive nachzuvollziehen. Eine solche Impersonation ist immer sichtbar
im Audit-Verlauf, wenn sie stattfindet, niemals verborgen.

### Besitzer

Der Benutzer, dem ein Datensatz zugewiesen ist, in den Feldern selbst als
"Gehört zu" bezeichnet. Die meisten Module verfolgen für jeden Datensatz
einen Besitzer (verwendet für Sichtbarkeit, Filterung und
"Meine Einträge"-Ansichten); eine Handvoll Systemmodule, die keine
Arbeitsobjekte einer Person darstellen (wie Benutzer selbst), haben keinen.

## Datensätze und Daten

### Datensatz

Ein einzelner Eintrag innerhalb eines Moduls, eine bestimmte Verkaufschance,
ein bestimmter Kontakt und so weiter. "Modul" beschreibt den Typ, "Datensatz"
ist eine einzelne Instanz davon.

### Listenansicht

Die Tabelle mit Datensätzen, die Sie sehen, wenn Sie ein Modul öffnen, eine
Zeile pro Datensatz, mit Spalten, die durch das Listen-Layout dieses Moduls
festgelegt sind. Unterstützt Suchen, Sortieren, Filtern und die Auswahl
mehrerer Datensätze gleichzeitig für Sammelaktionen.

### Listenfilter

Eine gespeicherte Suche, die Sie wiederverwenden können, statt dieselben
Kriterien jedes Mal erneut einzugeben. Filter können privat bleiben, mit
allen Benutzern des Moduls geteilt werden, oder (in einigen Fällen) von
Cubrel selbst als Systemfilter bereitgestellt werden, der nicht bearbeitet
oder gelöscht werden kann.

### Sammelaktionen

Das gleichzeitige Bearbeiten, Löschen oder Exportieren mehrerer Datensätze
aus einer Listenansicht heraus, entweder durch die einzelne Auswahl
bestimmter Datensätze, oder durch die Auswahl "alle, die dem aktuellen
Filter entsprechen" auf einmal, auch über mehrere Seiten hinweg.

### Export

Das Herunterladen eines Datensatzes, oder einer Sammelauswahl von
Datensätzen, als JSON- oder CSV-Datei aus der Listenansicht oder von der
Datensatzseite aus.

### Suche

Cubrels globale Suche (die Suchleiste, die von überall in der Anwendung aus
verfügbar ist) durchsucht alle Module gleichzeitig und führt Sie direkt zu
einem passenden Datensatz. Jedes Feld kann bei seiner Konfiguration als
durchsuchbar markiert werden oder nicht.

## Verlauf und Kontrolle

### Audit-Verlauf

Das automatische, dauerhaft aktive Protokoll jeder Erstellung, Änderung und
Löschung in jedem Modul, ohne dass dafür etwas eingerichtet werden muss.
Einsehbar pro Datensatz ("Verlauf anzeigen" im Aktionsmenü dieses
Datensatzes) oder vollständig unter **Einstellungen > Audit-Verlauf** für
Administratoren.

### Impersonation-Sitzung

Der Eintrag darüber, dass sich ein Super-Admin als ein anderer Benutzer
angemeldet hat, aufgelistet unter **Einstellungen >
Impersonation-Sitzungen**: wer, von welcher IP-Adresse aus, und wie lange.
Wird getrennt vom Audit-Verlauf geführt, der stattdessen die während einer
solchen Sitzung vorgenommenen *Änderungen* kennzeichnet.

### Sitzung

Cubrels Art, sich zu merken, dass Sie angemeldet sind. Sitzungen gelten pro
Gerät und laufen normalerweise nach einer Phase der Inaktivität ab, sofern
Sie sich nicht dafür entscheiden, länger angemeldet zu bleiben.

## Dokumente

### PDF-Vorlage

Ein wiederverwendbares Layout zum Erzeugen eines PDFs aus einem Datensatz,
am häufigsten für Angebote, Bestellungen oder Rechnungen. Verwaltet unter
**Einstellungen > PDF-Vorlagen**, ein Modul kann mehrere Vorlagen haben,
wobei eine als Standard markiert ist, die verwendet wird, wenn keine
bestimmte Vorlage ausgewählt wurde.

## Alltägliche Einstellungen

Ein paar weitere Einstellungsbereiche runden die Anwendung ab, ohne dass sie
einen eigenen Begriff benötigen:

- **Unternehmensdaten** — die eigenen Angaben Ihres Unternehmens (unter
  anderem verwendet auf erzeugten PDFs).
- **Region und Sprachoptionen** — Datums-, Uhrzeit- und Zahlenformatierung
  für Ihr Unternehmen.
- **Stil** — Branding: Primär-/Sekundärfarben, sowie ob jedes Modul seine
  eigene Akzentfarbe verwendet oder eine gemeinsame.
- **Präferenzen** — kleinere persönliche bzw. unternehmensweite
  Voreinstellungen, etwa wie viele Datensätze pro Seite in einer
  Listenansicht angezeigt werden.
- **Benutzereinladungen** — wie neue Benutzer zu Ihrem Unternehmen
  hinzugefügt werden; eine eingeladene Person vergibt ihr eigenes Passwort
  bei der ersten Anmeldung.

## Kurz gefasst

| Begriff | Definition in einem Satz |
| --- | --- |
| Modul | Ein Datensatztyp mit eigener Liste, Datensatzseite und Feldern |
| Kernmodul | Ein Modul, das mit Cubrel ausgeliefert wird |
| Benutzerdefiniertes Modul | Ein von einem Administrator erstelltes Modul |
| Feld | Eine einzelne Information auf einem Datensatz |
| Kernfeld | Ein Feld, mit dem das Modul ursprünglich angelegt wurde |
| Benutzerdefiniertes Feld | Ein Feld, das nachträglich hinzugefügt wurde |
| Layout | Wie die Felder eines Moduls auf einer Ansicht angeordnet sind |
| Bezeichnung | Der für Menschen lesbare Name, unter dem etwas angezeigt wird |
| Dropdown-Liste | Eine wiederverwendbare, zentral verwaltete Optionsliste für Auswahlfelder |
| Beziehung | Eine Verknüpfung zwischen zwei Modulen |
| Positionen | Eine bepreiste Liste innerhalb eines Datensatzes (Angebote/Bestellungen/Rechnungen) |
| Dashboard | Die anpassbare Übersichtsseite nach der Anmeldung |
| Administrator | Ein Benutzer mit Zugriff auf Einstellungen |
| Super-Admin | Ein Administrator, der zusätzlich andere Benutzer imitieren kann |
| Besitzer | Der Benutzer, dem ein Datensatz zugewiesen ist |
| Datensatz | Ein einzelner Eintrag innerhalb eines Moduls |
| Listenfilter | Eine gespeicherte, wiederverwendbare Suche |
| Audit-Verlauf | Das automatische Protokoll jeder Änderung, überall |
| Impersonation-Sitzung | Der Eintrag darüber, dass sich ein Super-Admin als jemand anderes angemeldet hat |
| Sitzung | Cubrels Art, sich Ihre Anmeldung zu merken |
| PDF-Vorlage | Ein wiederverwendbares Layout zum Erzeugen eines Datensatzes als PDF |
