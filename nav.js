// Makes .pillar-card and .card elements act as full-card links (accessible)
document.addEventListener('DOMContentLoaded', function() {
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