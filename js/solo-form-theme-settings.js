/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal) => {

  'use strict';
   const formDetails = document.querySelectorAll('.system-theme-settings>details');
           formDetails.forEach((formDetail) => {
            formDetail.removeAttribute('open');
        });

})(Drupal);
