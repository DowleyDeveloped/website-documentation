/**
 * @title Toolbar
 * @description Functionality for style guide toolbar
 */

class ToolBar {
    constructor(breakpoints, fullscreen, defaultSize, copy) {
        this.breakpoints = breakpoints;
        this.fullscreen = fullscreen;
        this.fullSize = false;
        this.defaultSize = defaultSize;
        this.copyLink = copy;
        this.queryStrings = window.location.search;
        this.websiteUrl = new URL(location.href);
        this.canvases = document.querySelectorAll("#canvas");
        this.headings = document.querySelectorAll("#canvas-wrapper > h2");
        this.sidebar = document.getElementById("aside");
        this.menuTrigger = document.querySelector("[data-burger]");
        this.menuWrapper = document.getElementById("sidebar-content");
        this.menuOpen = false;
        this.activeClass = "active";
    }

    get windowHash() {
        return window.location.hash;
    }

    initialize() {
        if (this.queryStrings) {
            const parameters = new URLSearchParams(this.queryStrings);

            parameters.forEach((value, key) => {
                if (key === "fullscreen") {
                    this.fullscreenActive();
                    this.fullSize = true;
                }

                if (key === "breakpoint") {
                    const target =
                        Array.from(this.breakpoints).filter(
                            (breakpoints) => breakpoints.getAttribute("data-breakpoint") === value,
                        )[0] ?? null;

                    if (target) {
                        target.classList.add(this.activeClass);

                        this.canvases.forEach(function (canvas) {
                            canvas.style.width = value + "px";
                        });
                    }
                }
            });
        }

        this.mobileMenu();
        this.controlBreakpoints();
        this.controlFullScreen();
        this.copyToClipBoard();
    }

    mobileMenu() {
        const self = this;

        if (this.menuTrigger) {
            this.menuTrigger.addEventListener("click", function (e) {
                if (!self.menuOpen) {
                    this.classList.add("open");
                    self.menuWrapper.classList.add("open");
                    self.menuOpen = true;
                } else {
                    this.classList.remove("open");
                    self.menuWrapper.classList.remove("open");
                    self.menuOpen = false;
                }
            });
        }
    }

    controlBreakpoints() {
        const self = this;

        // When a breakpoint is clicked
        this.breakpoints.forEach(function (breakpoint) {
            breakpoint.addEventListener("click", function (e) {
                const width = breakpoint.dataset.breakpoint;
                let path = "";
                const hash = window.location.hash.split("?");

                // Add Breakpoint to URL Query
                if (!event.target.classList.contains("active")) {
                    self.websiteUrl.searchParams.set("breakpoint", width);
                } else {
                    self.websiteUrl.searchParams.delete("breakpoint");
                }

                history.pushState(null, "", self.websiteUrl);

                // Change the Canvas Side
                self.canvases.forEach(function (canvas) {
                    if (canvas.style.width === width + "px") {
                        canvas.style.width = "100%";
                        breakpoint.classList.remove("active");
                    } else {
                        canvas.style.width = width + "px";
                        [...breakpoint.parentElement.children].forEach((sibling) =>
                            sibling.classList.remove("active"),
                        );
                        breakpoint.classList.add("active");
                    }
                });
            });
        });
    }

    controlFullScreen() {
        const self = this;

        if (self.fullscreen) {
            self.fullscreen.addEventListener("click", function (e) {
                // Sidebar Display
                if (self.fullSize) {
                    self.showSidebar();
                    this.classList.remove(self.activeClass);
                    self.fullSize = false;
                } else {
                    self.hideSidebar();
                    this.classList.add(self.activeClass);
                    self.fullSize = true;
                }
            });
        }

        if (self.defaultSize) {
            self.defaultSize.addEventListener("click", function (e) {
                self.showSidebar();
                self.fullscreen.classList.remove(self.activeClass);
                self.fullSize = false;
            });
        }
    }

    copyToClipBoard() {
        if (this.copyLink) {
            this.copyLink.addEventListener("click", function (e) {
                const input = document.body.appendChild(document.createElement("input"));
                input.value = window.location.href;
                input.focus();
                input.select();
                document.execCommand("copy");
                input.parentNode.removeChild(input);
            });
        }
    }

    fullscreenActive() {
        this.hideSidebar();
        this.fullscreen.classList.add("active");

        this.headings.forEach(function (heading) {
            heading.style.opacity = 0;
        });
    }

    showSidebar() {
        this.sidebar.style.marginLeft = "0";
        this.sidebar.style.left = "0";
        this.defaultSize.style.display = "none";

        this.headings.forEach(function (heading) {
            heading.style.opacity = 1;
        });

        this.canvases.forEach(function (canvas) {
            canvas.style.top = "5rem";
            canvas.style.height = "calc(100% - 5rem)";
        });

        this.websiteUrl = new URL(location.href);
        this.websiteUrl.searchParams.delete("fullscreen");
        history.pushState(null, "", this.websiteUrl);
    }

    hideSidebar() {
        this.sidebar.style.display = "none";
        this.sidebar.style.marginLeft = "-300px";
        this.sidebar.style.left = "-300px";
        this.defaultSize.style.display = "flex";

        this.headings.forEach(function (heading) {
            heading.style.opacity = 0;
        });

        this.canvases.forEach(function (canvas) {
            canvas.style.top = "1rem";
            canvas.style.height = "calc(100% - 1rem)";
        });

        this.websiteUrl = new URL(location.href);
        this.websiteUrl.searchParams.set("fullscreen", "true");
        history.pushState(null, "", this.websiteUrl);

        const self = this;
        setTimeout(function () {
            self.sidebar.style.display = null;
        }, 200);
    }
}

function initToolbar() {
    const breakpoints = document.querySelectorAll("[data-breakpoint]");
    const fullscreen = document.querySelector("[data-fullscreen]");
    const defaultSize = document.querySelector("[data-default]");
    const copyLink = document.querySelector("[data-copy-link]");

    if (breakpoints) {
        const toolbar = new ToolBar(breakpoints, fullscreen, defaultSize, copyLink);
        toolbar.initialize();
    }
}

initToolbar();
