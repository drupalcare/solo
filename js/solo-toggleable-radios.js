/**
 * @file
 * Contains utility functions for Solo module.
 *
 * Filename: solo-toggleable-radios.js
 * Website: https://www.flashwebcenter.com
 * Developer: Alaa Haddad https://www.alaahaddad.com.
 */

((Drupal, once) => {
  'use strict';

/**
 * Drupal behavior to enhance checkboxes and radio buttons with proper logic.
 */
Drupal.behaviors.soloToggleableRadios = {
  attach: (context, settings) => {
    const checkboxes = once('soloCustomCheckbox', 'input[type="checkbox"]', context);
    const radios = once('soloCustomRadio', 'input[type="radio"]', context);

    checkboxes.forEach((checkbox) => {
      checkbox.classList.add('solo-checkbox');
      detectMultiSelect(checkbox);
      updateCheckedState(checkbox);
      updateDisabledState(checkbox);
      updateIndeterminateState(checkbox);

      checkbox.addEventListener('change', () => {
        updateCheckedState(checkbox);
        updateIndeterminateState(checkbox);
      });

      addFocusAndHoverListeners(checkbox);
    });

    radios.forEach((radio) => {
      radio.classList.add('solo-radio');
      detectRadioGroup(radio);
      updateCheckedState(radio);
      updateDisabledState(radio);

      radio.addEventListener('change', () => {
        updateRadioGroupState(radio);
      });

      addFocusAndHoverListeners(radio);
      enableToggleableRadio(radio); // Optional: Allow deselecting radios
    });
  }
};

/**
 * Detects whether a checkbox allows multiple selections.
 * Adds a class to indicate if it's part of a group.
 */
function detectMultiSelect(checkbox) {
  const name = checkbox.name;
  if (name) {
    const checkboxesInGroup = document.querySelectorAll(`input[type="checkbox"][name="${name}"]`);
    if (checkboxesInGroup.length > 1) {
      checkbox.classList.add('solo-multi-checkbox');
    } else {
      checkbox.classList.add('solo-single-checkbox');
    }
  }
}

/**
 * Detects and marks radio buttons in groups.
 */
function detectRadioGroup(radio) {
  const name = radio.name;
  if (name) {
    const radiosInGroup = document.querySelectorAll(`input[type="radio"][name="${name}"]`);
    if (radiosInGroup.length > 1) {
      radio.classList.add('solo-radio-group');
    }
  }
}

/**
 * Ensures only the selected radio in a group has the active class.
 */
function updateRadioGroupState(selectedRadio) {
  const name = selectedRadio.name;
  const radiosInGroup = document.querySelectorAll(`input[type="radio"][name="${name}"]`);

  radiosInGroup.forEach((radio) => {
    updateCheckedState(radio);
  });
}

/**
 * Update the class state for checkboxes and radios.
 */
function updateCheckedState(element) {
  if (element.checked) {
    element.classList.add('solo-checked');
  } else {
    element.classList.remove('solo-checked');
  }
}

/**
 * Add a class if the checkbox or radio is disabled.
 */
function updateDisabledState(element) {
  if (element.disabled) {
    element.classList.add('solo-disabled');
  } else {
    element.classList.remove('solo-disabled');
  }
}

/**
 * Handle indeterminate checkboxes.
 */
function updateIndeterminateState(checkbox) {
  if (checkbox.indeterminate) {
    checkbox.classList.add('solo-indeterminate');
  } else {
    checkbox.classList.remove('solo-indeterminate');
  }
}

/**
 * Adds event listeners for focus and hover states.
 */
function addFocusAndHoverListeners(element) {
  element.addEventListener('focus', () => {
    element.classList.add('solo-focused');
  });

  element.addEventListener('blur', () => {
    element.classList.remove('solo-focused');
  });

  element.addEventListener('mouseover', () => {
    element.classList.add('solo-hover');
  });

  element.addEventListener('mouseout', () => {
    element.classList.remove('solo-hover');
  });
}

/**
 * Enable toggleable radio buttons (optional feature).
 * This allows users to deselect a radio button by clicking it again.
 */
/**
 * Enable toggleable radio buttons while keeping them in sync.
 */
function enableToggleableRadio(radio) {
  radio.addEventListener('click', function (event) {
    const name = this.name;
    const radiosInGroup = document.querySelectorAll(`input[type="radio"][name="${name}"]`);

    // If the clicked radio was already checked, allow deselection
    if (this.checked && this.dataset.wasChecked === "true") {
      this.checked = false;
      this.dataset.wasChecked = "false";

      // Ensure other radios in the group can now be selected properly
      radiosInGroup.forEach(radio => radio.dataset.wasChecked = "false");
    } else {
      // Reset all radios in the group before setting new selection
      radiosInGroup.forEach(radio => radio.dataset.wasChecked = "false");

      // Mark the clicked radio as selected
      this.dataset.wasChecked = "true";
    }
  });
}



})(Drupal, once);

