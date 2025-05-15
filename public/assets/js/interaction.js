document.addEventListener("DOMContentLoaded", () => {
  function sendInteraction(data) {
    // Create FormData object
    const formData = new FormData();

    // Add all data properties to the FormData
    for (const key in data) {
      formData.append(key, data[key]);
    }

    // Add the CSRF token
    formData.append(
      "csrf_token",
      document.querySelector('input[name="csrf_token"]').value
    );

    return fetch("interaction.php", {
      method: "POST",
      body: formData, // Send as FormData instead of JSON
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("network response was not ok");
        }
        return response.json();
      })
      .then((result) => {
        if (result.success) {
          console.log("Interaction successful:", result);
          // Update the CSRF token with the new one from the server
          if (result.new_csrf_token) {
            document.querySelector('input[name="csrf_token"]').value =
              result.new_csrf_token;
          }
          return result; // Return the result for further chaining
        } else {
          throw new Error(result.error || "Interaction failed");
        }
      });
  }

  // Example usage: Like button
  document.querySelectorAll(".like-button").forEach((button) => {
    button.addEventListener("click", () => {
      sendInteraction({ action: "like", postId: button.dataset.postId });
    });
  });

  // handle like/unlike
  document.querySelectorAll(".like-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postDiv = this.closest(".card");
      const postId = postDiv.getAttribute("data-post-id");
      const liked = this.getAttribute("data-liked") === "1";
      const action = liked ? "unlike" : "like";

      btn.disabled = true;
      sendInteraction({ target_id: postId, action: action })
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

      btn.disabled = true;
      sendInteraction({ target_id: userId, action: action })
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

      sendInteraction({ target_id: postId, action: "delete" }).then((data) => {
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
