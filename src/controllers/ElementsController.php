<?php
namespace fortytwostudio\websitedocumentation\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;

use fortytwostudio\websitedocumentation\WebsiteDocumentation;
use fortytwostudio\websitedocumentation\elements\NavElement;

use yii\web\Response;

class ElementsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionAddElements(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

		$elementsPost = $this->request->getRequiredParam('elements');

		if ($elementsPost) {
			foreach ($elementsPost as $key => $data)
			{
				$element = $this->_setElementFromPost("elements.{$key}.");

            	// Add this new element to the nav, to assist with validation
            	WebsiteDocumentation::getInstance()->elementTypes->setTempElements([$element]);

				if (!Craft::$app->getElements()->saveElement($element, true)) {
					return $this->asModelFailure($element, Craft::t('websitedocumentation', 'Couldn’t add Element.'), 'element');
				}

			}
		}

		return $this->asSuccess(Craft::t('websitedocumentation', 'Element{plural} added.', [
			'plural' => count($elementsPost) > 1 ? 's' : ''
		]));
    }

    public function actionGetParentOptions(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $menuId = $this->request->getRequiredParam('menuId');
        $siteId = $this->request->getParam('siteId');

        $elements = WebsiteDocumentation::getInstance()->elementTypes->getElementsForNav($menuId, $siteId);

        $options = [];

        if ($elements) {
            $options = WebsiteDocumentation::getInstance()->elementTypes->getParentOptions($elements, $elements[0]->nav);
        }

        return $this->asJson(['options' => $options]);
    }


    // Private Methods
    // =========================================================================

    private function _setElementFromPost($prefix = ''): NavElement
    {
        $navElement = new NavElement();
        $navElement->title = $this->request->getParam("{$prefix}title", $navElement->title);
        $navElement->enabled = (bool)$this->request->getParam("{$prefix}enabled", $navElement->enabled);

		$elementId = $this->request->getParam("{$prefix}elementId", $navElement->elementId);

        // Handle elementselect field
        if (is_array($elementId)) {
            $elementId = $elementId[0] ?? null;
        }

        $navElement->elementId = $elementId;
        $navElement->siteId = $this->request->getParam("{$prefix}siteId", $navElement->siteId);
        $navElement->menuId = $this->request->getParam("{$prefix}menuId", $navElement->menuId);
        $navElement->url = $this->request->getParam("{$prefix}url", $navElement->url);
        $navElement->type = $this->request->getParam("{$prefix}type", $navElement->type);

        $navElement->parentId = $this->request->getParam("{$prefix}parentId");

        // Set field values.
        $navElement->setFieldValuesFromRequest('fields');

        return $navElement;
    }

}
