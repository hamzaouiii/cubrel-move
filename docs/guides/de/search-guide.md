# Suchen in Cubrel

In diesem Artikel geht es darum, wie Sie die globale Suche öffnen, was sie durchsucht, wie Ergebnisse gruppiert werden, und ein paar Dinge, die sie bewusst noch nicht tut.

## Die Suche öffnen

Klicken Sie von überall in der Anwendung auf das Suchfeld in der Kopfleiste, oder drücken Sie **Strg+K** (**Cmd+K** auf einem Mac). Mit **Esc**, oder einem Klick daneben, schließen Sie sie wieder.

## Was durchsucht wird

Die Suche berücksichtigt nur Felder, die ein Administrator in den Feldeinstellungen eines Moduls explizit als **Durchsuchbar** markiert hat (**Einstellungen > Felder**), nicht jedes Feld eines Datensatzes ist standardmäßig dabei. Sie durchsucht ausschließlich die eigenen Felder eines Datensatzes, nicht Notizen, Anhänge, erzeugte PDFs oder Felder verknüpfter Datensätze.

Jedes aktive Modul wird durchsucht, Ergebnisse können also aus jedem Bereich stammen, auf den Sie Zugriff haben. **Benutzer** und **Einstellungen** sind von den Suchergebnissen ausgeschlossen, außer Sie sind Administrator.

::: warning
Die Suche filtert aktuell nicht nach Besitzer eines Datensatzes, wie es Listenansichten tun, ein Ergebnis kann also einen Datensatz zeigen, selbst wenn Sie ihn aus der Listenansicht des Moduls heraus nicht öffnen könnten. Betrachten Sie das als bekannte Lücke, nicht als Berechtigungsgrenze.
:::

## Eine Anfrage eingeben

Beginnen Sie zu tippen, ab 4 oder mehr Zeichen erscheinen die Ergebnisse automatisch, nach einer kurzen Pause, sobald Sie mit dem Tippen aufhören. Bei 1 bis 3 Zeichen drücken Sie **Enter**, um manuell zu suchen. Eine Suche braucht mindestens 2 Zeichen, alles Kürzere wird abgelehnt.

## Die Ergebnisse lesen

Ergebnisse sind nach Modul gruppiert, jede Gruppe beschriftet mit Name, Symbol und Farbe dieses Moduls, jedes Ergebnis zeigt die Bezeichnung des Datensatzes plus einen kurzen Ausschnitt aus einem beschreibungsähnlichen Feld. Ergebnisse werden über einen einfachen Textabgleich hinaus nicht nach Relevanz sortiert, und es gibt keine Obergrenze, wie viele Ergebnisse ein einzelnes Modul liefern kann.

Ein Klick auf ein Ergebnis führt direkt zu diesem Datensatz. Schließen oder Leeren des Suchfelds setzt es zurück, es gibt keine Liste zuletzt verwendeter Suchen, zu der Sie später zurückkehren könnten.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wie öffne ich die Suche? | Klick auf das Suchfeld in der Kopfleiste, oder **Strg+K** / **Cmd+K** |
| Welche Felder durchsucht sie? | Nur Felder, die pro Modul als **Durchsuchbar** markiert sind |
| Durchsucht sie Notizen, Anhänge oder PDFs? | Nein, nur die eigenen Felder des Datensatzes |
| Durchsucht sie verknüpfte Datensätze? | Nein, nur die eigenen Felder des Datensatzes |
| Sind Ergebnisse danach gefiltert, was ich sehen darf? | Auf Modulebene ja (Benutzer/Einstellungen für Nicht-Administratoren ausgeblendet), auf Datensatzebene nach Besitzer aktuell nicht |
| Wie viele Zeichen brauche ich? | Ab 4 Zeichen automatisch, bei 2–3 mit Enter, unter 2 wird abgelehnt |
| Gibt es einen Suchverlauf? | Aktuell nicht |
