# Website Documentation plugin for Craft CMS 5.x

Creates two links within admin for Style Guide and CMS Guide

## Requirements

This plugin requires Craft CMS 5+.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

```
cd /path/to/project/craft
```

2. Then tell Composer to load the plugin:

```
composer require fortytwo-studio/website-documentation
```

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Website Documentation.

## Usage

### Installing Demo Templates

Once the plugin has installed you will see a new menu item called Documentation.

If you go to **Plugin Settings** -> **Install Templates** and click install, this will copy the default guides to your templates folder.

Within this folder you can go to Layouts -> CMS Guide / Styleguide and update the paths to where your CSS and JS live.

### General Settings

Within General Settings you can add a logo, and choose colours which will be displayed across both Style and CMS Guides.

## Adding new sections to the styleguide

You can create the style guide menu within the Documentation menu section. This will then look inside the website-documentation folder in your templates folder for the correct file.

The naming of these items are used in kebab case for the file in the sections folder. For example if you call a section **Featured Hero** you'll name your file **featured-hero.twig**.

Brought to you by [Forty Two](https://fortytwo.studio)
