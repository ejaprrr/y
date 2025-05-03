/**
 * Post Actions JavaScript
 * Handles AJAX interactions for post actions (like, repost, bookmark)
 */
document.addEventListener("DOMContentLoaded", function () {
  console.log("Post actions JS loaded");

  const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

  // Handle all post action clicks
  document.addEventListener("click", function (e) {
    // Find closest action button if clicked on child element
    const actionButton = e.target.closest(".post-action");
    if (!actionButton) return;

    e.preventDefault();

    const postId = actionButton.dataset.postId;
    const action = actionButton.dataset.action;

    if (!postId || !action || !csrfToken) {
      console.error("Missing required data", { postId, action, csrfToken });
      return;
    }

    // Show loading state
    actionButton.classList.add("disabled");

    // Prepare form data
    const formData = new FormData();
    formData.append("post_id", postId);
    formData.append("action", action);
    formData.append("csrf_token", csrfToken);

    // Send AJAX request
    fetch("../../public/app/ajax_post_action.php", {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          updatePostUI(postId, action, data);
        } else {
          console.error("Error:", data.error);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
      })
      .finally(() => {
        // Remove loading state
        actionButton.classList.remove("disabled");
      });
  });

  /**
   * Update the UI based on action response
   */
  function updatePostUI(postId, action, data) {
    // Find all instances of this post on the page (could appear multiple times)
    const postActions = document.querySelectorAll(
      `.post-actions[data-post-id="${postId}"]`
    );

    postActions.forEach((postAction) => {
      // Update like UI
      if (action === "like") {
        const likeIcon = postAction.querySelector(".like-icon");
        const likeCount = postAction.querySelector(".like-count");

        if (likeIcon) {
          likeIcon.innerHTML = data.liked
            ? '<i class="bi bi-heart-fill text-danger"></i>'
            : '<i class="bi bi-heart"></i>';
        }

        if (likeCount && data.like_count !== undefined) {
          likeCount.textContent = data.like_count;
        }
      }

      // Update repost UI
      if (action === "repost") {
        const repostIcon = postAction.querySelector(".repost-icon");
        const repostCount = postAction.querySelector(".repost-count");

        if (repostIcon) {
          repostIcon.innerHTML = data.reposted
            ? '<i class="bi bi-repeat text-success"></i>'
            : '<i class="bi bi-repeat"></i>';
        }

        if (repostCount && data.repost_count !== undefined) {
          repostCount.textContent = data.repost_count;
        }
      }

      // Update bookmark UI
      if (action === "bookmark") {
        const bookmarkIcon = postAction.querySelector(".bookmark-icon");

        if (bookmarkIcon) {
          bookmarkIcon.innerHTML = data.bookmarked
            ? '<i class="bi bi-bookmark-fill text-primary"></i>'
            : '<i class="bi bi-bookmark"></i>';
        }

        const button = postAction.querySelector(`[data-action="bookmark"]`);
        if (button) {
          button.title = data.bookmarked
            ? "Remove from bookmarks"
            : "Add to bookmarks";
        }
      }
    });

    // Removed the showToast calls that were here
  }
});
