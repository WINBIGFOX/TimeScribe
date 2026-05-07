---
name: laravel-app-translations
description: "Use this skill for translation handling in Laravel apps. Trigger whenever UI text is being translated, added to language files, refactored into translations, or reviewed for localization. Translations are stored in Laravel's /lang/{locale}/app.php files. Translation keys must always be the lowercase English version of the source text and must remain identical across all languages. Covers: app.php translation files, __() and trans() usage, lowercase English text keys, placeholders, German informal tone, consistent locale files, and avoiding artificial keys like settings.title or user_created_successfully. Do not use for non-Laravel projects, JSON translation files, database-backed translations, or unrelated PHP code."
license: MIT
metadata:
  author: WINBIGFOX
---

# Laravel Translation Skill

This skill defines how translations must be handled in Laravel applications.

## Goal

Translations must be stored in the standard Laravel language files:

```text
/lang/{locale}/app.php
```

Examples:

```text
/lang/en/app.php
/lang/de/app.php
/lang/fr/app.php
```

All application UI strings should use the `app.php` translation file unless there is a strong project-specific reason to
use another file.

## Translation Key Rule

The translation key must **always** be the English lowercase version of the source text.

The key should be:

- written in English
- fully lowercase
- identical across all languages
- based on the full source sentence or phrase
- stored as a plain string key in `app.php`

Do **not** use artificial keys such as:

```php
'new_version_available' => '...'
'app.update.message' => '...'
'version.new_available' => '...'
```

Instead, use the English lowercase text itself as the key.

## Example

### `/lang/en/app.php`

```php
<?php

return [
    'a new version of the app is available. please install the latest version to enjoy new features and improvements.' => 'A new version of the app is available. Please install the latest version to enjoy new features and improvements.',
];
```

### `/lang/de/app.php`

```php
<?php

return [
    'a new version of the app is available. please install the latest version to enjoy new features and improvements.' => 'Eine neue Version der App ist verfügbar. Bitte installiere die neueste Version, um neue Funktionen und Verbesserungen zu nutzen.',
];
```

## Usage in Laravel

Translations should be referenced using Laravel’s translation helper:

```php
__('app.a new version of the app is available. please install the latest version to enjoy new features and improvements.')
```

or with `trans()`:

```php
trans('app.a new version of the app is available. please install the latest version to enjoy new features and improvements.')
```

## Adding a New Translation

When adding a new translatable string:

1. Take the original English text.
2. Convert it to lowercase.
3. Use that lowercase text as the translation key.
4. Add the key to `/lang/en/app.php`.
5. Add the same key to all other supported locale files.
6. Translate only the value, never the key.

Example source text:

```text
Project was created successfully.
```

Translation key:

```text
project was created successfully.
```

### `/lang/en/app.php`

```php
<?php

return [
    'project was created successfully.' => 'Project was created successfully.',
];
```

### `/lang/de/app.php`

```php
<?php

return [
    'project was created successfully.' => 'Das Projekt wurde erfolgreich erstellt.',
];
```

## Placeholders

Laravel placeholders may be used as usual.

The placeholder names must stay unchanged across all languages.

### `/lang/en/app.php`

```php
<?php

return [
    'hello :name, your project was updated.' => 'Hello :name, your project was updated.',
];
```

### `/lang/de/app.php`

```php
<?php

return [
    'hello :name, your project was updated.' => 'Hallo :name, dein Projekt wurde aktualisiert.',
];
```

Usage:

```php
__('app.hello :name, your project was updated.', ['name' => $user->name])
```

## Tone and Style

Translations should be natural and user-friendly.

For German translations:

- use informal address unless the project explicitly requires formal language
- prefer clear, simple wording
- avoid overly technical terms where a simpler phrase works
- keep the meaning close to the English source
- do not translate placeholders
- keep punctuation natural for the target language

Example:

```php
'delete project' => 'Projekt löschen',
```

not:

```php
'delete project' => 'Lösche Projekt',
```

unless the UI context specifically requires an imperative sentence.

## Consistency Rules

When updating translations:

- never change an existing key unless the English source meaning changed intentionally
- keep the same key in every locale file
- avoid duplicate keys with slightly different wording
- check whether a similar translation already exists before adding a new one
- preserve placeholders exactly
- preserve HTML, Markdown, or formatting tokens if present

## Formatting

Translation files must use valid PHP array syntax:

```php
<?php

return [
    'english lowercase key' => 'Translated value',
];
```

Use single quotes for keys and values unless escaping would make the string harder to read.

Example with apostrophe:

```php
<?php

return [
    'don\'t show this again' => 'Don\'t show this again',
];
```

If a translation contains many apostrophes, double quotes may be used for readability.

## Do Not

Do not create translation keys like this:

```php
'button.save' => 'Save',
'settings.title' => 'Settings',
'user_created_successfully' => 'User created successfully.',
```

Do not translate the key itself:

```php
// Wrong in /lang/de/app.php
'eine neue version der app ist verfügbar.' => 'Eine neue Version der App ist verfügbar.',
```

Do not use different keys for different languages.

The key must remain English and lowercase in every locale.

## Correct Pattern

```php
// /lang/en/app.php
return [
    'save' => 'Save',
    'settings' => 'Settings',
    'user created successfully.' => 'User created successfully.',
];

// /lang/de/app.php
return [
    'save' => 'Speichern',
    'settings' => 'Einstellungen',
    'user created successfully.' => 'Benutzer wurde erfolgreich erstellt.',
];
```
