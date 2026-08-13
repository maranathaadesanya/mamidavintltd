# MAMIDAV INTERNATIONAL LIMITED

> **Integrated Agribusiness, Engineering & Services**

A responsive, multi-page corporate website for **Mamidav International Limited**, bringing the company's agriculture, engineering, events, bakery/catering, investment and customer-service offerings together in one digital platform.

The project is intentionally lightweight: it uses **HTML, CSS and vanilla JavaScript**, with no frontend framework or package manager required.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Key Features](#key-features)
- [Business Areas](#business-areas)
- [Project Structure](#project-structure)
- [Technology Stack](#technology-stack)
- [Page Guide](#page-guide)
- [Navigation](#navigation)
- [Responsive Design](#responsive-design)
- [Cart System](#cart-system)
- [Inquiry and Email Workflows](#inquiry-and-email-workflows)
- [Visual Design System](#visual-design-system)
- [Getting Started](#getting-started)
- [Local Development](#local-development)
- [Testing Checklist](#testing-checklist)
- [Deployment](#deployment)
- [Content and Asset Management](#content-and-asset-management)
- [Security and Production Considerations](#security-and-production-considerations)
- [Known Limitations](#known-limitations)
- [Future Improvements](#future-improvements)
- [Maintenance Guide](#maintenance-guide)
- [License and Ownership](#license-and-ownership)

---

## Project Overview

Mamidav International Limited's website is designed as a **corporate and service-oriented web presence** rather than a single landing page.

The current implementation combines:

- Corporate information
- Agriculture and farm services
- Engineering services
- Event management
- Bakery and pastry products
- Rentals
- Agricultural ordering
- Farm investment inquiries
- Engineering consultation requests
- Customer cart functionality
- Payment navigation
- Investor information
- Contact information
- A dashboard placeholder

The visual redesign follows a more sophisticated corporate presentation while retaining Mamidav's established visual identity, navigation structure and service-oriented usability.

---

## Key Features

### Corporate Website

- Responsive corporate homepage
- Branded header and footer
- Consistent navigation across the main pages
- Active-page navigation state
- Responsive mobile navigation
- Hamburger menu for smaller screens
- Smooth visual reveal animations
- Mobile, tablet and desktop layouts

### Service Presentation

Dedicated pages for:

- Agriculture
- Engineering
- Events
- Bakery and pastries
- Rentals
- General services
- Farm ordering
- Engineering consultation

### Customer Cart

The project includes a lightweight browser-based cart system supporting:

- Add to cart
- Quantity selection
- Quantity updates
- Remove from cart
- Cart item count
- Cart total
- Naira currency formatting
- Order preparation through the user's email application

### Inquiry Workflows

The site includes inquiry forms for selected services, including:

- Farm investment
- Event management
- Engineering consultation

The forms use the browser's `mailto:` mechanism to prepare an email containing the submitted information.

### Investor Area

The investor section provides navigation to:

- Farm investment
- Payments
- Investment-related inquiries

### Responsive Navigation

On desktop, the full navigation is displayed.

On smaller screens, the navigation collapses into a hamburger menu:

```text
☰
```

When opened:

```text
Home
Agriculture
Engineering
Services
Investors
Dashboard
Contact
Cart
```

The menu can be closed by:

- Clicking the hamburger again
- Selecting a navigation link
- Clicking outside the menu
- Returning to a desktop viewport

---

# Business Areas

## Agriculture

The agriculture section provides access to the farm-related offerings and ordering/investment workflows.

Related pages:

- `agriculture.html`
- `farm-order.html`
- `farm-invest.html`

---

## Engineering

The engineering section presents engineering-related services and provides access to consultation.

Related pages:

- `engineering.html`
- `consultation.html`

---

## Events

The events section provides event-related services, packages and rental options.

Related pages:

- `event-packages.html`
- `rentals.html`

---

## Bakery & Catering

The bakery section presents available bakery/pastry products and integrates with the site's cart functionality.

Related page:

- `bakery.html`

---

## Investment

The investor workflow provides information and routes visitors toward investment inquiries and payments.

Related pages:

- `investors.html`
- `farm-invest.html`
- `payments.html`

---

# Project Structure

```text
mamidavintltd/
│
├── index.html
├── agriculture.html
├── engineering.html
├── services.html
├── investors.html
├── dashboard.html
├── contact.html
│
├── bakery.html
├── event-packages.html
├── rentals.html
│
├── farm-order.html
├── farm-invest.html
├── consultation.html
├── payments.html
├── cart.html
│
├── style.css
├── cart.js
├── logo.png
│
├── README.md
└── REDESIGN_NOTES.md
```

---

# Technology Stack

The project intentionally avoids unnecessary framework dependencies.

### Frontend

| Technology | Purpose |
|---|---|
| HTML5 | Page structure and content |
| CSS3 | Layout, branding, responsive design and animations |
| Vanilla JavaScript | Cart, navigation, interactions and form handling |
| Local Storage API | Browser-side cart persistence |
| `mailto:` | Email-based orders and inquiries |

### No Build System Required

There is currently no:

- Node.js dependency tree
- `package.json`
- React application
- Vue application
- Angular application
- Vite/Webpack build process

The website can therefore be opened directly in a browser or served using a simple local development server.

---

# Page Guide

| File | Purpose |
|---|---|
| `index.html` | Main corporate homepage |
| `agriculture.html` | Agriculture/farming section |
| `engineering.html` | Engineering services |
| `services.html` | Service directory |
| `investors.html` | Investor relations |
| `dashboard.html` | Dashboard placeholder |
| `contact.html` | Contact information |
| `bakery.html` | Bakery and pastry products |
| `event-packages.html` | Event management packages |
| `rentals.html` | Event/service rentals |
| `farm-order.html` | Crop and animal ordering |
| `farm-invest.html` | Farm investment inquiry |
| `consultation.html` | Engineering consultation |
| `payments.html` | Payment page/navigation |
| `cart.html` | Shopping cart |

---

# Navigation

The primary navigation is:

```text
Home
Agriculture
Engineering
Services
Investors
Dashboard
Contact
Cart
```

The navigation is implemented directly in the HTML files rather than being generated by a frontend framework.

### Active Navigation

`cart.js` determines the current page and applies the `active` class to the corresponding navigation link.

This means that when a visitor is on:

```text
engineering.html
```

the Engineering navigation item can be highlighted automatically.

---

# Responsive Design

The site is designed for three broad viewport categories:

### Desktop

Full navigation and multi-column layouts are displayed.

### Tablet

Content grids reduce their number of columns and spacing is adjusted.

### Mobile

The layout becomes single-column where appropriate and the navigation changes to a hamburger menu.

The primary mobile navigation breakpoint is approximately:

```css
@media (max-width: 760px)
```

A smaller-phone adjustment is provided around:

```css
@media (max-width: 420px)
```

### Hamburger Menu

The hamburger menu is controlled by `cart.js`.

The expected HTML structure is:

```html
<button class="menu-toggle"
        type="button"
        aria-label="Open navigation"
        aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
</button>
```

The navigation is opened by adding:

```text
.open
```

to the `<nav>` element.

---

# Cart System

The cart is implemented entirely in `cart.js`.

## Storage

Cart data is stored in the browser using:

```javascript
localStorage
```

under the key:

```text
mamidav_cart
```

A cart item has the following basic structure:

```javascript
{
    id: "...",
    name: "...",
    price: 0,
    category: "...",
    qty: 1
}
```

## Main Cart Functions

| Function | Purpose |
|---|---|
| `getCart()` | Retrieves cart data |
| `saveCart()` | Saves cart data |
| `addToCart()` | Adds a product |
| `removeFromCart()` | Removes a product |
| `updateCartQty()` | Updates quantity |
| `cartCount()` | Calculates total quantity |
| `cartTotal()` | Calculates cart value |
| `formatNaira()` | Formats prices in Nigerian Naira |
| `renderCartBadge()` | Updates cart counters |
| `renderCartTable()` | Displays cart contents |
| `placeOrder()` | Creates an order email |

---

# Order Workflow

When a visitor clicks **Place Order**, the website prepares an email using:

```text
mailto:info@mamidavintltd.com
```

The generated email includes:

- Product name
- Quantity
- Individual item value
- Total value
- Name field
- Phone field
- Delivery/pickup address field

The visitor's own email application is then opened.

### Important

This is **not a server-side checkout system**.

There is currently no payment gateway or backend order-processing API connected to the cart.

---

# Inquiry and Email Workflows

The reusable JavaScript function:

```javascript
submitInquiry(form, subject)
```

collects the form fields and prepares an email.

Current inquiry workflows include:

### Farm Investment

Subject:

```text
Farm Investment Inquiry
```

### Event Management

Subject:

```text
Event Management Booking
```

### Engineering

Subject:

```text
Engineering Consultation Request
```

The email destination is:

```text
info@mamidavintltd.com
```

---

# Visual Design System

The site's visual identity is defined centrally in `style.css`.

The principal brand colours currently include:

```css
--burgundy: #3e0303;
--burgundy-2: #5a0505;
--burgundy-3: #780b20;
--pink: #c41468;
--pink-soft: #f8e8ef;
--cream: #fbf6f3;
--cream-2: #f5ebe8;
--ink: #241b1d;
--muted: #746a6b;
--white: #fff;
--gold: #d6a74b;
```

Additional reusable variables define:

- Borders
- Shadows
- Corner radius
- Typography colours
- Background colours

### Why this matters

When the brand identity needs to change, update the CSS variables rather than searching through every page for individual colour values.

---

# Logo

The primary brand asset is:

```text
logo.png
```

It is used for:

- Header branding
- Browser favicon
- Footer branding

If the logo is replaced, keep the filename as:

```text
logo.png
```

unless all references throughout the HTML are updated.

---

# Getting Started

## Option 1 — Open Directly

Because this is a static frontend, individual HTML files can be opened directly in a browser.

For development, however, a local HTTP server is recommended.

---

## Option 2 — VS Code Live Server

A convenient workflow is to use the **Live Server** extension in VS Code.

### Steps

1. Open the project folder in VS Code.
2. Install the Live Server extension if it is not already installed.
3. Open `index.html`.
4. Right-click the file.
5. Select:

```text
Open with Live Server
```

The site will open in your browser.

---

## Option 3 — Python HTTP Server

If Python is installed:

```bash
python -m http.server 8000
```

Then visit:

```text
http://localhost:8000
```

---

# Local Development

Recommended development workflow:

```text
Edit
  ↓
Save
  ↓
Refresh browser
  ↓
Test desktop
  ↓
Test mobile
  ↓
Test links/forms/cart
  ↓
Deploy
```

For visual work, browser developer tools should be used to test at common widths.

---

# Testing Checklist

Before deploying a new version, test the following.

## Navigation

- [ ] Logo returns to homepage
- [ ] Home link works
- [ ] Agriculture link works
- [ ] Engineering link works
- [ ] Services link works
- [ ] Investors link works
- [ ] Dashboard link works
- [ ] Contact link works
- [ ] Cart link works

## Mobile

- [ ] Hamburger appears below 760px
- [ ] Hamburger opens navigation
- [ ] Hamburger changes to close state
- [ ] Navigation links are readable
- [ ] Clicking a link closes the menu
- [ ] Clicking outside closes the menu
- [ ] Menu closes when returning to desktop width
- [ ] No horizontal scrolling occurs

## Cart

- [ ] Product can be added
- [ ] Cart badge updates
- [ ] Quantity can be changed
- [ ] Product can be removed
- [ ] Total updates
- [ ] Empty cart state works
- [ ] Place Order opens email client

## Forms

- [ ] Required fields validate
- [ ] Inquiry email is generated
- [ ] Correct subject is generated
- [ ] Form data appears in the email
- [ ] Email destination is correct

## Visual

- [ ] Logo displays correctly
- [ ] Images load
- [ ] Buttons work
- [ ] Cards do not overflow
- [ ] Text remains readable
- [ ] Footer is responsive
- [ ] Mobile layouts work at approximately 320px–430px
- [ ] Tablet layout works
- [ ] Desktop layout works

---

# Deployment

The project is suitable for deployment as a static website.

For a Hostinger deployment, the HTML, CSS, JavaScript and image files should be uploaded to the directory configured as the site's public web root.

Typical deployment files include:

```text
index.html
style.css
cart.js
logo.png
*.html
```

### Before Uploading

Confirm:

- `index.html` is in the correct public directory.
- `logo.png` is uploaded.
- `style.css` is uploaded.
- `cart.js` is uploaded.
- All `.html` pages are uploaded.
- Relative links have not been changed.
- File names use the same capitalization as their links.
- The website works through the production domain.
- Browser caching is cleared when testing major CSS/JS changes.

---

# Content and Asset Management

## Adding a Product

Product pages use JavaScript calls similar to:

```javascript
addToCart(id, name, price, category)
```

When adding a product, make sure:

1. The product ID is unique.
2. The displayed price matches the JavaScript price.
3. The category is meaningful.
4. The quantity input uses the expected ID format if quantity selection is provided.

Example:

```html
<input id="qty-example" type="number" min="1" value="1">
<button onclick="addToCart('example', 'Example Product', 5000, 'Bakery')">
    Add to Cart
</button>
```

---

## Updating Contact Details

The current inquiry/order email is defined in:

```javascript
cart.js
```

Look for:

```javascript
const MAMIDAV_ORDER_EMAIL = "info@mamidavintltd.com";
```

If the business email changes, update this value and any direct `mailto:` links in the HTML files.

---

# Security and Production Considerations

This project is primarily a client-side website.

### Important Security Note

The current `dashboard.html` is a **static dashboard placeholder**. It should not be treated as a secure administrative system.

There is currently no demonstrated:

- Authentication system
- User login
- Session management
- Role-based access control
- Server-side authorization
- Database-backed dashboard

Therefore, sensitive business information should **not** be placed in the static dashboard until a proper backend authentication and authorization system is implemented.

### Client-Side Cart

Cart information is stored in the visitor's browser.

It should therefore not be considered authoritative transaction data.

A production e-commerce implementation should validate:

- Product prices
- Inventory
- Order quantities
- Customer identity
- Payment status

on the server.

---

# Known Limitations

The current project deliberately remains lightweight.

### 1. Email-Based Checkout

Orders and inquiries use `mailto:`.

This depends on the visitor having an email application configured.

### 2. No Payment Gateway Integration

The payment page is present, but this project does not demonstrate a server-side payment gateway implementation.

### 3. No Database

Product, cart and dashboard information are not backed by a database.

### 4. Static Dashboard

The dashboard currently displays placeholder metrics rather than live business data.

### 5. Browser-Local Cart

Cart data exists only in the visitor's browser/device.

### 6. No Central Template System

Header and footer markup are repeated across the HTML pages.

Changes to the global navigation may therefore need to be applied to multiple files.

---

# Future Improvements

The following upgrades would make the project significantly more robust as the business grows.

## High Priority

### Backend

Introduce a backend application and database for:

- Products
- Orders
- Customers
- Investments
- Inquiries
- Payments
- Dashboard metrics

### Secure Authentication

Implement:

- Admin login
- Password hashing
- Sessions
- Role-based permissions
- Protected dashboard routes

### Payment Integration

Replace the email-only payment/order flow with a proper payment provider and server-side verification.

### Centralized Components

Move repeated:

- Header
- Navigation
- Footer

into reusable components/templates.

This would prevent having to manually update every HTML page whenever the navigation changes.

---

## Medium Priority

### Product Management

Add an administrative interface for:

- Adding products
- Editing prices
- Updating stock
- Uploading product images
- Disabling unavailable products

### Order Management

Allow administrators to:

- View orders
- Update order status
- Contact customers
- Track delivery/pickup
- Export order data

### Investment Management

Create a secure investor management system for:

- Investment applications
- Investor records
- Project allocations
- Payment status
- Reporting

---

## Advanced Improvements

Potential future architecture:

```text
                 ┌──────────────────────┐
                 │      Mamidav Web     │
                 │       Frontend       │
                 └──────────┬───────────┘
                            │
                            ▼
                 ┌──────────────────────┐
                 │       Backend        │
                 │   API / Application  │
                 └──────────┬───────────┘
                            │
             ┌──────────────┼──────────────┐
             ▼              ▼              ▼
        ┌─────────┐   ┌──────────┐   ┌──────────┐
        │Database │   │ Payments │   │  Email   │
        └─────────┘   └──────────┘   └──────────┘
```

This would allow the website to evolve from a static corporate site into a complete business platform.

---

# Maintenance Guide

## Editing Global Styling

Most visual changes should begin in:

```text
style.css
```

The colour variables are at the top of the file.

## Editing Homepage

Main homepage:

```text
index.html
```

## Editing Cart Behaviour

Cart logic:

```text
cart.js
```

## Editing Navigation

The navigation is embedded in the individual HTML pages.

If the navigation structure changes, check every page containing:

```html
<nav aria-label="Main navigation">
```

---

# Recommended Git Workflow

For future development, use Git so that every major change can be tracked.

A simple workflow:

```bash
git status
git add .
git commit -m "Improve mobile navigation"
git push
```

Recommended commit style:

```text
feat: add bakery products
feat: improve mobile navigation
fix: correct cart quantity calculation
fix: repair contact link
style: refine homepage hero
docs: update README
```

Avoid making large undocumented changes directly on the production website.

---

# Production Checklist

Before a production deployment:

```text
[ ] Backup current website
[ ] Verify all HTML files
[ ] Verify style.css
[ ] Verify cart.js
[ ] Verify logo.png
[ ] Test desktop navigation
[ ] Test hamburger menu
[ ] Test all internal links
[ ] Test cart
[ ] Test order email
[ ] Test investment inquiry
[ ] Test event inquiry
[ ] Test engineering inquiry
[ ] Test contact information
[ ] Test mobile layout
[ ] Test tablet layout
[ ] Test desktop layout
[ ] Check browser console for errors
[ ] Check page titles
[ ] Check image alt text
[ ] Check production domain
[ ] Clear/revalidate browser cache
```

---

# License and Ownership

This website is developed for **Mamidav International Limited**.

Unless otherwise specified, the project's:

- Website content
- Brand identity
- Logo
- Business information
- Product information
- Visual assets
- Custom code

should be treated as proprietary business materials.

Third-party assets, libraries, fonts or images must be used according to their respective licenses.

---

# Project Status

**Current status:** Active development / pre-production

The website is functional as a client-side corporate website, with responsive navigation, service pages, inquiry workflows and a browser-based cart.

Before the project is treated as a full production commerce/investment platform, backend data management, authentication, payment processing and server-side validation should be implemented.

---

## Maintainer

**Mamidav International Limited**

Website:

```text
https://mamidavintltd.com
```

Business email:

```text
mail@mamidavintltd.com
```

---

> **Mamidav International Limited — Integrated Agribusiness, Engineering & Services.**