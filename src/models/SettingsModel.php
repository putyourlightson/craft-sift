<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift\models;

use craft\base\Model;

class SettingsModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string[]|array
     */
    public $categoryFieldHandles = [];

    /**
     * @var string
     */
    public $entryRelatedToKey = 'targetElement';

    /**
     * @var string
     */
    public $categoryRelatedToKey = 'sourceElement';
}
