# Contributing to TimeScribe

Thank you for considering contributing to TimeScribe! This document provides guidelines and instructions to help you contribute effectively to the project.

## Table of Contents

- [Getting Started](#getting-started)
    - [Development Environment Setup](#development-environment-setup)
    - [Running the Application](#running-the-application)
- [Development Workflow](#development-workflow)
    - [Branching Strategy](#branching-strategy)
    - [Commit Messages](#commit-messages)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
    - [PHP Code Style](#php-code-style)
    - [TypeScript/Vue Code Style](#typescriptvue-code-style)
- [Testing](#testing)
- [Translations](#translations)
- [Reporting Bugs](#reporting-bugs)
- [Feature Requests](#feature-requests)
- [Communication](#communication)

## Getting Started

### Development Environment Setup

1. **Prerequisites**:

    - PHP 8.4 or higher
    - Composer
    - Node.js (LTS version recommended)
    - npm
    - Git

2. **Clone the repository**:

    ```bash
    git clone https://github.com/WINBIGFOX/timescribe.git
    cd timescribe
    ```

3. **Install dependencies**:

    ```bash
    composer install
    npm install
    ```

4. **Environment setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

### Running the Application

For local development, you can use the following commands:

- **Web development mode**:

    ```bash
    composer dev
    ```

    This runs the Laravel server, queue worker, log viewer, and Vite development server concurrently.

- **Native app development mode**:
    ```bash
    composer native:dev
    ```
    This runs the NativePHP/Electron app with hot reloading.

## Development Workflow

### Branching Strategy

- `main` - Production-ready code
- `develop` - Development branch for integrating features
- Feature branches - Create from `develop` with the format: `feature/your-feature-name`
- Bug fix branches - Create from `develop` with the format: `fix/bug-description`

### Commit Messages

Write clear, concise commit messages that explain the changes made. Follow this format:

```
type: Short description (50 chars or less)

More detailed explanation if necessary
```

Types:

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, missing semicolons, etc.)
- `refactor`: Code changes that neither fix bugs nor add features
- `test`: Adding or modifying tests
- `chore`: Changes to the build process or auxiliary tools

## Pull Request Process

1. Create a branch from `main` for your changes
2. Make your changes and commit them with descriptive messages
3. Push your branch to your fork
4. Submit a pull request to the `main` branch
5. Ensure all tests pass and code style checks are successful
6. Update documentation if necessary
7. Request a review from maintainers

## Coding Standards

### PHP Code Style and Rector

We follow the Laravel coding standards. Use Laravel Pint for code formatting and Rector for code refactoring.

```bash
./vendor/bin/rector
./vendor/bin/pint
```

### TypeScript/Vue Code Style

We use ESLint and Prettier for TypeScript and Vue files:

```bash
# Check code style
npm run style
npm run lint

# Fix code style issues
npm run style:fix
npm run lint:fix

# Type checking
npm run typecheck
```

## Translations

TimeScribe supports multiple languages. If you're adding new text strings:

1. Add them to the appropriate language files in the `lang` directory
2. Ensure they're properly translated in all supported languages (English, German, Chinese)

## Reporting Bugs

When reporting bugs, please include:

1. A clear, descriptive title
2. Steps to reproduce the issue
3. Expected behavior
4. Actual behavior
5. Screenshots if applicable
6. Your environment (OS, app version, etc.)

Use the GitHub issue tracker to report bugs.

## Feature Requests

Feature requests are welcome. Please provide:

1. A clear description of the feature
2. The problem it solves
3. How it benefits users
4. Any implementation ideas you have

## Communication

- **GitHub Issues**: For bug reports and feature requests
- **Pull Requests**: For code contributions
- **GitHub Discussions**: For general questions and discussions

Thank you for contributing to TimeScribe!
