<?php
namespace dowleydeveloped\websitedocumentation\variables;

use dowleydeveloped\websitedocumentation\WebsiteDocumentation;
use dowleydeveloped\websitedocumentation\elements\NavElement;
use dowleydeveloped\websitedocumentation\elements\db\NavElementQuery;
use dowleydeveloped\websitedocumentation\elements\GuideEntry;
use dowleydeveloped\websitedocumentation\elements\db\GuideQuery;

use Craft;
use craft\helpers\UrlHelper;
use yii\di\ServiceLocator;

class DocumentationVariable extends ServiceLocator
{
	public function getUrl(string $siteHandle = null)
	{
		// Get the Config File
		$config = WebsiteDocumentation::customConfig();

		if (empty($config))
		{
			return 'website-documentation';
		}

		if (isset($config['documentationUrl']) || isset($config['url'])) {
			$docUrl = isset($config['documentationUrl']) ? $config['documentationUrl'] : $config['url'];
		} elseif(isset($config[$siteHandle]['documentationUrl']) || isset($config[$siteHandle]['url'])) {
			$docUrl = isset($config[$siteHandle]['documentationUrl']) ? $config[$siteHandle]['documentationUrl'] : $config[$siteHandle]['url'];
		} else {
			$docUrl = 'website-documentation';
		}

		return $docUrl;

	}

	public function getSettings(string $siteHandle = null)
	{
		if ($siteHandle) {
			return WebsiteDocumentation::$settings->sites[$siteHandle];
		} else {
			return WebsiteDocumentation::$settings;
		}
	}

	public function navigation($criteria = null): NavElementQuery
	{
		if ($criteria instanceof NavElementQuery) {
			$query = $criteria;
		} else {
			$query = NavElement::find();
		}

		if ($criteria) {
			if (is_string($criteria)) {
				$criteria = ['handle' => $criteria];
			}

			Craft::configure($query, $criteria);
		}

		return $query;
	}

	public function navElements($criteria = null): NavElementQuery
	{
		if ($criteria instanceof NavElementQuery) {
			$query = $criteria;
		} else {
			$query = NavElement::find();
		}

		if ($criteria) {
			if (is_string($criteria)) {
				$criteria = ['handle' => $criteria];
			}

			Craft::configure($query, $criteria);
		}

		return $query;
	}

	public function guides($criteria = null): GuideQuery
	{
		if ($criteria instanceof GuideQuery) {
			$query = $criteria;
		} else {
			$query = GuideEntry::find();
		}

		if ($criteria) {
			if (is_string($criteria)) {
				$criteria = ['handle' => $criteria];
			}

			Craft::configure($query, $criteria);
		}

		return $query;
	}

    /**
    * Returns 'dark' or 'light' based on contrast score
    *
    * @param  string $hexcolor
    * @return string 'dark' or 'light'
    */
    public function getContrastColor($hexcolor)
    {
        $r = hexdec(substr($hexcolor, 1, 2));
        $g = hexdec(substr($hexcolor, 3, 2));
        $b = hexdec(substr($hexcolor, 5, 2));
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        return ($yiq >= 160) ? '#000' : '#fff';
    }

	public function getElementTabs($menu): array
	{
		return WebsiteDocumentation::getInstance()->guideMenus->getElementTabs($menu);
	}
}
