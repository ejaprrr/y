document.addEventListener("DOMContentLoaded", function () {
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("password");
  const loginButton = document.querySelector('form button[type="submit"]');

  function updateButtonState() {
    const isUsernameValid = usernameInput.value.trim() !== "";
    const isPasswordValid = passwordInput.value.trim() !== "";

    if (isUsernameValid && isPasswordValid) {
      loginButton.disabled = false;
    } else {
      loginButton.disabled = true;
    }
  }

  usernameInput.addEventListener("input", function () {
    updateButtonState();
  });

  passwordInput.addEventListener("input", function () {
    updateButtonState();
  });

  // Initial check on page load
  updateButtonState();
});
