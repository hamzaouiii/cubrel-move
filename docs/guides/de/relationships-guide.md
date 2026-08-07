# Beziehungen in Cubrel verstehen

In diesem Artikel geht es darum, was eine Beziehung eigentlich ist, welche Formen sie annehmen kann, wie Sie eigene erstellen und löschen, und wie das Verknüpfen von Datensätzen im Alltag funktioniert.

## Was eine Beziehung ist

Eine Beziehung verbindet zwei Module miteinander, zum Beispiel "Firmen haben viele Kontakte" oder "Jede Verkaufschance gehört zu einer Firma". Sobald zwischen zwei Modulen eine Beziehung besteht, sehen Sie sie als Panel im Zugehörig-Tab jedes Datensatzes in beiden Modulen, mit den jeweils aktuell verknüpften Datensätzen des anderen Moduls.

Cubrel bringt bereits ein Set an Beziehungen zwischen den Standardmodulen mit (Firmen, Kontakte, Verkaufschancen, Angebote und so weiter), diese heißen **Systembeziehungen**. Sie können außerdem eigene **benutzerdefinierte Beziehungen** zwischen beliebigen zwei Modulen anlegen, auch zwischen selbst erstellten Modulen.

## Die drei Formen einer Beziehung

Beim Erstellen einer Beziehung legen Sie fest, wie viele Datensätze auf der einen Seite mit wie vielen auf der anderen verbunden sein können:

- **Eins zu Eins**: Jeder Datensatz auf beiden Seiten kann mit höchstens einem Datensatz auf der anderen Seite verknüpft sein. Nützlich für eine strikte Paarung, wie eine Bestellung zu einem Versandetikett.
- **Eins zu Viele / Viele zu Eins**: Ein Datensatz auf einer Seite kann viele verknüpfte Datensätze auf der anderen haben, aber jeder dieser "vielen" Datensätze gehört immer nur zu einem davon. "Eine Firma hat viele Verkaufschancen" und "Viele Verkaufschancen gehören zu einer Firma" sind exakt dieselbe Beziehung, nur aus entgegengesetzter Sicht beschrieben, Sie können sie von jedem der beiden Module aus anlegen, welches auch immer für Sie sinnvoller ist, Cubrel ermittelt die Richtung automatisch.
- **Viele zu Viele**: Jede Seite kann mit beliebig vielen Datensätzen der anderen Seite verknüpft sein, ohne Begrenzung auf beiden Enden. "Kontakte zu Tickets" ist ein typisches Beispiel: Ein Kontakt kann an mehreren Tickets beteiligt sein, und ein Ticket kann mehrere Kontakte betreffen.

## Wie eine Beziehung angezeigt wird, hängt von ihrer Form ab

- Ist Ihr Modul auf der "Viele"-Seite (oder handelt es sich um eine Viele-zu-Viele-Beziehung), zeigt das Panel eine **Liste** aller verknüpften Datensätze, mit Anzahl und Seitenblättern bei vielen Einträgen, plus einer Schaltfläche zum Hinzufügen weiterer.
- Ist Ihr Modul auf der "Eins"-Seite einer Eins-zu-Viele-Beziehung, oder handelt es sich um eine Eins-zu-Eins-Beziehung, zeigt das Panel nur den **einzelnen** verknüpften Datensatz (falls vorhanden), mit einer schnellen Möglichkeit, ihn zu entfernen oder gegen einen anderen auszutauschen.

## Eine benutzerdefinierte Beziehung erstellen

Gehen Sie zu **Einstellungen > Module > [Modul auswählen] > Beziehungen > Neue Beziehung erstellen** und füllen Sie aus:

- **Name**: ein interner Bezeichner, für Endnutzer nicht sichtbar.
- **Bezeichnung**: der tatsächliche Titel des Panels auf der Datensatzseite.
- **Verknüpftes Modul**: das andere Modul, mit dem diese Beziehung verbindet.
- **Typ**: eine der vier oben beschriebenen Formen.

Es spielt keine Rolle, von welchem der beiden Module aus Sie sie erstellen, wählen Sie einfach das, in dem Sie sich gerade befinden. Wollen Sie "viele Verkaufschancen zu einer Firma" und befinden sich auf der Beziehungsseite von Verkaufschancen, wählen Sie "Viele zu Eins" und als verknüpftes Modul Firmen, Cubrel speichert es in beiden Fällen korrekt.

## Was sich nach dem Erstellen nicht mehr ändern lässt

Einmal erstellt, lassen sich die Form einer Beziehung und die beiden verbundenen Module nicht mehr nachträglich bearbeiten, es gibt keine "Bearbeiten"-Option, nur Erstellen und Löschen. Brauchen Sie etwas anderes (einen anderen Typ, oder ein anderes Modulpaar), löschen Sie die alte Beziehung und legen an ihrer Stelle eine neue an.

## Eine Beziehung löschen

- **Systembeziehungen** (die von Cubrel mitgelieferten) **lassen sich überhaupt nicht löschen**, die Löschen-Schaltfläche ist dafür deaktiviert.
- **Benutzerdefinierte Beziehungen**, die Sie selbst erstellt haben, lassen sich jederzeit löschen. Bestehen aktuell Verknüpfungen über diese Beziehung, sehen Sie vor der Bestätigung genau, wie viele es sind, das Löschen der Beziehung entfernt alle diese Verknüpfungen dauerhaft, zusammen mit der Beziehung selbst. Das lässt sich nicht rückgängig machen.

## Datensätze verknüpfen und entfernen

Öffnen Sie im Zugehörig-Tab eines Datensatzes das Panel einer Beziehung und:

- **Um einen Datensatz zu verknüpfen**, klicken Sie auf die Hinzufügen-/Verknüpfen-Schaltfläche, suchen den gewünschten Datensatz und wählen ihn aus.
- **Um einen Datensatz zu entfernen**, nutzen Sie die Entfernen-Aktion an diesem Datensatz (ein einzelner Klick bei einer "Eins"-Beziehung, oder eine Entfernen-Aktion neben jedem Eintrag in einer Liste bei einer "Viele"-Beziehung).

Verknüpfen Sie einen Datensatz bei einer Eins-zu-Viele-Beziehung mit einem neuen "Eins", zum Beispiel wenn Sie eine Verkaufschance von einer Firma zu einer anderen verschieben, wird die alte Verknüpfung automatisch ersetzt, ein Datensatz auf der "Viele"-Seite gehört immer nur zu einem Datensatz auf der "Eins"-Seite, ihn woanders neu zu verknüpfen ist einfach die Art, wie Sie ihn verschieben, kein Fehler.

Jedes Verknüpfen und Entfernen wird im Audit-Verlauf beider beteiligten Datensätze erfasst, siehe den [Leitfaden zum Audit-Verlauf](audit-trail-guide.md).

## Anpassen, was angezeigt wird und wonach die Suche sucht

Zwei getrennte Dinge lassen sich pro Modul konfigurieren, unter **Einstellungen > Module > [Modul] > Layouts**:

- **Welche Beziehungen als Panels erscheinen**, und wie sie im Zugehörig-Tab angeordnet sind, der Editor für das Layout der zugehörigen Panels lässt Sie diese hinzufügen, entfernen und neu anordnen.
- **Welche zusätzlichen Spalten** im Suchfenster erscheinen, das beim Verknüpfen eines neuen Datensatzes aufgeht, über den Namen hinaus können Sie ein paar relevante Felder (wie Status oder Besitzer) anzeigen, um die Auswahl zu erleichtern, konfiguriert über den Editor für das Verknüpfungs-Panel-Layout.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Kann ich eigene Beziehungen erstellen? | Ja, zwischen beliebigen zwei Modulen, von der Einstellungsseite eines der beiden Module aus |
| Kann ich eine Beziehung nach dem Erstellen bearbeiten? | Nein, löschen und neu anlegen ist der einzige Weg |
| Kann ich eine eingebaute (System-)Beziehung löschen? | Nein, niemals |
| Kann ich eine benutzerdefinierte Beziehung löschen? | Ja, jederzeit, Sie sehen vorher, wie viele Verknüpfungen betroffen sind |
| Was passiert mit Verknüpfungen, wenn ich eine Beziehung lösche? | Sie werden alle dauerhaft entfernt, zusammen mit der Beziehung |
| Was passiert, wenn ich einen "Viele"-Datensatz mit einem neuen "Eins" verknüpfe? | Er wird verschoben, die alte Verknüpfung wird automatisch ersetzt |
| Wird Verknüpfen/Entfernen protokolliert? | Ja, bei beiden Datensätzen, im Audit-Verlauf |
