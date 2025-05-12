document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".like-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postDiv = this.closest(".card");
      const postId = postDiv.getAttribute("data-post-id");
      const liked = this.getAttribute("data-liked") === "1";
      const action = liked ? "unlike" : "like";
      const csrfToken = document.querySelector(
        'input[name="csrf_token"]'
      ).value;

      btn.disabled = true;
      fetch("interaction.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${encodeURIComponent(
          postId
        )}&action=${action}&csrf_token=${encodeURIComponent(csrfToken)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (!data.error) {
            this.setAttribute("data-liked", data.liked ? "1" : "0");
            const icon = this.querySelector("i");
            const count = this.querySelector("span");
            icon.className = data.liked
              ? "bi bi-heart-fill text-danger"
              : "bi bi-heart";
            count.textContent = data.like_count;
          }
        })
        .finally(() => {
          btn.disabled = false;
        });
    });
  });
});
