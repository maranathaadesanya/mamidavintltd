const MAMIDAV_ORDER_EMAIL = "mail@mamidavintltd.com";
const MAMIDAV_LOCAL_CART_KEY = "mamidav_cart";
const API_BASE = "/api/";

let cartState = [];
let currentUser = null;

function formatNaira(amount) {
  return "₦" + amount.toLocaleString("en-NG");
}

function escapeHtml(str) {
  const div = document.createElement("div");
  div.textContent = str == null ? "" : String(str);
  return div.innerHTML;
}

async function apiPost(endpoint, body) {
  const res = await fetch(API_BASE + endpoint, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body || {}),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

async function apiGet(endpoint) {
  const res = await fetch(API_BASE + endpoint);
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

// ---------- Guest (localStorage) cart ----------
function getLocalCart() {
  try {
    return JSON.parse(localStorage.getItem(MAMIDAV_LOCAL_CART_KEY)) || [];
  } catch (e) {
    return [];
  }
}
function saveLocalCart(items) {
  localStorage.setItem(MAMIDAV_LOCAL_CART_KEY, JSON.stringify(items));
}

// ---------- Unified cart (server when logged in, localStorage as guest) ----------
async function loadCart() {
  if (currentUser) {
    const { ok, data } = await apiGet("cart_get.php");
    cartState = ok && Array.isArray(data.items) ? data.items : [];
  } else {
    cartState = getLocalCart();
  }
  renderCartBadge();
  renderCartTable();
}

async function addToCart(id, name, price, category) {
  const qtyInput = document.getElementById("qty-" + id);
  const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;

  if (currentUser) {
    await apiPost("cart_add.php", { id, qty });
  } else {
    const items = getLocalCart();
    const existing = items.find((i) => i.id === id);
    if (existing) existing.qty += qty;
    else items.push({ id, name, price, category, qty });
    saveLocalCart(items);
  }
  await loadCart();

  const btn = document.getElementById("add-" + id);
  if (btn) {
    const original = btn.textContent;
    btn.textContent = "Added!";
    setTimeout(() => { btn.textContent = original; }, 1200);
  }
}

async function removeFromCart(id) {
  if (currentUser) {
    await apiPost("cart_remove.php", { id });
  } else {
    saveLocalCart(getLocalCart().filter((i) => i.id !== id));
  }
  await loadCart();
}

async function updateCartQty(id, qty) {
  qty = Math.max(1, parseInt(qty, 10) || 1);
  if (currentUser) {
    await apiPost("cart_update.php", { id, qty });
  } else {
    const items = getLocalCart();
    const item = items.find((i) => i.id === id);
    if (item) item.qty = qty;
    saveLocalCart(items);
  }
  await loadCart();
}

function cartCount() {
  return cartState.reduce((sum, item) => sum + item.qty, 0);
}
function cartTotal() {
  return cartState.reduce((sum, item) => sum + item.qty * item.price, 0);
}

function renderCartBadge() {
  const count = cartCount();
  document.querySelectorAll(".cart-badge").forEach((el) => {
    el.textContent = count;
    el.style.display = count > 0 ? "inline-block" : "none";
  });
}

function renderCartTable() {
  const body = document.getElementById("cart-body");
  if (!body) return;
  const emptyMsg = document.getElementById("cart-empty");
  const totalEl = document.getElementById("cart-total");
  const placeOrderBtn = document.getElementById("place-order-btn");

  if (cartState.length === 0) {
    body.innerHTML = "";
    if (emptyMsg) emptyMsg.style.display = "block";
    if (totalEl) totalEl.style.display = "none";
    if (placeOrderBtn) placeOrderBtn.disabled = true;
    return;
  }

  if (emptyMsg) emptyMsg.style.display = "none";
  if (totalEl) totalEl.style.display = "block";
  if (placeOrderBtn) placeOrderBtn.disabled = false;

  body.innerHTML = cartState.map((item) => `
    <tr>
      <td>${escapeHtml(item.name)}</td>
      <td>${formatNaira(item.price)}</td>
      <td><input type="number" class="qty-input" min="1" value="${item.qty}" onchange="updateCartQty('${escapeHtml(item.id)}', this.value)"></td>
      <td>${formatNaira(item.price * item.qty)}</td>
      <td><button class="remove-btn" onclick="removeFromCart('${escapeHtml(item.id)}')">Remove</button></td>
    </tr>
  `).join("");

  if (totalEl) totalEl.textContent = "Total: " + formatNaira(cartTotal());
}

function placeOrder() {
  if (cartState.length === 0) return;
  const lines = cartState.map((item) => `- ${item.name} x${item.qty} = ${formatNaira(item.price * item.qty)}`);
  const body = [
    "Hello Mamidav International Limited,",
    "",
    "I would like to place the following order:",
    "",
    ...lines,
    "",
    "Total: " + formatNaira(cartTotal()),
    "",
    "My contact details:",
    "Name: " + (currentUser ? currentUser.full_name : ""),
    "Phone: " + (currentUser ? (currentUser.phone || "") : ""),
    "Delivery/Pickup address:",
  ].join("\n");
  const mailto = "mailto:" + MAMIDAV_ORDER_EMAIL +
    "?subject=" + encodeURIComponent("New Order from mamidavintltd.com") +
    "&body=" + encodeURIComponent(body);
  window.location.href = mailto;
}

function requestPayment(method) {
  const body = [
    "Hello Mamidav International Limited,",
    "",
    `I would like to make a payment via ${method}. Please send me a secure payment link or further instructions.`,
    "",
    "Name:",
    "Amount:",
    "Reason for payment:",
  ].join("\n");
  const mailto = "mailto:" + MAMIDAV_ORDER_EMAIL +
    "?subject=" + encodeURIComponent("Payment via " + method) +
    "&body=" + encodeURIComponent(body);
  window.location.href = mailto;
}

// ---------- Inquiry forms (investment/booking/consultation — unchanged, mailto-based) ----------
function submitInquiry(form, subject) {
  const data = new FormData(form);
  const lines = [];
  for (const [key, value] of data.entries()) {
    lines.push(key + ": " + (value || "-"));
  }
  const mailto = "mailto:" + MAMIDAV_ORDER_EMAIL +
    "?subject=" + encodeURIComponent(subject) +
    "&body=" + encodeURIComponent(lines.join("\n"));
  window.location.href = mailto;
  return false;
}

// ---------- Accounts ----------
function renderAuthNav() {
  document.querySelectorAll("nav").forEach((nav) => {
    const existing = nav.querySelector(".auth-nav");
    if (existing) existing.remove();
    const span = document.createElement("span");
    span.className = "auth-nav";
    if (currentUser) {
      const firstName = escapeHtml((currentUser.full_name || "").split(" ")[0] || "Account");
      span.innerHTML = `<a href="profile.html">Hi, ${firstName}</a> <a href="#" onclick="doLogout();return false;">Logout</a>`;
    } else {
      span.innerHTML = `<a href="login.html">Login</a> <a href="signup.html">Sign Up</a>`;
    }
    nav.appendChild(span);
  });
}

async function checkSession() {
  const { data } = await apiGet("session.php");
  currentUser = data && data.logged_in ? data : null;
  renderAuthNav();
}

async function mergeGuestCartIfAny() {
  const guestItems = getLocalCart();
  if (guestItems.length > 0) {
    await apiPost("cart_merge.php", { items: guestItems });
    saveLocalCart([]);
  }
}

function formEntries(form) {
  return Object.fromEntries(new FormData(form).entries());
}

async function doSignup(form) {
  const errEl = document.getElementById("form-error");
  if (errEl) errEl.textContent = "";
  const data = formEntries(form);
  if (data.password !== data.confirm_password) {
    if (errEl) errEl.textContent = "Passwords do not match.";
    return false;
  }
  const { ok, data: res } = await apiPost("signup.php", {
    full_name: data.full_name,
    email: data.email,
    phone: data.phone,
    password: data.password,
  });
  if (!ok) {
    if (errEl) errEl.textContent = res.error || "Something went wrong. Please try again.";
    return false;
  }
  currentUser = res;
  await mergeGuestCartIfAny();
  window.location.href = "profile.html";
  return false;
}

async function doLogin(form) {
  const errEl = document.getElementById("form-error");
  if (errEl) errEl.textContent = "";
  const data = formEntries(form);
  const { ok, data: res } = await apiPost("login.php", {
    email: data.email,
    password: data.password,
  });
  if (!ok) {
    if (errEl) errEl.textContent = res.error || "Something went wrong. Please try again.";
    return false;
  }
  currentUser = res;
  await mergeGuestCartIfAny();
  window.location.href = "profile.html";
  return false;
}

async function doLogout() {
  await apiPost("logout.php", {});
  currentUser = null;
  window.location.href = "index.html";
}

async function doUpdateProfile(form) {
  const errEl = document.getElementById("form-error");
  const okEl = document.getElementById("form-success");
  if (errEl) errEl.textContent = "";
  if (okEl) okEl.textContent = "";
  const data = formEntries(form);
  const { ok, data: res } = await apiPost("profile_update.php", {
    full_name: data.full_name,
    phone: data.phone,
  });
  if (!ok) {
    if (errEl) errEl.textContent = res.error || "Something went wrong. Please try again.";
    return false;
  }
  currentUser.full_name = data.full_name;
  currentUser.phone = data.phone;
  renderAuthNav();
  if (okEl) okEl.textContent = "Profile updated.";
  return false;
}

function renderProfilePage() {
  if (!currentUser) return;
  const nameEl = document.getElementById("profile-name");
  const emailEl = document.getElementById("profile-email");
  if (nameEl) nameEl.textContent = currentUser.full_name;
  if (emailEl) emailEl.textContent = currentUser.email;
  const form = document.getElementById("profile-form");
  if (form) {
    form.full_name.value = currentUser.full_name || "";
    form.phone.value = currentUser.phone || "";
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  await checkSession();
  if (document.body.dataset.requireLogin === "true" && !currentUser) {
    window.location.href = "login.html";
    return;
  }
  renderProfilePage();
  await loadCart();
});


document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.querySelector(".menu-toggle");
  const nav = document.querySelector("header nav");
  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      const open = nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", String(open));
    });
    nav.querySelectorAll("a").forEach(a => a.addEventListener("click", () => {
      nav.classList.remove("open");
      toggle.setAttribute("aria-expanded","false");
    }));
  }

  const page = location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll("header nav a").forEach(a => {
    const href=(a.getAttribute("href")||"").split("#")[0];
    if (href === page && !a.classList.contains("cart-link")) a.classList.add("active");
  });

  const revealEls = document.querySelectorAll(".reveal");
  const prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (!("IntersectionObserver" in window) || prefersReducedMotion) {
    revealEls.forEach(el => el.classList.add("visible"));
  } else {
    revealEls.forEach(el => {
      const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) { entry.target.classList.add("visible"); obs.unobserve(entry.target); }
        });
      }, {threshold:.12});
      obs.observe(el);
    });
    // Safety net: never leave content permanently invisible if the observer
    // doesn't fire in time (e.g. page loaded in a background/hidden tab).
    setTimeout(() => revealEls.forEach(el => el.classList.add("visible")), 2000);
  }
});
