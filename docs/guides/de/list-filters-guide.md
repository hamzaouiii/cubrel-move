# Listenfilter in Cubrel verstehen

Jede Listenansicht in Cubrel, Verkaufschancen, Kontakte, Rechnungen, jedes Modul, lässt sich mit einem Filter eingrenzen. Dieser Artikel beschreibt, wie Sie einen aufbauen, den Unterschied zwischen einem privaten Filter und einem mit Ihrem ganzen Team geteilten, die von Cubrel mitgelieferten Filter, und wer einen gespeicherten Filter bearbeiten oder löschen darf.

## Den Filter-Editor öffnen

Klicken Sie oben in einer beliebigen Liste auf **Filter hinzufügen** (oder das Filter-Dropdown, falls bereits einer angewendet ist), um den **Filter-Editor** zu öffnen. Von dort aus wählen Sie entweder einen bereits gespeicherten Filter aus, oder bauen einen neuen von Grund auf.

## Bedingungen aufbauen

Ein Filter besteht aus einer oder mehreren Bedingungen, jede aus einem **Feld**, einem **Operator** und einem **Wert**. Klicken Sie auf **Bedingung hinzufügen**, um eine Zeile hinzuzufügen, und legen Sie fest, ob der Filter **ALLE** Bedingungen erfüllen muss oder **EINE BELIEBIGE** davon.

Welche Operatoren verfügbar sind, hängt vom Typ des Feldes ab. Textfelder bieten Dinge wie *enthält* und *beginnt mit*. Zahlen und Daten bieten *größer als*, *kleiner als*, *vor*, *nach* und *zwischen* (das nach einem Von- und einem Bis-Wert fragt). Auswahl- und Mehrfachauswahlfelder bieten *ist eine von*. Einen Operator, der für das Feld keinen Sinn ergibt, können Sie nicht wählen, die ungültigen werden einfach nicht angezeigt.

Es ist derselbe Bedingungs-Editor, der auch bei automatischen Umwandlungsregeln zum Einsatz kommt. Siehe den [Leitfaden zu Umwandlungsregeln](conversion-guide.md), falls Sie ihn dort bereits verwendet haben.

## Einen Filter speichern

Vergeben Sie einen Namen und klicken Sie auf **Filter speichern**. Standardmäßig ist ein von Ihnen gespeicherter Filter privat, nur Sie können ihn sehen und anwenden. Schalten Sie vor dem Speichern **Mit allen teilen** ein, um ihn stattdessen dem ganzen Team zur Verfügung zu stellen. Jeder kann ihn dann anwenden, bearbeiten oder löschen können ihn aber weiterhin nur Sie (oder ein Administrator).

Das Anwenden eines Filters grenzt die Liste sofort ein. Ein Klick auf **Filter löschen** entfernt ihn wieder und zeigt jeden Datensatz.

## Bearbeiten und löschen

Öffnen Sie einen Filter aus dem Dropdown und nutzen Sie **Filter bearbeiten**, um seinen Namen, die Freigabe oder die Bedingungen zu ändern, oder **Filter löschen**, um ihn endgültig zu entfernen. Cubrel bittet Sie vorher um Bestätigung, da sich ein gelöschter Filter nicht wiederherstellen lässt. Sie können jeden von Ihnen selbst erstellten Filter verwalten, Administratoren können jeden verwalten. Einen von jemand anderem erstellten und geteilten Filter können Sie anwenden, aber nicht ändern oder löschen.

## Mitgelieferte Filter

Manche Module kommen bereits mit einer Handvoll fertiger Filter, **Meine Datensätze** bei den meisten Modulen, dazu modulspezifische wie **Offene Bestellungen**, **Unbezahlte Rechnungen**, **Gewonnene Verkaufschancen** oder **Aktive Produkte**. Diese sind als Systemfilter markiert, jeder Benutzer kann sie sehen und anwenden, aber niemand, auch kein Administrator, kann sie bearbeiten oder löschen. **Meine Datensätze** ist getrennt von den übrigen angeheftet, sodass Sie damit immer am schnellsten zu dem zurückkommen, was Ihnen gehört.

## Einen Filter außerhalb der Listenansicht nutzen

Ein gespeicherter Filter ist nicht nur für die Listenansicht da. Nutzen Sie die [REST-API](rest-api-guide.md), nimmt der Parameter `filter` an einem Listen-Endpunkt den Slug oder die ID eines bereits in der App gespeicherten Filters entgegen, keinen rohen Filterausdruck. Alles, was Sie hier aufgebaut und gespeichert haben, lässt sich so auch außerhalb von Cubrel wiederverwenden.

## Kurz gefasst

| Frage | Antwort |
| --- | --- |
| Wo baue ich einen Filter auf? | Klick auf **Filter hinzufügen** in einer beliebigen Liste öffnet den Filter-Editor |
| Kann ich nach jedem Feld filtern? | Nur nach Feldern, die der Bedingungs-Editor für dieses Modul erlaubt, mit zum Feldtyp passenden Operatoren |
| Was unterscheidet einen privaten von einem geteilten Filter? | Privat ist nur für Sie sichtbar, geteilt ist für alle sichtbar und anwendbar, bearbeiten kann ihn aber weiterhin nur der Besitzer oder ein Administrator |
| Kann ich den geteilten Filter von jemand anderem bearbeiten? | Nein, nur der Besitzer oder ein Administrator |
| Was sind die mitgelieferten Filter wie "Meine Datensätze"? | Von Cubrel mitgelieferte Systemfilter, nutzbar von jedem, bearbeitbar von niemandem |
| Kann ich einen gespeicherten Filter über die REST-API nutzen? | Ja, übergeben Sie seinen Slug oder seine ID als Parameter `filter` |
