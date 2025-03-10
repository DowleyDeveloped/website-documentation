<?php
namespace fortytwostudio\websitedocumentation\services;

use yii\base\Component;

use fortytwostudio\websitedocumentation\WebsiteDocumentation;

use Craft;
use craft\elements\Entry;
use craft\helpers\StringHelper;
use craft\models\Structure;
use craft\web\View;

// Illuminate
use Illuminate\Support\Collection;

use Exception;

class GuideService extends Component
{
	public function getGuideType() {

		$entryType = Collection::make(
			Craft::$app->getEntries()->getEntryTypeByHandle('websiteDocumentationContent')
		);

		$guideEntryType = [];

		if ($entryType) {
			$guideEntryType = [
				'handle' => $entryType["handle"],
				'id' => $entryType["id"],
				'name' => $entryType["name"],
				'uid' => $entryType["uid"],
			];
		}

		return [
			'entryTypes' => $guideEntryType,
		];

	}

}
