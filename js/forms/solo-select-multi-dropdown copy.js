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

  Drupal.behaviors.soloCustomMultiSelectDropdown = {
    attach: (context, settings) => {
      once('soloMultiSelectDropdown', '.solo-select-multi-dropdown', context).forEach((customSelect) => {
        initializeCustomMultiSelectDropdown(customSelect);
      });
    }
  };

  function initializeCustomMultiSelectDropdown(customSelect) {
    const selectedDisplay = customSelect.querySelector('.solo-select-multi-header');
    const selectedText = customSelect.querySelector('.select-multi-header-text');
    const arrowIcon = customSelect.querySelector('.select-multi-header-arrow');
    const dropdownMenu = customSelect.querySelector('.solo-select-multi-content');
    const options = customSelect.querySelectorAll('.solo-select-multi-option input[type="checkbox"]');
    const clearAllButton = customSelect.querySelector('.multi-clear-all');
    const hiddenSelect = document.getElementById(customSelect.dataset.targetId); // Get the hidden <select>

    const maxSelections = customSelect.dataset.maxSelections !== 'null' ? parseInt(customSelect.dataset.maxSelections, 10) : null;

    if (!selectedDisplay || !dropdownMenu || !options.length || !hiddenSelect) {
      return;
    }

    // Toggle dropdown open/close
    selectedDisplay.addEventListener("click", () => {
      if (dropdownMenu.classList.contains("toggled")) {
        closeDropdown(dropdownMenu, arrowIcon);
      } else {
        openDropdown(dropdownMenu, arrowIcon);
        scrollToFirstSelectedOption(dropdownMenu);
      }
    });

    // Handle option selection with limit enforcement
    options.forEach(option => {
      option.addEventListener("change", function() {
        if (maxSelections && getSelectedCount(customSelect) > maxSelections) {
          this.checked = false;
          showLimitReachedMessage(customSelect);
        }
        updateSelectedOptions(customSelect, selectedText, hiddenSelect);
      });
    });

    // Handle "Clear All" click
    if (clearAllButton) {
      clearAllButton.addEventListener("click", function () {
        options.forEach(option => option.checked = false);
        updateSelectedOptions(customSelect, selectedText, hiddenSelect);
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        closeDropdown(dropdownMenu, arrowIcon);
      }
    });

    // Keyboard navigation
    customSelect.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        selectedDisplay.click();
      } else if (event.key === "Escape") {
        closeDropdown(dropdownMenu, arrowIcon);
      }
    });

    // Initialize selected options
    updateSelectedOptions(customSelect, selectedText, hiddenSelect);
  }

  /**
   * Scroll to the first selected option inside the dropdown.
   */
  function scrollToFirstSelectedOption(dropdownMenu) {
    const firstSelected = dropdownMenu.querySelector('.solo-select-multi-option input[type="checkbox"]:checked');
    if (firstSelected) {
      firstSelected.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function openDropdown(dropdown, arrowIcon) {
    Drupal.solo.slideDown(dropdown);
    dropdown.classList.add("toggled");
    arrowIcon.classList.add("open-dropdown");
    dropdown.setAttribute("aria-hidden", "false");
  }

  function closeDropdown(dropdown, arrowIcon) {
    Drupal.solo.slideUp(dropdown);
    dropdown.classList.remove("toggled");
    arrowIcon.classList.remove("open-dropdown");
    dropdown.setAttribute("aria-hidden", "true");
  }

  /**
   * Syncs selected checkboxes with the hidden <select multiple> field.
   */
  function updateSelectedOptions(customSelect, selectedText, hiddenSelect) {
    const selectedOptions = Array.from(customSelect.querySelectorAll('.solo-select-multi-option input[type="checkbox"]:checked'))
      .map(option => option.value);

    selectedText.textContent = selectedOptions.length > 0 ? selectedOptions.join(', ') : 'Select options...';

    // Sync selected values to the hidden <select>
    hiddenSelect.querySelectorAll("option").forEach(option => {
      option.selected = selectedOptions.includes(option.value);
    });

    hiddenSelect.dispatchEvent(new Event("change")); // Ensure Drupal recognizes the update
  }

  function getSelectedCount(customSelect) {
    return customSelect.querySelectorAll('.solo-select-multi-option input[type="checkbox"]:checked').length;
  }

  function showLimitReachedMessage(customSelect) {
    if (!customSelect.querySelector('.multi-select-limit-warning')) {
      const warning = document.createElement('div');
      warning.classList.add('multi-select-limit-warning');
      warning.textContent = `You can only select up to ${customSelect.dataset.maxSelections} options.`;
      customSelect.appendChild(warning);

      setTimeout(() => {
        warning.remove();
      }, 3000);
    }
  }

})(Drupal, once);

