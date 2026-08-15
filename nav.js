// Shared, accessible site navigation. The existing page URLs are deliberately
// preserved so old bookmarks and server routes continue to work.
document.addEventListener('DOMContentLoaded', function() {
  const header = document.querySelector('header');
  const nav = header && header.querySelector('nav');
  const toggle = header && header.querySelector('.menu-toggle');

  if (header && nav && toggle) {
    const page = (location.pathname.split('/').pop() || 'index.html').toLowerCase();
    const solutionPages = ['services.html', 'agriculture.html', 'engineering.html', 'event-packages.html', 'rentals.html', 'bakery.html', 'basic-event-package.html', 'standard-event-package.html', 'premium-event-package.html', 'consultation.html'];
    const companyPages = ['about.html', 'contact.html'];
    const isSolution = solutionPages.includes(page);
    const isCompany = companyPages.includes(page);

    nav.id = 'primary-navigation';
    toggle.setAttribute('aria-controls', nav.id);
    toggle.setAttribute('aria-label', 'Open navigation');
    nav.innerHTML = `
      <a href="index.html"${page === 'index.html' ? ' class="active" aria-current="page"' : ''}>Home</a>
      <div class="nav-dropdown${isSolution ? ' is-current' : ''}">
        <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="solutions-menu">Solutions <span class="nav-chevron" aria-hidden="true"></span></button>
        <div class="nav-dropdown-menu" id="solutions-menu" role="menu">
          <a role="menuitem" href="agriculture.html">Agribusiness</a>
          <a role="menuitem" href="engineering.html">Engineering</a>
          <a role="menuitem" href="rentals.html">Events &amp; Rentals</a>
          <a role="menuitem" href="bakery.html">Culinary Services</a>
        </div>
      </div>
      <a href="investors.html"${page === 'investors.html' || page === 'farm-invest.html' ? ' class="active" aria-current="page"' : ''}>Invest</a>
      <div class="nav-dropdown${isCompany ? ' is-current' : ''}">
        <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="company-menu">Company <span class="nav-chevron" aria-hidden="true"></span></button>
        <div class="nav-dropdown-menu" id="company-menu" role="menu">
          <a role="menuitem" href="about.html">About Us</a>
          <a role="menuitem" href="about.html#our-approach">Our Approach</a>
          <a role="menuitem" href="contact.html">Contact Us</a>
        </div>
      </div>
      <a href="cart.html" class="cart-link" aria-label="View cart"><svg class="cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 1.9-1.5L21 8H6"></path><circle cx="9" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg><span class="cart-badge">0</span></a>`;

    const closeMenus = (except) => nav.querySelectorAll('.nav-dropdown').forEach((dropdown) => {
      if (dropdown !== except) {
        dropdown.classList.remove('is-open');
        dropdown.querySelector('.nav-dropdown-toggle').setAttribute('aria-expanded', 'false');
      }
    });
    const closeNavigation = () => {
      nav.classList.remove('open');
      header.classList.remove('navigation-open');
      document.body.classList.remove('nav-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Open navigation');
      closeMenus();
    };

    toggle.addEventListener('click', () => {
      const open = !nav.classList.contains('open');
      nav.classList.toggle('open', open);
      header.classList.toggle('navigation-open', open);
      document.body.classList.toggle('nav-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    });

    const supportsHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    nav.querySelectorAll('.nav-dropdown').forEach((dropdown) => {
      const button = dropdown.querySelector('.nav-dropdown-toggle');
      const setOpen = (open) => {
        if (open) closeMenus(dropdown);
        dropdown.classList.toggle('is-open', open);
        button.setAttribute('aria-expanded', String(open));
      };

      button.addEventListener('click', (event) => {
        event.preventDefault();
        setOpen(!dropdown.classList.contains('is-open'));
      });

      // The dropdown container owns both trigger and menu, so leave fires only
      // after the pointer has departed from the complete interactive area.
      if (supportsHover) {
        dropdown.addEventListener('pointerenter', () => setOpen(true));
        dropdown.addEventListener('pointerleave', () => setOpen(false));
      }

      dropdown.addEventListener('focusout', (event) => {
        if (!dropdown.contains(event.relatedTarget)) setOpen(false);
      });
    });

    document.addEventListener('click', (event) => {
      if (!header.contains(event.target)) closeMenus();
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeNavigation();
    });
    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeNavigation));
    nav.dataset.navigationReady = 'true';

    // Standardize footer navigation labels while preserving every destination.
    document.querySelectorAll('.site-footer a').forEach((link) => {
      const href = (link.getAttribute('href') || '').split('#')[0];
      if (href === 'services.html') link.textContent = 'Solutions';
      if (href === 'investors.html') link.textContent = 'Invest';
      if (href === 'about.html') link.textContent = 'About Us';
    });
  }

  // Makes .pillar-card and .card elements act as full-card links (accessible)
  function makeCardsClickable(selector) {
    document.querySelectorAll(selector).forEach(card => {
      // if the element is already a semantic link, skip JS enhancement
      if (card.tagName && card.tagName.toLowerCase() === 'a') return;
      // prefer explicit data-href if set, otherwise find first internal link
      const explicit = card.getAttribute('data-href');
      let link = explicit ? explicit : null;
      if (!link) {
        const a = card.querySelector('a[href]');
        if (a) link = a.getAttribute('href');
      }
      if (!link) return;
      card.setAttribute('role','link');
      card.tabIndex = 0;
      card.addEventListener('click', (e) => {
        // if clicking on an interactive element, ignore so inner buttons/links work
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input')) return;
        window.location.href = link;
      });
      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          window.location.href = link;
        }
      });
    });
  }
  makeCardsClickable('.pillar-card');
  makeCardsClickable('.card');

  console.log("MAMIDAV INVESTMENT FORM JS LOADED");

});
document.querySelectorAll('.copy-email').forEach(link => {
  link.addEventListener('click', async function (event) {
    event.preventDefault();

    const email = this.dataset.email;
    const originalText = this.textContent;

    try {
      await navigator.clipboard.writeText(email);

      this.textContent = 'Copied!';

      setTimeout(() => {
        this.textContent = originalText;
      }, 1500);

    } catch (error) {
      console.error('Failed to copy email:', error);
    }
  });
});
