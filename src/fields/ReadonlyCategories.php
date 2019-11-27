<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift\fields;

use Craft;
use craft\base\ElementInterface;
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

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        if (!Craft::$app->getUser()->getIsAdmin()) {
            return $this->getStaticHtml($value, $element);
        }

        return parent::getInputHtml($value, $element);
    }
}
