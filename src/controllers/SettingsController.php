<?php
namespace fortytwostudio\websitedocumentation\controllers;

use fortytwostudio\websitedocumentation\WebsiteDocumentation;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use craft\helpers\StringHelper;
use craft\services\Volumes;
use craft\volumes\Local;

use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
	// Protected Properties
	// =========================================================================

	protected array|bool|int $allowAnonymous = [];

	// Public Methods
	// =========================================================================

	/**
	 * Plugin settings
	 *
	 * @param null|bool|Settings $settings
	 *
	 * @return Response The rendered result
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionPluginSettings(
		string $siteHandle = null,
		$settings = null): Response
    {
		if ($settings === null) {
			$settings = WebsiteDocumentation::$settings;
		}

		if ($siteHandle === null) {
            $request = Craft::$app->getRequest();
            $siteParam = $request->getQueryParam('site');
			$siteHandle = $siteParam ?? Craft::$app->sites->primarySite->handle;
		}

		$siteId = $this->getSiteIdFromHandle($siteHandle);
		$section = Craft::$app->request->getSegment(3);

// 		if ($settings["structure"]) {
// 			// Check if Structure Exists
// 			$sectionRequired = Craft::$app->entries->getSectionByHandle(
// 				StringHelper::toCamelCase($settings["structure"])
// 			);
//
// 			if ($sectionRequired != null) {
// 				$settings["structureExists"] = true;
// 			} else {
// 				$settings["structureExists"] = false;
// 			}
// 		} else {
// 			$settings["structureExists"] = false;
// 		}

		// Basic variables
		$variables["fullPageForm"] = true;
		$variables["selectedSubnavItem"] = "settings";
		$variables["settings"] = $settings;

		$variables["controllerHandle"] = "settings" . "/" . $section;
		$this->setMultiSiteVariables($siteHandle, $siteId, $variables);

		// Logo
		$logo = null;

		if (!empty($settings->sites[$siteHandle]["logo"])) {
            $logoId = $settings->sites[$siteHandle]["logo"];
            $logo = Craft::$app->elements->getElementById($logoId);
		}

		$variables["logo"] = $logo;

		// Create Variable of current site
		$variables["currentSite"] = $siteHandle;

		return $this->renderTemplate(
			"websitedocumentation/settings/" .
				($section ? (string) $section : ""),
			$variables
		);
	}

	/**
	 * Save General Settings
	 *
	 * @return Response The rendered result
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionSaveGeneralSettings()
	{
		$this->requirePostRequest();
		$updates = Craft::$app->getRequest()->getBodyParams();
		$plugin = Craft::$app->getPlugins()->getPlugin("websitedocumentation");
		$savedSettings = $plugin->settings->sites;

		unset($savedSettings[$updates["siteHandle"]]);

		$currentSettings = [
			$updates["siteHandle"] => [
				"logo" => $updates["logo"],
				"brandBgColor" => $updates["brandBgColor"],
				"brandTextColor" => $updates["brandTextColor"],
				"accentBgColor" => $updates["accentBgColor"],
				"accentTextColor" => $updates["accentTextColor"],
				"displayStyleGuide" => $updates["displayStyleGuide"],
				"displayCmsGuide" => $updates["displayCmsGuide"],
			],
		];

		if ($savedSettings == null) {
			$savedSettings = [];
		}

		$settings = [
			"sites" => array_merge($currentSettings, $savedSettings),
		];

		if (
			!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings)
		) {
			Craft::$app
				->getSession()
				->setError(Craft::t("app", "Couldn't save plugin settings."));

			return $this->redirectToPostedUrl();
		}
	}

	// Protected Methods
	// =========================================================================

	/**
	 * Return a siteId from a siteHandle
	 *
	 * @param string $siteHandle
	 *
	 * @return int|null
	 * @throws NotFoundHttpException
	 */
	protected function getSiteIdFromHandle($siteHandle)
	{
		// Get the site to edit
		if ($siteHandle !== null) {
			$site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
			if (!$site) {
				throw new NotFoundHttpException(
					"Invalid site handle: " . $siteHandle
				);
			}
			$siteId = $site->id;
		} else {
			$siteId = Craft::$app->getSites()->currentSite->id;
		}

		return $siteId;
	}

	/**
	 * @param string $siteHandle
	 * @param        $siteId
	 * @param        $variables
	 *
	 * @throws ForbiddenHttpException
	 */
	protected function setMultiSiteVariables(
		$siteHandle,
		&$siteId,
		array &$variables,
		$element = null
	) {
		// Enabled sites
		$sites = Craft::$app->getSites();
		if (Craft::$app->getIsMultiSite()) {
			// Set defaults based on the section settings
			$variables["enabledSiteIds"] = [];
			$variables["siteIds"] = [];
			$variables["sites"] = (object) [];

			/** @var Site $site */
			foreach ($sites->getEditableSiteIds() as $editableSiteId) {
				$variables["enabledSiteIds"][] = $editableSiteId;
				$variables["siteIds"][] = $editableSiteId;
			}

			foreach ($sites->getEditableSites() as $editableSite) {
				$handle = $editableSite->handle;
				$variables["sites"]->$handle = (object) [];
			}

			// Make sure the $siteId they are trying to edit is in our array of editable sites
			if (!in_array($siteId, $variables["enabledSiteIds"], false)) {
				if (!empty($variables["enabledSiteIds"])) {
					$siteId = reset($variables["enabledSiteIds"]);
				} else {
					$this->requirePermission("editSite:" . $siteId);
				}
			}
		}

		// Set the currentSiteId and currentSiteHandle
		$variables["currentSiteId"] = empty($siteId)
			? Craft::$app->getSites()->currentSite->id
			: $siteId;
		$variables["currentSiteHandle"] = empty($siteHandle)
			? Craft::$app->getSites()->currentSite->handle
			: $siteHandle;

		// Page title
		$variables["showSites"] =
			Craft::$app->getIsMultiSite() &&
			count($variables["enabledSiteIds"]);

		if ($variables["showSites"]) {
			$variables["sitesMenuLabel"] = Craft::t(
				"site",
				$sites->getSiteById((int) $variables["currentSiteId"])->name
			);
		} else {
			$variables["sitesMenuLabel"] = "";
		}
	}
}
