console.log(
  "MAMIDAV ENGINEERING.JS LOADED - VERSION 2026-08-14-1"
);

document.addEventListener(
  "DOMContentLoaded",
  () => {

    const engineeringForm =
      document.getElementById(
        "engineering-form"
      );

    console.log(
      "MAMIDAV: Engineering form found:",
      !!engineeringForm
    );

    if (!engineeringForm) {
      return;
    }

    engineeringForm.addEventListener(
      "submit",
      async (event) => {

        event.preventDefault();

        const submitButton =
          engineeringForm.querySelector(
            'button[type="submit"]'
          );

        const statusMessage =
          document.getElementById(
            "engineering-form-message"
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
            "form-message";
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
            "Sending Request...";
        }

        // ----------------------------------------
        // Collect form data
        // ----------------------------------------

        const formData =
          new FormData(
            engineeringForm
          );

        const payload = {

          full_name:
            (
              formData.get(
                "Full Name"
              ) || ""
            ).toString().trim(),

          company:
            (
              formData.get(
                "Company"
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

          project_type:
            (
              formData.get(
                "Project Type"
              ) || ""
            ).toString().trim(),

          project_description:
            (
              formData.get(
                "Project Description"
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

        console.log(
          "MAMIDAV: Engineering consultation payload prepared."
        );

        // ----------------------------------------
        // Send to PHP API
        // ----------------------------------------

        try {

          const response =
            await fetch(
              "api/engineering_consultation.php",
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

          console.log(
            "MAMIDAV: Engineering API response:",
            result
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
              "Unable to submit your consultation request."
            );
          }

          // --------------------------------------
          // Success
          // --------------------------------------

          if (statusMessage) {

            statusMessage.textContent =
              result.message ||
              "Your consultation request has been submitted successfully. Our team will contact you shortly.";

            statusMessage.className =
              "form-message success";
          }

          // Clear form

          engineeringForm.reset();

        } catch (error) {

          console.error(
            "MAMIDAV: Engineering consultation error:",
            error
          );

          if (statusMessage) {

            statusMessage.textContent =
              error.message ||
              "We could not submit your consultation request. Please try again.";

            statusMessage.className =
              "form-message error";
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
              "Request Consultation";
          }
        }
      }
    );
  }
);