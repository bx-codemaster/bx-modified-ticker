# BX Modified Ticker

Konfigurierbare, mehrsprachige Laufschrift (News-Ticker) für **modified eCommerce**. Zeigt aktuelle Meldungen (z. B. Versandhinweise, Aktionen, Störungsmeldungen) als endlos scrollenden Ticker-Balken im Frontend an – vollständig über die Admin-Oberfläche gepflegt, ohne Template-Anpassungen.

## Features

### Meldungsverwaltung
- Beliebig viele Ticker-Meldungen, sortierbar per Drag & Drop oder Pfeiltasten.
- Jede Meldung einzeln aktivierbar/deaktivierbar.
- Mehrsprachige Texte je Meldung – fehlt eine Übersetzung, greift automatisch die Shop-Standardsprache als Fallback.
- Optionaler Link je Meldung (nur relative Pfade oder `http(s)://` werden akzeptiert).
- Zeitgesteuerte Anzeige über "Von"/"Bis"-Datum (z. B. für befristete Aktionen).
- Frei editierbares Label links im Ticker (z. B. "Aktuell"/"Live"), ebenfalls mehrsprachig.

### Darstellung & Verhalten
- Geschwindigkeit, Abstand, Schriftgröße, Innenabstand (Padding) und Kantenausblendung frei einstellbar.
- Laufrichtung (links/rechts) und Position (oben im Textfluss oder unten fixiert am Bildschirmrand).
- Schriftart (Shop-Schrift, System-Sans, Serif, Monospace) und Schriftschnitt (normal/halbfett/fett).
- Farben für Hintergrund, Text, Akzent (Live-Punkt) und Trennlinien per Farbwähler.
- Pause bei Hover/Tastaturfokus umschaltbar.
- Respektiert automatisch `prefers-reduced-motion` (nicht abschaltbar) – Animation wird für betroffene Nutzer deaktiviert.
- Ticker-Dauer wird im Frontend automatisch anhand der tatsächlichen Textbreite berechnet, damit die Geschwindigkeit unabhängig von der Textmenge konstant bleibt.
- Ohne aktive/gültige Meldungen wird kein leerer Ticker-Balken ausgegeben.

### Ausgabetyp: Standard oder Smarty
- **Standard**: automatische Ausgabe des Ticker-Markups im Frontend (oben im HTML-Fluss oder unten fixiert).
- **Smarty**: keine automatische Ausgabe – stattdessen wird nur die globale Smarty-Template-Variable `{$bx_modified_ticker}` befüllt, die frei im eigenen Template platziert werden kann. Die Positions-Einstellung (oben/unten) ist in diesem Modus wirkungslos, da die Platzierung vollständig dem Template obliegt.

### Admin-Oberfläche
- Ein zusammenhängendes Konfigurations-Panel mit Live-Vorschau (Änderungen wirken sofort im Vorschau-Ticker).
- Sprachumschalter für alle gepflegten Texte (Label, Meldungen) direkt im Panel.
- JSON-Vorschau der Werte, wie sie gespeichert werden (Klappbox für Entwickler/Support).
- Speichern läuft asynchron per AJAX inkl. Erfolgs-/Fehlerrückmeldung.

### Sicherheit & Robustheit
- CSRF-Schutz über den globalen Shop-Mechanismus (`CSRFName`/`CSRFToken`).
- Serverseitige Validierung aller Eingaben: Zahlenwerte werden auf sinnvolle Bereiche begrenzt, Farben auf gültige Hex-Werte geprüft, Auswahlfelder (Richtung, Position, Typ, Schriftart, Schriftschnitt) gegen Whitelists validiert.
- Links werden auf relative Pfade oder `http(s)://` beschränkt (kein `javascript:`/`data:` etc.).
- Freitexte werden von HTML/Tags befreit und längenbegrenzt.
- Speichern erfolgt in einer Datenbank-Transaktion (Rollback bei Fehlern).
- Ausgabe im Frontend ist durchgehend HTML-escaped.

## Konfiguration (Hilfsprogramme → BX Modified Ticker)

| Einstellung | Werte | Beschreibung |
|---|---|---|
| Status | An/Aus | Ticker im Shop anzeigen |
| Geschwindigkeit | 20–240 px/s | Scrollgeschwindigkeit |
| Abstand | 1–8 rem | Abstand zwischen Meldungen |
| Schriftgröße | 12–24 px | Größe des Ticker-Texts |
| Höhe (Padding) | 0,4–1,6 rem | Vertikaler Innenabstand |
| Kantenbreite | 0–8 rem | Weiche Ausblendung an den Rändern |
| Pause bei Hover | An/Aus | Ticker pausiert bei Maus/Tastaturfokus |
| Richtung | links/rechts | Laufrichtung |
| Position | oben/unten | Einbindung im Textfluss oder fixiert am unteren Bildschirmrand (nur bei Typ „Standard“) |
| Typ | standard/smarty | Automatische Ausgabe oder Bereitstellung als Smarty-Variable `{$bx_modified_ticker}` |
| Schriftart | inherit/system/serif/mono | Feste, sichere Font-Stacks |
| Schriftschnitt | 400/600/700 | Normal, halbfett, fett |
| Farben | Hex | Hintergrund, Text, Akzent, Linie |

## Datenbank

Das Modul legt bei der Installation folgende Tabellen an:

- `bx_ticker_items` – Meldungen (Sortierung, Status, Link, Gültigkeitszeitraum)
- `bx_ticker_items_description` – Mehrsprachige Texte je Meldung
- `bx_ticker_label` – Mehrsprachiges Ticker-Label

sowie die Konfigurationswerte in einer eigenen Gruppe unter *Konfiguration*.

## Voraussetzungen

- PHP ^7.4 oder ^8.0
- modified eCommerce 3.1.0 – 3.3.0
