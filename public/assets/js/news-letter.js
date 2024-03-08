document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('_news_letter_modal');
  var closeButton = document.getElementById('closeModalButton'); 
  var focusedElementBeforeModal;

  function openModal() {
    focusedElementBeforeModal = document.activeElement;
    modal.style.display = 'flex';
    document.getElementById('email').focus();
    modal.addEventListener('keydown', trapTabKey);
    closeButton.addEventListener('click', closeModal);
  }

  function closeModal() {
    focusedElementBeforeModal.focus();
    modal.style.display = 'none';
    modal.removeEventListener('keydown', trapTabKey);
    closeButton.removeEventListener('click', closeModal);
  }

  function trapTabKey(e) {

    if (e.key === 'Tab' || e.keyCode === 9) {
        e.preventDefault();

        var focusableElements = modal.querySelectorAll('a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select');
        var firstElement = focusableElements[0];
        var lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === firstElement) {
                lastElement.focus();
            } else {
                var index = Array.from(focusableElements).indexOf(document.activeElement);
                focusableElements[index - 1].focus();
            }
        } else {
            if (document.activeElement === lastElement) {
                firstElement.focus();
            } else {
                var index = Array.from(focusableElements).indexOf(document.activeElement);
                focusableElements[index + 1].focus();
            }
        }
    }
  }
  document.getElementById('openModalButton').addEventListener('click', function(event) {
      event.preventDefault();
      openModal();
  });
});