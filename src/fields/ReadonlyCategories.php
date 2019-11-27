<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift\fields;

use craft\fields\Categories;

class ReadonlyCategories extends Categories
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Read-only Categories';
    }
}
