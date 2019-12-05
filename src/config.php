<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

/**
 * Sift Plugin config.php
 *
 * This file exists only as a template for the Sift plugin settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'sift.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    '*' => [
        /**
         * The category field handles to filter entries/categories by
         */
        'categoryFieldHandles' => ['categoriesA', 'categoriesB'],

        /**
         * The `relatedTo` key to use for entry queries
         */
        'entryRelatedToKey' => 'targetElement',

        /**
         * The `relatedTo` key to use for category queries
         */
        'categoryRelatedToKey' => 'sourceElement',
    ],
];
