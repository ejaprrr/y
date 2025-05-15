document.addEventListener("DOMContentLoaded", function () {
  // Post content validation and character counter
  const postTextarea = document.querySelector("textarea[name='content']");
  const postForm = document.querySelector(".composer form");

  if (postTextarea) {
    const postCounter = document.getElementById("post-counter");
    const postButton = postForm.querySelector("button[type='submit']");

    postTextarea.addEventListener("input", function () {
      // Update character count
      const count = this.value.length;
      postCounter.textContent = `${count}/256`;

      // Limit newlines - allow one empty line but not more
      if (this.value.includes("\n\n\n")) {
        this.value = this.value.replace(/\n{3,}/g, "\n\n");
      }

      // Color coding based on character count
      if (count > 256 - 20) {
        postCounter.classList.remove("text-danger");
        postCounter.classList.add("text-warning");
      } else {
        postCounter.classList.remove("text-warning", "text-danger");
      }

      if (count > 256) {
        postCounter.classList.remove("text-warning");
        postCounter.classList.add("text-danger");
        postButton.disabled = true;
      } else {
        postButton.disabled = false;
      }
    });
  }
});
