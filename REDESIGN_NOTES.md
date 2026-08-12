# Mamidav International Limited — redesign

This version is based on the actual supplied project and keeps the existing multi-page static-site structure.

Redesign work:
- Rebuilt the homepage to follow the visual hierarchy of the supplied Hostinger reference: hero, core pillars, commitment, capability indicators, investment CTA/form, final CTA and footer.
- Preserved the actual Mamidav logo from `logo.png`.
- Preserved the existing burgundy/pink identity from the original stylesheet rather than switching to the reference site's teal palette.
- Kept the existing pages and navigation: Agriculture, Engineering, Services, Investors, Dashboard, Contact and Cart.
- Restyled the supporting pages through the shared `style.css`.
- Added a responsive mobile navigation menu.
- Preserved the existing `cart.js`, including cart/localStorage and email-based inquiry/order behavior, while adding navigation/reveal behavior.
- Preserved all existing product/order/investment/event/consultation page content.
- Kept the existing email address used by the project: mail@mamidavintltd.com.
- Added responsive layouts for desktop, tablet and mobile.

Important before production:
- The service images currently use remote Unsplash URLs because the supplied project contains only the company logo and no service photography. Replace these with Mamidav-owned/licensed images before final deployment if preferred.
- Existing catalog text explicitly marks some prices/items as samples. Verify all prices, packages and inventory before launch.
- Payment buttons remain integration-ready as in the supplied project; no payment credentials were invented or added.
