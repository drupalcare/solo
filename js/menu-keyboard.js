/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal) => {

  'use strict';

  const onStartFocusedElement = document.querySelectorAll(".d-inner nav .navigation__menubar>li:first-child>[role='menuitem']");
  const siteMenuBars = document.querySelectorAll('.d-inner nav .navigation__menubar');



onStartFocusedElement.forEach(function(element) {
    // element.classList.add('focus');
    element.setAttribute('tabindex', 0);
    element.focus();
});





})(Drupal);
