/**
 * @file
 * Solo
 *
 * Filename:     solo-menu-keyboard.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal) => {

  'use strict';

  const onStartFocusedElement = document.querySelectorAll(".main-navigation-wrapper .navigation__menubar>li:first-child>[role='menuitem']");
  const siteMenuBars = document.querySelectorAll('.main-navigation-wrapper .navigation__menubar');

  onStartFocusedElement.forEach(function(element) {
    // element.classList.add('focus');
    element.setAttribute('tabindex', 0);
    element.focus();
  });

})(Drupal);
