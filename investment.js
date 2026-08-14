console.log(
  "MAMIDAV INVESTMENT.JS LOADED - VERSION 2026-08-14-1"
);

document.addEventListener(
  "DOMContentLoaded",
  () => {

    const investmentForms =
      document.querySelectorAll(
        ".investment-form"
      );

    console.log(
      "MAMIDAV: Investment forms found:",
      investmentForms.length
    );

    investmentForms.forEach(
      (form) => {

        form.addEventListener(
          "submit",
          async (event) => {

            event.preventDefault();

            const submitButton =
              form.querySelector(
                'button[type="submit"]'
              );

            const statusMessage =
              form.querySelector(
                ".form-status"
              );

            // ----------------------------------------
            // Prevent duplicate submissions
            // ----------------------------------------

            if (
              submitButton &&
              submitButton.disabled
            ) {
              return;
            }

            // ----------------------------------------
            // Clear previous status
            // ----------------------------------------

            if (statusMessage) {

              statusMessage.textContent =
                "";

              statusMessage.className =
                "form-status";
            }

            // ----------------------------------------
            // Disable button
            // ----------------------------------------

            if (submitButton) {

              submitButton.disabled =
                true;

              submitButton.dataset.originalText =
                submitButton.innerHTML;

              submitButton.innerHTML =
                "Sending Inquiry...";
            }

            // ----------------------------------------
            // Collect form data
            // ----------------------------------------

            const formData =
              new FormData(form);

            const payload = {

              full_name:
                (
                  formData.get(
                    "Full Name"
                  ) || ""
                ).toString().trim(),

              email:
                (
                  formData.get(
                    "Email"
                  ) || ""
                ).toString().trim(),

              phone:
                (
                  formData.get(
                    "Phone"
                  ) || ""
                ).toString().trim(),

              area_of_interest:
                (
                  formData.get(
                    "Area of Interest"
                  ) || ""
                ).toString().trim(),

              investment_amount:
                (
                  formData.get(
                    "Investment Amount (NGN)"
                  ) || ""
                ).toString().trim(),

              message:
                (
                  formData.get(
                    "Message"
                  ) || ""
                ).toString().trim(),

              // Honeypot
              website:
                (
                  formData.get(
                    "website"
                  ) || ""
                ).toString().trim()
            };

            // ----------------------------------------
            // Send to PHP API
            // ----------------------------------------

            try {

              const response =
                await fetch(
                  "api/investment_inquiry.php",
                  {
                    method: "POST",

                    headers: {
                      "Content-Type":
                        "application/json",

                      "Accept":
                        "application/json"
                    },

                    body:
                      JSON.stringify(
                        payload
                      )
                  }
                );

              const result =
                await response.json()
                  .catch(
                    () => null
                  );

              // --------------------------------------
              // Server error
              // --------------------------------------

              if (
                !response.ok ||
                !result ||
                result.success !== true
              ) {

                throw new Error(
                  (
                    result &&
                    result.error
                  ) ||
                  "Unable to submit your inquiry."
                );
              }

              // --------------------------------------
              // Success
              // --------------------------------------

              if (statusMessage) {

                statusMessage.textContent =
                  result.message ||
                  "Your investment inquiry has been submitted successfully. Our team will contact you shortly.";

                statusMessage.className =
                  "form-status success";
              }

              // Clear form

              form.reset();

            } catch (error) {

              console.error(
                "MAMIDAV: Investment inquiry error:",
                error
              );

              if (statusMessage) {

                statusMessage.textContent =
                  error.message ||
                  "We could not submit your inquiry. Please try again.";

                statusMessage.className =
                  "form-status error";
              }

            } finally {

              // --------------------------------------
              // Restore button
              // --------------------------------------

              if (submitButton) {

                submitButton.disabled =
                  false;

                submitButton.innerHTML =
                  submitButton.dataset.originalText ||
                  'Send Investment Inquiry <span>→</span>';
              }
            }
          }
        );
      }
    );
  }
);