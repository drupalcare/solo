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
      once('soloMultiSelectSearchable', '.solo-select-multi-searchable', context).forEach((customSelect) => {
        initializeCustomMultiSelectSearchable(customSelect);
      });
    }
  };

function initializeCustomMultiSelectSearchable(customSelect) {
  const selectedDisplay = customSelect.querySelector('.solo-select-multi-search-header');
  const searchInput = customSelect.querySelector('.multi-search-input');
  const dropdownMenu = customSelect.querySelector('.solo-select-multi-content');
  const options = customSelect.querySelectorAll('.solo-select-multi-option');
  const hiddenSelect = document.getElementById(customSelect.dataset.targetId);

  if (!selectedDisplay || !dropdownMenu || !options.length || !hiddenSelect || !searchInput) {
    return;
  }

  // Toggle dropdown open/close
  selectedDisplay.addEventListener("click", () => {
    if (dropdownMenu.classList.contains("toggled")) {
      closeDropdown(dropdownMenu, selectedDisplay);
    } else {
      adjustDropdownHeight(customSelect, dropdownMenu);
      openDropdown(dropdownMenu, selectedDisplay);
    }
  });

  // Ensure options can be selected
  options.forEach(option => {
    option.addEventListener("click", function (event) {
      event.stopPropagation(); // Prevent dropdown from closing immediately

      const value = this.dataset.value;
      const optionInHiddenSelect = hiddenSelect.querySelector(`option[value="${value}"]`);

      if (optionInHiddenSelect) {
        optionInHiddenSelect.selected = !optionInHiddenSelect.selected;
        this.classList.toggle("selected-option-multi", optionInHiddenSelect.selected);
      }

      hiddenSelect.dispatchEvent(new Event("change"));
      updateMultiSelectDisplay(hiddenSelect, selectedDisplay);
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (event) => {
    if (!customSelect.contains(event.target)) {
      closeDropdown(dropdownMenu, selectedDisplay);
    }
  });

  // Search functionality
  searchInput.addEventListener("input", function () {
    const searchValue = this.value.toLowerCase();
    options.forEach(option => {
      const text = option.textContent.toLowerCase();
      option.style.display = text.includes(searchValue) ? "block" : "none";
    });
  });

  // Initialize selected options
  updateMultiSelectDisplay(hiddenSelect, selectedDisplay);
}

/**
 * Opens the dropdown with JavaScript animation.
 */
function openDropdown(dropdown, selectedDisplay) {
  Drupal.solo.slideDown(dropdown);
  dropdown.classList.add("toggled");
  dropdown.setAttribute("aria-hidden", "false");
  selectedDisplay.classList.add("open");
}

/**
 * Closes the dropdown with JavaScript animation.
 */
function closeDropdown(dropdown, selectedDisplay) {
  Drupal.solo.slideUp(dropdown);
  dropdown.classList.remove("toggled");
  dropdown.setAttribute("aria-hidden", "true");
  selectedDisplay.classList.remove("open");
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
  function updateMultiSelectDisplay(hiddenSelect, selectedDisplay) {
    const selectedValues = Array.from(hiddenSelect.options)
      .filter(opt => opt.selected)
      .map(opt => `<span class="solo-multi-tag" data-value="${opt.value}">
        ${opt.textContent} <button class="remove-tag">✕</button>
      </span>`).join(" ");

    selectedDisplay.innerHTML = selectedValues.length > 0 ? selectedValues : '<span class="placeholder-text">Select options...</span>';

    // Attach remove event to dynamically added buttons
    selectedDisplay.querySelectorAll('.remove-tag').forEach(button => {
      button.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent dropdown from opening
        removeTag(hiddenSelect, this.parentElement, selectedDisplay);
      });
    });
  }

  /**
   * Handles multi-select logic: adds/removes selected options.
   */
  function toggleMultiSelectOption(hiddenSelect, optionElement, selectedDisplay) {
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
    updateMultiSelectDisplay(hiddenSelect, selectedDisplay);
  }

  /**
   * Removes a selected tag and updates the multi-select dropdown.
   */
  function removeTag(hiddenSelect, tagElement, selectedDisplay) {
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
    updateMultiSelectDisplay(hiddenSelect, selectedDisplay);
  }

})(Drupal, once);
