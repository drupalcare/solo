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
      once('soloMultiSelectTags', '.solo-select-multi.searchable', context).forEach((customSelect) => {
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
    const dropdownArrow = customSelect.querySelector('.select-multi-header-arrow');
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

    const searchInput = customSelect.querySelector('.multi-search-input');
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let optionsArray = Array.from(customSelect.querySelectorAll('.solo-select-multi-option'));
        // Filter options based on search input
        let filteredOptions = optionsArray.filter(option => {
          return option.textContent.trim().toLowerCase().includes(searchTerm);
        });
        // Separate options based on type
        let textOptions = [];
        let numberOptions = [];
        let emailOptions = [];
        let dateOptions = [];
        let entityOptions = [];
        let otherOptions = [];
        filteredOptions.forEach(option => {
          const optionText = option.textContent.trim();
          if (optionText.includes('@') && optionText.includes('.')) {
            // Faster email check (instead of regex)
            emailOptions.push(option);
          } else if (!isNaN(parseFloat(optionText)) && isFinite(optionText)) {
            // Detect numbers & floats efficiently
            numberOptions.push(option);
          } else if (optionText.length >= 10 && optionText[4] === '-' && optionText[7] === '-') {
            // Basic date check (YYYY-MM-DD format)
            dateOptions.push(option);
          } else if (optionText.startsWith("Node:") || optionText.startsWith("User:") || optionText.startsWith("Term:")) {
            // Entity references (Users, Nodes, Taxonomy)
            entityOptions.push(option);
          } else {
            // General text data
            textOptions.push(option);
          }
        });
        // Sort only text alphabetically
        textOptions.sort((a, b) => a.textContent.trim().localeCompare(b.textContent.trim()));
        // Hide all options before displaying the filtered ones
        optionsArray.forEach(option => option.classList.add('hidden'));
        // Append sorted text, then original order for other types
        [...textOptions, ...numberOptions, ...dateOptions, ...emailOptions, ...entityOptions, ...otherOptions].forEach(option => {
          option.classList.remove('hidden');
        });
      });
    }

once('soloMultiSelectDropdownEvents', dropdownMenu).forEach(() => {
  dropdownMenu.addEventListener("click", function(event) {
    event.stopPropagation();

    if (event.target.classList.contains("solo-select-multi-option")) {
      Drupal.solo.toggleMultiSelectOption(hiddenSelect, event.target, selectedDisplay, clearAllButton);
      event.target.classList.add('hidden'); // Hide instead of remove
    }
  });
});


once('clearAllTags', clearAllButton).forEach(() => {
  clearAllButton.addEventListener("click", function () {
    // Clear all selected options
    hiddenSelect.querySelectorAll("option").forEach(option => (option.selected = false));

    // Remove all selected tags inside .solo-multi-tag
    const multiTagContainer = selectedDisplay.querySelector('.solo-multi-tag');
    if (multiTagContainer) {
      multiTagContainer.innerHTML = ''; // Only remove tags, keep structure
    }

    // Unhide all options in the dropdown
    dropdownMenu.querySelectorAll('.solo-select-multi-option.hidden').forEach(option => {
      option.classList.remove('hidden');
    });

    // Sync state
    Drupal.solo.syncHiddenSelect(hiddenSelect, []);
    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
    hiddenSelect.dispatchEvent(new Event("change"));
  });
});



    // Close dropdown when clicking **outside**
    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      }
    });
    // Enable keyboard navigation
    Drupal.solo.handleKeyboardNavigation(customSelect, dropdownMenu, selectedDisplay);
    // Initialize selected options
    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  }
})(Drupal, once);
