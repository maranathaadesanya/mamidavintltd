const MAMIDAV_CART_KEY = "mamidav_cart";
const MAMIDAV_ORDER_EMAIL = "info@mamidavintltd.com";

function getCart() {
  try {
    return JSON.parse(localStorage.getItem(MAMIDAV_CART_KEY)) || [];
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem(MAMIDAV_CART_KEY, JSON.stringify(cart));
  renderCartBadge();
}

function addToCart(id, name, price, category) {
  const qtyInput = document.getElementById("qty-" + id);
  const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;
  const cart = getCart();
  const existing = cart.find((item) => item.id === id);
  if (existing) {
    existing.qty += qty;
  } else {
    cart.push({ id, name, price, category, qty });
  }
  saveCart(cart);
  const btn = document.getElementById("add-" + id);
  if (btn) {
    const original = btn.textContent;
    btn.textContent = "Added!";
    setTimeout(() => { btn.textContent = original; }, 1200);
  }
}

function removeFromCart(id) {
  saveCart(getCart().filter((item) => item.id !== id));
  renderCartTable();
}

function updateCartQty(id, qty) {
  qty = Math.max(1, parseInt(qty, 10) || 1);
  const cart = getCart();
  const item = cart.find((i) => i.id === id);
  if (item) item.qty = qty;
  saveCart(cart);
  renderCartTable();
}

function cartCount() {
  return getCart().reduce((sum, item) => sum + item.qty, 0);
}

function cartTotal() {
  return getCart().reduce((sum, item) => sum + item.qty * item.price, 0);
}

function formatNaira(amount) {
  return "₦" + amount.toLocaleString("en-NG");
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
  const cart = getCart();
  const emptyMsg = document.getElementById("cart-empty");
  const totalEl = document.getElementById("cart-total");
  const placeOrderBtn = document.getElementById("place-order-btn");

  if (cart.length === 0) {
    body.innerHTML = "";
    if (emptyMsg) emptyMsg.style.display = "block";
    if (totalEl) totalEl.style.display = "none";
    if (placeOrderBtn) placeOrderBtn.disabled = true;
    return;
  }

  if (emptyMsg) emptyMsg.style.display = "none";
  if (totalEl) totalEl.style.display = "block";
  if (placeOrderBtn) placeOrderBtn.disabled = false;

  body.innerHTML = cart.map((item) => `
    <tr>
      <td>${item.name}</td>
      <td>${formatNaira(item.price)}</td>
      <td><input type="number" class="qty-input" min="1" value="${item.qty}" onchange="updateCartQty('${item.id}', this.value)"></td>
      <td>${formatNaira(item.price * item.qty)}</td>
      <td><button class="remove-btn" onclick="removeFromCart('${item.id}')">Remove</button></td>
    </tr>
  `).join("");

  if (totalEl) totalEl.textContent = "Total: " + formatNaira(cartTotal());
}

function placeOrder() {
  const cart = getCart();
  if (cart.length === 0) return;
  const lines = cart.map((item) => `- ${item.name} x${item.qty} = ${formatNaira(item.price * item.qty)}`);
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
    "Name:",
    "Phone:",
    "Delivery/Pickup address:",
  ].join("\n");
  const mailto = "mailto:" + MAMIDAV_ORDER_EMAIL +
    "?subject=" + encodeURIComponent("New Order from mamidavintltd.com") +
    "&body=" + encodeURIComponent(body);
  window.location.href = mailto;
}

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

document.addEventListener("DOMContentLoaded", () => {
  renderCartBadge();
  renderCartTable();
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

  document.querySelectorAll(".reveal").forEach(el => {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if(entry.isIntersecting){ entry.target.classList.add("visible"); obs.unobserve(entry.target); }
      });
    }, {threshold:.12});
    obs.observe(el);
  });
});
