<?php
namespace dowleydeveloped\websitedocumentation\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class DocumentationAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@dowleydeveloped/websitedocumentation/resources";

		$this->css = [
            'css/index.css',
			'css/sidebar.css',
			'css/components.css',
        ];

		$this->js = [
			'js/sidebar.js',
			'js/toolbar.js',
		];

        parent::init();
    }
}
