// Find the main navigation item and add a class
const mainNavItem = document.querySelector('a[href*="website-documentation"]');
if (mainNavItem) {
	mainNavItem.classList.add("sel");
}

// Find subnav items related to the plugin and add a class
const subNavItem = document.querySelector('a[href*="website-documentation/guides"]');
if (subNavItem) {
	const parent = subNavItem.parentNode;
	subNavItem.classList.add("sel");

	if (parent) {
		parent.classList.add("sel");
	}
}
