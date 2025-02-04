/**
 * @file
 * Contains utility functions for Solo module.
 *
 * Filename: solo-custom-select.js
 * Website: https://www.flashwebcenter.com
 * Developer: Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {
  'use strict';
  /**
   * Drupal behavior for custom single-select dropdowns with a dynamically inserted SVG arrow.
   */
  Drupal.behaviors.soloCustomSingleSelect = {
    attach: (context, settings) => {
      document.querySelectorAll('select:not([multiple])', context).forEach((select) => {
        if (!select.dataset.processed) {
          select.dataset.processed = "true";
          createCustomSingleSelect(select);
        }
      });
    }
  };

  function createCustomSingleSelect(selectElement) {
    const options = Array.from(selectElement.querySelectorAll("option"));
    const customSelect = document.createElement("div");
    customSelect.classList.add("solo-single-select");
    customSelect.setAttribute("tabindex", "0");
    const selectedDisplay = document.createElement("div");
    selectedDisplay.classList.add("solo-single-selected-option");
    // Text container for selected value
    const selectedText = document.createElement("span");
    selectedText.classList.add("selected-text");
    selectedText.textContent = options.find((opt) => opt.selected)?.textContent || options[0].textContent;
    const arrowIcon = document.createElement("span");
    arrowIcon.classList.add("select-single-arrow");
    arrowIcon.innerHTML = `
<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-344 240-584l56-56 184 184 184-184 56 56-240 240Z"/></svg>
  `;
    selectedDisplay.appendChild(selectedText);
    selectedDisplay.appendChild(arrowIcon);
    // Dropdown menu container
    const dropdownMenu = document.createElement("div");
    dropdownMenu.classList.add("solo-select-dropdown");
    dropdownMenu.setAttribute("aria-hidden", "true");
    // Populate the dropdown options
    options.forEach((option) => {
      const customOption = document.createElement("div");
      customOption.classList.add("solo-single-select-option");
      customOption.textContent = option.textContent;
      customOption.dataset.value = option.value;
      if (option.selected) {
        customOption.classList.add("selected");
      }
      customOption.addEventListener("click", function() {
        selectElement.value = this.dataset.value;
        selectElement.dispatchEvent(new Event("change"));
        selectedText.textContent = this.textContent;
        dropdownMenu.querySelectorAll(".solo-single-select-option").forEach(opt => opt.classList.remove("selected"));
        this.classList.add("selected");
        Drupal.solo.slideUp(dropdownMenu);
        arrowIcon.classList.remove("open-select");
      });
      dropdownMenu.appendChild(customOption);
    });
    customSelect.appendChild(selectedDisplay);
    customSelect.appendChild(dropdownMenu);
    selectElement.style.display = "none";
    selectElement.parentNode.insertBefore(customSelect, selectElement);
    selectedDisplay.addEventListener("click", () => {
      if (dropdownMenu.classList.contains("toggled")) {
        Drupal.solo.slideUp(dropdownMenu);
        arrowIcon.classList.remove("open-select");
      } else {
        Drupal.solo.slideDown(dropdownMenu);
        arrowIcon.classList.add("open-select");
      }
    });
    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        Drupal.solo.slideUp(dropdownMenu);
        arrowIcon.classList.remove("open-select");
      }
    });
  }
})(Drupal, once);
