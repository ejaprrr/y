document.addEventListener("DOMContentLoaded", function () {
  // Get form elements
  const profileForm = document.querySelector("form");
  const displayNameInput = document.getElementById("display_name");
  const bioTextarea = document.getElementById("bio");
  const bioCounter = document.getElementById("bio-counter");
  const saveButton = profileForm.querySelector("button[type='submit']");

  // Max allowed characters
  const MAX_DISPLAY_NAME_LENGTH = 48;
  const MAX_BIO_LENGTH = 128;

  // Function to validate the form and update button state
  function validateForm() {
    const displayNameLength = displayNameInput.value.length;
    const bioLength = bioTextarea.value.length;

    let isValid = true;

    // Validate display name
    if (displayNameLength > MAX_DISPLAY_NAME_LENGTH) {
      isValid = false;
    }

    // Validate bio
    if (bioLength > MAX_BIO_LENGTH) {
      isValid = false;
    }

    // Update button state
    saveButton.disabled = !isValid;
  }

  // Profile Picture Preview (existing code)
  const profilePictureInput = document.getElementById("profile_picture");
  const profilePicPreview = document.getElementById("profile-pic-preview");

  profilePictureInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
      const reader = new FileReader();

      reader.onload = function (e) {
        // Check if preview is an img element or the default div
        if (profilePicPreview.tagName === "IMG") {
          // Update existing image
          profilePicPreview.src = e.target.result;
        } else {
          // Replace div with new image
          const newImg = document.createElement("img");
          newImg.src = e.target.result;
          newImg.id = "profile-pic-preview";
          newImg.alt = "Profile";
          newImg.className = "profile-picture-edit-img";

          profilePicPreview.parentNode.replaceChild(newImg, profilePicPreview);
        }
      };

      reader.readAsDataURL(this.files[0]);
    }
  });

  // Cover Image Preview
  const coverImageInput = document.getElementById("cover_image");
  const coverPreview = document.getElementById("cover-preview");

  coverImageInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
      const reader = new FileReader();

      reader.onload = function (e) {
        // Check if there's already an img element
        let coverImg = coverPreview.querySelector("img");

        if (coverImg) {
          // Update existing image
          coverImg.src = e.target.result;
        } else {
          // Remove placeholder if it exists
          const placeholder = coverPreview.querySelector(".cover-placeholder");
          if (placeholder) {
            placeholder.remove();
          }

          // Create new image
          coverImg = document.createElement("img");
          coverImg.src = e.target.result;
          coverImg.alt = "Cover";
          coverImg.className = "cover-img";

          // Add the new image to the preview
          coverPreview.appendChild(coverImg);
        }
      };

      reader.readAsDataURL(this.files[0]);
    }
  });

  // Enhanced Bio Character Counter with validation
  bioTextarea.addEventListener("input", function () {
    const count = this.value.length;
    bioCounter.textContent = `${count}/128`;

    // Remove empty newlines from bio
    if (this.value.includes("\n\n")) {
      this.value = this.value.replace(/\n\n+/g, "\n");
    }

    // Color coding based on character count
    if (count > 128 - 15) {
      bioCounter.classList.remove("text-danger");
      bioCounter.classList.add("text-warning");
    } else {
      bioCounter.classList.remove("text-warning", "text-danger");
    }

    if (count > 128) {
      bioCounter.classList.remove("text-warning");
      bioCounter.classList.add("text-danger");
    }

    // Run form validation
    validateForm();
  });

  // Make the entire cover image area clickable
  const coverContainer = document.querySelector(".cover-container");
  coverContainer.addEventListener("click", function (e) {
    // Don't trigger if clicking on the label itself
    if (!e.target.closest("label")) {
      document.getElementById("cover_image").click();
    }
  });

  // Make the entire profile picture area clickable
  const profilePicContainer = document.querySelector(
    ".profile-picture-container-edit"
  );
  profilePicContainer.addEventListener("click", function (e) {
    // Don't trigger if clicking on the label itself
    if (!e.target.closest("label")) {
      document.getElementById("profile_picture").click();
    }
  });

  // Display name validation
  displayNameInput.addEventListener("input", validateForm);

  // Initial validation on page load
  validateForm();
});
