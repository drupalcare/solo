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
   * Drupal behavior for custom multi-select dual list.
   */
  Drupal.behaviors.soloCustomMultiSelectDualList = {
    attach: (context, settings) => {
      once('soloMultiSelectDualList', '.solo-select-multi-dual-list', context).forEach((customSelect) => {
        initializeCustomMultiSelectDualList(customSelect);
      });
    }
  };

  function initializeCustomMultiSelectDualList(customSelect) {
    const availableList = customSelect.querySelector('.dual-list-available');
    const selectedList = customSelect.querySelector('.dual-list-selected');
    const hiddenSelect = document.getElementById(customSelect.dataset.targetId);

    if (!availableList || !selectedList || !hiddenSelect) {
      return;
    }

    // Prevent dropdown from closing when clicking inside the dual list
    customSelect.addEventListener("click", (event) => {
      event.stopPropagation();
    });

    // 🟢 Handle adding items (Event Delegation)
    availableList.addEventListener("click", function (event) {
      const button = getActionButton(event.target, '.dual-list-add');
      if (button) {
        const option = button.closest('.dual-list-option');
        moveOption(option, availableList, selectedList, true, hiddenSelect);
      }
    });

    // 🟢 Handle removing items (Event Delegation)
    selectedList.addEventListener("click", function (event) {
      const button = getActionButton(event.target, '.dual-list-remove');
      if (button) {
        const option = button.closest('.dual-list-option');
        moveOption(option, selectedList, availableList, false, hiddenSelect);
      }
    });

    // ✅ Attach "Clear All" button
    const clearAllBtn = customSelect.querySelector(".multi-clear-all");
    if (clearAllBtn) {
      clearAllBtn.addEventListener("click", function () {
        clearAllSelections(selectedList, availableList, hiddenSelect);
      });
    }

    // 🔄 Sync UI on page load
    syncUIWithHiddenSelect(hiddenSelect, selectedList);
  }

  /**
   * Moves an option between the available and selected lists.
   */
  function moveOption(optionElement, fromList, toList, isSelecting, hiddenSelect) {
    const value = optionElement.dataset.value;
    const newOption = optionElement.cloneNode(true);

    if (isSelecting) {
      newOption.classList.add("selected-option");
      newOption.querySelector('.dual-list-add').classList.replace("dual-list-add", "dual-list-remove");
      newOption.querySelector('.dual-list-remove').addEventListener("click", function () {
        moveOption(newOption, toList, fromList, false, hiddenSelect);
      });
    } else {
      newOption.classList.remove("selected-option");
      newOption.querySelector('.dual-list-remove').classList.replace("dual-list-remove", "dual-list-add");
      newOption.querySelector('.dual-list-add').addEventListener("click", function () {
        moveOption(newOption, toList, fromList, true, hiddenSelect);
      });
    }

    fromList.removeChild(optionElement);
    toList.appendChild(newOption);
    updateHiddenSelect(hiddenSelect, toList);
  }

  /**
   * Clears all selected options and moves them back to available list.
   */
  function clearAllSelections(selectedList, availableList, hiddenSelect) {
    const selectedOptions = [...selectedList.querySelectorAll('.dual-list-option')];

    selectedOptions.forEach(option => {
      moveOption(option, selectedList, availableList, false, hiddenSelect);
    });

    updateHiddenSelect(hiddenSelect, selectedList);
  }

  /**
   * Updates the hidden select field based on the selected items.
   */
  function updateHiddenSelect(hiddenSelect, selectedList) {
    const selectedValues = [...selectedList.querySelectorAll('.dual-list-option')].map(option => option.dataset.value);

    hiddenSelect.querySelectorAll("option").forEach(option => {
      option.selected = selectedValues.includes(option.value);
    });

    hiddenSelect.dispatchEvent(new Event("change"));
  }

  /**
   * Syncs the UI with the hidden select field on page load.
   */
  function syncUIWithHiddenSelect(hiddenSelect, selectedList) {
    const selectedValues = Array.from(hiddenSelect.options).filter(option => option.selected).map(option => option.value);

    selectedList.querySelectorAll('.dual-list-option').forEach(option => {
      if (selectedValues.includes(option.dataset.value)) {
        option.classList.add("selected-option");
      } else {
        option.classList.remove("selected-option");
      }
    });
  }

  /**
   * Ensures the click is captured even if the user clicks on an SVG or span inside the button.
   * @param {HTMLElement} target - The element that was clicked.
   * @param {string} selector - The button class to find.
   * @returns {HTMLElement|null} - The found button or null.
   */
  function getActionButton(target, selector) {
    if (target.matches(selector)) {
      return target; // If the user clicked directly on the button
    } else {
      return target.closest(selector); // If the user clicked on a child element like an SVG or span
    }
  }

})(Drupal, once);


