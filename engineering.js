document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("engineering-form");
    const message = document.getElementById("engineering-form-message");

    if (!form) return;

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Sending...";
        }

        if (message) {
            message.textContent = "";
            message.className = "form-message";
        }

        const formData = new FormData(form);

        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch("api/engineering_consultation.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                if (message) {
                    message.textContent =
                        result.message ||
                        "Your consultation request has been sent successfully.";
                    message.className = "form-message success";
                }

                form.reset();
            } else {
                throw new Error(
                    result.message ||
                    "Unable to send your consultation request."
                );
            }

        } catch (error) {
            console.error("Engineering consultation error:", error);

            if (message) {
                message.textContent =
                    error.message ||
                    "Something went wrong. Please try again later.";
                message.className = "form-message error";
            }

        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = "Request Consultation";
            }
        }
    });
});