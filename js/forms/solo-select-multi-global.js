/**
 * @file
 * Contains utility functions for Solo module multi-select.
 *
 * Filename: solo-multi-select.js
 * Website: https://www.flashwebcenter.com
 * Developer: Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal) => {
  'use strict';
  // Ensure Drupal.solo namespace exists
  Drupal.solo = Drupal.solo || {};


Drupal.solo.handleSelectionLimit = function (container, itemSelector, maxSelections) {
  if (!Number.isInteger(maxSelections)) {
    return;
  }

  const items = container.querySelectorAll(itemSelector);
  //const hiddenSelect = document.querySelector(`[data-target-id="${container.dataset.targetId}"]`);
  const hiddenSelect = container.nextElementSibling;
  const selectedDisplay = container.querySelector('.solo-select-multi-header');
  const clearAllButton = container.querySelector('.multi-clear-all');

  if (!hiddenSelect) {
    console.error("Selectify: Hidden select not found for", container);
    return;
  }

  // Get the first matching item to determine the type
  const firstItem = items[0];
  if (!firstItem) {
    console.warn("Selectify: No items found for selector", itemSelector);
    return;
  }

  let selectedCount = 0;

  // ✅ **Run the checkbox loop if the first item is a checkbox**
  if (firstItem.tagName === "INPUT" && firstItem.type === "checkbox") {
    items.forEach((item) => {
      item.addEventListener('change', function () {
        let selectedItems = [...items].filter(i => i.checked);
        selectedCount = selectedItems.length;
        console.log('Number of checkboxes selected:', selectedCount);

        if (this.checked) {
          if (selectedCount > maxSelections) {
            this.checked = false;
            selectedCount--;
            return;
          }
        } else {
          selectedCount--;
        }

        if (selectedCount === maxSelections) {
          return;
        }

        const selectedValues = selectedItems.map(i => i.value);

        // Sync hidden select
        Drupal.solo.syncHiddenSelect(hiddenSelect, selectedValues);

        Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);

      });
    });
  }

  // ✅ **Run the non-checkbox loop if the first item is not a checkbox**
  else {
    items.forEach((item) => {
      item.addEventListener('click', function (event) {
        event.preventDefault();

        let selectedItems = [...items].filter(i => i.classList.contains('selected'));
        selectedCount = selectedItems.length;
        console.log('Number of non-checkbox items selected:', selectedCount);

        if (!item.classList.contains('selected')) {
          if (selectedCount >= maxSelections) {
            return;
          }
          item.classList.add('selected');
          selectedCount++;
        } else {
          item.classList.remove('selected');
          selectedCount--;
        }

        selectedItems = [...items].filter(i => i.classList.contains('selected'));
        const selectedValues = selectedItems.map(i => i.getAttribute('data-value') || i.textContent.trim());

        // Sync hidden select
        Drupal.solo.syncHiddenSelect(hiddenSelect, selectedValues);



        Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
      });
    });
  }
};




  Drupal.solo.openDropdown = (dropdown, triggerElement = null) => {
    document.querySelectorAll(".solo-select-multi-content.toggled").forEach(otherDropdown => {
      if (otherDropdown !== dropdown) {
        Drupal.solo.closeDropdown(otherDropdown);
      }
    });
    Drupal.solo.slideDown(dropdown);
    const parentSelect = triggerElement.closest('.solo-select-multi');
    if (parentSelect) {
      parentSelect.classList.add("multi-opened");
    }
  };
  /**
   * Closes the dropdown using the existing `slideUp` function.
   */
  Drupal.solo.closeDropdown = (dropdown, triggerElement = null) => {
    Drupal.solo.slideUp(dropdown);
    const parentSelect = triggerElement.closest('.solo-select-multi');
    if (parentSelect) {
      parentSelect.classList.remove("multi-opened");
    }
  };
  /**
   * Adjusts dropdown height dynamically.
   */
  Drupal.solo.adjustDropdownHeight = (customSelect, dropdownMenu) => {
    const windowHeight = window.innerHeight;
    const dropdownRect = customSelect.getBoundingClientRect();
    const spaceBelow = windowHeight - dropdownRect.bottom;
    const spaceAbove = dropdownRect.top;
    let maxHeight = Math.min(spaceBelow - 20, 300);
    if (maxHeight < 150 && spaceAbove > spaceBelow) {
      maxHeight = Math.min(spaceAbove - 20, 300);
      dropdownMenu.style.top = "auto";
      dropdownMenu.style.bottom = "100%";
    } else {
      dropdownMenu.style.top = "100%";
      dropdownMenu.style.bottom = "auto";
    }
    dropdownMenu.style.maxHeight = `${maxHeight}px`;
  };
  /**
   * Synchronizes the hidden `<select>` field with UI selections.
   */
  Drupal.solo.syncHiddenSelect = (hiddenSelect, selectedValues) => {
    hiddenSelect.querySelectorAll("option").forEach(option => {
      option.selected = selectedValues.includes(option.value);
    });
    hiddenSelect.dispatchEvent(new Event("change"));
  };
  /**
   * Updates the displayed selected options.
   */
  Drupal.solo.updateMultiSelectDisplay = (hiddenSelect, selectedDisplay, clearAllButton) => {
    const selectedValues = Drupal.solo.getSelectedValues(hiddenSelect);
    const parentMultiSelect = clearAllButton.closest('.solo-select-multi');
    if (!parentMultiSelect) return;
    const placeholder = selectedDisplay.querySelector('.placeholder-text');
    const arrowIcon = selectedDisplay.querySelector('.select-multi-header-arrow');
    // Remove existing .solo-multi-tag while keeping the arrow
    let existingWrapper = selectedDisplay.querySelector('.solo-multi-tag');
    if (existingWrapper) {
      existingWrapper.remove();
    }
    // Detect Multi-Select Type
    const isTags = parentMultiSelect.classList.contains('tags');
    const isSearchable = parentMultiSelect.classList.contains('searchable');
    const isDropdown = parentMultiSelect.classList.contains('dropdown');
    const isDualList = parentMultiSelect.classList.contains('dual-list');
    // **Refactored: Handle tags & searchable using a separate function**
    if (isTags || isSearchable) {
      Drupal.solo.renderMultiSelectTags(hiddenSelect, selectedDisplay, placeholder, arrowIcon, selectedValues);
    }
    // Handle Dropdown Multi-Select
    if (isDropdown) {
      Drupal.solo.renderDropdownMultiSelect(hiddenSelect, selectedDisplay, arrowIcon, selectedValues);
    }
    // Ensure "Clear All" button visibility
    clearAllButton.classList.toggle('hidden', selectedValues.length === 0);
    // Reattach event listeners for tag removal
    selectedDisplay.querySelectorAll('.remove-tag').forEach(button => {
      button.addEventListener("click", function(event) {
        event.stopPropagation();
        Drupal.solo.removeTag(hiddenSelect, this.parentElement, selectedDisplay, clearAllButton);
      });
    });
  };
  /**
   * Renders the selected values inside the multi-select header (for tags & searchable).
   */
  Drupal.solo.renderMultiSelectTags = (hiddenSelect, selectedDisplay, placeholder, arrowIcon, selectedValues) => {
    if (selectedValues.length > 0) {
      let selectedItemsHTML = '';
      selectedValues.forEach(value => {
        const option = hiddenSelect.querySelector(`option[value="${value}"]`);
        if (option) {
          selectedItemsHTML += `
                    <div class="solo-multi-tag-item" data-value="${value}">
                        <span>${option.textContent}</span>
                        <button class="remove-tag">
                            <svg fill="var(--r-tx)" class="icon-delete" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>
                        </button>
                    </div>`;
        }
      });
      // Create the .solo-multi-tag wrapper
      const wrapperHTML = `
            <div class="solo-multi-tag">
                ${selectedItemsHTML}
            </div>
        `;
      // Insert the wrapper before the arrow
      arrowIcon.insertAdjacentHTML('beforebegin', wrapperHTML);
      // Hide placeholder when selections exist
      if (placeholder) {
        placeholder.classList.add('hidden');
      }
    } else {
      // Show placeholder if no selection
      if (placeholder) {
        placeholder.classList.remove('hidden');
      }
    }
  };
  Drupal.solo.renderDropdownMultiSelect = (hiddenSelect, selectedDisplay, arrowIcon, selectedValues) => {
    let dropdownHTML = ``;
    // Check if any options are selected
    if (selectedValues.length > 0) {
      dropdownHTML += `<div class="solo-multi-tag">`;
      // Loop through selected values
      selectedValues.forEach(value => {
        const option = hiddenSelect.querySelector(`option[value="${value}"]`);
        if (option) {
          dropdownHTML += `
                    <span class="solo-multi-tag-item" data-value="${value}">
                        ${option.textContent}
                    </span>`;
        }
      });
      dropdownHTML += `</div>`; // Close .solo-multi-tag div
    }
    // Append the dropdown arrow (expansion icon)
    dropdownHTML += `
        <span class="select-multi-header-arrow">
            ${arrowIcon.innerHTML}
        </span>`;
    // Apply the new HTML inside `selectedDisplay`
    selectedDisplay.innerHTML = dropdownHTML;
  };
  /**
   * Toggles option selection.
   */
  Drupal.solo.toggleMultiSelectOption = (hiddenSelect, optionElement, selectedDisplay, clearAllButton) => {
    const value = optionElement.dataset.value;
    const option = hiddenSelect.querySelector(`option[value="${value}"]`);
    if (option.selected) {
      option.selected = false;
      optionElement.classList.remove("selected-option-multi");
    } else {
      option.selected = true;
      optionElement.classList.add("selected-option-multi");
    }
    Drupal.solo.syncHiddenSelect(hiddenSelect, Array.from(hiddenSelect.options).filter(opt => opt.selected).map(opt => opt.value));
    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
  };
  /**
   * Removes a selected tag.
   */
  Drupal.solo.removeTag = (hiddenSelect, tagElement, selectedDisplay, clearAllButton) => {
    if (!tagElement) {
      return;
    }
    const value = tagElement.dataset.value;
    const option = hiddenSelect.querySelector(`option[value="${value}"]`);
    if (option) {
      option.selected = false;
    }
    const optionElement = document.querySelector(`.solo-select-multi-option[data-value="${value}"]`);
    if (optionElement) {
      optionElement.classList.remove("selected-option-multi");
      optionElement.classList.remove("hidden"); // Ensure option becomes visible again
    }
    // Sync the hidden select field
    const selectedValues = Array.from(hiddenSelect.options)
      .filter(opt => opt.selected)
      .map(opt => opt.value);
    Drupal.solo.syncHiddenSelect(hiddenSelect, selectedValues);
    Drupal.solo.updateMultiSelectDisplay(hiddenSelect, selectedDisplay, clearAllButton);
    // Remove the tag from the UI
    tagElement.remove();
  };
  /**
   * Retrieves selected values from a hidden `<select>`.
   */
  Drupal.solo.getSelectedValues = (hiddenSelect) => {
    return Array.from(hiddenSelect.options)
      .filter(option => option.selected)
      .map(option => option.value);
  };
  /**
   * Handles keyboard navigation for multi-select components.
   */
  Drupal.solo.handleKeyboardNavigation = (customSelect, dropdownMenu, selectedDisplay) => {
    // Ensure keyboard navigation event is only bound once per dropdown
    once('soloMultiSelectKeyboard', customSelect).forEach(() => {
      customSelect.addEventListener("keydown", (event) => {
        const key = event.key;
        const isOpen = dropdownMenu.classList.contains("toggled");
        if ((key === "Enter" || key === " ") && !isOpen) {
          event.preventDefault();
          Drupal.solo.openDropdown(dropdownMenu, selectedDisplay);
        } else if (key === "Escape" && isOpen) {
          Drupal.solo.closeDropdown(dropdownMenu, selectedDisplay);
        } else if (key === "ArrowDown" || key === "ArrowUp") {
          event.preventDefault();
          const options = dropdownMenu.querySelectorAll('.solo-select-multi-option');
          const selected = dropdownMenu.querySelector('.selected-option-multi');
          let newIndex;
          if (selected) {
            const currentIndex = Array.from(options).indexOf(selected);
            newIndex = key === "ArrowDown" ? currentIndex + 1 : currentIndex - 1;
          } else {
            newIndex = key === "ArrowDown" ? 0 : options.length - 1;
          }
          if (newIndex >= 0 && newIndex < options.length) {
            options[newIndex].focus();
          }
        }
      });
    });
  };
  // Ensure dropdown close event is only bound once
  once('soloMultiSelectCloseDropdown', document).forEach(() => {
    document.addEventListener("click", (event) => {
      if (!event.target.closest('.solo-select-multi-content') && !event.target.closest('.solo-select-multi-header')) {
        document.querySelectorAll(".solo-select-multi-content.toggled").forEach(dropdown => {
          Drupal.solo.closeDropdown(dropdown);
        });
      }
    });
  });
})(Drupal);
