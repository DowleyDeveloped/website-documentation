/** global: Craft */
/** global: Garnish */
/**
 * GuideEntries class
 */

if (typeof Craft.GuideEntries === typeof undefined) {
	Craft.GuideEntries = {};
}

Craft.GuideEntries.TemplateIndex = Craft.BaseElementIndex.extend({
	$newTemplateBtnGroup: null,
	$newTemplateBtn: null,
	init(elements, main, controller) {
		this.on("selectSource", this.createButton.bind(this));
		this.on("selectSite", this.createButton.bind(this));
		this.base(elements, main, controller);
	},
	createButton: function () {
		if (null === this.$source) return;

		null !== this.$newTemplateBtnGroup && this.$newTemplateBtnGroup.remove(),
			(this.$newTemplateBtnGroup = $('<div class="btngroup submit" data-wrapper/>'));

		this.$newTemplateBtn = Craft.ui
			.createButton({
				label: Craft.t("app", "New Entry"),
				spinner: true,
			})
			.addClass("submit add icon")
			.appendTo(this.$newTemplateBtnGroup);

		this.addListener(this.$newTemplateBtn, "click", () => {
			this._createTemplate();
		});

		this.addButton(this.$newTemplateBtnGroup);
	},
	_createTemplate: async function () {
		const table = this.$source.data("handle");

		Craft.sendActionRequest("POST", "websitedocumentation/templates/create", {
			data: {
				siteId: this.siteId,
				entryType: "websiteDocumentationContent",
			},
		}).then(({ data: table }) => {
			document.location.href = Craft.getUrl(table.cpEditUrl, {
				fresh: 1,
			});
		});
	},
});

Craft.registerElementIndexClass(
	"fortytwostudio\\websitedocumentation\\elements\\GuideEntry",
	Craft.GuideEntries.TemplateIndex,
);
