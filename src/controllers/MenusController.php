<?php
namespace fortytwostudio\websitedocumentation\controllers;

use fortytwostudio\websitedocumentation\WebsiteDocumentation;
use fortytwostudio\websitedocumentation\elements\NavElement;
use fortytwostudio\websitedocumentation\models\Navigation;
use fortytwostudio\websitedocumentation\models\Settings;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;

use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use Throwable;

class MenusController extends Controller
{
	// Public Methods
	// =========================================================================

	/*
	 * @title: Index
	 * @desc: Used by the Menu Index
	 */
	public function actionIndex(): Response
	{
		// Get the current site from the global query param
		$siteHandle = Craft::$app->getRequest()->getParam('site', Craft::$app->getSites()->getPrimarySite()->handle);
		$site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

		$navigations = WebsiteDocumentation::getInstance()->guideMenus->getSiteMenus($site);

		return $this->renderTemplate('websitedocumentation/menus/index', [
			'navigations' => $navigations,
		]);
	}

	/*
	 * @title: Edit
	 * @desc: Used by the Menu Edit Screen
	 */

	public function actionEditMenu(int $menuId = null): Response
	{
		$defaultSite = false;

		$menu = WebsiteDocumentation::getInstance()->guideMenus->getMenuById($menuId);

		if (!$menu) {
			throw new NotFoundHttpException('Menu not found');
		}

		$siteHandle = $this->request->getParam('site');

		// If not requesting a specific site, use the primary one
		if (!$siteHandle) {
			$defaultSite = true;
			$siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;

			// If they don't have access to the default site, pick the first enabled one
			$site = ArrayHelper::firstWhere([$menu], 'handle', $siteHandle);

			if (!$site) {
				$siteHandle = $menu->handle ?? '';
			}
		} else {
			$site = Craft::$app->sites->getSiteByHandle($siteHandle);
			$site = $site->id;
		}

		// Get all the elements used by the menu
		$menuElements = WebsiteDocumentation::getInstance()->guideMenus->getMenuElements($menu->id, $site);

		// Get all the parents within the menu
		$parentOptions = WebsiteDocumentation::getInstance()->guideMenus->getParentOptions($menuElements, $menu);

		// Get structure
		$structureService = Craft::$app->getStructures();
		$structure = $structureService->getStructureById($menu->structureId, true);

		return $this->renderTemplate('websitedocumentation/menus/_edit', [
			'menuId' => $menuId,
			'menu' => $menu,
			'elements' => $menuElements,
			'site' => $site,
			'structure' => $structureService->getStructureById($menu->structureId, true),
			'defaultSite' => $defaultSite,
			'parentOptions' => $parentOptions,
		]);
	}

}
