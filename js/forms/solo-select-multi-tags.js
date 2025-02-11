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
      once('soloMultiSelectTags', '.solo-select-multi.tags', context).forEach((customSelect) => {
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

    console.log("Initializing Multi-Select Tags:", customSelect);

    // Toggle dropdown open/close
    selectedDisplay.addEventListener("click", (event) => {
      event.stopPropagation();
      if (dropdownMenu.classList.contains("toggled")) {
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      } else {
        Drupal.solo.adjustDropdownHeight(customSelect, dropdownMenu);
        Drupal.solo.openDropdown(dropdownMenu, selectedDisplay);
      }
    });

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
        console.log("Clearing all selected tags");

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
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      }
    });

    // Enable keyboard navigation
    Drupal.solo.handleKeyboardNavigation(customSelect, dropdownMenu, selectedDisplay);

    // Ensure UI syncs with hidden select field on load
    syncUIWithHiddenSelect(hiddenSelect, options, selectedDisplay, clearAllButton);

    console.log("Multi-Select Tags initialized successfully.");
  }

  function syncUIWithHiddenSelect(hiddenSelect, options, selectedDisplay, clearAllButton) {
    console.log("Syncing UI with hidden select");

    const selectedValues = Array.from(hiddenSelect.options)
      .filter(option => option.selected)
      .map(option => option.value);

    selectedValues.forEach(value => {
      const correspondingOption = Array.from(options).find(opt => opt.dataset.value === value);
      if (correspondingOption) {
        correspondingOption.classList.add("hidden"); // Ensure selected values are hidden in dropdown
      }
    });

    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  }

})(Drupal, once);
