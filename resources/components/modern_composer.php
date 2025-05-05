<?php
// Set defaults if not provided
$is_reply = isset($is_reply) ? $is_reply : false;
$placeholder = isset($placeholder) ? $placeholder : "What's happening?";
$parent_post_id = isset($parent_post_id) ? $parent_post_id : null;
?>

<div class="composer-container rounded-4 bg-white">
  <form method="post" class="modern-composer-form" autocomplete="off">
    <!-- Add parent post ID if this is a reply -->
    <?php if ($is_reply && $parent_post_id): ?>
    <input type="hidden" name="parent_post_id" value="<?php echo $parent_post_id; ?>">
    <?php endif; ?>
    
    <!-- User info and textarea container -->
    <div class="d-flex">
      <!-- User avatar -->
      <div class="me-3">
        <?php 
          $profile_picture_url = $user['profile_picture_url'] ?? null;
          $username = $user['user_name'];
          $size = '48';
          include __DIR__ . '/user_avatar.php'; 
        ?>
      </div>
      
      <!-- Composer area -->
      <div class="flex-grow-1">
        <!-- Hidden field to store actual content -->
        <input type="hidden" name="tweet_content" id="hidden-content-field">
        
        <!-- Editable div for composer -->
        <div class="form-control border-0 p-0 mb-2 composer-editable" 
             contenteditable="true" 
             data-placeholder="<?php echo $placeholder; ?>" 
             id="composer-editable"></div>
             
        <!-- Real-time preview area -->
        <div class="preview-area rounded p-3 bg-light mb-3 d-none">
          <div class="small text-muted mb-1">Preview:</div>
          <div id="composer-preview"></div>
        </div>
             
        <!-- Suggestions dropdown -->
        <div class="position-relative">
          <div class="suggestion-dropdown bg-white rounded-3 shadow-sm position-absolute w-100 d-none" id="suggestion-dropdown"></div>
        </div>
        
        <!-- Character count and post button -->
        <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
          <div class="character-counter d-flex align-items-center">
            <div class="progress" style="width: 60px; height: 6px;">
              <div class="progress-bar" id="char-progress" role="progressbar" style="width: 0%"></div>
            </div>
            <span class="ms-2 small text-muted">
              <span id="char-count">0</span><span class="text-muted">/280</span>
            </span>
          </div>
          
          <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-medium" id="post-button" disabled>
            <?php echo $is_reply ? 'Reply' : 'Post'; ?>
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  /**
   * Modern Composer - Complete Rebuild
   * Handles contenteditable input, suggestions, and UI updates
   */
  class ModernComposer {
    constructor() {
      // Core elements
      this.editorEl = document.getElementById('composer-editable');
      this.hiddenField = document.getElementById('hidden-content-field');
      this.charCount = document.getElementById('char-count');
      this.charProgress = document.getElementById('char-progress');
      this.postButton = document.getElementById('post-button');
      this.suggestionDropdown = document.getElementById('suggestion-dropdown');
      this.previewArea = document.querySelector('.preview-area');
      this.previewContent = document.getElementById('composer-preview');
      
      // Constants and state
      this.MAX_LENGTH = 280;
      this.currentQuery = '';
      this.searchType = null; // 'hashtag' or 'mention'
      this.suggestionTimer = null;
      this.currentWordRange = null;
      
      // Initialize
      this.init();
    }
    
    /**
     * Initialize component
     */
    init() {
      if (!this.editorEl) return;
      
      // Set placeholder if content is empty
      this.updatePlaceholder();
      
      // Input handler
      this.editorEl.addEventListener('input', this.handleInput.bind(this));
      
      // Key events for navigation
      this.editorEl.addEventListener('keydown', this.handleKeyDown.bind(this));
      
      // Focus/blur events
      this.editorEl.addEventListener('focus', () => {
        // Store current selection for restoring later
        this.saveSelection();
      });
      
      // Form submission
      const form = document.querySelector('.modern-composer-form');
      if (form) {
        form.addEventListener('submit', this.handleSubmit.bind(this));
      }
      
      // Click outside to hide suggestions
      document.addEventListener('click', (e) => {
        if (!this.suggestionDropdown.contains(e.target) && e.target !== this.editorEl) {
          this.hideSuggestions();
        }
      });
    }
    
    /**
     * Handle user input in the editor
     */
    handleInput() {
      const text = this.editorEl.textContent;
      
      // Update character count and preview
      this.updateCharCount(text);
      this.updatePreview(text);
      this.updateHiddenField(text);
      this.updatePlaceholder();
      
      // Check for hashtags or mentions being typed
      this.checkForSuggestions();
    }
    
    /**
     * Handle key presses in the editor
     */
    handleKeyDown(e) {
      // If suggestions are shown, handle navigation
      if (!this.suggestionDropdown.classList.contains('d-none')) {
        const items = this.suggestionDropdown.querySelectorAll('.suggestion-item');
        let activeItem = this.suggestionDropdown.querySelector('.bg-light');
        let activeIndex = -1;
        
        // Find current active item index
        if (activeItem) {
          activeIndex = Array.from(items).indexOf(activeItem);
        }
        
        switch (e.key) {
          case 'ArrowDown':
            e.preventDefault();
            // Move selection down
            if (activeItem) activeItem.classList.remove('bg-light');
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            items[activeIndex].classList.add('bg-light');
            items[activeIndex].scrollIntoView({ block: 'nearest' });
            break;
            
          case 'ArrowUp':
            e.preventDefault();
            // Move selection up
            if (activeItem) activeItem.classList.remove('bg-light');
            activeIndex = Math.max(activeIndex - 1, 0);
            if (activeIndex >= 0) {
              items[activeIndex].classList.add('bg-light');
              items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
            break;
            
          case 'Escape':
            e.preventDefault();
            // Hide suggestions
            this.hideSuggestions();
            break;
            
          case 'Tab':
          case 'Enter':
            if (items.length > 0) {
              e.preventDefault();
              // Use currently selected or first item
              const itemToUse = activeItem || items[0];
              this.insertSuggestion(itemToUse.dataset.value);
            }
            break;
        }
      }
    }
    
    /**
     * Handle form submission
     */
    handleSubmit(e) {
      const text = this.editorEl.textContent.trim();
      
      if (text.length === 0 || text.length > this.MAX_LENGTH) {
        e.preventDefault();
        return false;
      }
      
      this.hiddenField.value = text;
      return true;
    }
    
    /**
     * Update the character counter
     */
    updateCharCount(text) {
      const length = text.length;
      this.charCount.textContent = length;
      
      // Update progress bar
      const percentage = (length / this.MAX_LENGTH) * 100;
      this.charProgress.style.width = `${percentage}%`;
      
      // Color coding based on remaining chars
      this.charProgress.classList.remove('bg-danger', 'bg-warning', 'bg-primary');
      
      if (length > this.MAX_LENGTH) {
        this.charProgress.classList.add('bg-danger');
      } else if (length > this.MAX_LENGTH * 0.8) {
        this.charProgress.classList.add('bg-warning');
      } else {
        this.charProgress.classList.add('bg-primary');
      }
      
      // Enable/disable post button
      this.postButton.disabled = length === 0 || length > this.MAX_LENGTH;
    }
    
    /**
     * Update the formatted preview
     */
    updatePreview(text) {
      // Format content with hashtags and mentions highlighted
      let formatted = text
        .replace(/#(\w+)/g, '<span class="text-primary fw-medium">#$1</span>')
        .replace(/@(\w+)/g, '<span class="text-primary fw-medium">@$1</span>')
        .replace(/https?:\/\/\S+/g, url => 
          `<a href="${url}" class="text-decoration-underline" target="_blank">${url}</a>`);
        
      // Update preview area visibility
      if (text.trim() === '') {
        this.previewArea.classList.add('d-none');
      } else {
        this.previewContent.innerHTML = formatted;
        this.previewArea.classList.remove('d-none');
      }
    }
    
    /**
     * Update hidden field with current content
     */
    updateHiddenField(text) {
      this.hiddenField.value = text;
    }
    
    /**
     * Update placeholder visibility
     */
    updatePlaceholder() {
      if (!this.editorEl.textContent.trim()) {
        this.editorEl.classList.add('empty');
      } else {
        this.editorEl.classList.remove('empty');
      }
    }
    
    /**
     * Check if user is typing a hashtag or mention
     */
    checkForSuggestions() {
      // Get current selection and text under cursor
      const result = this.getWordAtCursor();
      if (!result) {
        this.hideSuggestions();
        return;
      }
      
      const { word, range } = result;
      
      // Check for hashtag
      if (word.startsWith('#')) {
        const query = word.substring(1);
        if (query.length > 0) {
          this.currentQuery = query;
          this.searchType = 'hashtag';
          this.currentWordRange = range; // Store range for later insertion
          this.fetchSuggestions(query, 'hashtag');
          return;
        }
      }
      
      // Check for mention
      if (word.startsWith('@')) {
        const query = word.substring(1);
        if (query.length > 0) {
          this.currentQuery = query;
          this.searchType = 'mention';
          this.currentWordRange = range; // Store range for later insertion
          this.fetchSuggestions(query, 'mention');
          return;
        }
      }
      
      // Not typing hashtag or mention
      this.hideSuggestions();
    }
    
    /**
     * Get the word at current cursor position
     */
    getWordAtCursor() {
      const selection = window.getSelection();
      if (!selection.rangeCount) return null;
      
      const range = selection.getRangeAt(0);
      
      // Get the text node and position
      const node = range.startContainer;
      if (node.nodeType !== Node.TEXT_NODE) return null;
      
      const text = node.textContent;
      const cursorPos = range.startOffset;
      
      // Find word boundaries
      let startPos = cursorPos;
      while (startPos > 0 && !/\s/.test(text[startPos - 1])) {
        startPos--;
      }
      
      let endPos = cursorPos;
      while (endPos < text.length && !/\s/.test(text[endPos])) {
        endPos++;
      }
      
      // Create a word range for later replacement
      const wordRange = document.createRange();
      wordRange.setStart(node, startPos);
      wordRange.setEnd(node, endPos);
      
      const word = text.substring(startPos, endPos);
      
      return {
        word,
        range: wordRange
      };
    }
    
    /**
     * Fetch suggestions from API
     */
    fetchSuggestions(query, type) {
      // Clear previous timer
      clearTimeout(this.suggestionTimer);
      
      // Min length check
      if (query.length < 1) {
        this.hideSuggestions();
        return;
      }
      
      // Set new timer to avoid API spam
      this.suggestionTimer = setTimeout(() => {
        // Show loading indicator
        this.suggestionDropdown.innerHTML = `
          <div class="p-2 text-muted">
            <i class="bi bi-hourglass-split me-2"></i>Loading...
          </div>
        `;
        this.suggestionDropdown.classList.remove('d-none');
        
        // Make API request
        fetch(`/y/public/app/api/suggestions.php?type=${type}&query=${encodeURIComponent(query)}`)
          .then(response => {
            if (!response.ok) throw new Error('Network response error');
            return response.json();
          })
          .then(data => {
            if (data.length === 0) {
              this.hideSuggestions();
              return;
            }
            
            this.renderSuggestions(data, type);
          })
          .catch(error => {
            console.error('Error fetching suggestions:', error);
            this.hideSuggestions();
          });
      }, 300);
    }
    
    /**
     * Render suggestion dropdown
     */
    renderSuggestions(data, type) {
      let html = '';
      
      if (type === 'hashtag') {
        data.slice(0, 5).forEach(tag => {
          html += `<div class="suggestion-item p-2" data-value="${tag.name}">
                    <div class="d-flex align-items-center">
                      <div class="bg-primary-bg-subtle text-primary rounded-3 p-1 me-2">
                        <i class="bi bi-hash"></i>
                      </div>
                      <div>
                        <div class="fw-medium">${tag.name}</div>
                        <div class="small text-muted">${tag.count} posts</div>
                      </div>
                    </div>
                  </div>`;
        });
      } else {
        data.slice(0, 5).forEach(user => {
          html += `<div class="suggestion-item p-2" data-value="${user.username}">
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle overflow-hidden me-2" style="width: 32px; height: 32px;">
                        ${user.profile_picture ? 
                          `<img src="${user.profile_picture}" alt="${user.display_name || user.username}" class="w-100 h-100 object-fit-cover">` : 
                          `<div class="d-flex justify-content-center align-items-center h-100 bg-light">
                            <i class="bi bi-person-circle text-secondary" style="font-size: 1.2rem;"></i>
                          </div>`
                        }
                      </div>
                      <div>
                        <div class="fw-medium">${user.display_name || user.username}</div>
                        <div class="small text-muted">@${user.username}</div>
                      </div>
                    </div>
                  </div>`;
        });
      }
      
      this.suggestionDropdown.innerHTML = html;
      this.suggestionDropdown.classList.remove('d-none');
      
      // Highlight first item
      const firstItem = this.suggestionDropdown.querySelector('.suggestion-item');
      if (firstItem) firstItem.classList.add('bg-light');
      
      // Add click handlers
      this.suggestionDropdown.querySelectorAll('.suggestion-item').forEach(item => {
        // Insert suggestion on click
        item.addEventListener('click', e => {
          e.preventDefault();
          e.stopPropagation();
          
          // Insert the suggestion
          this.insertSuggestion(item.dataset.value);
        });
        
        // Highlight on hover
        item.addEventListener('mouseenter', () => {
          // Remove highlight from all items
          this.suggestionDropdown.querySelectorAll('.suggestion-item').forEach(el => {
            el.classList.remove('bg-light');
          });
          // Add highlight to current item
          item.classList.add('bg-light');
        });
      });
    }
    
    /**
     * Insert suggestion into editor
     */
    insertSuggestion(value) {
      // Save current selection
      this.saveSelection();
      
      // Focus editor
      this.editorEl.focus();
      
      // Make sure we have a valid range to replace
      if (!this.currentWordRange) {
        // Fallback: append to content
        const symbol = this.searchType === 'hashtag' ? '#' : '@';
        this.editorEl.textContent += `${symbol}${value} `;
      } else {
        // Replace current word with suggestion
        const symbol = this.searchType === 'hashtag' ? '#' : '@';
        
        // Create a selection and replace its content
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(this.currentWordRange);
        document.execCommand('insertText', false, `${symbol}${value} `);
      }
      
      // Update UI
      const text = this.editorEl.textContent;
      this.updateCharCount(text);
      this.updatePreview(text);
      this.updateHiddenField(text);
      this.updatePlaceholder();
      
      // Hide suggestions
      this.hideSuggestions();
    }
    
    /**
     * Hide suggestion dropdown
     */
    hideSuggestions() {
      this.suggestionDropdown.classList.add('d-none');
      this.currentQuery = '';
      this.searchType = null;
    }
    
    /**
     * Save current selection for later use
     */
    saveSelection() {
      const selection = window.getSelection();
      if (selection.rangeCount > 0) {
        this.savedRange = selection.getRangeAt(0).cloneRange();
      }
    }
    
    /**
     * Restore saved selection
     */
    restoreSelection() {
      if (this.savedRange) {
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(this.savedRange);
      }
    }
  }
  
  // Initialize the composer
  new ModernComposer();
});
</script>

<style>
  /* Use only necessary custom CSS that can't be done with Bootstrap */
  .composer-editable {
    min-height: 100px;
    max-height: 300px;
    overflow-y: auto;
    font-size: 1.1rem;
  }
  
  .composer-editable:focus {
    outline: none;
    box-shadow: none;
  }
  
  .composer-editable.empty:before {
    content: attr(data-placeholder);
    color: #6c757d;
    pointer-events: none;
  }
  
  .suggestion-dropdown {
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
  }
  
  .suggestion-item {
    cursor: pointer;
    transition: background-color var(--bs-transition-speed);
  }
  
  .transition-all {
    transition: all var(--bs-transition-speed);
  }
</style>