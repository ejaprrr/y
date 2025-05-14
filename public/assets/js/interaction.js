document.addEventListener("DOMContentLoaded", () => {
  // handle like/unlike
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
        body: `target_id=${encodeURIComponent(
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
              ? "bi bi-heart-fill liked"
              : "bi bi-heart";
            count.textContent = data.like_count;
          }
        })
        .finally(() => {
          btn.disabled = false;
        });
    });
  });

  // handle follow/unfollow
  document.querySelectorAll(".follow-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const userId = this.getAttribute("data-user-id");
      const following = this.getAttribute("data-following") === "1";
      const action = following ? "unfollow" : "follow";
      const csrfToken = document.querySelector(
        'input[name="csrf_token"]'
      ).value;

      btn.disabled = true;
      fetch("interaction.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `target_id=${encodeURIComponent(
          userId
        )}&action=${action}&csrf_token=${encodeURIComponent(csrfToken)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (!data.error) {
            this.setAttribute("data-following", data.following ? "1" : "0");
            this.textContent = data.following ? "unfollow" : "follow";

            // Update follower count
            const followerCountElement = document.querySelector(
              ".user-stats a:nth-child(2) .fw-bold"
            );
            if (followerCountElement) {
              followerCountElement.textContent = data.follower_count;
            }
          }
        })
        .finally(() => {
          btn.disabled = false;
        });
    });
  });

  // handle delete post
  document.querySelectorAll(".delete-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postId = this.getAttribute("data-post-id");
      const csrfToken = document.querySelector(
        'input[name="csrf_token"]'
      ).value;

      fetch("interaction.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `target_id=${encodeURIComponent(
          postId
        )}&action=delete&csrf_token=${encodeURIComponent(csrfToken)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Remove the post from the DOM
            const postElement = document.querySelector(
              `[data-post-id="${postId}"]`
            );
            if (postElement) {
              postElement.remove();
            }
          }
        });
    });
  });
});
