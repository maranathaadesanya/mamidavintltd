console.log("MAMIDAV CART.JS LOADED - VERSION 2026-08-13");

const MAMIDAV_ORDER_EMAIL = "mail@mamidavintltd.com";
const MAMIDAV_LOCAL_CART_KEY = "mamidav_cart";
const API_BASE = "/api/";

let cartState = [];
let currentUser = null;

// ---------- General helpers ----------

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
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(body || {})
  });

  const data = await res.json().catch(() => ({}));

  return {
    ok: res.ok,
    status: res.status,
    data
  };
}

async function apiGet(endpoint) {
  const res = await fetch(API_BASE + endpoint);
  const data = await res.json().catch(() => ({}));

  return {
    ok: res.ok,
    status: res.status,
    data
  };
}

// ---------- Guest (localStorage) cart ----------

function getLocalCart() {
  try {
    return JSON.parse(
      localStorage.getItem(MAMIDAV_LOCAL_CART_KEY)
    ) || [];
  } catch (e) {
    return [];
  }
}

function saveLocalCart(items) {
  localStorage.setItem(
    MAMIDAV_LOCAL_CART_KEY,
    JSON.stringify(items)
  );
}

// ---------- Unified cart ----------

async function loadCart() {
  if (currentUser) {
    const { ok, data } = await apiGet("cart_get.php");

    cartState =
      ok && Array.isArray(data.items)
        ? data.items
        : [];
  } else {
    cartState = getLocalCart();
  }

  renderCartBadge();
  renderCartTable();
}

async function addToCart(id, name, price, category) {
  const qtyInput =
    document.getElementById("qty-" + id);

  const qty = qtyInput
    ? Math.max(
        1,
        parseInt(qtyInput.value, 10) || 1
      )
    : 1;

  if (currentUser) {
    await apiPost("cart_add.php", {
      id,
      qty
    });
  } else {
    const items = getLocalCart();

    const existing = items.find(
      (i) => i.id === id
    );

    if (existing) {
      existing.qty += qty;
    } else {
      items.push({
        id,
        name,
        price,
        category,
        qty
      });
    }

    saveLocalCart(items);
  }

  await loadCart();

  const btn =
    document.getElementById("add-" + id);

  if (btn) {
    const original = btn.textContent;

    btn.textContent = "Added!";

    setTimeout(() => {
      btn.textContent = original;
    }, 1200);
  }
}

async function removeFromCart(id) {
  if (currentUser) {
    await apiPost("cart_remove.php", {
      id
    });
  } else {
    saveLocalCart(
      getLocalCart().filter(
        (i) => i.id !== id
      )
    );
  }

  await loadCart();
}

async function updateCartQty(id, qty) {
  qty = Math.max(
    1,
    parseInt(qty, 10) || 1
  );

  if (currentUser) {
    await apiPost("cart_update.php", {
      id,
      qty
    });
  } else {
    const items = getLocalCart();

    const item = items.find(
      (i) => i.id === id
    );

    if (item) {
      item.qty = qty;
    }

    saveLocalCart(items);
  }

  await loadCart();
}

function cartCount() {
  return cartState.reduce(
    (sum, item) => sum + item.qty,
    0
  );
}

function cartTotal() {
  return cartState.reduce(
    (sum, item) =>
      sum + item.qty * item.price,
    0
  );
}

function renderCartBadge() {
  const count = cartCount();

  document
    .querySelectorAll(".cart-badge")
    .forEach((el) => {
      el.textContent = count;

      el.style.display =
        count > 0
          ? "inline-block"
          : "none";
    });
}

function renderCartTable() {
  const body =
    document.getElementById(
      "cart-body"
    );

  if (!body) return;

  const emptyMsg =
    document.getElementById(
      "cart-empty"
    );

  const totalEl =
    document.getElementById(
      "cart-total"
    );

  const placeOrderBtn =
    document.getElementById(
      "place-order-btn"
    );
  const clearCartBtn =
    document.getElementById(
      "clear-cart-btn"
    );

  if (cartState.length === 0) {
    body.innerHTML = "";

    if (emptyMsg) {
      emptyMsg.style.display = "block";
    }

    if (totalEl) {
      totalEl.style.display = "none";
    }

    if (placeOrderBtn) {
      placeOrderBtn.disabled = true;
    }
    if (clearCartBtn) {
      clearCartBtn.disabled = true;
    }

    return;
  }

  if (emptyMsg) {
    emptyMsg.style.display = "none";
  }

  if (totalEl) {
    totalEl.style.display = "block";
  }

  if (placeOrderBtn) {
    placeOrderBtn.disabled = false;
  }
  if (clearCartBtn) {
    clearCartBtn.disabled = false;
  }

  body.innerHTML = cartState
    .map(
      (item) => `
        <tr>
          <td>${escapeHtml(item.name)}</td>

          <td>
            ${formatNaira(item.price)}
          </td>

          <td>
            <input
              type="number"
              class="qty-input"
              min="1"
              value="${item.qty}"
              onchange="updateCartQty('${escapeHtml(item.id)}', this.value)"
            >
          </td>

          <td>
            ${formatNaira(
              item.price * item.qty
            )}
          </td>

          <td>
            <button
              class="remove-btn"
              onclick="removeFromCart('${escapeHtml(item.id)}')"
            >
              Remove
            </button>
          </td>
        </tr>
      `
    )
    .join("");

  if (totalEl) {
    totalEl.textContent =
      "Total: " +
      formatNaira(cartTotal());
  }
}

// ---------- Orders / Payments ----------

async function placeOrder() {
  if (cartState.length === 0) {
    return;
  }

  const button = document.getElementById("place-order-btn");
  if (button) button.disabled = true;
  try {
    const { ok, data } = await apiPost("checkout_create.php", {});
    if (!ok || !data.reference) throw new Error(data.error || "Could not start checkout.");
    sessionStorage.setItem("mamidav_checkout", JSON.stringify(data));
    window.location.href = "payments.html?order=" + encodeURIComponent(data.reference);
  } catch (error) {
    alert(error.message || "Could not start checkout. Please try again.");
    if (button) button.disabled = false;
  }
}

async function clearCart() {
  if (cartState.length === 0 || !window.confirm("Are you sure you want to clear your cart?")) {
    return;
  }

  if (currentUser) {
    await Promise.all(cartState.map((item) => apiPost("cart_remove.php", { id: item.id })));
  } else {
    saveLocalCart([]);
  }

  await loadCart();
}

function requestPayment(method) {
  const body = [
    "Hello Mamidav International Limited,",
    "",
    `I would like to make a payment via ${method}. Please send me a secure payment link or further instructions.`,
    "",
    "Name:",
    "Amount:",
    "Reason for payment:"
  ].join("\n");

  const mailto =
    "mailto:" +
    MAMIDAV_ORDER_EMAIL +
    "?subject=" +
    encodeURIComponent(
      "Payment via " + method
    ) +
    "&body=" +
    encodeURIComponent(body);

  window.location.href = mailto;
}

// ---------- Inquiry forms ----------

function submitInquiry(form, subject) {
  const data = new FormData(form);

  const lines = [];

  for (const [key, value] of data.entries()) {
    lines.push(
      key + ": " + (value || "Not provided")
    );
  }

  const body =
    "Dear Mamidav International Limited,\n\n" +
    "I would like to make an investment inquiry through your website.\n\n" +
    "INVESTMENT INQUIRY DETAILS\n" +
    "--------------------------\n" +
    lines.join("\n") +
    "\n\n" +
    "Please contact me with further information regarding this investment opportunity.\n\n" +
    "Thank you.";

  const mailto =
    "mailto:" +
    MAMIDAV_ORDER_EMAIL +
    "?subject=" +
    encodeURIComponent(subject) +
    "&body=" +
    encodeURIComponent(body);

  window.location.href = mailto;

  return false;
}

// ---------- Accounts ----------

function renderAuthNav() {
  document
    .querySelectorAll("nav")
    .forEach((nav) => {

      const existing =
        nav.querySelector(
          ".auth-nav"
        );

      if (existing) {
        existing.remove();
      }

      const span =
        document.createElement(
          "span"
        );

      span.className =
        "auth-nav";

      if (currentUser) {

        const firstName =
          escapeHtml(
            (
              currentUser.full_name ||
              ""
            )
              .split(" ")[0] ||
              "Account"
          );

        span.innerHTML = `
          <div class="account-control">

            <button
              class="account-btn"
              type="button"
              aria-expanded="false"
            >
              👤 ${firstName} ▾
            </button>

            <div
              class="account-menu"
              hidden
            >
              ${currentUser.is_admin ? '<a href="dashboard.html">Dashboard</a>' : ''}

              <a href="profile.html">
                My Account
              </a>

              <a
                href="#"
                onclick="doLogout();return false;"
              >
                Logout
              </a>
            </div>

          </div>
        `;

      } else {

        span.innerHTML =
          `<a href="login.html">Login</a>`;
      }

      nav.appendChild(span);

      const accBtn =
        span.querySelector(
          ".account-btn"
        );

      const accMenu =
        span.querySelector(
          ".account-menu"
        );

      if (accBtn && accMenu) {

        accBtn.addEventListener(
          "click",
          (e) => {

            e.stopPropagation();

            const open =
              accMenu.hidden;

            accMenu.hidden = !open;

            accBtn.setAttribute(
              "aria-expanded",
              String(open)
            );
          }
        );

        document.addEventListener(
          "click",
          (e) => {

            if (!span.contains(e.target)) {

              accMenu.hidden = true;

              accBtn.setAttribute(
                "aria-expanded",
                "false"
              );
            }
          }
        );
      }
    });
}

// ---------- Cart icon ----------

function renderCartIcons() {

  const svgNS =
    "http://www.w3.org/2000/svg";

  document
    .querySelectorAll(".cart-link")
    .forEach((a) => {

      if (
        a.querySelector(
          ".cart-icon"
        )
      ) {
        return;
      }

      a.setAttribute(
        "aria-label",
        "Cart"
      );

      const svg =
        document.createElementNS(
          svgNS,
          "svg"
        );

      svg.setAttribute(
        "class",
        "cart-icon"
      );

      svg.setAttribute(
        "viewBox",
        "0 0 24 24"
      );

      svg.setAttribute(
        "fill",
        "none"
      );

      svg.setAttribute(
        "stroke",
        "currentColor"
      );

      svg.setAttribute(
        "stroke-width",
        "1.6"
      );

      svg.setAttribute(
        "stroke-linecap",
        "round"
      );

      svg.setAttribute(
        "stroke-linejoin",
        "round"
      );

      svg.setAttribute(
        "aria-hidden",
        "true"
      );

      const path =
        document.createElementNS(
          svgNS,
          "path"
        );

      path.setAttribute(
        "d",
        "M6 6h15l-1.5 9h-12z"
      );

      const c1 =
        document.createElementNS(
          svgNS,
          "circle"
        );

      c1.setAttribute(
        "cx",
        "9"
      );

      c1.setAttribute(
        "cy",
        "20"
      );

      c1.setAttribute(
        "r",
        "1"
      );

      const c2 =
        document.createElementNS(
          svgNS,
          "circle"
        );

      c2.setAttribute(
        "cx",
        "18"
      );

      c2.setAttribute(
        "cy",
        "20"
      );

      c2.setAttribute(
        "r",
        "1"
      );

      svg.appendChild(path);
      svg.appendChild(c1);
      svg.appendChild(c2);

      const sr =
        document.createElement(
          "span"
        );

      sr.className =
        "sr-only";

      sr.textContent =
        "Cart";

      const badge =
        a.querySelector(
          ".cart-badge"
        );

      a.insertBefore(
        svg,
        badge
      );

      a.insertBefore(
        sr,
        badge
      );

      a.childNodes.forEach(
        (n) => {

          if (
            n.nodeType ===
              Node.TEXT_NODE &&
            n.textContent
              .trim() ===
              "Cart"
          ) {
            n.textContent = "";
          }
        }
      );
    });
}

// ---------- Session ----------

async function checkSession() {

  const { data } =
    await apiGet(
      "session.php"
    );

  currentUser =
    data &&
    data.logged_in
      ? data
      : null;

  renderAuthNav();
}

async function mergeGuestCartIfAny() {

  const guestItems =
    getLocalCart();

  if (
    guestItems.length > 0
  ) {

    const {
      ok,
      data
    } = await apiPost(
      "cart_merge.php",
      {
        items: guestItems
      }
    );

    if (!ok || !data || data.success !== true) {
      throw new Error("Guest cart merge was not accepted by the server.");
    }

    saveLocalCart([]);
    await loadCart();
  }
}

function approvedPostAuthDestination() {
  const next = new URLSearchParams(window.location.search).get("next");
  const allowedDestinations = new Set([
    "cart.html",
    "payments.html",
    "dashboard.html",
    "profile.html"
  ]);

  return allowedDestinations.has(next) ? next : "index.html";
}

// ---------- Form helpers ----------

function formEntries(form) {
  return Object.fromEntries(
    new FormData(form).entries()
  );
}

// ---------- Signup / Verification ----------

let pendingSignup = null;

function setVerificationPanelVisible(
  visible
) {

  const verifyPanel =
    document.getElementById(
      "signup-verify-panel"
    );

  if (!verifyPanel) {

    console.error(
      "MAMIDAV: Verification panel #signup-verify-panel was NOT found in the DOM."
    );

    return false;
  }

  console.log(
    "MAMIDAV: Verification panel found:",
    verifyPanel
  );

  if (visible) {

    verifyPanel.removeAttribute(
      "hidden"
    );

    verifyPanel.setAttribute(
      "aria-hidden",
      "false"
    );

    verifyPanel.style.setProperty(
      "display",
      "block",
      "important"
    );

    verifyPanel.style.visibility =
      "visible";

    verifyPanel.style.opacity =
      "1";

    verifyPanel.style.height =
      "auto";

    verifyPanel.style.minHeight =
      "100px";

    verifyPanel.style.overflow =
      "visible";

    console.log(
      "MAMIDAV: Verification panel is now visible"
    );

  } else {

    verifyPanel.setAttribute(
      "hidden",
      ""
    );

    verifyPanel.setAttribute(
      "aria-hidden",
      "true"
    );

    verifyPanel.style.setProperty(
      "display",
      "none",
      "important"
    );
  }

  return true;
}

function hideVerificationPanel() {
  setVerificationPanelVisible(
    false
  );
}

function showVerificationPanel(email) {

  console.log(
    "MAMIDAV: Showing verification panel for:",
    email
  );

  const signupForm =
    document.getElementById(
      "signup-form"
    );

  const verifyHidden =
    document.getElementById(
      "verify-email-hidden"
    );

  const verifyMessage =
    document.getElementById(
      "verify-message"
    );

  const verifyErrorEl =
    document.getElementById(
      "form-error-verify"
    );

  const verifyInput =
    document.querySelector(
      "#verify-form input[name='code']"
    );

  // Hide original signup form
  if (signupForm) {

    signupForm.setAttribute(
      "hidden",
      ""
    );

    signupForm.style.setProperty(
      "display",
      "none",
      "important"
    );

    console.log(
      "MAMIDAV: Signup form hidden."
    );
  }

  // Show verification panel
  const visible =
    setVerificationPanelVisible(
      true
    );

  if (!visible) {

    console.error(
      "MAMIDAV: Unable to show verification panel."
    );

    return false;
  }

  // Set email
  if (verifyHidden) {
    verifyHidden.value =
      email;
  }

  // Set message
  if (verifyMessage) {

    verifyMessage.textContent =
      `A 6-digit verification code was sent to ${email}.`;

    verifyMessage.style.color =
      "#0a4d32";
  }

  if (verifyErrorEl) {
    verifyErrorEl.textContent =
      "";
  }

  // Scroll panel into view
  const verifyPanel =
    document.getElementById(
      "signup-verify-panel"
    );

  if (verifyPanel) {

    verifyPanel.scrollIntoView({
      behavior: "smooth",
      block: "start"
    });
  }

  // Focus verification input
  if (verifyInput) {

    setTimeout(() => {

      verifyInput.focus();

    }, 300);
  }

  return true;
}

// ---------- Resend verification code ----------

async function resendSignupCode() {

  if (!pendingSignup) {

    console.warn(
      "MAMIDAV: No pending signup available for resend."
    );

    return;
  }

  const verifyMsg =
    document.getElementById(
      "verify-message"
    );

  const verifyErrorEl =
    document.getElementById(
      "form-error-verify"
    );

  const resendBtn =
    document.getElementById(
      "resend-code-btn"
    );

  if (resendBtn) {
    resendBtn.disabled = true;
  }

  try {

    const {
      ok,
      data: res
    } = await apiPost(
      "signup.php",
      {
        action: "send_code",
        full_name:
          pendingSignup.full_name,
        email:
          pendingSignup.email,
        phone:
          pendingSignup.phone,
        password:
          pendingSignup.password
      }
    );

    if (
      !ok ||
      !res ||
      res.success !== true
    ) {

      if (verifyErrorEl) {

        verifyErrorEl.textContent =
          res.error ||
          "Unable to resend the verification code. Please try again.";
      }

      return;
    }

    if (verifyMsg) {

      verifyMsg.textContent =
        `A new verification code was sent to ${pendingSignup.email}.`;

      verifyMsg.style.color =
        "#0a4d32";
    }

    if (verifyErrorEl) {
      verifyErrorEl.textContent =
        "";
    }

  } catch (error) {

    console.error(
      "MAMIDAV: Resend error:",
      error
    );

    if (verifyErrorEl) {

      verifyErrorEl.textContent =
        "Unable to resend the verification code. Please try again.";
    }

  } finally {

    if (resendBtn) {
      resendBtn.disabled = false;
    }
  }
}

// ---------- Main signup function ----------

async function doSignup(form) {

  console.log(
    "MAMIDAV: doSignup() started."
  );

  const errEl =
    document.getElementById(
      "form-error"
    );

  if (errEl) {

    errEl.textContent =
      "";

    errEl.style.color =
      "#a33";
  }

  const formData =
    formEntries(form);

  console.log(
    "MAMIDAV: Signup form submitted.",
    {
      full_name:
        formData.full_name,
      email:
        formData.email
    }
  );

  // Password confirmation
  if (
    formData.password !==
    formData.confirm_password
  ) {

    hideVerificationPanel();

    if (errEl) {

      errEl.textContent =
        "Passwords do not match.";
    }

    return false;
  }

  // Required fields
  if (
    !formData.full_name ||
    !formData.email ||
    !formData.password
  ) {

    hideVerificationPanel();

    if (errEl) {

      errEl.textContent =
        "Please fill in all required fields.";
    }

    return false;
  }

  // Store pending signup
  pendingSignup = {

    full_name:
      formData.full_name
        .trim(),

    email:
      formData.email
        .trim()
        .toLowerCase(),

    phone:
      formData.phone || "",

    password:
      formData.password
  };

  console.log(
    "MAMIDAV: pendingSignup created:",
    {
      full_name:
        pendingSignup.full_name,
      email:
        pendingSignup.email
    }
  );

  try {

    console.log(
      "MAMIDAV: Sending verification code..."
    );

    const {
      ok,
      data
    } = await apiPost(
      "signup.php",
      {
        action:
          "send_code",

        full_name:
          pendingSignup.full_name,

        email:
          pendingSignup.email,

        phone:
          pendingSignup.phone,

        password:
          pendingSignup.password
      }
    );

    console.log(
      "MAMIDAV: signup.php response:",
      {
        ok,
        data
      }
    );

    if (
      !ok ||
      !data ||
      data.success !== true
    ) {

      hideVerificationPanel();

      if (errEl) {

        errEl.textContent =
          (data &&
            data.error) ||
          "Unable to send verification code. Please try again.";

        errEl.style.color =
          "#a33";
      }

      return false;
    }

    // SUCCESS
    if (errEl) {
      errEl.textContent =
        "";
    }

    console.log(
      "MAMIDAV: Verification code successfully sent."
    );

    console.log(
      "MAMIDAV: Showing verification panel for:",
      pendingSignup.email
    );

    showVerificationPanel(
      pendingSignup.email
    );

  } catch (error) {

    console.error(
      "MAMIDAV: Signup request failed:",
      error
    );

    hideVerificationPanel();

    if (errEl) {

      errEl.textContent =
        "Unable to connect to the server. Please try again.";

      errEl.style.color =
        "#a33";
    }

    return false;
  }

  return false;
}

// ---------- Verify signup code ----------

async function doVerifySignupCode(
  form
) {

  console.log(
    "MAMIDAV: Verification form submitted."
  );

  const errEl =
    document.getElementById(
      "form-error-verify"
    );

  if (errEl) {
    errEl.textContent =
      "";
  }

  const email =
    form.email.value ||
    (
      pendingSignup
        ? pendingSignup.email
        : ""
    );

  const code =
    (
      form.code.value ||
      ""
    ).trim();

  if (!email || !code) {

    if (errEl) {

      errEl.textContent =
        "Please enter the verification code.";
    }

    return false;
  }

  console.log(
    "MAMIDAV: Verifying code for:",
    email
  );

  const verifyRes =
    await apiPost(
      "signup.php",
      {
        action:
          "verify_code",

        email,

        verification_code:
          code
      }
    );

  if (
    !verifyRes.ok ||
    !verifyRes.data ||
    verifyRes.data.success !== true
  ) {

    if (errEl) {

      errEl.textContent =
        verifyRes.data.error ||
        "Invalid verification code.";
    }

    return false;
  }

  console.log(
    "MAMIDAV: Verification code accepted."
  );

  const finalizeRes =
    await apiPost(
      "signup.php",
      {
        action:
          "create",

        full_name:
          pendingSignup
            ? pendingSignup.full_name
            : "",

        email,

        phone:
          pendingSignup
            ? pendingSignup.phone
            : "",

        password:
          pendingSignup
            ? pendingSignup.password
            : "",

        verification_code:
          code
      }
    );

  if (
    !finalizeRes.ok ||
    !finalizeRes.data ||
    finalizeRes.data.success !== true
  ) {

    if (errEl) {

      errEl.textContent =
        finalizeRes.data.error ||
        "Unable to create your account.";
    }

    return false;
  }

  currentUser =
    finalizeRes.data;

  pendingSignup =
    null;

  if (errEl) {
    errEl.textContent =
      "";
  }

  const verifyMsg =
    document.getElementById(
      "verify-message"
    );

  if (verifyMsg) {

    verifyMsg.textContent =
      "✓ Signup successful! Welcome to Mamidav International Limited.";

    verifyMsg.style.color =
      "#0a4d32";
  }

  try {

    await mergeGuestCartIfAny();

  } catch (e) {

    console.warn(
      "MAMIDAV: Cart merge failed after signup:",
      e
    );
  }

  setTimeout(() => {

    if (
      typeof approvedPostAuthDestination ===
      "function"
    ) {

      window.location.href =
        approvedPostAuthDestination();

    } else {

      window.location.href =
        "dashboard.html";
    }

  }, 1500);

  return false;
}

// ---------- Login ----------

async function doLogin(form) {

  const errEl =
    document.getElementById(
      "form-error"
    );

  const successEl =
    document.getElementById(
      "form-success"
    );

  if (errEl) {
    errEl.textContent =
      "";
  }

  if (successEl) {
    successEl.textContent =
      "";
  }

  const data =
    formEntries(form);

  const {
    ok,
    data: res
  } = await apiPost(
    "login.php",
    {
      email:
        data.email,

      password:
        data.password
    }
  );

  if (
    !ok ||
    !res ||
    res.success === false
  ) {

    if (errEl) {

      errEl.textContent =
        res.error ||
        "Something went wrong. Please try again.";
    }

    return false;
  }

  currentUser =
    res;

  if (successEl) {

    successEl.textContent =
      "Login successful!";
  }

  try {

    await mergeGuestCartIfAny();

  } catch (e) {

    console.warn(
      "MAMIDAV: Cart merge failed after login:",
      e
    );
  }

  setTimeout(() => {

    if (
      typeof approvedPostAuthDestination ===
      "function"
    ) {

      window.location.href =
        approvedPostAuthDestination();

    } else {

      window.location.href =
        "dashboard.html";
    }

  }, 1500);

  return false;
}

// ---------- Logout ----------

async function doLogout() {

  await apiPost(
    "logout.php",
    {}
  );

  currentUser =
    null;

  window.location.href =
    "index.html";
}

// ---------- Profile ----------

async function doUpdateProfile(
  form
) {

  const errEl =
    document.getElementById(
      "form-error"
    );

  const okEl =
    document.getElementById(
      "form-success"
    );

  if (errEl) {
    errEl.textContent =
      "";
  }

  if (okEl) {
    okEl.textContent =
      "";
  }

  const data =
    formEntries(form);

  const {
    ok,
    data: res
  } = await apiPost(
    "profile_update.php",
    {
      full_name:
        data.full_name,

      phone:
        data.phone
    }
  );

  if (!ok) {

    if (errEl) {

      errEl.textContent =
        res.error ||
        "Something went wrong. Please try again.";
    }

    return false;
  }

  currentUser.full_name =
    data.full_name;

  currentUser.phone =
    data.phone;

  renderAuthNav();

  if (okEl) {

    okEl.textContent =
      "Profile updated.";
  }

  return false;
}

function renderProfilePage() {

  if (!currentUser) {
    return;
  }

  const nameEl =
    document.getElementById(
      "profile-name"
    );

  const emailEl =
    document.getElementById(
      "profile-email"
    );

  if (nameEl) {

    nameEl.textContent =
      currentUser.full_name;
  }

  if (emailEl) {

    emailEl.textContent =
      currentUser.email;
  }

  const form =
    document.getElementById(
      "profile-form"
    );

  if (form) {

    form.full_name.value =
      currentUser.full_name ||
      "";

    form.phone.value =
      currentUser.phone ||
      "";
  }
}

async function loadDashboardSummary(page = 1) {
  const { ok, data } = await apiGet("dashboard_summary.php?page=" + page);
  if (!ok) return;

  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  set("stat-orders", data.total_orders);
  set("stat-revenue", formatNaira(data.total_revenue));
  set("stat-bookings", data.total_event_bookings);
  set("stat-investments", data.total_investment_inquiries);
  set("stat-consultations", data.total_consultation_requests);

  const body = document.getElementById("recent-activity-body");
  if (!body) return;

  if (!data.recent || data.recent.length === 0) {
    body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--muted)">No activity yet.</td></tr>';
    return;
  }

  const typeLabels = {
    order: "Order",
    investment_inquiry: "Investment Inquiry",
    event_booking: "Event Booking",
    consultation_request: "Consultation",
  };

  body.innerHTML = data.recent.map((row) => `
    <tr>
      <td>${escapeHtml(row.created_at)}</td>
      <td>${escapeHtml(typeLabels[row.type] || row.type)}</td>
      <td>${escapeHtml(row.customer_name || "-")}</td>
      <td>${escapeHtml(row.summary)}</td>
      <td>${row.amount != null ? formatNaira(row.amount) : "-"}</td>
    </tr>
  `).join("");

  const pager = document.getElementById("recent-activity-pages");
  if (pager) {
    const current = data.recent_page || 1;
    const pages = data.recent_pages || 1;
    const numberButtons = Array.from({ length: pages }, (_, i) => i + 1).map((number) => `<button type="button" class="${number === current ? "active" : ""}" ${number === current ? 'aria-current="page"' : ""} onclick="loadDashboardSummary(${number})">${number}</button>`).join("");
    pager.innerHTML = `<span>Showing ${data.recent_total ? ((current - 1) * 10 + 1) : 0}–${Math.min(current * 10, data.recent_total || 0)} of ${data.recent_total || 0}</span><button type="button" ${current === 1 ? "disabled" : ""} onclick="loadDashboardSummary(${current - 1})">Previous</button>${numberButtons}<button type="button" ${current === pages ? "disabled" : ""} onclick="loadDashboardSummary(${current + 1})">Next</button>`;
  }
}

async function loadMyActivity() {
  const body = document.getElementById("my-activity-body");
  if (!body) return;

  const { ok, data } = await apiGet("my_activity.php");
  if (!ok || !data.activity || data.activity.length === 0) {
    body.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--muted)">No activity yet - your orders and inquiries will show up here.</td></tr>';
    return;
  }

  const typeLabels = {
    order: "Order",
    investment_inquiry: "Investment Inquiry",
    event_booking: "Event Booking",
    consultation_request: "Consultation",
  };

  body.innerHTML = data.activity.map((row) => `
    <tr>
      <td>${escapeHtml(row.created_at)}</td>
      <td>${escapeHtml(typeLabels[row.type] || row.type)}</td>
      <td>${escapeHtml(row.summary)}</td>
      <td>${row.amount != null ? formatNaira(row.amount) : "-"}</td>
    </tr>
  `).join("");
}

// ---------- DOM initialization ----------

document.addEventListener(
  "DOMContentLoaded",
  async () => {

    console.log(
      "MAMIDAV: DOM initialization started."
    );

    // ----------------------------------------
    // SIGNUP FORM
    // ----------------------------------------

    const signupForm = document.getElementById("signup-form");

if (signupForm) {

  signupForm.addEventListener("submit", (event) => {

    event.preventDefault();

    void doSignup(signupForm);

  });

  hideVerificationPanel();
}

    // ----------------------------------------
    // VERIFICATION FORM
    // ----------------------------------------

    const verifyForm =
      document.getElementById(
        "verify-form"
      );

    console.log(
      "MAMIDAV: verifyForm:",
      verifyForm
    );

    if (verifyForm) {

      console.log(
        "MAMIDAV: Verification form detected."
      );

      verifyForm.addEventListener(
        "submit",
        (event) => {

          event.preventDefault();

          console.log(
            "MAMIDAV: VERIFY SUBMIT EVENT FIRED."
          );

          void doVerifySignupCode(
            verifyForm
          );
        }
      );
    }

    // ----------------------------------------
    // RESEND BUTTON
    // ----------------------------------------

    const resendCodeBtn =
      document.getElementById(
        "resend-code-btn"
      );

    if (resendCodeBtn) {

      console.log(
        "MAMIDAV: Resend button detected."
      );

      resendCodeBtn.addEventListener(
        "click",
        (event) => {

          event.preventDefault();

          void resendSignupCode();
        }
      );
    }

    // ----------------------------------------
    // SIGNUP -> PAYMENTS REDIRECT
    // ----------------------------------------

    const signupLink =
      document.getElementById(
        "signup-link"
      );

    const next =
      new URLSearchParams(
        window.location.search
      ).get("next");

    if (
      signupLink &&
      (next === "cart.html" || next === "payments.html")
    ) {

      signupLink.href =
        "signup.html?next=" + encodeURIComponent(next);
    }

    // ----------------------------------------
    // SESSION
    // ----------------------------------------

    await checkSession();

    if (!(currentUser && currentUser.is_admin)) {
      document.querySelectorAll('footer a[href="dashboard.html"]').forEach((el) => {
        el.remove();
      });
    }

    if (
      document.body.dataset.requireLogin ===
        "true" &&
      !currentUser
    ) {

      window.location.href =
        "login.html";

      return;
    }

    if (
      document.body.dataset.requireAdmin === "true" &&
      !(currentUser && currentUser.is_admin)
    ) {

      window.location.href = "profile.html";

      return;
    }

    renderProfilePage();

    if (document.getElementById("stat-orders")) {
      await loadDashboardSummary();
    }

    if (document.getElementById("my-activity-body")) {
      await loadMyActivity();
    }

    await loadCart();

    console.log(
      "MAMIDAV: DOM initialization completed."
    );
  }
);

// ---------- Navigation / mobile menu ----------

document.addEventListener(
  "DOMContentLoaded",
  () => {

    renderCartIcons();

    const toggle =
      document.querySelector(
        ".menu-toggle"
      );

    const nav =
      document.querySelector(
        "header nav"
      );

    if (toggle && nav && nav.dataset.navigationReady !== "true") {

      toggle.addEventListener(
        "click",
        () => {

          const open =
            nav.classList.toggle(
              "open"
            );

          toggle.setAttribute(
            "aria-expanded",
            String(open)
          );
        }
      );

      nav
        .querySelectorAll("a")
        .forEach((a) => {

          a.addEventListener(
            "click",
            () => {

              nav.classList.remove(
                "open"
              );

              toggle.setAttribute(
                "aria-expanded",
                "false"
              );
            }
          );
        });
    }

    // ----------------------------------------
    // Active navigation item
    // ----------------------------------------

    const page =
      location.pathname
        .split("/")
        .pop() ||
      "index.html";

    document
      .querySelectorAll(
        "header nav a"
      )
      .forEach((a) => {

        const href =
          (
            a.getAttribute(
              "href"
            ) || ""
          ).split("#")[0];

        if (
          href === page &&
          !a.classList.contains(
            "cart-link"
          )
        ) {

          a.classList.add(
            "active"
          );
        }
      });

    // ----------------------------------------
    // Reveal animation
    // ----------------------------------------

    const revealEls =
      document.querySelectorAll(
        ".reveal"
      );

    const prefersReducedMotion =
      window.matchMedia &&
      window.matchMedia(
        "(prefers-reduced-motion: reduce)"
      ).matches;

    if (
      !(
        "IntersectionObserver" in
        window
      ) ||
      prefersReducedMotion
    ) {

      revealEls.forEach(
        (el) => {

          el.classList.add(
            "visible"
          );
        }
      );

    } else {

      revealEls.forEach(
        (el) => {

          const obs =
            new IntersectionObserver(
              (entries) => {

                entries.forEach(
                  (entry) => {

                    if (
                      entry.isIntersecting
                    ) {

                      entry.target.classList.add(
                        "visible"
                      );

                      obs.unobserve(
                        entry.target
                      );
                    }
                  }
                );

              },
              {
                threshold: 0.12
              }
            );

          obs.observe(el);
        }
      );

      // Safety net
      setTimeout(
        () => {

          revealEls.forEach(
            (el) => {

              el.classList.add(
                "visible"
              );
            }
          );

        },
        2000
      );
    }
  }
);

// Orders administration and customer order history use the existing APIs and
// are only activated on their respective pages.
document.addEventListener('DOMContentLoaded', () => {
  const page = location.pathname.split('/').pop();
  const money = (n) => formatNaira(Number(n || 0));
  const pager = (page, pages, fn) => `<button ${page===1?'disabled':''} onclick="${fn}(${page-1})">← Previous</button>${Array.from({length:pages},(_,i)=>i+1).map(n=>`<button class="${n===page?'active':''}" onclick="${fn}(${n})">${n}</button>`).join('')}<button ${page===pages?'disabled':''} onclick="${fn}(${page+1})">Next →</button>`;
  if (page === 'orders.html') {
    document.querySelector('main').innerHTML = `<p><a href="dashboard.html">← Dashboard</a></p><h2>Order History</h2><button class="btn" id="manual-order-toggle">Add Manual Order</button><form id="manual-order-form" class="form-card" hidden><h3>Manual Order</h3><label>Customer name<input name="customer_name" required></label><label>Phone<input name="customer_phone"></label><label>Email<input name="customer_email" type="email"></label><label>Address<textarea name="delivery_address"></textarea></label><label>Product/service<input name="item_name" required></label><label>Quantity<input name="qty" type="number" min="1" value="1" required></label><label>Unit price<input name="unit_price" type="number" min="0" required></label><label>Discount<input name="discount" type="number" min="0" value="0"></label><label>Payment method<select name="payment_method"><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="other">Other</option></select></label><label>Payment status<select name="payment_status"><option value="pending">Pending</option><option value="paid">Paid</option><option value="failed">Failed</option></select></label><label>Source<select name="order_source"><option value="phone">Phone</option><option value="whatsapp">WhatsApp</option><option value="walk_in">Walk-in</option><option value="face_to_face">Face-to-face</option><option value="other">Other</option></select></label><label>Notes<textarea name="notes"></textarea></label><button class="btn">Save Manual Order</button></form><div class="card"><input id="oq" placeholder="Search reference, customer, email or phone"><select id="ops"><option value="">Payment status</option><option>pending</option><option>paid</option><option>failed</option></select><select id="oos"><option value="">Order status</option><option value="pending_payment">Pending payment</option><option>confirmed</option><option>processing</option><option>completed</option><option>cancelled</option></select><select id="osrc"><option value="">Source</option><option>online</option><option>phone</option><option>whatsapp</option><option value="walk_in">walk-in</option><option value="face_to_face">face-to-face</option><option>other</option></select><select id="opm"><option value="">Payment method</option><option>bank_transfer</option><option>cash</option><option>paystack</option></select><input id="odf" type="date"><input id="odt" type="date"><button class="btn" id="apply-orders">Apply Filters</button><button id="clear-orders">Clear Filters</button><a id="orders-export" class="btn-outline-dark">Export CSV</a></div><div class="table-wrap"><table class="cart-table"><thead><tr><th>Reference</th><th>Customer</th><th>Source</th><th>Payment</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody id="orders-body"></tbody></table></div><div id="orders-pages" class="cart-actions"></div><div id="order-detail"></div>`;
    let current=1; const params=()=>new URLSearchParams({q:oq.value,payment_status:ops.value,order_status:oos.value,order_source:osrc.value,payment_method:opm.value,date_from:odf.value,date_to:odt.value}); window.loadOrders=async n=>{current=n;const p=params();p.set('page',n);const d=await (await fetch('/api/orders_list.php?'+p)).json();orders_body.innerHTML=d.orders.map(o=>`<tr><td>${o.reference}</td><td>${o.customer_name}</td><td>${o.order_source}</td><td>${o.payment_method||'-'}</td><td>${o.payment_status}/${o.order_status}</td><td>${money(o.total)}</td><td><button onclick="showOrder(${o.id})">Details</button></td></tr>`).join('')||'<tr><td colspan="7">No orders found.</td></tr>';orders_pages.innerHTML=pager(d.page,d.pages,'loadOrders');orders_export.href='/api/orders_export.php?'+params()};window.showOrder=async id=>{const d=await(await fetch('/api/order_detail.php?id='+id)).json(),o=d.order;order_detail.innerHTML=`<div class="card"><h3>${o.reference}</h3><p>${o.customer_name}<br>${o.customer_email||''}<br>${o.customer_phone||''}</p><p>${d.items.map(i=>`${i.item_name} × ${i.qty} — ${money(i.subtotal)}`).join('<br>')}</p><p>Subtotal ${money(o.subtotal)} · Discount ${money(o.discount)} · Total ${money(o.total)}</p><p>${o.payment_method||'-'} / ${o.payment_status} · ${o.order_status}<br>Reference: ${o.payment_reference||o.reference}<br>Created: ${o.created_at}<br>Verified: ${o.verifier_name||'-'} ${o.verified_at||''}<br>${o.notes||''}</p></div>`};apply_orders.onclick=()=>loadOrders(1);clear_orders.onclick=()=>{document.querySelectorAll('#oq,#ops,#oos,#osrc,#opm,#odf,#odt').forEach(x=>x.value='');loadOrders(1)};manual_order_toggle.onclick=()=>manual_order_form.hidden=!manual_order_form.hidden;manual_order_form.onsubmit=async e=>{e.preventDefault();const d=Object.fromEntries(new FormData(e.target));const r=await fetch('/api/manual_order_create.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});if(!r.ok)return alert((await r.json()).error);e.target.reset();e.target.hidden=true;loadOrders(1)};loadOrders(1);
  }
  if (page === 'profile.html') { const host=document.querySelector('.container'); const section=document.createElement('section');section.innerHTML='<div style="margin-top:36px"><h3>My Orders</h3><div class="table-wrap"><table class="cart-table"><thead><tr><th>Reference</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead><tbody id="my-orders-body"></tbody></table></div><div id="my-orders-pages" class="cart-actions"></div></div>';host.append(section);window.loadMyOrders=async n=>{const d=await(await fetch('/api/my_orders.php?page='+n)).json();my_orders_body.innerHTML=d.orders.map(o=>`<tr><td>${o.reference}</td><td>${o.created_at}</td><td>${money(o.total)}</td><td>${o.payment_method||'-'}</td><td>${o.payment_status}/${o.order_status}</td></tr>`).join('');my_orders_pages.innerHTML=pager(d.page,d.pages,'loadMyOrders')};loadMyOrders(1); }
});
