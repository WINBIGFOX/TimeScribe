[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/kc7qv2k6jqr)

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
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubar.png?raw=true">
  <img style="border-radius: 10px; max-width: 380px" alt="Menu Bar" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/menubarLight.png?raw=true">
</picture>
<br/>
<br/>
</p>

### Weekly Overview

<p align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/overview_en_dark.webp?raw=true">
  <img alt="Weekly Overview" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/overview_en_light.webp?raw=true">
</picture>
</p>

### Detail View

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/dayview_en_dark.webp?raw=true">
  <img alt="Detail View" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/dayview_en_light.webp?raw=true">
</picture>
</p>

### Absence Calendar

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absences_en_dark.webp?raw=true">
  <img alt="Absence Calendar" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/absences_en_light.webp?raw=true">
</picture>
</p>

### Settings

<p align="center">
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings1_en_dark.webp?raw=true">
  <img width="32%" alt="Settings" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings1_en_light.webp?raw=true">
</picture>
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings2_en_dark.webp?raw=true">
  <img width="32%" alt="Settings" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings2_en_light.webp?raw=true">
</picture>
<picture >
  <source media="(prefers-color-scheme: dark)" srcset="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings3_en_dark.webp?raw=true">
  <img width="32%" alt="Settings" src="https://github.com/WINBIGFOX/Stempeluhr/blob/main/.github/images/settings3_en_light.webp?raw=true">
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
