# Sift Plugin

A plugin for Craft CMS 3 that filters entries/categories that can be selected to only those that match a user's categories.

Install the plugin by first copying the plugin directory into a directory called `plugins` (in the Craft project path) and then add the following code to the main `composer.json` file:

    "repositories": [
        {
            "type": "path",
            "url": "./plugins/*"
        }
    ]

Then run the following composer command:

    composer require putyourlightson/craft-sift


