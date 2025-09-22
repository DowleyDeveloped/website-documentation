<?php
namespace dowleydeveloped\websitedocumentation\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

class GuideIndexAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@dowleydeveloped/websitedocumentation/resources";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

		// $this->css = [
        //     'css/dist/guide-index.min.css',
        // ];

        $this->js = [
            'js/guide-index.js',
        ];

        parent::init();
    }
}
