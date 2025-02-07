/**
 * @file
 * Contains utility functions for Solo module multi-select.
 *
 * Filename: solo-multi-select.js
 * Website: https://www.flashwebcenter.com
 * Developer: Alaa Haddad https://www.alaahaddad.com.
 */

((Drupal, once) => {
  'use strict';

  /**
   * Drupal behavior for custom multi-select tags.
   */
  Drupal.behaviors.soloCustomMultiSelectTags = {
    attach: (context, settings) => {
      once('soloMultiSelectTags', '.solo-select-multi-searchable', context).forEach((customSelect) => {
        initializeCustomMultiSelectTags(customSelect);
      });
    }
  };

  function initializeCustomMultiSelectTags(customSelect) {
    const selectedDisplay = customSelect.querySelector('.solo-select-multi-header');
    const dropdownMenu = customSelect.querySelector('.solo-select-multi-content');
    const hiddenSelect = document.getElementById(customSelect.dataset.targetId);
    const options = customSelect.querySelectorAll('.solo-select-multi-option');
    const clearAllButton = customSelect.querySelector('.multi-clear-all');

    if (!selectedDisplay || !hiddenSelect || !options.length || !clearAllButton) {
      return;
    }

    // Toggle dropdown open/close
    selectedDisplay.addEventListener("click", (event) => {
      event.stopPropagation();
      if (dropdownMenu.classList.contains("toggled")) {
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      } else {
        Drupal.solo.adjustDropdownHeight(customSelect, dropdownMenu);
        Drupal.solo.openDropdown(dropdownMenu, selectedDisplay);

        // Focus the search input when dropdown opens
        if (searchInput) {
          setTimeout(() => searchInput.focus(), 100);
        }
      }
    });

    // Prevent closing when interacting with dropdown
    dropdownMenu.addEventListener("click", (event) => {
      event.stopPropagation();
    });

    const searchInput = customSelect.querySelector('.multi-search-input');
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        options.forEach(option => {
          const optionText = option.textContent.toLowerCase();
          if (optionText.includes(searchTerm)) {
            option.style.display = "";
          } else {
            option.style.display = "none";
          }
        });
      });
    }

    // Handle option selection
    options.forEach(option => {
      option.addEventListener("click", function () {
        Drupal.solo.toggleMultiSelectOption(hiddenSelect, this, selectedDisplay, clearAllButton);
      });
    });

    // Clear All functionality
    clearAllButton.addEventListener("click", function () {
      hiddenSelect.querySelectorAll("option").forEach(option => (option.selected = false));
      options.forEach(opt => opt.classList.remove("selected-option-multi"));

      Drupal.solo.syncHiddenSelect(hiddenSelect, []);
      Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
      hiddenSelect.dispatchEvent(new Event("change"));
    });

    // Close dropdown when clicking **outside**
    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      }
    });

    // Enable keyboard navigation
    Drupal.solo.handleKeyboardNavigation(customSelect, dropdownMenu, selectedDisplay);

    // Initialize UI with existing selections
    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  }
})(Drupal, once);
