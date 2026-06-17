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
			drawerBadge.classList.add("flex");
		} else {
			drawerBadge.classList.add("hidden");
			drawerBadge.classList.remove("flex");
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
		<div class="flex flex-col items-center justify-center h-64 gap-3 text-primary/40">
			<span class="icon-[tabler--loader-2] size-8 animate-spin"></span>
			<p class="font-medium text-14px">جاري التحميل...</p>
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

			// Count items
			const count = drawerBody.querySelectorAll("[data-cart-item-id]").length;
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
			<div class="flex flex-col items-center justify-center h-64 gap-3 text-primary/40">
				<span class="icon-[tabler--alert-circle] size-8"></span>
				<p class="font-medium text-14px">حدث خطأ، يرجى المحاولة مجدداً</p>
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
			// Count from HTML
			const parser = new DOMParser();
			const doc = parser.parseFromString(data.html, "text/html");
			const count = doc.querySelectorAll("[data-cart-item-id]").length;
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
		const response = await fetch(`/cart/${cartItemId}/delete`, {
			headers: { "X-Requested-With": "XMLHttpRequest" },
		});
		const data = await response.json();

		if (data.code === 200) {
			if (isOnCartPage()) {
				// Reload so the server recalculates everything correctly
				window.location.reload();
			} else {
				await loadCartDrawer();
				showCartToast("تم الحذف", "تم إزالة الدورة من السلة", "info");
			}
		}
	} catch (_) {}
}

/**
 * Returns true when the current page is the landing-v1 cart page.
 */
function isOnCartPage() {
	return window.location.pathname.includes('/landing-v1/cart');
}

/**
 * Bind remove buttons inside the dynamically loaded drawer HTML
 */
function bindDrawerRemoveButtons() {
	const btns = document.querySelectorAll("[data-cart-remove]");
	btns.forEach((btn) => {
		btn.addEventListener("click", async (e) => {
			e.preventDefault();
			const id = btn.getAttribute("data-cart-remove");
			btn.disabled = true;
			btn.innerHTML = '<span class="icon-[tabler--loader-2] size-4 animate-spin"></span>';
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
				showCartToast(
					data.title || "تمت الإضافة",
					data.msg || "تمت إضافة الدورة إلى سلة التسوق",
					"success"
				);

				if (isOnCartPage()) {
					// Reload so the cart page reflects the new item and recalculated totals
					window.location.reload();
				} else {
					// Refresh the badge count
					await refreshCartCount();

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
			if (btn) {
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
// INIT on DOM ready
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
	// Load badge count silently on page load
	refreshCartCount();

	// Wire drawer open event
	initCartDrawer();

	// Handle all add-to-cart form submissions
	handleAddToCartForms();
});
