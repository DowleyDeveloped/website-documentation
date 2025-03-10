<?php
namespace fortytwostudio\websitedocumentation\controllers;

use fortytwostudio\websitedocumentation\WebsiteDocumentation;
use fortytwostudio\websitedocumentation\elements\GuideEntry;

use Craft;
use craft\base\Element;
use craft\enums\PropagationMethod;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\DateTimeHelper;
use craft\helpers\ElementHelper;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;

use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use Throwable;

class GuideController extends Controller
{

	// Public Methods
	// =========================================================================

	/*
	 * @title: Index
	 * @desc: Used by the Guides Index
	 */
	public function actionIndex(): Response
	{
		// Get the current site from the global query param
		$siteHandle = Craft::$app->getRequest()->getParam('site', Craft::$app->getSites()->getPrimarySite()->handle);
		$site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

		return $this->renderTemplate('websitedocumentation/guide/index', [
			'elementType' => GuideEntry::class,
			'site' => $site,
			'settings' => [WebsiteDocumentation::$plugin->guideService->getGuideType()],
		]);
	}

	/**
	 * Creates a new unpublished draft and redirects to its edit page.
	 *
	 * @param string|null $section The section’s handle
	 * @return Response|null
	 * @throws BadRequestHttpException
	 * @throws ForbiddenHttpException
	 * @throws ServerErrorHttpException
	 */
	public function actionCreate(): ?Response
	{
		$sitesService = Craft::$app->getSites();
		$siteId = $this->request->getBodyParam('siteId');

		if ($siteId) {
			$site = $sitesService->getSiteById($siteId);
			if (!$site) {
				throw new BadRequestHttpException("Invalid site ID: $siteId");
			}
		} else {
			$site = Cp::requestedSite();
			if (!$site) {
				throw new ForbiddenHttpException('User not authorized to edit content in any sites.');
			}
		}

		$user = static::currentUser();

		// Create & populate the draft
		$entry = Craft::createObject(GuideEntry::class);
		$entry->siteId = $site->id;
		$entry->setAuthorIds($user->id);

		// Status
		if (($status = $this->request->getParam('status')) !== null) {
			$enabled = $status === 'enabled';
		} else {
			$enabled = true;
		}

		if (Craft::$app->getIsMultiSite() && count($entry->getSupportedSites()) > 1) {
			$entry->enabled = true;
			$entry->setEnabledForSite($enabled);
		} else {
			$entry->enabled = $enabled;
			$entry->setEnabledForSite(true);
		}

		// Set the initially selected parent
		$entry->setParentId($this->request->getParam('parentId'));

		// Title & slug
		$entry->title = $this->request->getParam('title');
		$entry->slug = $this->request->getParam('slug');
		if ($entry->title && !$entry->slug) {
			$entry->slug = ElementHelper::generateSlug($entry->title, null, $site->language);
		}
		if (!$entry->slug) {
			$entry->slug = ElementHelper::tempSlug();
		}

		// Post & expiry dates
		if (($postDate = $this->request->getParam('postDate')) !== null) {
			$entry->postDate = DateTimeHelper::toDateTime($postDate);
		} else {
			$entry->postDate = DateTimeHelper::now();
		}

		if (($expiryDate = $this->request->getParam('expiryDate')) !== null) {
			$entry->expiryDate = DateTimeHelper::toDateTime($expiryDate);
		}

		// Custom fields
		foreach ($entry->getFieldLayout()->getCustomFields() as $field) {
			if (($value = $this->request->getParam($field->handle)) !== null) {
				$entry->setFieldValue($field->handle, $value);
			}
		}

		// Save it
		$entry->setScenario(Element::SCENARIO_ESSENTIALS);
		$success = Craft::$app->getDrafts()->saveElementAsDraft($entry, $user->id, markAsSaved: false);

		// Resume time
		DateTimeHelper::resume();

		if (!$success) {
			return $this->asModelFailure($entry, StringHelper::upperCaseFirst(Craft::t('app', 'Couldn’t create {type}.', [
				'type' => GuideEntry::lowerDisplayName(),
			])), 'guideEntry');
		}

		// Set its position in the structure if a before/after param was passed
		if ($nextId = $this->request->getParam('before')) {
			$nextEntry = Craft::$app->getEntries()->getEntryById($nextId, $site->id, [
				'structureId' => $section->structureId,
			]);
			Craft::$app->getStructures()->moveBefore($section->structureId, $entry, $nextEntry);
		} elseif ($prevId = $this->request->getParam('after')) {
			$prevEntry = Craft::$app->getEntries()->getEntryById($prevId, $site->id, [
				'structureId' => $section->structureId,
			]);
			Craft::$app->getStructures()->moveAfter($section->structureId, $entry, $prevEntry);
		}

		$editUrl = $entry->getCpEditUrl();

		$response = $this->asModelSuccess($entry, Craft::t('app', '{type} created.', [
			'type' => GuideEntry::displayName(),
		]), 'entry', array_filter([
			'cpEditUrl' => $this->request->getIsCpRequest() ? $editUrl : null,
		]));

		if (!$this->request->getAcceptsJson()) {
			$response->redirect(UrlHelper::urlWithParams($editUrl, [
				'fresh' => 1,
			]));
		}

		return $response;

	}

	/*
	 * @title: Edit
	 * @desc: Used by the Menu Edit Screen
	 */

	public function actionEntry(int $id = null): Response
	{
		return $this->renderTemplate('websitedocumentation/guide/_entry', [
			'title' => 'Create a new entry',
			'fullPageForm' => true,
		]);
	}

}
