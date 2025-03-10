<?php
namespace fortytwostudio\websitedocumentation\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class MenusAsset extends AssetBundle
{
	// Public Methods
	// =========================================================================

	public function init()
	{
		$this->sourcePath = "@fortytwostudio/websitedocumentation/resources";

		// define the dependencies
		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'js/navigation-index.js',
		];

		$this->css = [
			'css/navigation.css',
		];

		parent::init();
	}
}
