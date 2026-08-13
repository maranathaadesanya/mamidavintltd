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

    const investmentForm = document.getElementById("investment-form");

    if (!investmentForm) {
        return;
    }

    investmentForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(investmentForm);

        const getValue = (name) => {
            const value = formData.get(name);
            return value ? value.toString().trim() : "";
        };

        const name = getValue("name");
        const email = getValue("email");
        const phone = getValue("phone");
        const company = getValue("company");
        const investmentType = getValue("investment-type");
        const investmentAmount = getValue("investment-amount");
        const message = getValue("message");

        const subject = "Investment Inquiry - Mamidav International Limited";

        const body =
`Dear Mamidav International Limited,

I would like to make an investment inquiry.

INVESTOR INFORMATION
--------------------
Name: ${name || "Not provided"}
Email: ${email || "Not provided"}
Phone: ${phone || "Not provided"}
Company/Organization: ${company || "Not provided"}

INVESTMENT INFORMATION
----------------------
Investment Type: ${investmentType || "Not provided"}
Investment Amount: ${investmentAmount || "Not provided"}

MESSAGE
-------
${message || "No additional message provided."}

Thank you.

This inquiry was submitted through the Mamidav International Limited website.`;

        const mailtoURL =
            "mailto:mail@mamidavintltd.com" +
            "?subject=" + encodeURIComponent(subject) +
            "&body=" + encodeURIComponent(body);

        window.location.href = mailtoURL;
    });
});

document.addEventListener("DOMContentLoaded", function () {
    
});