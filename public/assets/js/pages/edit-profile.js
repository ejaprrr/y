document.addEventListener("DOMContentLoaded", function () {
  // Get form elements
  const profileForm = document.querySelector("form");
  const displayNameInput = document.getElementById("display_name");
  const bioTextarea = document.getElementById("bio");
  const bioCounter = document.getElementById("bio-counter");
  const saveButton = profileForm.querySelector("button[type='submit']");

  // File inputs
  const profilePictureInput = document.getElementById("profile_picture");
  const coverImageInput = document.getElementById("cover_image");

  // Max allowed characters
  const MAX_DISPLAY_NAME_LENGTH = 48;
  const MAX_BIO_LENGTH = 128;

  // Function to validate the form and update button state
  function validateForm() {
    const displayNameLength = displayNameInput.value.length;
    const bioLength = bioTextarea.value.length;

    let isValid = true;

    if (
      displayNameLength > MAX_DISPLAY_NAME_LENGTH ||
      bioLength > MAX_BIO_LENGTH
    ) {
      isValid = false;
    }

    saveButton.disabled = !isValid;
  }

  // Profile Picture Preview
  profilePictureInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
      const reader = new FileReader();

      reader.onload = function (e) {
        console.log("Profile Picture Base64:", e.target.result); // Debugging
        const profilePicPreview = document.getElementById(
          "profile-pic-preview"
        );

        if (profilePicPreview.tagName.toLowerCase() === "img") {
          // Update the image source
          profilePicPreview.src = e.target.result;
        } else {
          // Replace the default icon with an image
          const parentContainer = profilePicPreview.parentNode;
          const newImg = document.createElement("img");
          newImg.src = e.target.result;
          newImg.id = "profile-pic-preview";
          newImg.alt = "profile";
          newImg.className = "profile-picture-edit-img";

          parentContainer.replaceChild(newImg, profilePicPreview);
        }
      };

      reader.onerror = function () {
        console.error("Error reading profile picture file.");
      };

      reader.readAsDataURL(this.files[0]);
    } else {
      console.warn("No file selected for profile picture.");
    }
  });

  // Cover Image Preview
  coverImageInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
      const reader = new FileReader();

      reader.onload = function (e) {
        console.log("Cover Image Base64:", e.target.result); // Debugging
        const coverPreview = document.getElementById("cover-preview");
        let coverImg = coverPreview.querySelector("img");

        if (coverImg) {
          // Update the image source
          coverImg.src = e.target.result;
        } else {
          // Remove placeholder if it exists
          const placeholder = coverPreview.querySelector(".cover-placeholder");
          if (placeholder) {
            placeholder.remove();
          }

          // Create and add the new image
          coverImg = document.createElement("img");
          coverImg.src = e.target.result;
          coverImg.alt = "cover";
          coverImg.className = "cover-img";

          coverPreview.appendChild(coverImg);
        }
      };

      reader.onerror = function () {
        console.error("Error reading cover image file.");
      };

      reader.readAsDataURL(this.files[0]);
    } else {
      console.warn("No file selected for cover image.");
    }
  });

  // Bio character counter with validation
  bioTextarea.addEventListener("input", function () {
    const count = this.value.length;
    bioCounter.textContent = `${count}/128`;

    // Color coding based on character count
    if (count > 128 - 15 && count <= 128) {
      bioCounter.classList.remove("text-danger");
      bioCounter.classList.add("text-warning");
    } else if (count > 128) {
      bioCounter.classList.remove("text-warning");
      bioCounter.classList.add("text-danger");
    } else {
      bioCounter.classList.remove("text-warning", "text-danger");
    }

    validateForm();
  });

  // Display name validation
  displayNameInput.addEventListener("input", validateForm);

  // Initial validation on page load
  validateForm();
});
