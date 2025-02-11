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
   * Drupal behavior for custom multi-select searchable dropdowns.
   */
  Drupal.behaviors.soloCustomMultiSelectSearchable = {
    attach: (context, settings) => {
      once('soloMultiSelectSearchable', '.solo-select-multi.searchable', context).forEach((customSelect) => {
        initializeCustomMultiSelectSearchable(customSelect);
      });
    }
  };

  function initializeCustomMultiSelectSearchable(customSelect) {
    const selectedDisplay = customSelect.querySelector('.solo-select-multi-header');
    const dropdownMenu = customSelect.querySelector('.solo-select-multi-content');
    const hiddenSelect = document.getElementById(customSelect.dataset.targetId);
    const options = customSelect.querySelectorAll('.solo-select-multi-option');
    const clearAllButton = customSelect.querySelector('.multi-clear-all');
    const dropdownArrow = customSelect.querySelector('.select-multi-header-arrow');
    const searchInput = customSelect.querySelector('.multi-search-input');

    if (!selectedDisplay || !hiddenSelect || !options.length || !clearAllButton) {
      return;
    }

    console.log("Initializing Multi-Select Searchable Dropdown:", customSelect);

    // Toggle dropdown open/close
    selectedDisplay.addEventListener("click", (event) => {
      event.stopPropagation();
      if (dropdownMenu.classList.contains("toggled")) {
        console.log("Closing dropdown");
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      } else {
        console.log("Opening dropdown");
        Drupal.solo.adjustDropdownHeight(customSelect, dropdownMenu);
        Drupal.solo.openDropdown(dropdownMenu, selectedDisplay);
        // Focus the search input when dropdown opens
        if (searchInput) {
          setTimeout(() => searchInput.focus(), 100);
        }
      }
    });

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
          console.log("Option selected:", event.target.dataset.value);
        }
      });
    });

    once('clearAllTags', clearAllButton).forEach(() => {
      clearAllButton.addEventListener("click", function () {
        console.log("Clearing all selected options");

        // Clear all selected options
        hiddenSelect.querySelectorAll("option").forEach(option => {
          option.selected = false;
        });

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
        console.log("Clicked outside, closing dropdown");
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      }
    });

    // Enable keyboard navigation
    Drupal.solo.handleKeyboardNavigation(customSelect, dropdownMenu, selectedDisplay);

    // Ensure UI syncs with hidden select field on load
    syncUIWithHiddenSelect(hiddenSelect, options, selectedDisplay, clearAllButton);

    // Initialize UI with existing selections
    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);

    console.log("Multi-Select Searchable Dropdown initialized successfully.");
  }

  /**
   * Ensure UI reflects the hidden select field at page load.
   */
  function syncUIWithHiddenSelect(hiddenSelect, options, selectedDisplay, clearAllButton) {
    console.log("Syncing UI with hidden select");

    const selectedValues = Array.from(hiddenSelect.options)
      .filter(option => option.selected)
      .map(option => option.value);

    selectedValues.forEach(value => {
      const correspondingOption = Array.from(options).find(opt => opt.dataset.value === value);
      if (correspondingOption) {
        correspondingOption.classList.add("hidden"); // Ensure selected values are hidden in dropdown
        console.log(`Hiding selected option in dropdown: ${value}`);
      }
    });

    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  }

})(Drupal, once);
