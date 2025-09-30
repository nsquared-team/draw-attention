document.addEventListener("DOMContentLoaded", function () {
  var modal = document.getElementById("_news_letter_modal");
  var closeButton = document.getElementById("closeModalButton");
  var focusedElementBeforeModal;

  const HIDE_ON_SUCCESS_SELECTOR = "[data-hideonsuccess]";
  const SUCCESS_SELECTOR = "[data-nodeonsuccess]";
  const HIDE_ON_RESET_SELECTOR = "[data-hideonreset]";
  const SHOW_ON_RESET_SELECTOR = "[data-showonreset]";
  const DOMAIN = "https://wpdrawattention.com";
  const APIURL = `${DOMAIN}/wp-json/da-mailerlite/v1/subscribe`;

  function resetUi() {
    modal
      .querySelectorAll(HIDE_ON_RESET_SELECTOR)
      .forEach((el) => el.classList.add("da-hidden"));
    modal
      .querySelectorAll(SHOW_ON_RESET_SELECTOR)
      .forEach((el) => el.classList.remove("da-hidden"));
  }

  function openModal() {
    focusedElementBeforeModal = document.activeElement;
    modal.style.display = "flex";
    document.getElementById("da-newsletter-email").focus();
    modal.addEventListener("keydown", trapTabKey);
    closeButton.addEventListener("click", closeModal);
    resetUi();
  }

  function closeModal() {
    focusedElementBeforeModal.focus();
    modal.style.display = "none";
    modal.removeEventListener("keydown", trapTabKey);
    closeButton.removeEventListener("click", closeModal);
  }

  function trapTabKey(e) {
    if (e.key === "Tab" || e.keyCode === 9) {
      e.preventDefault();

      var focusableElements = modal.querySelectorAll(
        'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select',
      );
      var firstElement = focusableElements[0];
      var lastElement = focusableElements[focusableElements.length - 1];

      if (e.shiftKey) {
        if (document.activeElement === firstElement) {
          lastElement.focus();
        } else {
          var index = Array.from(focusableElements).indexOf(
            document.activeElement,
          );
          focusableElements[index - 1].focus();
        }
      } else {
        if (document.activeElement === lastElement) {
          firstElement.focus();
        } else {
          var index = Array.from(focusableElements).indexOf(
            document.activeElement,
          );
          focusableElements[index + 1].focus();
        }
      }
    }
  }
  document
    .getElementById("openModalButton")
    .addEventListener("click", function (event) {
      event.preventDefault();
      openModal();
    });

  document
    .getElementById("_news_letter_modal")
    .addEventListener("click", function (event) {
      if (event.target === this) {
        event.preventDefault();
        modal.style.display = "none";
      }
    });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" || event.keyCode === 27) {
      event.preventDefault();
      modal.style.display = "none";
    }
  });

  document
    .getElementById("da-newsletter-form")
    .addEventListener("submit", function (event) {
      event.preventDefault();
      resetUi();
      var email = document.getElementById("da-newsletter-email");
      var inputValue = email.value;

      if (inputValue.trim() === "") {
        document
          .getElementById("da_newsletter_msg_error_invalid_input")
          .classList.remove("da-hidden");
        email.focus();
        return;
      }
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(inputValue.trim())) {
        document
          .getElementById("da_newsletter_msg_error_invalid_input")
          .classList.remove("da-hidden");
        email.focus();
        return;
      }

      const submitBtn = document.getElementById(
        "da_newsletter_form_submit_btn",
      );

      fetch(APIURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: inputValue,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const elementsToHide = modal.querySelectorAll(
              HIDE_ON_SUCCESS_SELECTOR,
            );
            const elementToShow = modal.querySelectorAll(SUCCESS_SELECTOR);
            elementsToHide.forEach((el) => el.classList.add("da-hidden"));
            elementToShow.forEach((el) => el.classList.remove("da-hidden"));
            document.getElementById("da-newsletter-form").reset();
            closeButton.focus();
          } else {
            if (data && data.code === "rest_invalid_param") {
              document
                .getElementById("da_newsletter_msg_error_invalid_input")
                .classList.remove("da-hidden");
            } else {
              document
                .getElementById("da_newsletter_msg_error_generic")
                .classList.remove("da-hidden");
            }
            email.focus();
          }
        })
        .catch((error) => {
          document
            .getElementById("da_newsletter_msg_error_generic")
            .classList.remove("da-hidden");
          email.focus();
        })
        .finally(() => {
          submitBtn.disabled = false;
        });
    });
});
