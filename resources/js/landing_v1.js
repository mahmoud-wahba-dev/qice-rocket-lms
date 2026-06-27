// landing_v1.js — FlyonUI components used on landing pages only
import "flyonui/dist/js/carousel.mjs";
import "flyonui/dist/js/accordion.mjs";
import "flyonui/dist/js/overlay.mjs";
import "flyonui/dist/js/dropdown.mjs";
import "flyonui/dist/js/toggle-count.mjs";
import "flyonui/dist/js/select.mjs";
import "flyonui/dist/js/tabs.mjs";
import "flyonui/dist/js/collapse.mjs";

function reinitFlyonUI() {
	["HSCarousel", "HSAccordion", "HSOverlay", "HSDropdown", "HSToggleCount", "HSSelect", "HSTabs", "HSCollapse"].forEach(
		(name) => {
			if (window[name]?.autoInit) {
				window[name].autoInit();
			}
		}
	);
}

// ============================================================
// Toggle Count (existing code)
// ============================================================
const getToggleCountInstance = (element) => {
	if (!window.HSToggleCount) {
		return null;
	}

	if (!window.$hsToggleCountCollection) {
		window.$hsToggleCountCollection = [];
	}

	const existing = window.HSToggleCount.getInstance(element, true);
	if (existing && existing.element) {
		return existing.element;
	}

	return new window.HSToggleCount(element);
};

const startToggleCount = (element) => {
	if (element.dataset.countStarted === "true") {
		return;
	}

	const instance = getToggleCountInstance(element);
	if (!instance) {
		return;
	}

	element.dataset.countStarted = "true";
	instance.countUp();
};

window.addEventListener("load", () => {
	const counters = document.querySelectorAll("[data-toggle-count]");

	if (!counters.length) {
		return;
	}

	if (window.HSToggleCount?.autoInit) {
		window.HSToggleCount.autoInit();
	}

	if ("IntersectionObserver" in window) {
		const observer = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						startToggleCount(entry.target);
						observer.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.35 }
		);

		counters.forEach((counter) => observer.observe(counter));
		return;
	}

	counters.forEach((counter) => startToggleCount(counter));
});


// ============================================================
// CART MODULE
// ============================================================

/**
 * Get the CSRF token from meta tag
 */
function getCsrfToken() {
	const meta = document.querySelector('meta[name="csrf-token"]');
	return meta ? meta.getAttribute("content") : "";
}

/**
 * Update the cart badge numbers
 */
function updateCartBadge(count) {
	const badge = document.getElementById("cart-badge");
	const drawerBadge = document.getElementById("cart-drawer-count-badge");

	if (!badge) return;

	if (count > 0) {
		badge.textContent = count > 99 ? "99+" : count;
		badge.classList.remove("hidden");
		badge.classList.add("flex");
	} else {
		badge.classList.add("hidden");
		badge.classList.remove("flex");
	}

	if (drawerBadge) {
		if (count > 0) {
			drawerBadge.textContent = count > 99 ? "99+" : count;
			drawerBadge.classList.remove("hidden");
		} else {
			drawerBadge.classList.add("hidden");
		}
	}
}

/**
 * Fetch cart data and populate the drawer
 */
async function loadCartDrawer() {
	const drawerBody = document.getElementById("cart-drawer-body");

	if (!drawerBody) return;

	// Show loading state
	drawerBody.innerHTML = `
		<div class="cart-drawer-loading">
			<span class="icon-[tabler--loader-2] size-8 animate-spin text-primary/40"></span>
			<p class="font-medium text-14px text-primary/50">جاري التحميل...</p>
		</div>
	`;

	try {
		const response = await fetch("/cart/get-drawer-info?skin=landing_v1", {
			headers: { "X-Requested-With": "XMLHttpRequest" },
		});
		const data = await response.json();

		if (data.code === 200) {
			drawerBody.innerHTML = data.html;

			// Update subtotal — handlePrice() returns HTML (currency spans), use innerHTML
			const subtotalEl = document.getElementById("cart-drawer-subtotal");
			const footerEl   = document.getElementById("cart-drawer-footer");

			if (subtotalEl) {
				subtotalEl.innerHTML = data.is_empty ? "0" : (data.subtotal || "0");
			}

			// Hide the footer (subtotal + checkout) when cart is empty
			if (footerEl) {
				footerEl.style.display = data.is_empty ? "none" : "";
			}

			const count = typeof data.items_count === "number"
				? data.items_count
				: drawerBody.querySelectorAll("[data-cart-item-id]").length;
			updateCartBadge(count);

			// Bind remove buttons rendered in the drawer HTML
			bindDrawerRemoveButtons();

			// Re-init FlyonUI tooltips if any
			reinitFlyonUI();
		}
	} catch (err) {
		drawerBody.innerHTML = `
			<div class="cart-drawer-empty">
				<div class="cart-drawer-empty__icon">
					<span class="icon-[tabler--alert-circle] size-8 text-red-400"></span>
				</div>
				<p class="cart-drawer-empty__title">حدث خطأ</p>
				<p class="cart-drawer-empty__text">تعذّر تحميل السلة. يرجى المحاولة مجدداً.</p>
			</div>
		`;
	}
}

/**
 * Fetch only the badge count (lightweight, for page load)
 */
async function refreshCartCount() {
	try {
		const response = await fetch("/cart/get-drawer-info?skin=landing_v1", {
			headers: { "X-Requested-With": "XMLHttpRequest" },
		});
		const data = await response.json();

		if (data.code === 200) {
			const count = typeof data.items_count === "number"
				? data.items_count
				: (() => {
					const parser = new DOMParser();
					const doc = parser.parseFromString(data.html, "text/html");
					return doc.querySelectorAll("[data-cart-item-id]").length;
				})();
			updateCartBadge(count);
		}
	} catch (_) {}
}

/**
 * Remove a cart item by ID.
 * - If the user is currently on the cart page → reload so the
 *   server recalculates subtotal / discount / tax / total.
 * - Otherwise (drawer on any other page) → AJAX-refresh the drawer.
 */
async function removeCartItem(cartItemId) {
	try {
		const response = await fetch(
			`/cart/${encodeURIComponent(cartItemId)}/delete`,
			{ headers: { "X-Requested-With": "XMLHttpRequest" } }
		);
		const data = await response.json();

		if (data.code === 200) {
			if (isOnCartPage()) {
				window.location.reload();
			} else {
				await loadCartDrawer();
				await refreshCartCount();
				showCartToast("تم الحذف", "تم إزالة الدورة من السلة", "info");
			}
			return true;
		}
	} catch (_) {}

	return false;
}

/**
 * Returns true when the current page is the landing-v1 cart page.
 */
function isOnCartPage() {
	return !!document.querySelector("[data-cart-page]");
}

/**
 * Cart page — delegated delete handler (supports string cookie IDs e.g. webinar_id_5)
 */
function initCartPageRemove() {
	const container = document.getElementById("cart-items-container");
	if (!container || container.dataset.removeBound === "1") return;
	container.dataset.removeBound = "1";

	container.addEventListener("click", async (e) => {
		const btn = e.target.closest("[data-cart-remove]");
		if (!btn || !container.contains(btn)) return;

		e.preventDefault();

		const id = btn.getAttribute("data-cart-remove");
		if (!id) return;

		btn.disabled = true;
		const originalHtml = btn.innerHTML;
		btn.innerHTML = '<span class="icon-[tabler--loader-2] size-4 animate-spin inline-block"></span>';

		const success = await removeCartItem(id);
		if (!success) {
			btn.disabled = false;
			btn.innerHTML = originalHtml;
			showCartToast("خطأ", "تعذّر حذف العنصر من السلة", "error");
		}
	});
}

/**
 * Bind remove buttons inside the dynamically loaded drawer HTML
 */
function bindDrawerRemoveButtons() {
	const drawerBody = document.getElementById("cart-drawer-body");
	if (!drawerBody) return;

	drawerBody.querySelectorAll("[data-cart-remove]").forEach((btn) => {
		if (btn.dataset.removeBound === "1") return;
		btn.dataset.removeBound = "1";

		btn.addEventListener("click", async (e) => {
			e.preventDefault();
			const id = btn.getAttribute("data-cart-remove");
			btn.disabled = true;
			const label = btn.querySelector("span:last-child");
			if (label) label.textContent = "...";
			await removeCartItem(id);
		});
	});
}

/**
 * Show a toast notification
 */
window.showCartToast = function (title, msg, type = "success") {
	const toast = document.getElementById("cart-toast");
	const toastTitle = document.getElementById("cart-toast-title");
	const toastMsg = document.getElementById("cart-toast-msg");
	const toastIcon = document.getElementById("cart-toast-icon");

	if (!toast) return;

	toastTitle.textContent = title;
	toastMsg.textContent = msg;

	// Icon & color
	toastIcon.className = "size-6 shrink-0 " + (
		type === "success" ? "icon-[tabler--circle-check-filled] text-green-500" :
		type === "error"   ? "icon-[tabler--circle-x-filled] text-red-500" :
		type === "info"    ? "icon-[tabler--info-circle-filled] text-blue-500" :
		"icon-[tabler--circle-check-filled] text-green-500"
	);

	// Show
	toast.classList.remove("translate-y-20", "opacity-0", "pointer-events-none");
	toast.classList.add("translate-y-0", "opacity-100");

	// Auto-hide after 3.5s
	clearTimeout(window._cartToastTimeout);
	window._cartToastTimeout = setTimeout(() => hideCartToast(), 3500);
};

window.hideCartToast = function () {
	const toast = document.getElementById("cart-toast");
	if (!toast) return;
	toast.classList.remove("translate-y-0", "opacity-100");
	toast.classList.add("translate-y-20", "opacity-0", "pointer-events-none");
};

/**
 * Handle Add to Cart form submission via AJAX
 */
function handleAddToCartForms() {
	// Both the regular form submit and the button approach
	document.addEventListener("submit", async (e) => {
		const form = e.target.closest(".add-to-cart-form");
		if (!form) return;

		e.preventDefault();

		const btn = form.querySelector("button[type='submit']");
		const originalContent = btn ? btn.innerHTML : "";
		let addedSuccessfully = false;
		if (btn) {
			btn.disabled = true;
			btn.innerHTML = '<span class="icon-[tabler--loader-2] size-5 animate-spin inline-block"></span>';
		}

		const formData = new FormData(form);

		try {
			const response = await fetch("/cart/store", {
				method: "POST",
				headers: {
					"X-CSRF-TOKEN": getCsrfToken(),
					"X-Requested-With": "XMLHttpRequest",
				},
				body: formData,
			});

			const data = await response.json();

			if (response.ok && (data.status === "success" || data.code === 200)) {
				addedSuccessfully = true;
				showCartToast(
					data.title || "تمت الإضافة",
					data.msg || "تمت إضافة الدورة إلى سلة التسوق",
					"success"
				);

				if (typeof data.cart_count === "number") {
					updateCartBadge(data.cart_count);
				} else {
					await refreshCartCount();
				}

				if (btn) {
					btn.disabled = true;
					btn.classList.add("is-added-to-cart", "btn-disabled", "opacity-60", "cursor-not-allowed");
					const seatIcon = form.dataset.seatIcon || form.querySelector("img")?.src || "";
					btn.innerHTML = "تمت الإضافة للسلة";
					if (seatIcon) {
						const img = document.createElement("img");
						img.src = seatIcon;
						img.alt = "";
						img.className = "size-6 shrink-0";
						btn.appendChild(img);
					}
				}

				if (isOnCartPage()) {
					// Reload so the cart page reflects the new item and recalculated totals
					window.location.reload();
				} else {
					// If the drawer is open, refresh it too
					const drawer = document.getElementById("cart-drawer");
					if (drawer && !drawer.classList.contains("hidden")) {
						await loadCartDrawer();
					}
				}
			} else {
				// Error
				const errData = data.toast_alert || data;
				showCartToast(
					errData.title || "خطأ",
					errData.msg || "لم يتم إضافة الدورة",
					"error"
				);
			}
		} catch (_) {
			showCartToast("خطأ", "فشل الاتصال بالخادم", "error");
		} finally {
			if (btn && !addedSuccessfully) {
				btn.disabled = false;
				btn.innerHTML = originalContent;
			}
		}
	});
}

/**
 * Wire the cart drawer button to load data on open
 */
function initCartDrawer() {
	const drawer = document.getElementById("cart-drawer");
	const drawerBtn = document.getElementById("cart-drawer-btn");

	if (!drawer || !drawerBtn) return;

	// Listen to FlyonUI overlay:open event
	drawer.addEventListener("open.overlay", () => {
		loadCartDrawer();
	});

	// Fallback: also listen to the button click
	drawerBtn.addEventListener("click", () => {
		// Small delay to let FlyonUI open the drawer first
		setTimeout(() => loadCartDrawer(), 50);
	});
}

// ============================================================
// COURSE DETAILS SCROLLSPY
// ============================================================
function initCourseScrollspy() {
	if (window.__courseScrollspyReady) return;
	window.__courseScrollspyReady = true;

	const getNav = () => document.querySelector(".course_details_page [data-scrollspy]");

	const getOffset = (nav) => {
		const stickyWrapper = nav.closest(".sticky") || nav;
		const custom = getComputedStyle(nav).getPropertyValue("--scrollspy-offset").trim();
		if (custom) {
			const parsed = parseInt(custom, 10);
			if (!Number.isNaN(parsed)) return parsed;
		}
		return 88 + stickyWrapper.getBoundingClientRect().height + 16;
	};

	const getSections = (nav) =>
		Array.from(nav.querySelectorAll('a[href^="#"]'))
			.map((link) => {
				const id = link.getAttribute("href")?.slice(1);
				if (!id) return null;
				const el = document.getElementById(id);
				return el ? { link, el, id } : null;
			})
			.filter(Boolean);

	const setActive = (nav, id) => {
		nav.querySelectorAll('a[href^="#"]').forEach((link) => {
			const isActive = link.getAttribute("href") === `#${id}`;
			link.classList.toggle("active", isActive);
			link.setAttribute("aria-current", isActive ? "location" : "false");
		});
	};

	const scrollToSection = (el, nav) => {
		const offset = getOffset(nav);
		const top =
			el.getBoundingClientRect().top +
			(window.pageYOffset || document.documentElement.scrollTop) -
			offset;

		window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
	};

	const updateActiveSection = () => {
		const nav = getNav();
		if (!nav) return;

		const sections = getSections(nav);
		if (!sections.length) return;

		const marker =
			(window.pageYOffset || document.documentElement.scrollTop) + getOffset(nav);
		let current = sections[0].id;

		sections.forEach(({ el, id }) => {
			const top =
				el.getBoundingClientRect().top +
				(window.pageYOffset || document.documentElement.scrollTop);
			if (top <= marker + 2) current = id;
		});

		setActive(nav, current);
	};

	document.addEventListener(
		"click",
		(event) => {
			const link = event.target.closest(
				".course_details_page [data-scrollspy] a[href^='#']"
			);
			if (!link) return;

			const nav = link.closest("[data-scrollspy]");
			const id = link.getAttribute("href")?.slice(1);
			const el = id ? document.getElementById(id) : null;
			if (!nav || !el) return;

			event.preventDefault();
			scrollToSection(el, nav);
			setActive(nav, id);
		},
		false
	);

	let ticking = false;
	const onScroll = () => {
		if (ticking) return;
		ticking = true;
		requestAnimationFrame(() => {
			updateActiveSection();
			ticking = false;
		});
	};

	window.addEventListener("scroll", onScroll, { passive: true });
	window.addEventListener("resize", updateActiveSection, { passive: true });
	updateActiveSection();
}

window.initCourseScrollspy = initCourseScrollspy;

// ============================================================
// Paid course hero — background video play / pause
// ============================================================
function initCourseHeroVideo() {
	const wrap = document.getElementById("course-hero-video");

	if (!wrap || wrap.dataset.heroVideoInit === "true") {
		return;
	}

	const videoType = wrap.dataset.videoType || "poster";
	const youtubeIframe = document.getElementById("course-hero-youtube");
	const vimeoIframe = document.getElementById("course-hero-vimeo");
	const bunnyIframe = document.getElementById("course-hero-bunny");
	const html5 = document.getElementById("course-hero-html5");
	const toggle = document.getElementById("course-hero-video-toggle");
	const muteToggle = document.getElementById("course-hero-video-mute");

	if (videoType === "poster") {
		return;
	}

	if (!toggle || !muteToggle) {
		return;
	}

	if (!youtubeIframe && !vimeoIframe && !bunnyIframe && !html5) {
		return;
	}

	wrap.dataset.heroVideoInit = "true";

	const iconPlay = toggle.querySelector("[data-icon-play]");
	const iconPause = toggle.querySelector("[data-icon-pause]");
	const iconMuted = muteToggle.querySelector("[data-icon-muted]");
	const iconUnmuted = muteToggle.querySelector("[data-icon-unmuted]");
	let youtubePlayer = null;
	let vimeoPlayer = null;
	let bunnyPlayer = null;
	let youtubeApiReady = false;

	const setPlayingUI = (playing) => {
		toggle.dataset.playing = playing ? "true" : "false";
		iconPlay?.classList.toggle("hidden", playing);
		iconPause?.classList.toggle("hidden", !playing);
		toggle.setAttribute(
			"aria-label",
			playing ? "إيقاف الفيديو" : "تشغيل الفيديو"
		);
	};

	const setMutedUI = (muted) => {
		muteToggle.dataset.muted = muted ? "true" : "false";
		iconMuted?.classList.toggle("hidden", !muted);
		iconUnmuted?.classList.toggle("hidden", muted);
		muteToggle.setAttribute(
			"aria-label",
			muted ? "تشغيل الصوت" : "كتم الصوت"
		);
	};

	const postYouTubeCommand = (func) => {
		youtubeIframe?.contentWindow?.postMessage(
			JSON.stringify({ event: "command", func, args: "" }),
			"*"
		);
	};

	const togglePlayback = async () => {
		if (html5) {
			if (html5.paused) {
				html5.play().catch(() => {});
				setPlayingUI(true);
			} else {
				html5.pause();
				setPlayingUI(false);
			}
			return;
		}

		if (vimeoPlayer) {
			const paused = await vimeoPlayer.getPaused();
			if (paused) {
				await vimeoPlayer.play();
			} else {
				await vimeoPlayer.pause();
			}
			return;
		}

		if (bunnyPlayer) {
			bunnyPlayer.getPaused((paused) => {
				if (paused) {
					bunnyPlayer.play();
					setPlayingUI(true);
				} else {
					bunnyPlayer.pause();
					setPlayingUI(false);
				}
			});
			return;
		}

		const playing = toggle.dataset.playing === "true";

		if (youtubePlayer && typeof youtubePlayer.getPlayerState === "function") {
			const state = youtubePlayer.getPlayerState();
			if (state === window.YT?.PlayerState?.PLAYING) {
				youtubePlayer.pauseVideo();
			} else {
				youtubePlayer.playVideo();
			}
			return;
		}

		if (playing) {
			postYouTubeCommand("pauseVideo");
			setPlayingUI(false);
		} else {
			postYouTubeCommand("playVideo");
			setPlayingUI(true);
		}
	};

	const toggleMute = async () => {
		if (html5) {
			html5.muted = !html5.muted;
			setMutedUI(html5.muted);
			return;
		}

		if (vimeoPlayer) {
			const muted = await vimeoPlayer.getMuted();
			await vimeoPlayer.setMuted(!muted);
			setMutedUI(!muted);
			return;
		}

		if (bunnyPlayer) {
			bunnyPlayer.getMuted((muted) => {
				if (muted) {
					bunnyPlayer.unmute();
					setMutedUI(false);
				} else {
					bunnyPlayer.mute();
					setMutedUI(true);
				}
			});
			return;
		}

		const muted = muteToggle.dataset.muted === "true";

		if (youtubePlayer && typeof youtubePlayer.isMuted === "function") {
			if (youtubePlayer.isMuted()) {
				youtubePlayer.unMute();
				setMutedUI(false);
			} else {
				youtubePlayer.mute();
				setMutedUI(true);
			}
			return;
		}

		if (muted) {
			postYouTubeCommand("unMute");
			setMutedUI(false);
		} else {
			postYouTubeCommand("mute");
			setMutedUI(true);
		}
	};

	const bindYouTubePlayer = () => {
		if (youtubeApiReady || !window.YT?.Player || !youtubeIframe) {
			return;
		}

		youtubeApiReady = true;
		youtubePlayer = new window.YT.Player("course-hero-youtube", {
			events: {
				onStateChange(event) {
					if (event.data === window.YT.PlayerState.PLAYING) {
						setPlayingUI(true);
					}
					if (event.data === window.YT.PlayerState.PAUSED) {
						setPlayingUI(false);
					}
				},
			},
		});
	};

	const loadYouTubeApi = () => {
		if (!youtubeIframe) {
			return;
		}

		if (window.YT?.Player) {
			bindYouTubePlayer();
			return;
		}

		if (window.__courseHeroApiLoading) {
			return;
		}

		window.__courseHeroApiLoading = true;
		const previousReady = window.onYouTubeIframeAPIReady;

		window.onYouTubeIframeAPIReady = () => {
			if (typeof previousReady === "function") {
				previousReady();
			}
			bindYouTubePlayer();
		};

		const tag = document.createElement("script");
		tag.src = "https://www.youtube.com/iframe_api";
		document.head.appendChild(tag);
	};

	const loadScript = (src, globalCheck) =>
		new Promise((resolve) => {
			if (globalCheck()) {
				resolve();
				return;
			}

			const existing = document.querySelector(`script[src="${src}"]`);
			if (existing) {
				existing.addEventListener("load", () => resolve(), { once: true });
				return;
			}

			const tag = document.createElement("script");
			tag.src = src;
			tag.onload = () => resolve();
			document.head.appendChild(tag);
		});

	const initVimeoPlayer = async () => {
		if (!vimeoIframe) {
			return;
		}

		await loadScript(
			"https://player.vimeo.com/api/player.js",
			() => Boolean(window.Vimeo?.Player)
		);

		vimeoPlayer = new window.Vimeo.Player(vimeoIframe);
		vimeoPlayer.on("play", () => setPlayingUI(true));
		vimeoPlayer.on("pause", () => setPlayingUI(false));
		setPlayingUI(true);
		setMutedUI(true);
	};

	const initBunnyPlayer = async () => {
		if (!bunnyIframe) {
			return;
		}

		await loadScript(
			"https://assets.mediadelivery.net/playerjs/player-0.1.0.min.js",
			() => Boolean(window.playerjs?.Player)
		);

		bunnyPlayer = new window.playerjs.Player(bunnyIframe);
		bunnyPlayer.on("play", () => setPlayingUI(true));
		bunnyPlayer.on("pause", () => setPlayingUI(false));
		setPlayingUI(true);
		setMutedUI(true);
	};

	toggle.addEventListener("click", (event) => {
		event.stopPropagation();
		togglePlayback();
	});

	muteToggle.addEventListener("click", (event) => {
		event.stopPropagation();
		toggleMute();
	});

	if (videoType === "youtube") {
		loadYouTubeApi();
		setPlayingUI(true);
		setMutedUI(true);
	} else if (videoType === "vimeo") {
		initVimeoPlayer();
	} else if (videoType === "bunny") {
		initBunnyPlayer();
	} else if (html5) {
		html5.addEventListener("play", () => setPlayingUI(true));
		html5.addEventListener("pause", () => setPlayingUI(false));
		setPlayingUI(!html5.paused);
		setMutedUI(html5.muted);
	}
}

window.initCourseHeroVideo = initCourseHeroVideo;

// ============================================================
// Password show / hide toggles (login, register)
// ============================================================
let passwordToggleListenerBound = false;

function initPasswordToggles() {
	if (passwordToggleListenerBound) {
		return;
	}

	passwordToggleListenerBound = true;

	document.addEventListener("click", (event) => {
		const button = event.target.closest(".password-toggle");

		if (!button?.dataset.target) {
			return;
		}

		const input = document.querySelector(button.dataset.target);

		if (!input) {
			return;
		}

		event.preventDefault();

		const showIcon = button.querySelector(".password-toggle-show");
		const hideIcon = button.querySelector(".password-toggle-hide");
		const revealing = input.type === "password";

		input.type = revealing ? "text" : "password";
		button.setAttribute("aria-pressed", String(revealing));
		button.setAttribute(
			"aria-label",
			revealing ? "إخفاء كلمة المرور" : "إظهار كلمة المرور"
		);

		if (showIcon && hideIcon) {
			showIcon.classList.toggle("hidden", revealing);
			hideIcon.classList.toggle("hidden", !revealing);
		}
	});
}

window.initPasswordToggles = initPasswordToggles;

// ============================================================
// Register account-type tabs (student / instructor / organization)
// ============================================================
let registerAccountTabsInitialized = false;

function initRegisterAccountTabs() {
	if (registerAccountTabsInitialized) {
		return;
	}

	const root = document.querySelector("[data-register-tabs]");

	if (!root) {
		return;
	}

	registerAccountTabsInitialized = true;

	const tabButtons = root.querySelectorAll("[data-register-tab]");
	const panels = root.querySelectorAll("[data-register-panel]");

	const activate = (panelSelector) => {
		tabButtons.forEach((button) => {
			const isActive = button.dataset.registerTab === panelSelector;

			button.classList.toggle("active", isActive);
			button.classList.toggle("bg-primary", isActive);
			button.classList.toggle("text-white", isActive);
			button.classList.toggle("text-primary", !isActive);
			button.setAttribute("aria-selected", isActive ? "true" : "false");
		});

		panels.forEach((panel) => {
			const isActive = `#${panel.id}` === panelSelector;

			panel.classList.toggle("hidden", !isActive);

			const form = panel.querySelector("form");

			if (!form) {
				return;
			}

			form.querySelectorAll("input, select, textarea, button[type='submit']").forEach((control) => {
				if (control.name === "_token") {
					return;
				}

				control.disabled = !isActive;
			});
		});
	};

	tabButtons.forEach((button) => {
		button.addEventListener("click", () => {
			activate(button.dataset.registerTab);
		});
	});

	activate(root.dataset.activeRegisterTab || "#tabs-pill-icon-1");
}

window.initRegisterAccountTabs = initRegisterAccountTabs;

// ============================================================
// INIT on DOM ready
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
	// Load badge count silently on page load
	refreshCartCount();

	// Wire drawer open event
	initCartDrawer();

	// Cart page delete buttons
	initCartPageRemove();

	// Handle all add-to-cart form submissions
	handleAddToCartForms();

	initCourseScrollspy();

	initCourseHeroVideo();

	initPasswordToggles();

	initRegisterAccountTabs();
});

if (document.readyState !== "loading") {
	initCourseScrollspy();
	initCourseHeroVideo();
	initPasswordToggles();
	initRegisterAccountTabs();
}
