document.addEventListener("DOMContentLoaded", function () {
  var modal = document.getElementById("_news_letter_modal");
  var closeButton = document.getElementById("closeModalButton");
  var focusedElementBeforeModal;

  function openModal() {
    focusedElementBeforeModal = document.activeElement;
    modal.style.display = "flex";
    const input = modal.querySelector("input[type='email']");
    if (input) {
      input.placeholder = "Email Address";
      input.removeAttribute("aria-label");
      input.setAttribute(
        "aria-describedby",
        "da-newsletter-modal-email-input-label",
      );
      input.focus();

      if (typeof daUserData?.email !== "undefined") {
        input.value = daUserData.email;
      }
    }
    const form = modal.querySelector("form");
    form.addEventListener("submit", onFormSubmitted);
    modal.addEventListener("keydown", trapTabKey);
    closeButton.addEventListener("click", closeModal);
  }

  function onFormSubmitted(e) {
    const input = modal.querySelector("input[type='email']");

    // Clean up if needed
    let previousHelpText = document.getElementById(
      "da-newsletter-modal-input-helptext",
    );
    if (previousHelpText) {
      previousHelpText.remove();
      let previousAriaDescribedBy = input.getAttribute("aria-describedby");
      previousAriaDescribedBy = previousAriaDescribedBy.replace(
        "da-newsletter-modal-input-helptext",
        "",
      );
      input.setAttribute("aria-describedby", previousAriaDescribedBy);
    }

    if (input.getAttribute("aria-invalid") === "false") {
      const label = document.getElementById(
        "da-newsletter-modal-email-input-label",
      );
      if (label) label.style.display = "none";
      setTimeout(() => {
        const form = modal.querySelector("form.ml-block-form");
        if (form) form.style.display = "none";
        const thankYou = modal.querySelector(
          ".ml-form-successBody.row-success",
        );
        if (thankYou) thankYou.style.display = "block";
        modal.querySelector("button#closeModalButton").focus();
      }, 100);
    } else {
      input.focus();
      const inputParent = input.parentElement;
      const p = document.createElement("p");
      p.id = "da-newsletter-modal-input-helptext";
      if (input.value) {
        // Invalid input
        p.textContent = "Invalid Email Address";
      } else {
        p.textContent = "Email Address is Required!";
      }
      let existingAriaDescribedBy = input.getAttribute("aria-describedby");
      existingAriaDescribedBy += " da-newsletter-modal-input-helptext";
      input.setAttribute("aria-describedby", existingAriaDescribedBy);
      inputParent.appendChild(p);
    }
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
        'button#closeModalButton, button[type="submit"], input[type="email"]',
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
        closeModal();
      }
    });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" || event.keyCode === 27) {
      event.preventDefault();
      closeModal();
    }
  });
});
