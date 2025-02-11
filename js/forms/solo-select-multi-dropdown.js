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
   * Drupal behavior for the custom multi-select dropdown.
   */
  Drupal.behaviors.soloCustomMultiSelectDropdown = {
    attach: (context, settings) => {
      once('soloMultiSelectDropdown', '.solo-select-multi.dropdown', context).forEach((customSelect) => {
        initializeCustomMultiSelectDropdown(customSelect);
        let maxSelections = customSelect.getAttribute('data-max-selections');
        maxSelections = maxSelections === 'null' ? null : parseInt(maxSelections, 10);
        //const maxSelections = drupalSettings.tmp_cleaner.maxSelections[customSelect] ?? null;
        Drupal.solo.handleSelectionLimit(customSelect, 'input[type="checkbox"]', maxSelections);
      });
    }
  };

  function initializeCustomMultiSelectDropdown(customSelect) {
    const selectedDisplay = customSelect.querySelector('.solo-select-multi-header');
    const dropdownMenu = customSelect.querySelector('.solo-select-multi-content');
    const options = customSelect.querySelectorAll('.solo-select-multi-option input[type="checkbox"]');
    const clearAllButton = customSelect.querySelector('.multi-clear-all');
    const dropdownArrow = customSelect.querySelector('.select-multi-header-arrow');
    const hiddenSelect = customSelect.nextElementSibling;
    if (!hiddenSelect || hiddenSelect.tagName !== 'SELECT') {
      console.error('Selectify: Could not find the corresponding hidden <select> for', customSelect);
      return;
    }
    if (!selectedDisplay || !dropdownMenu || !options.length) {
      return;
    }
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
      }
    });
    // Handle option selection
    dropdownMenu.addEventListener("change", function(event) {
      if (event.target.matches('.solo-select-multi-option input[type="checkbox"]')) {
        const optionValue = event.target.value;
        const correspondingOption = hiddenSelect.querySelector(`option[value="${optionValue}"]`);
        if (correspondingOption) {
          correspondingOption.selected = event.target.checked;
          console.log(`Option changed: ${optionValue} - Selected: ${event.target.checked}`);
        }
        // Sync selected values
        Drupal.solo.syncHiddenSelect(hiddenSelect, Drupal.solo.getSelectedValues(hiddenSelect));
        Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
        hiddenSelect.dispatchEvent(new Event("change"));
      }
    });
    // Clear All functionality
    if (clearAllButton) {
      clearAllButton.addEventListener("click", function() {
        console.log("Clearing all selected options");
        hiddenSelect.querySelectorAll("option").forEach(option => (option.selected = false));
        options.forEach(opt => (opt.checked = false));
        Drupal.solo.syncHiddenSelect(hiddenSelect, []);
        Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
        hiddenSelect.dispatchEvent(new Event("change"));
      });
    }
    // Close dropdown when clicking outside
    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        console.log("Clicked outside, closing dropdown");
        Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
      }
    });
    // Enable keyboard navigation
    Drupal.solo.handleKeyboardNavigation(customSelect, dropdownMenu, selectedDisplay);
    // Ensure UI syncs with hidden select field on load
    syncUIWithHiddenSelect(hiddenSelect, options);
    // Initialize UI with existing selections
    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
    console.log("Multi-Select Dropdown initialized successfully.");
  }
  /**
   * Ensure checkboxes match the hidden select field at page load.
   */
  function syncUIWithHiddenSelect(hiddenSelect, checkboxes) {
    console.log("Syncing UI with hidden select");
    const selectedValues = Drupal.solo.getSelectedValues(hiddenSelect);
    checkboxes.forEach(checkbox => {
      checkbox.checked = selectedValues.includes(checkbox.value);
      console.log(`Checkbox: ${checkbox.value} - Checked: ${checkbox.checked}`);
    });
  }
})(Drupal, once);
