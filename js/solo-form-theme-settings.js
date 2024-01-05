/**
 * @file
 * Solo
 *
 * Filename:     solo-form-theme-settings.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal) => {

  'use strict';
  const formDetails = document.querySelectorAll('.system-theme-settings>details');
  formDetails.forEach((formDetail) => {
    formDetail.removeAttribute('open');
  });

  Drupal.behaviors.soloFormThemeSettings = {
    attach: function(context, settings) {
      // Query selector within the context
      const categorySelect = context.querySelector('#edit-theme-category');
      if (!categorySelect) {
        return;
      }

      // Initialize once
      if (categorySelect.getAttribute('data-solo-theme-settings-processed') !== 'true') {
        categorySelect.setAttribute('data-solo-theme-settings-processed', 'true');

        const themeSelect = context.querySelector('#edit-predefined-current-theme');
        if (!themeSelect) {
          return;
        }

        // Clone original options
        const initialOptions = Array.from(themeSelect.options);

        categorySelect.addEventListener('change', function() {
          const selectedCategory = this.value;
          themeSelect.innerHTML = ''; // Clear current options

          if (selectedCategory !== 'none') {
            initialOptions.forEach(function(option) {
              if (option.value.startsWith(selectedCategory + '|')) {
                themeSelect.appendChild(option.cloneNode(true)); // Append matching option
              }
            });
          }
        });
      }
    }
  };
})(Drupal);
