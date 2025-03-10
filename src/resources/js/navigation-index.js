if (typeof Craft.Documentation === typeof undefined) {
	Craft.Documentation = {};
}

Craft.setQueryParam("sort", null);

Craft.Documentation.ElementIndex = Craft.BaseElementIndex.extend({
	init(className, container, element) {
		this.menuId = element.menuId;
		this.menuSidebar = document.querySelector(".documentation-menu-sidebar");
		this.base(className, container, element);
	},
	afterInit: function () {
		Object.keys(this.sourceStates).forEach((key) => {
			this.sourceStates[key].order = "structure";
		});

		// Get the base
		this.base();

		// Get object
		const self = this;

		// Add JS for the edit button on each table row
		this.$elements.on("click", "tbody tr a.element-edit-btn", this.editElement.bind(this));

		// Add JS to the tabs so we can switch between types
		const sidebarTabs = this.menuSidebar.querySelectorAll(".tab-list-item");
		sidebarTabs.forEach(function (tab) {
			const siblings = self.getSiblings(tab);
			const link = tab.querySelector(".tab");
			const panel = tab.querySelector(".tab-list-pane");

			link.addEventListener("click", (event) => {
				event.preventDefault();

				if (link.classList.contains("sel")) {
					link.classList.remove("sel");
					panel.classList.add("hidden");
				} else {
					link.classList.add("sel");
					panel.classList.remove("hidden");

					siblings.forEach(function (sibling) {
						const otherLink = sibling.querySelector(".tab");
						const otherPanel = sibling.querySelector(".tab-list-pane");

						otherLink.classList.remove("sel");
						otherPanel.classList.add("hidden");
					});
				}
			});
		});

		// ADD MORE

		// Sidebar Forms
		const sidebarForms = this.menuSidebar.querySelectorAll("form");
		sidebarForms.forEach(function (form, index) {
			form.addEventListener("submit", (event) => {
				event.preventDefault();

				if (form.classList.contains("form-type-element")) {
					self.showElementModal.bind(self, event)();
					console.log("Form Type Element");
				} else {
					self.onElementInstantSubmit.bind(self, event)();
					console.log("Element Submits");
				}
			});
		});
	},
	getSiblings: function (element) {
		let siblings = [];
		let sibling = element.parentNode.firstElementChild;

		while (sibling) {
			if (sibling.nodeType === 1 && sibling !== element) {
				siblings.push(sibling);
			}
			sibling = sibling.nextSibling;
		}
		return siblings;
	},
	editElement: function (event) {
		event.preventDefault();

		// Get target
		const target = event.target;

		// Open the element slide-out
		const slideout = target.closest("tr").querySelector(".element");
		const type = slideout.getAttribute("data-type");

		Craft.createElementEditor(type, slideout);
	},
	showElementModal: function (event, ignore) {
		event.preventDefault();
	},
	onElementInstantSubmit: function (event, ignore) {
		event.preventDefault();

		this.form = event.target;
		const data = new FormData(this.form);

		data.append("menuId", this.menuId);
		data.append("siteId", this.siteId);

		let object = {};
		for (let [key, value] of data) {
			object[key] = value;
		}

		this.addElements({
			elements: [object],
		});
	},
	addElements: function (data) {
		const loading = this.form.querySelector(".loading");
		const errors = this.form.querySelector("ul.errors");
		const submit = this.form.querySelector("button[type=submit]");

		// Remove spinner
		loading.classList.remove("hidden");

		// Remove Errors
		if (errors && errors.length) {
			errors.remove();
		}

		// Add Elements
		Craft.sendActionRequest("POST", "websitedocumentation/elements/add-elements", {
			data,
		})
			.then((response) => {
				// Display the response flash message
				Craft.cp.displayNotice(response.data.message);

				// Get the form
				const form = this.form;

				// Check for Errors already, and if they exist remove them
				const errors = form.querySelector(".errors");
				if (errors) {
					errors.remove();
				}

				// Get the parent Id
				const parentDropdown = form.querySelector('[name="parentId"');
				const parentId = parentDropdown.value;

				// Update the elements
				this.updateElements();

				// Reset the form, but keep the parent set
				form.reset();
				parentDropdown.value = parentId;
			})
			.catch((error) => {
				const response = error.response;

				// Get the form
				const form = this.form;

				// Get the submit button
				const submit = this.form.querySelector("button[type=submit]");
				const submitParent = submit.parentNode;

				// Check for Errors already, and if they exist remove them
				const errors = form.querySelector(".errors");
				if (errors) {
					errors.remove();
				}

				// If we have new errors, lets create them
				if (response && response.data && response.data.errors) {
					const errorList = document.createElement("ul");
					errorList.classList.add("errors");

					// Add the error list
					form.insertBefore(errorList, submitParent);

					// Add each error in
					const errorObject = response.data.errors;
					for (const property in errorObject) {
						if (!response.data.errors.hasOwnProperty(property)) {
							continue;
						}

						const errorItem = document.createElement("li");
						errorItem.innerText = errorObject[property];

						errorList.append(errorItem);
					}
				}

				// Display flash message
				if (response && response.data && response.data.message) {
					Craft.cp.displayError(response.data.message);
				} else {
					console.error(error);

					Craft.cp.displayError();
				}
			})
			.finally(() => {
				loading.classList.add("hidden");
			});
	},
	onUpdateElements: function () {
		this.updateParentSelect();
	},
	updateParentSelect: function () {
		const data = {
			menuId: this.menuId,
			siteId: this.siteId,
		};

		console.log(data);

		Craft.sendActionRequest("POST", "websitedocumentation/elements/get-parent-options", {
			data,
		}).then((response) => {
			let html = "";

			const dropdowns = document.querySelectorAll(".dropdown-parent-element select");
			const options = response.data.options;

			options.forEach((option, index) => {
				html += '<option value="' + option.value + '">' + option.label + "</option>";
			});

			dropdowns.forEach((dropdown, index) => {
				const selected = dropdown.value;

				dropdown.innerHTML = html;
				dropdown.value = selected;
			});
		});
	},
});

Craft.registerElementIndexClass(
	"fortytwostudio\\websitedocumentation\\elements\\NavElement",
	Craft.Documentation.ElementIndex,
);
