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
      once('soloMultiSelectTags', '.solo-select-multi-tags', context).forEach((customSelect) => {
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

    // Attach "Clear All" functionality
    clearAllButton.addEventListener("click", function () {
      hiddenSelect.querySelectorAll("option").forEach(option => (option.selected = false));
      options.forEach(opt => opt.classList.remove("selected-option-multi"));
      updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
      hiddenSelect.dispatchEvent(new Event("change"));
    });

    // Attach click events to each option
    options.forEach(option => {
      option.addEventListener("click", function () {
        toggleMultiSelectOption(hiddenSelect, this, selectedDisplay, clearAllButton);
      });
    });

    // Toggle dropdown open/close
    selectedDisplay.addEventListener("click", () => {
      if (dropdownMenu.classList.contains("toggled")) {
        closeDropdown(dropdownMenu);
      } else {
        adjustDropdownHeight(customSelect, dropdownMenu);
        openDropdown(dropdownMenu);
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        closeDropdown(dropdownMenu);
      }
    });

    // Initialize selected options
    updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  }

  /**
   * Adjust dropdown height dynamically based on available space.
   */
  function adjustDropdownHeight(customSelect, dropdownMenu) {
    const windowHeight = window.innerHeight;
    const dropdownRect = customSelect.getBoundingClientRect();
    const spaceBelow = windowHeight - dropdownRect.bottom;
    const spaceAbove = dropdownRect.top;

    let maxHeight = Math.min(spaceBelow - 20, 300); // Default limit: 300px
    if (maxHeight < 150 && spaceAbove > spaceBelow) {
      maxHeight = Math.min(spaceAbove - 20, 300); // Open upwards if space is limited below
      dropdownMenu.style.top = "auto";
      dropdownMenu.style.bottom = "100%";
    } else {
      dropdownMenu.style.top = "100%";
      dropdownMenu.style.bottom = "auto";
    }

    dropdownMenu.style.maxHeight = `${maxHeight}px`;
  }

  function openDropdown(dropdown) {
    Drupal.solo.slideDown(dropdown);
    dropdown.classList.add("toggled");
    dropdown.setAttribute("aria-hidden", "false");
  }

  function closeDropdown(dropdown) {
    Drupal.solo.slideUp(dropdown);
    dropdown.classList.remove("toggled");
    dropdown.setAttribute("aria-hidden", "true");
  }

  /**
   * Updates the display for selected multi-select options.
   */
  function updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton) {
    const selectedValues = Array.from(hiddenSelect.options)
      .filter(opt => opt.selected)
      .map(opt => `<span class="solo-multi-tag" data-value="${opt.value}">
        ${opt.textContent} <button class="remove-tag">✕</button>
      </span>`).join(" ");

    selectedDisplay.innerHTML = selectedValues.length > 0 ? selectedValues : '<span class="placeholder-text">Select options...</span>';

    // Show/hide "Clear All" button based on selection
    clearAllButton.style.display = selectedValues.length > 0 ? 'inline-flex' : 'none';

    // Attach remove event to dynamically added buttons
    selectedDisplay.querySelectorAll('.remove-tag').forEach(button => {
      button.addEventListener("click", function (event) {
        event.stopPropagation();
        removeTag(hiddenSelect, this.parentElement, selectedDisplay, clearAllButton);
      });
    });
  }

  /**
   * Handles multi-select logic: adds/removes selected options.
   */
  function toggleMultiSelectOption(hiddenSelect, optionElement, selectedDisplay, clearAllButton) {
    const value = optionElement.dataset.value;
    const option = hiddenSelect.querySelector(`option[value="${value}"]`);

    if (option.selected) {
      option.selected = false;
      optionElement.classList.remove("selected-option-multi");
    } else {
      option.selected = true;
      optionElement.classList.add("selected-option-multi");
    }

    hiddenSelect.dispatchEvent(new Event("change")); // Ensure Drupal recognizes the update
    updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  }

  /**
   * Removes a selected tag and updates the multi-select dropdown.
   */
  function removeTag(hiddenSelect, tagElement, selectedDisplay, clearAllButton) {
    const value = tagElement.dataset.value;
    const option = hiddenSelect.querySelector(`option[value="${value}"]`);

    if (option) {
      option.selected = false;
      const optionElement = document.querySelector(`.solo-select-multi-option[data-value="${value}"]`);
      if (optionElement) {
        optionElement.classList.remove("selected-option-multi");
      }
    }

    hiddenSelect.dispatchEvent(new Event("change"));
    updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  }

})(Drupal, once);



