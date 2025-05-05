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
        console.log("Response data:", data); // Debug logging
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
    console.log(`Updating UI for ${action}:`, data);

    // Find all instances of this post on the page
    const postActions = document.querySelectorAll(
      `.post-actions[data-post-id="${postId}"]`
    );

    postActions.forEach((postAction) => {
      // Update like UI
      if (action === "like") {
        const likeWrapper = postAction.querySelector(".like-icon-wrapper");
        const likeIcon = likeWrapper ? likeWrapper.querySelector("i") : null;
        const likeCount = postAction.querySelector(".like-count");

        if (likeWrapper && likeIcon) {
          if (data.liked) {
            likeWrapper.classList.add("active");
            // Force color update by setting style directly
            likeIcon.className = "bi bi-heart-fill";
            likeIcon.style.color = "#dc3545";
          } else {
            likeWrapper.classList.remove("active");
            likeIcon.className = "bi bi-heart";
            likeIcon.style.color = "#6c757d"; // Reset to default
          }
        }

        if (likeCount && data.like_count !== undefined) {
          likeCount.textContent = data.like_count;
        }
      }

      // Update repost UI
      else if (action === "repost") {
        const repostWrapper = postAction.querySelector(".repost-icon-wrapper");
        const repostIcon = repostWrapper
          ? repostWrapper.querySelector("i")
          : null;
        const repostCount = postAction.querySelector(".repost-count");

        if (repostWrapper && repostIcon) {
          if (data.reposted) {
            repostWrapper.classList.add("active");
            // Force color update
            repostIcon.style.color = "#198754";
          } else {
            repostWrapper.classList.remove("active");
            repostIcon.style.color = "#6c757d"; // Reset to default
          }
        }

        if (repostCount && data.repost_count !== undefined) {
          repostCount.textContent = data.repost_count;
        }
      }

      // Update bookmark UI
      else if (action === "bookmark") {
        const bookmarkWrapper = postAction.querySelector(
          ".bookmark-icon-wrapper"
        );
        const bookmarkIcon = bookmarkWrapper
          ? bookmarkWrapper.querySelector("i")
          : null;

        if (bookmarkWrapper && bookmarkIcon) {
          if (data.bookmarked) {
            bookmarkWrapper.classList.add("active");
            bookmarkIcon.className = "bi bi-bookmark-fill";
            // Force color update
            bookmarkIcon.style.color = "#0dcaf0";
          } else {
            bookmarkWrapper.classList.remove("active");
            bookmarkIcon.className = "bi bi-bookmark";
            bookmarkIcon.style.color = "#6c757d"; // Reset to default
          }
        }

        const button = postAction.querySelector(`[data-action="bookmark"]`);
        if (button) {
          button.title = data.bookmarked
            ? "Remove from bookmarks"
            : "Add to bookmarks";
        }
      }
    });
  }
});
