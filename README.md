[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/kc7qv2k6jqr)

<a href="https://timescribe.app" target="_blank"><img src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/icon.png?raw=true" width="150" alt="TimeScribe Logo"></a>
# TimeScribe

## Description

This repository offers a modern and flexible time clock app for accurately tracking and managing personal working hours. It's perfect for anyone who wants to keep a precise record of their work time, whether they're working from home or in an office.

## Features

TimeScribe lets you effortlessly start, stop, and pause your work time with just a click, ensuring a straightforward time tracking experience. The app provides an intuitive dashboard to monitor your daily activities, including breaks, and allows you to customize your weekly work schedule to suit your needs. It even accounts for regional public holidays, ensuring your time management is as precise as possible. With built-in automation features like automatic start/stop functions and notifications for breaks and work sessions, TimeScribe streamlines your time management. Upcoming features include detailed analytics and reports, export options for your tracking data, and integration with popular calendar apps. Prioritizing user privacy, your data remains secure and private, all while delivering lightweight, high-performance functionality tailored for macOS.

Ideal for anyone looking to track their work hours efficiently and effortlessly!

## Screenshots

### Menu Bar

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubar_dark.png?raw=true">
  <img style="border-radius: 10px; max-width: 380px" alt="Menu Bar" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubar_light.png?raw=true">
</picture>
<br/>
<br/>
</p>

### Time Tracking

<p align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/dayview_en_dark.webp?raw=true">
  <img alt="Time Tracking" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/dayview_en_light.webp?raw=true">
</picture>
</p>

### App Activity

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/app_activity_en_dark.webp?raw=true">
  <img alt="App Activity" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/app_activity_en_light.webp?raw=true">
</picture>
</p>

### Absence Planning

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absences_en_dark.webp?raw=true">
  <img alt="Absence Planning" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absences_en_light.webp?raw=true">
</picture>
</p>

### Automatic Start/Pause

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/start_break_en_dark.webp?raw=true">
  <img alt="Automatic Start/Pause" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/start_break_en_light.webp?raw=true">
</picture>
</p>

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/WINBIGFOX/timescribe.git
    ```
2. Navigate to the project directory:
    ```bash
    cd timescribe
    ```
3. Install dependencies:
    ```bash
    composer install
    npm install
    ```
4. Build the macOS application:
    ```bash
    npm run build
    php artisan native:build mac
    ```
