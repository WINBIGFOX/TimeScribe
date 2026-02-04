<a href="https://timescribe.app?utm_source=github&utm_medium=banner" target="_blank">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/banner_dark.png?raw=true">
    <img alt="Banner TimeScribe" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/banner_light.png?raw=true">
  </picture>
</a>

<p align="center">
  <b>Smart & Private Time Tracking for macOS & Windows</b>
</p>
<p align="center">
  <b>Track, analyze and own your work hours</b><br/>
  No cloud. No registration. No paywall. Just productivity.
</p>

<p align="center">
  <a href="https://github.com/WINBIGFOX/timescribe/releases/latest">
    <img src="https://img.shields.io/github/v/release/WINBIGFOX/timescribe?label=Download&logo=github" />
  </a>
  <a href="https://formulae.brew.sh/cask/timescribe">
    <img src="https://img.shields.io/homebrew/cask/v/timescribe?logo=homebrew&logoColor=white&label=Homebrew" />
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

## 🚀 About

**TimeScribe is the easiest way to track your work hours — without the hassle.**

It is designed for everyone who needs to keep track of their time but doesn't want to pay for a subscription, create an
account, or upload their private data to the cloud. Whether you are a freelancer billing clients, a remote worker
logging hours, or just want to improve your personal productivity, TimeScribe gives you full control.

**Why use TimeScribe?**

- Completely Free: No hidden costs, no premium plans, no paywalls.
- No Registration: No email required. Just download, open, and start tracking.
- 100% Offline & Private: Your data stays on your device. We don't track you.

Simply put: It's a professional time tracking tool that respects your privacy and your wallet.

---

## ✨ Key Features

- ✅ Start, pause, and stop tracking with one click
- 📊 Visualize your day and weekly work patterns
- ⏱ See app usage and categorize work vs distractions
- 🗓️ Plan absences like vacation, sick leave, and holidays
- 📋 Track time on projects with descriptions, hourly rates, and billing calculations
- ⚙️ Auto start/pause based on screen time and idle status
- 💾 Export as CSV and Excel: Easily export your time tracking data for further analysis or reporting.
- 🪟 Supports macOS & Windows
- ⌨️ Custom keyboard shortcuts
- 🔒 100% Local: No cloud, no registration, no paywall
- 🔄 Auto Updates: Always up-to-date

---

## 🌍 Supported Languages

- 🇬🇧 English (UK/US)
- 🇫🇷 French (FR/CA)
- 🇩🇪 German
- 🇮🇹 Italian
- 🇧🇷 Portuguese (BR)
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

### Option 2: Install via Homebrew (macOS)

If you're on macOS and have [Homebrew](https://brew.sh/) installed, you can install TimeScribe with:

```bash
brew install timescribe
```

After installation, you can launch TimeScribe via Spotlight or from your Applications folder.

---

### Option 3: Build from Source (Developers)

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
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/menubar_dark.png?raw=true">
        <img alt="Menu Bar" width="550" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/menubar_light.png?raw=true">
    </picture>
</p>

### 🧭 Time Tracking

<p align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/dayview_en_dark.webp?raw=true">
  <img alt="Time Tracking" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/dayview_en_light.webp?raw=true">
</picture>
</p>

### 🧠 App Activity

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/app_activity_en_dark.webp?raw=true">
  <img alt="App Activity" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/app_activity_en_light.webp?raw=true">
</picture>
</p>

### 📋 Project Tracking

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/projects_en_dark.webp?raw=true">
  <img alt="Project Tracking" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/projects_en_light.webp?raw=true">
</picture>
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/project_detail_en_dark.webp?raw=true">
  <img alt="Project Tracking" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/project_detail_en_light.webp?raw=true">
</picture>
</p>

### 🗓️ Absence Planning

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/absences_en_dark.webp?raw=true">
  <img alt="Absence Planning" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/absences_en_light.webp?raw=true">
</picture>
</p>

### ⚙️ Automatic Start/Pause

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/start_break_en_dark.webp?raw=true">
  <img alt="Automatic Start/Pause" src="https://github.com/WINBIGFOX/TimeScribe/blob/main/.github/images/start_break_en_light.webp?raw=true">
</picture>
</p>

---

## 🛠️ Tech Stack

TimeScribe is built with a modern stack, leveraging the best of web and desktop technologies:

- Core: [Laravel](https://laravel.com/) (PHP)
- Desktop Runtime: [NativePHP](https://nativephp.com/) (Electron)
- Frontend: [Vue.js](https://vuejs.org/) + [Tailwind CSS](https://tailwindcss.com/)
- Database: SQLite (Local)

---

## 👥 Community & Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are greatly appreciated.

- 👉 [GitHub Discussions](https://github.com/WINBIGFOX/TimeScribe/discussions)
- 🐞 [GitHub Issues](https://github.com/WINBIGFOX/TimeScribe/issues)
- 🛠️ [Contributing Guide](CONTRIBUTING.md)
- ⛳️ [TimeScribe Feature-Roadmap](https://github.com/users/WINBIGFOX/projects/5)

---

## 💖 Sponsor & License

If TimeScribe helps you save time or money, please consider supporting the development.

<a href="https://github.com/sponsors/WINBIGFOX" target="_blank">
<img src="https://img.shields.io/badge/GitHub Sponsors-EA4AAA?style=for-the-badge&logo=githubsponsors&logoColor=white" />
</a>
<a href="https://www.buymeacoffee.com/kc7qv2k6jqr" target="_blank">
<img height="28px" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" />
</a>

## 📄 License

Distributed under the GPL-3.0 License. See [LICENSE](LICENSE) for more information.
