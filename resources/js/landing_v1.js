// landing_v1.js
import "flyonui/flyonui";

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

	if (window.HSStaticMethods && window.HSStaticMethods.autoInit) {
		window.HSStaticMethods.autoInit(["toggle-count"]);
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
			if (window.HSStaticMethods && window.HSStaticMethods.autoInit) {
				window.HSStaticMethods.autoInit();
			}
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
// Paid course hero — YouTube play / pause
// ============================================================
function initCourseHeroVideo() {
	const wrap = document.getElementById("course-hero-video");
	const iframe = document.getElementById("course-hero-youtube");
	const toggle = document.getElementById("course-hero-video-toggle");
	const muteToggle = document.getElementById("course-hero-video-mute");

	if (!wrap || !iframe || !toggle || !muteToggle) {
		return;
	}

	if (wrap.dataset.heroVideoInit === "true") {
		return;
	}

	wrap.dataset.heroVideoInit = "true";

	const iconPlay = toggle.querySelector("[data-icon-play]");
	const iconPause = toggle.querySelector("[data-icon-pause]");
	const iconMuted = muteToggle.querySelector("[data-icon-muted]");
	const iconUnmuted = muteToggle.querySelector("[data-icon-unmuted]");
	let player = null;
	let apiReady = false;

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

	const postCommand = (func) => {
		iframe.contentWindow?.postMessage(
			JSON.stringify({ event: "command", func, args: "" }),
			"*"
		);
	};

	const togglePlayback = () => {
		const playing = toggle.dataset.playing === "true";

		if (player && typeof player.getPlayerState === "function") {
			const state = player.getPlayerState();
			if (state === window.YT?.PlayerState?.PLAYING) {
				player.pauseVideo();
			} else {
				player.playVideo();
			}
			return;
		}

		if (playing) {
			postCommand("pauseVideo");
			setPlayingUI(false);
		} else {
			postCommand("playVideo");
			setPlayingUI(true);
		}
	};

	const toggleMute = () => {
		const muted = muteToggle.dataset.muted === "true";

		if (player && typeof player.isMuted === "function") {
			if (player.isMuted()) {
				player.unMute();
				setMutedUI(false);
			} else {
				player.mute();
				setMutedUI(true);
			}
			return;
		}

		if (muted) {
			postCommand("unMute");
			setMutedUI(false);
		} else {
			postCommand("mute");
			setMutedUI(true);
		}
	};

	const bindPlayer = () => {
		if (apiReady || !window.YT?.Player) {
			return;
		}

		apiReady = true;
		player = new window.YT.Player("course-hero-youtube", {
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
		if (window.YT?.Player) {
			bindPlayer();
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
			bindPlayer();
		};

		const tag = document.createElement("script");
		tag.src = "https://www.youtube.com/iframe_api";
		document.head.appendChild(tag);
	};

	toggle.addEventListener("click", (event) => {
		event.stopPropagation();
		togglePlayback();
	});

	muteToggle.addEventListener("click", (event) => {
		event.stopPropagation();
		toggleMute();
	});

	loadYouTubeApi();
	setPlayingUI(true);
	setMutedUI(true);
}

window.initCourseHeroVideo = initCourseHeroVideo;

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
});

if (document.readyState !== "loading") {
	initCourseScrollspy();
	initCourseHeroVideo();
}
