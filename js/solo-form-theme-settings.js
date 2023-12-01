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

})(Drupal);
