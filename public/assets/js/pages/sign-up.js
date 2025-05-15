document.addEventListener("DOMContentLoaded", function () {
  const userNameInput = document.getElementById("username");
  const userNameFeedback = document.getElementById("username-feedback");
  const passwordInput = document.getElementById("password");
  const passwordStrengthProgressBar = document.getElementById(
    "password-strength-progress"
  );
  const passwordFeedback = document.getElementById("password-feedback");
  const signUpButton = document.querySelector('form button[type="submit"]'); // Get the button

  function updateButtonState() {
    const isUsernameValid =
      userNameInput.classList.contains("is-valid") &&
      userNameInput.value.trim() !== "";
    const isPasswordValid =
      passwordInput.classList.contains("is-valid") &&
      passwordInput.value.trim() !== "";

    if (isUsernameValid && isPasswordValid) {
      signUpButton.disabled = false;
    } else {
      signUpButton.disabled = true;
    }
  }

  userNameInput.addEventListener("input", function () {
    const userName = this.value;
    let feedback = "";
    let isValid = true;

    // Reset validation state for username
    userNameInput.classList.remove("is-valid", "is-invalid");
    userNameFeedback.className = "form-text";

    if (userName.length === 0) {
      feedback = ""; // No feedback if empty, rely on 'required' attribute for submission
      // Keep is-invalid or is-valid off if empty, button logic will handle emptiness
    } else if (userName.length < 3 || userName.length > 24) {
      feedback = "username must be between 3 and 24 characters";
      isValid = false;
    } else if (!/^[a-z0-9_]+$/.test(userName)) {
      feedback =
        "username can only contain lowercase letters, numbers, and underscores";
      isValid = false;
    } else if (userName[0] === "_") {
      feedback = "username cannot start with an underscore";
      isValid = false;
    } else {
      feedback = "username format is valid";
    }

    userNameFeedback.textContent = feedback;
    if (isValid && userName.length > 0) {
      userNameFeedback.className = "form-text text-success";
      userNameInput.classList.add("is-valid");
    } else if (!isValid && userName.length > 0) {
      userNameFeedback.className = "form-text text-danger";
      userNameInput.classList.add("is-invalid");
    }
    // For empty field, neither is-valid nor is-invalid is added explicitly here,
    // but updateButtonState will correctly disable button if empty.

    updateButtonState(); // Call after username validation
  });

  passwordInput.addEventListener("input", function () {
    const password = this.value;
    let strength = 0;
    const feedbackMessages = [];

    passwordStrengthProgressBar.style.width = "0%";
    passwordStrengthProgressBar.className = "progress-bar";
    passwordFeedback.textContent = "";
    passwordFeedback.className = "form-text mt-1";
    passwordInput.classList.remove("is-valid", "is-invalid");

    if (password.length === 0) {
      updateButtonState(); // Call if password becomes empty
      return;
    }

    // --- Strength Calculation ---
    // 5 Core Criteria, each worth 15 points (total 75 for "Good")
    // Bonus for 12+ length: 25 points (total 100 for "Very Good")

    if (/[a-z]/.test(password)) {
      strength += 15;
    } else {
      feedbackMessages.push("one lowercase letter");
    }
    if (/[A-Z]/.test(password)) {
      strength += 15;
    } else {
      feedbackMessages.push("one uppercase letter");
    }
    if (/[0-9]/.test(password)) {
      strength += 15;
    } else {
      feedbackMessages.push("one number");
    }
    if (/[\W_]/.test(password)) {
      strength += 15;
    } else {
      feedbackMessages.push("one special character");
    }
    if (password.length >= 8) {
      strength += 15;
    } else {
      feedbackMessages.push("8 characters");
    }

    let isVeryGoodLength = false;
    if (password.length >= 12 && feedbackMessages.length === 0) {
      if (strength >= 75) {
        strength += 25;
        isVeryGoodLength = true;
      }
    }
    strength = Math.min(strength, 100);

    passwordStrengthProgressBar.style.width = strength + "%";
    passwordStrengthProgressBar.setAttribute("aria-valuenow", strength);

    let barColorClass = "";
    let textColorClass = "";
    let feedbackText = "";

    if (feedbackMessages.length > 0) {
      feedbackText = "password needs at least " + feedbackMessages.join(", ");
      passwordInput.classList.add("is-invalid");
      if (strength < 30) {
        barColorClass = "bg-danger";
        textColorClass = "text-danger";
      } else if (strength < 60) {
        barColorClass = "bg-danger";
        textColorClass = "text-danger";
      } else {
        barColorClass = "bg-warning";
        textColorClass = "text-warning";
      }
    } else {
      passwordInput.classList.add("is-valid");
      barColorClass = "bg-success";
      textColorClass = "text-success";
      if (isVeryGoodLength) {
        feedbackText = "password strength is very good";
      } else {
        feedbackText = "password strength is good";
      }
    }

    passwordStrengthProgressBar.className = "progress-bar";
    if (strength > 0) {
      passwordStrengthProgressBar.classList.add(barColorClass);
    }
    passwordFeedback.textContent = feedbackText;
    passwordFeedback.className = `form-text mt-1 ${textColorClass}`;

    updateButtonState(); // Call after password validation
  });

  updateButtonState(); // Initial check on page load
});
