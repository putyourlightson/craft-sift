# Sift Plugin for Craft CMS 3

A plugin for Craft CMS 3 that filters entries/categories/submissions that can be selected to only those that match a user's categories.

## Requirements

Craft CMS 3.0.0 or later.

## Installation

Install the plugin using composer.

```
composer require putyourlightson/craft-sift
```

## Configuration

Copy the `src/config.php` config file to `craft/config` as `sift.php`, adding the entry field handles and the associated user field handles.
```php
return [
    '*' => [
        /**
         * The field handles to sift by for entry queries
         */
        'entryFieldHandles' => [
            'entryFieldHandleA' => 'userFieldHandleA',
            'entryFieldHandleB' => 'userFieldHandleB',
        ],
    ],
];
```

## Fieldtype

The plugin adds a _Read-only Categories_ fieldtype that is visible to all users but that only admins can edit.

## License

This plugin is licensed for free under the MIT License.

<small>Created by [PutYourLightsOn](https://putyourlightson.com/).</small>
