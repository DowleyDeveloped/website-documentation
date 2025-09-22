<?php
namespace dowleydeveloped\websitedocumentation\services;

use yii\base\Component;

use Craft;

use dowleydeveloped\websitedocumentation\WebsiteDocumentation;
use dowleydeveloped\websitedocumentation\elements\NavElement;
use dowleydeveloped\websitedocumentation\elementtypes\StyleGuideType;

use Exception;

class CreateNavElements extends Component
{
	public static function createDefault(?int $siteId = null) : bool
	{
		if ($siteId) {
			$menus = WebsiteDocumentation::getInstance()->guideMenus->getMenusBySiteId($siteId);
		} else {
			$menus = WebsiteDocumentation::getInstance()->guideMenus->getMenus();
		}

		$defaultItems = [
			(object) [
				"title" => "Colours",
			],
			(object) [
				"title" => "Typography",
			],
			(object) [
				"title" => "UI Elements",
				"sub" => [
					(object) [
						"title" => "Buttons & Links",
						"sub" => [
							"Buttons",
							"Links",
						]
					],
					(object) [
						"title" => "Form Fields",
					],
				],
			],
		];

		foreach ($menus as $menu)
		{
			// Check if any navigation items already exist, if they do we don't want to create the defaults
			$elements = NavElement::find()
				->menuId($menu->id)
				->siteId($siteId)
				->status(null)
				->all();

			if (!$elements)
			{
				foreach ($defaultItems as $item) {

					$parentNavElement = new NavElement();
					$parentNavElement->title = $item->title;
					$parentNavElement->enabled = true;
					$parentNavElement->siteId = $menu->siteId;
					$parentNavElement->menuId = $menu->id;
					$parentNavElement->type = StyleGuideType::class;

					Craft::$app->getElements()->saveElement($parentNavElement, true);

					if (isset($item->sub))
					{
						foreach ($item->sub as $subchild) {
							$childNavElement = new NavElement();
							$childNavElement->title = $subchild->title;
							$childNavElement->enabled = true;
							$childNavElement->siteId = $menu->siteId;
							$childNavElement->menuId = $menu->id;
							$childNavElement->parentId = $parentNavElement->id;
							$childNavElement->type = StyleGuideType::class;

							Craft::$app->getElements()->saveElement($childNavElement, true);

							if (isset($subchild->sub))
							{
								foreach ($subchild->sub as $grandchild) {
									$grandChildElement = new NavElement();
									$grandChildElement->title = $grandchild;
									$grandChildElement->enabled = true;
									$grandChildElement->siteId = $menu->siteId;
									$grandChildElement->menuId = $menu->id;
									$grandChildElement->parentId = $childNavElement->id;
									$grandChildElement->type = StyleGuideType::class;

									Craft::$app->getElements()->saveElement($grandChildElement, true);
								}
							}
						}
					}
				}
			}
		}

		return true;

	}
}
