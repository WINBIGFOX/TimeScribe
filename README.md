# Stempeluhr

## Beschreibung

Dieses Repository bietet eine moderne und flexible Stempeluhr-App zur präzisen Erfassung und Verwaltung persönlicher
Arbeitszeiten. Perfekt für jeden, der seine Arbeitsstunden genau im Blick behalten möchte – ob im Homeoffice oder im
Büro.

## Funktionen

- **Einfache Zeiterfassung**: Starte, stoppe und pausiere deine Arbeitszeit mit einem Klick.
- **Übersichtliches Dashboard**: Behalte den Überblick über deine täglichen Arbeitsaktivitäten, inklusive Pausen.
- **Anpassbare Arbeitspläne**: Passe deinen Wochenarbeitsplan individuell an.
- **Feiertags-Integration**: Berücksichtigt regionale Feiertage für eine präzise Zeitplanung.
- **Automatisierung**: Automatische Start-/Stopp-Funktionen für reibungsloses Timemanagement.
- **Benachrichtigungen**: Erhalte Erinnerungen für Pausen und Arbeitszeiterfassungen.
- **Auswertungen & Berichte (coming soon)**: Analysiere deine Arbeitsgewohnheiten mit grafischen Berichten.
- **Export-Optionen (coming soon)**: Exportiere deine Zeiterfassungsdaten für die Weiterverwendung.
- **Integration mit Kalendern (coming soon)**: Synchronisiere deine Arbeitszeiten mit gängigen Kalender-Apps.
- **Datenschutz**: Deine Daten bleiben stets privat und sicher.
- **Leichtgewichtig & Schnell**: Entwickelt für optimale Performance auf deinem Mac.

Ideal für alle, die ihre Arbeitszeit effizient und unkompliziert tracken möchten!

## Screenshots

### Menübar

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubar.png?raw=true">
  <img style="border-radius: 10px; max-width: 380px" alt="Menübar" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubarLight.png?raw=true">
</picture>
<br/>
<br/>
</p>

### Wochenansicht

<p align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/overview.png?raw=true">
  <img alt="Wochenansicht" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/overviewLight.png?raw=true">
</picture>
</p>

### Detailansicht

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/day.png?raw=true">
  <img alt="Detailansicht" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/dayLight.png?raw=true">
</picture>
</p>

### Abwesenheitskalender

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absence.png?raw=true">
  <img alt="Abwesenheitskalender" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absenceLight.png?raw=true">
</picture>
</p>

### Einstellungen

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings1.png?raw=true">
  <img width="32%" alt="Einstellungen" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings1Light.png?raw=true">
</picture>
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings2.png?raw=true">
  <img width="32%" alt="Einstellungen" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings2Light.png?raw=true">
</picture>
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings3.png?raw=true">
  <img width="32%" alt="Einstellungen" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings3Light.png?raw=true">
</picture>
</p>

## Installation

1. Repository klonen:
   ```bash
   git clone https://github.com/WINBIGFOX/Stempeluhr.git
    ```
2. In das Projektverzeichnis wechseln:
   ```bash
   cd Stempeluhr
    ```
3. Abhängigkeiten installieren:
   ```bash
   composer install
   npm install
    ```
4. Mac Anwendung bauen:
   ```bash
   npm run build
   php artisan native:build mac
    ```
