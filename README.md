<p align="center">
  <a href="https://timescribe.app" target="_blank">
    <img src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/icon.png?raw=true" width="120" alt="TimeScribe Logo">
  </a>
</p>

<h1 align="center">TimeScribe</h1>
<p align="center">
  <b>Smart & private time tracking for macOS & Windows</b><br/>
Track, analyze and own your work hours—no cloud, no registration, no paywall.
</p>

<p align="center">
  <a href="https://github.com/WINBIGFOX/timescribe/releases/latest">
    <img src="https://img.shields.io/github/v/release/WINBIGFOX/timescribe?label=Download&logo=github" />
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/github/license/WINBIGFOX/timescribe?color=blue" />
  </a>
</p>

<h3 align="center">Download Now</h2>
<p align="center">
  <a href="https://timescribe.app/download/windows">
    <img src="https://img.shields.io/badge/Windows-0078D4?style=for-the-badge&logo=microsoft&logoColor=white" />
  </a>
  <a href="https://timescribe.app/download/macos/arm64">
    <img src="https://img.shields.io/badge/Apple%20Silicon-000000?style=for-the-badge&logo=apple&logoColor=white" />
  </a>
  <a href="https://timescribe.app/download/macos/x64">
    <img src="https://img.shields.io/badge/Intel-000000?style=for-the-badge&logo=apple&logoColor=white" />
  </a>
</p>

---

## 🕒 Description

**TimeScribe** is a modern, flexible time clock app for accurately tracking and managing working hours. Whether you're
remote, hybrid, or office-based, TimeScribe helps you stay focused and organized with minimal effort.

---

## ✨ Features

- ✅ Start, pause, and stop tracking with one click
- 📊 Visualize your day and weekly work patterns
- ⏱ See app usage and categorize work vs distractions
- 🗓️ Plan absences like vacation, sick leave, and holidays
- ⚙️ Auto start/pause based on screen time and idle status
- 🪟 **Supports macOS & Windows**
- 🔒 **100% Local**: No cloud, no registration, no paywall

---

## 💬 Supported Languages

- 🇬🇧 English (UK/US)
- 🇩🇪 German
- 🇨🇳 Chinese (中文)

---

## 📦 Download & Installation

### Option 1: Download the App

Head to the [latest release](https://github.com/WINBIGFOX/timescribe/releases/latest) and download:

- 🖥 **Windows**:
  `TimeScribe-setup.exe` [👉🏻 Direct download link Windows](https://timescribe.app/download/windows)
- 🍏 **macOS**:
  `TimeScribe.dmg` [👉🏻 Direct download link macOS (Apple Silicon)](https://timescribe.app/download/macos/arm64) | [(Intel)](https://timescribe.app/download/macos/x64)

Then:

- **Windows**: Run the `.exe` and follow the setup instructions.
- **macOS**: Open the `.dmg`, then drag TimeScribe to your Applications folder.

---

### Option 2: Build from Source (Developers)

```bash
# Clone the repo
git clone https://github.com/WINBIGFOX/timescribe.git
cd timescribe

# Install dependencies
composer install
npm install

# Copy the example environment file
cp .env.example .env

# Generate an application key
php artisan key:generate

# Build for macOS
npm run build
php artisan native:build mac

# Build for Windows (coming soon or adjust accordingly)
php artisan native:build win
```

## 🖼 Screenshots

### 🧭 Menu Bar

<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubar_dark.png?raw=true">
        <img alt="Menu Bar" width="550" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubar_light.png?raw=true">
    </picture>
</p>

### 🧭 Time Tracking

<p align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/dayview_en_dark.webp?raw=true">
  <img alt="Time Tracking" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/dayview_en_light.webp?raw=true">
</picture>
</p>

### 🧠 App Activity

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/app_activity_en_dark.webp?raw=true">
  <img alt="App Activity" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/app_activity_en_light.webp?raw=true">
</picture>
</p>

### 🗓️ Absence Planning

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absences_en_dark.webp?raw=true">
  <img alt="Absence Planning" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absences_en_light.webp?raw=true">
</picture>
</p>

### ⚙️ Automatic Start/Pause

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/start_break_en_dark.webp?raw=true">
  <img alt="Automatic Start/Pause" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/start_break_en_light.webp?raw=true">
</picture>
</p>

---

## 💖 Sponsor & License

<b>Love TimeScribe?</b><br/>
<a href="https://github.com/sponsors/WINBIGFOX" target="_blank">
<img src="https://img.shields.io/badge/GitHub Sponsors-EA4AAA?style=for-the-badge&logo=githubsponsors&logoColor=white" />
</a>
<a href="https://www.buymeacoffee.com/kc7qv2k6jqr" target="_blank">
<img height="28px" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" />
</a>

This project is licensed under the [GPL-3.0 License](LICENSE).




