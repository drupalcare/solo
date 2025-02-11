/**
 * @file
 * Contains utility functions for Solo module.
 *
 * Filename: solo-select-single.js
 * Website: https://www.flashwebcenter.com
 * Developer: Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {
  'use strict';

  /**
   * Drupal behavior for custom single-select dropdowns.
   */
  Drupal.behaviors.soloCustomSingleSelect = {
    attach: (context, settings) => {
      once('soloSingleSelect', '.solo-select-single', context).forEach((customSelect) => {
        initializeCustomSingleSelect(customSelect);
      });
    }
  };

  function initializeCustomSingleSelect(customSelect) {
    const selectedDisplay = customSelect.querySelector('.solo-select-single-header');
    const selectedText = customSelect.querySelector('.select-single-header-text');
    const arrowIcon = customSelect.querySelector('.select-single-header-arrow');
    const dropdownMenu = customSelect.querySelector('.solo-select-single-content');
    const options = customSelect.querySelectorAll('.solo-select-single-option');
    const hiddenSelect = document.getElementById(customSelect.dataset.targetId);

    if (!selectedDisplay || !dropdownMenu || !options.length || !hiddenSelect) {
      return;
    }

    dropdownMenu.addEventListener("click", (event) => {
      event.stopPropagation();
    });

    selectedDisplay.addEventListener("click", (event) => {
      event.stopPropagation();
      if (dropdownMenu.classList.contains("toggled")) {
        closeDropdown(customSelect, dropdownMenu, arrowIcon);
      } else {
        adjustDropdownHeight(customSelect, dropdownMenu);
        openDropdown(customSelect, dropdownMenu, arrowIcon);
      }
    });

    options.forEach(option => {
      option.addEventListener("click", function () {
        options.forEach(opt => opt.classList.remove("selected-option"));
        this.classList.add("selected-option");

        selectedText.textContent = this.textContent;
        hiddenSelect.value = this.dataset.value;
        hiddenSelect.dispatchEvent(new Event("change"));

        updateOptionVisibility(options, this.dataset.value);
        closeDropdown(customSelect, dropdownMenu, arrowIcon);
      });
    });

    document.addEventListener("click", (event) => {
      if (!customSelect.contains(event.target)) {
        closeDropdown(customSelect, dropdownMenu, arrowIcon);
      }
    });

    customSelect.addEventListener("keydown", (event) => {
      const key = event.key;
      const isOpen = dropdownMenu.classList.contains("toggled");

      if ((key === "Enter" || key === " ") && !isOpen) {
        event.preventDefault();
        selectedDisplay.click();
      } else if (key === "Escape" && isOpen) {
        closeDropdown(customSelect, dropdownMenu, arrowIcon);
      } else if (key === "ArrowDown" || key === "ArrowUp") {
        event.preventDefault();
        handleArrowNavigation(options, key);
      }
    });

    syncUIWithHiddenSelect(hiddenSelect, options);
  }

  function adjustDropdownHeight(customSelect, dropdownMenu) {
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
  }

  function openDropdown(customSelect, dropdown, arrowIcon) {
    Drupal.solo.slideDown(dropdown);
    customSelect.classList.add("single-opened");
  }

  function closeDropdown(customSelect, dropdown, arrowIcon) {
    Drupal.solo.slideUp(dropdown);
    customSelect.classList.remove("single-opened");
  }

  function updateOptionVisibility(options, selectedValue) {
      let visibleOptions = 0;

      options.forEach(option => {
          if (option.dataset.value === selectedValue) {
              option.classList.add("hidden");
          } else {
              option.classList.remove("hidden");
              visibleOptions++;
          }
      });

      // Ensure at least one option remains visible
      if (visibleOptions === 0) {
          options.forEach(option => option.classList.remove("hidden"));
      }
  }

  function handleArrowNavigation(options, key) {
    const visibleOptions = Array.from(options).filter(opt => !opt.classList.contains("hidden"));
    let currentIndex = visibleOptions.findIndex(opt => document.activeElement === opt);

    let newIndex = key === "ArrowDown" ? currentIndex + 1 : currentIndex - 1;
    if (newIndex >= 0 && newIndex < visibleOptions.length) {
      visibleOptions[newIndex].focus();
    }
  }

  function syncUIWithHiddenSelect(hiddenSelect, options) {
    const selectedValue = hiddenSelect.value;
    const selectedOption = Array.from(options).find(opt => opt.dataset.value === selectedValue);

    if (selectedOption) {
      selectedOption.classList.add("selected-option");
      selectedOption.closest('.solo-select-single').querySelector('.select-single-header-text').textContent = selectedOption.textContent;

      updateOptionVisibility(options, selectedValue);
    }
  }

})(Drupal, once);
