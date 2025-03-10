/**
 * @title Sidebar
 * @description Functionality for sidebar Navigation
 */

class SidebarMenu {
	constructor(menu, sections) {
		this.menu = menu;
		this.navItems = sections;
		this.contentSections = document.querySelectorAll("[data-content]");
		this.activeSectionClass = "active-section";
		this.activeNavClass = "active-element";
		this.activeIconClass = "active-element--icon";
		this.activeContentClass = "active-content";
		this.activeElement = null;
	}

	get firstItem() {
		let firstItem = this.navItems[0];
		if (firstItem.hasAttribute("data-sub") && firstItem.dataset.sub === "true") {
			const submenu = firstItem.nextElementSibling;
			firstItem = submenu.querySelector("[data-section]");
		}

		return firstItem;
	}

	get windowHash() {
		return window.location.hash;
	}

	initialize() {
		let element = this.firstItem;

		if (this.windowHash) {
			const id = this.windowHash.replace("#", "");
			const target =
				this.navItems.filter(
					(navItems) => navItems.getAttribute("data-section") === id,
				)[0] ?? null;

			element = target ?? this.firstItem;
		}

		// Check if element is within a submenu
		let ancestors = this.getAncestors(element);
		let ancestorsSubmenu = [];

		ancestors.forEach(function (ancestor) {
			if (ancestor.hasAttribute("data-submenu")) {
				ancestorsSubmenu.push(ancestor);
			}
		});

		const self = this;
		ancestorsSubmenu.forEach(function (submenu) {
			const button = submenu.previousElementSibling;
			self.activeNavigationItem(button, true, true);
			self.activeSubMenuItem(button, true);
		});

		if (element.hasAttribute("aria-controls")) {
			let elementIsOpen = element.getAttribute("aria-expanded");
			elementIsOpen = elementIsOpen == "true";

			// Activate/Deactivate Navigation Item
			this.activeNavigationItem(element, !elementIsOpen, true);

			// Open/Close Submenu
			this.activeSubMenuItem(element, !elementIsOpen);
		} else {
			// Activate Navigation Item
			this.activeNavigationItem(element, true);

			// Active Content Item
			this.activeContentItem(element, true);
		}

		this.menuItemStateChange();
	}

	// If a hashtag exists in the url
	hashtagActive(menu, section, item) {
		// Check if this is in a submenu
		let parentSubMenu = item.closest("[data-submenu]");

		if (parentSubMenu) {
			this.activeSubMenuItem(item, true);
		} else {
			this.toggleActiveSection(section, item);
			menu.scrollTop = item.offsetTop + 50;
		}
	}

	menuItemStateChange() {
		const self = this;

		this.navItems.forEach(function (element) {
			element.addEventListener("click", function (event) {
				let parent = null;

				// Check if element has a submenu
				if (element.hasAttribute("aria-controls")) {
					let elementIsOpen = element.getAttribute("aria-expanded");
					elementIsOpen = elementIsOpen == "true";

					// Activate/Deactivate Navigation Item
					self.activeNavigationItem(element, !elementIsOpen, true);

					// Open/Close Submenu
					self.activeSubMenuItem(element, !elementIsOpen);
				} else {
					// Activate Navigation Item
					self.activeNavigationItem(element, true);

					// Active Content Item
					self.activeContentItem(element, true);
				}

				const siblings = self.getSiblings(element.parentNode);

				// Check if content section exists
				const id = element.getAttribute("data-section");
				const target =
					Array.from(self.contentSections).filter(
						(contentSections) => contentSections.getAttribute("data-content") === id,
					)[0] ?? null;

				siblings.forEach(function (sibling) {
					const item = sibling.querySelector("button");
					if (item !== element) {
						self.activeNavigationItem(item, false, false);
						self.activeSubMenuItem(item, false);
					}
				});

				self.navItems.forEach(function (item) {
					if (item !== element && target) {
						self.activeContentItem(item, false);
					}
				});
			});
		});
	}

	// toggle the active section
	toggleActiveSection(section, item) {
		const self = this;

		this.navItems.forEach(function (element) {
			self.activeNavigationItem(element, element === item);
			self.activeContentItem(element, element === item);
		});
	}

	// Toggle the active menu Item
	activeNavigationItem(item, active = false, iconRotate = false) {
		if (active === true) {
			const id = item.getAttribute("data-section");

			if (id && !item.hasAttribute("aria-controls")) {
				window.location.hash = id;
			}

			item.classList.add(this.activeNavClass);
		} else {
			item.classList.remove(this.activeNavClass);
		}

		if (iconRotate === true) {
			item.classList.add(this.activeIconClass);
		} else {
			item.classList.remove(this.activeIconClass);
		}
	}

	// Toggle the active menu Item
	activeContentItem(item, active = false) {
		const id = item.getAttribute("data-section");
		const target =
			Array.from(this.contentSections).filter(
				(contentSections) => contentSections.getAttribute("data-content") === id,
			)[0] ?? null;

		if (target) {
			if (active) {
				target.classList.add(this.activeContentClass);
				this.activateIframe(target, true);
			} else {
				target.classList.remove(this.activeContentClass);
				this.activateIframe(target, false);
			}
		}
	}

	// Toggle the active submenu
	activeSubMenuItem(item, active = false) {
		// Get this
		const self = this;

		// Update the aria
		item.setAttribute("aria-expanded", active);

		// Display the submenu
		const menuControl = item.getAttribute("aria-controls");
		const submenu = document.querySelector(`#${menuControl}`);

		if (submenu) {
			if (active) {
				submenu.classList.add(this.activeNavClass);
			} else {
				submenu.classList.remove(this.activeNavClass);
				const children = submenu.querySelectorAll(".nav-element");

				children.forEach(function (child) {
					self.activeNavigationItem(child, false, false);
					self.activeSubMenuItem(child, false);
				});
			}
		}
	}

	activateIframe(element, active = true) {
		const iframe = element.querySelector("iframe");
		if (iframe) {
			const loader = element.querySelector("#loader");
			const path = iframe.getAttribute("data-path");

			if (active) {
				iframe.src = path;
				loader.classList.add("hidden");
				iframe.classList.add("loaded");
			} else {
				iframe.removeAttribute("src");
				loader.classList.remove("hidden");
				iframe.classList.remove("loaded");
			}
		}
	}

	// Get ancestors of an element
	getAncestors(element) {
		let ancestors = [];

		while (element && element !== this.menu) {
			ancestors.unshift(element);
			element = element.parentNode;
		}

		return ancestors;
	}

	// Get siblings of an element
	getSiblings(element) {
		let siblings = [];
		// if no parent, return no sibling
		if (!element.parentNode) {
			return siblings;
		}
		// first child of the parent node
		let sibling = element.parentNode.firstChild;

		// collecting siblings
		while (sibling) {
			if (sibling.nodeType === 1 && sibling !== element) {
				siblings.push(sibling);
			}
			sibling = sibling.nextSibling;
		}
		return siblings;
	}
}

function initSidebar() {
	const sidebarMenu = document.getElementById("sidebar-menu");

	if (sidebarMenu) {
		let navSections = sidebarMenu.querySelectorAll("[data-section]");
		navSections = Array.from(navSections);

		const sidebar = new SidebarMenu(sidebarMenu, navSections);

		sidebar.initialize();
	}
}

initSidebar();
