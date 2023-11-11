/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal) => {
  'use strict';
  let isClicked = false;
  let currentWidth;
  let timeoutId;
  const querySelectorElements = (selector) => document.querySelectorAll(
    selector) ?? null;
  const hamburgerIconButtons = querySelectorElements(
    '.solo-inner .navigation-responsive .mobile-nav');
  const siteMenuBars = querySelectorElements(
    '.solo-inner .solo-menu .navigation__menubar');
  const siteSubMenus = querySelectorElements(
    '.solo-inner .solo-menu .navigation__menubar ul');
  const svgIcons = querySelectorElements(
    '.solo-inner .solo-menu .navigation__menubar .toggler-icon>svg');

  // On mega menu click we select the first level for the big screen.
  const mmClickBig = querySelectorElements(
    '.solo-inner .solo-menu.navigation-responsive-click .navigation__megamenu>li>.dropdown-toggler'
    );
  // On mega menu click we select all level for the small screen.
  const mmClickSmall = querySelectorElements(
    '.solo-inner .solo-menu.navigation-responsive-click .navigation__megamenu li .dropdown-toggler'
    );
  const mmHoverSmall = querySelectorElements(
    '.solo-inner .solo-menu.navigation-responsive-hover .navigation__megamenu li .dropdown-toggler'
    );
  // ON mega menu hover we select all ULs
  const navigationMegamenuHover = querySelectorElements(
    '.solo-inner .solo-menu.navigation-responsive-hover .navigation__megamenu li .dropdown-toggler'
    );
  // Default menu mouse over
  const navigationResponsiveHover = querySelectorElements(
    '.solo-inner .solo-menu.navigation-responsive-hover .navigation__menubar:not(.navigation__megamenu) .dropdown-toggler'
    );
  // Default menu mouse click
  const navigationResponsiveClick = querySelectorElements(
    '.solo-inner .solo-menu.navigation-responsive-click .navigation__menubar:not(.navigation__megamenu) .dropdown-toggler'
    );

  // Default menu
  const navigationDefault = querySelectorElements(
    '.solo-inner .solo-menu .navigation__default .dropdown-toggler');
  //  Sidebar Menus.
  const navigationSidebar = querySelectorElements(
    '.solo-inner .solo-menu.navigation-sidebar .dropdown-toggler');
  // Get current width
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;
  currentWidth = getCurrentWidth();
  // Function to handle the click, so don't click fast twice.
  const delay = (duration) => new Promise(resolve => setTimeout(resolve,
    duration));
  const clickedHandler = async (callback) => {
    if (!isClicked) {
      isClicked = true;
      await callback();
      await delay(500);
      isClicked = false;
    }
  }
  Drupal.solo.clickedHandler = clickedHandler;
  const hideSubMenus = (childElement) => {
    Drupal.solo.slideUp(childElement, 300);
  }
  Drupal.solo.hideSubMenus = hideSubMenus;
  const getNavigationMenubarClass = (menuBar) => {
    return document.querySelector(
      `.solo-inner #${menuBar} .navigation__menubar`);
  }
  Drupal.solo.getNavigationMenubarClass = getNavigationMenubarClass;
  const getSubMenuClasses = (subMenus) => {
    return document.querySelectorAll(
      `.solo-inner #${subMenus} .navigation__menubar ul.sub__menu`);
  }
  Drupal.solo.getSubMenuClasses = getSubMenuClasses;
  const hasParentWithClass = (element, className) => !!element.closest(
    `.${className}`);
  const getNavTagId = (dropdownTogglerButton) => {
    const {
      id: navId
    } = dropdownTogglerButton.closest('nav');
    return navId;
  }
  const getRotated = (dropdownTogglerButton) => {
    return dropdownTogglerButton.querySelector('.toggler-icon svg');
  }
  const getArrowDirection = (verticalNav) => {
    return (currentWidth >= 993 && !verticalNav) ? 'rotate(-90deg)' :
      'rotate(180deg)';
  }
  // Change the arrow direction on close.
  const revertIcons = (navId) => {
    let svgIcons = document.querySelectorAll(
      `.solo-inner #${navId} .toggler-icon svg`);
    svgIcons.forEach((svgIcon) => {
      svgIcon.style.removeProperty('transform');
    });
  }
  Drupal.solo.revertIcons = revertIcons
  //click anywhere to close the nav
  const removesiteMenuBarsStyles = (siteMenuBars) => {
    siteMenuBars.forEach((siteMenuBar) => {
      siteMenuBar.removeAttribute('style');
    });
  }
  const getDropdownElements = (dropdownTogglerButton) => {
    const togglerSibling = dropdownTogglerButton.closest(
      '.solo-inner .solo-menu ul');
    const nestedSubMenus = [...togglerSibling.querySelectorAll(
      '.solo-inner .solo-menu ul')];
    const nestedTogglers = [...togglerSibling.querySelectorAll(
      '.solo-inner .solo-menu ul .dropdown-toggler svg')];
    return [nestedSubMenus, nestedTogglers];
  }
  // This function handle the attributes
  const closeMenuHelper = (rotated, dropdownTogglerButton, subMenu) => {
    rotated.style.removeProperty('transform');
    dropdownTogglerButton.setAttribute('aria-expanded', 'false');
    dropdownTogglerButton.setAttribute('aria-hidden', 'true');
    dropdownTogglerButton.setAttribute('tabindex', '-1');
    Drupal.solo.slideUp(subMenu, 400);
  }
  const openMenuHelper = (dropdownTogglerButton, subMenu) => {
    currentWidth = getCurrentWidth();
    if (subMenu.classList.contains('sub-mega') && currentWidth >= 993 ) {
      Drupal.solo.slideDown(subMenu, 'grid', 1000);
      //subMenu.style.display = "grid";
    } else {
      Drupal.solo.slideDown(subMenu);
    }
    // Drupal.solo.slideDown(subMenu);
    dropdownTogglerButton.setAttribute('aria-expanded', 'true');
    dropdownTogglerButton.setAttribute('aria-hidden', 'false');
    dropdownTogglerButton.setAttribute('tabindex', '0');
  }
  // This function is used in two times. 1- When clicked any where closer to
  // menubar it will close any submenus. 2- When resizing the screen, it will
  // close any submenus.
  const resetSubMenus = (siteSubMenus, svgIcons) => {
    svgIcons.forEach(el => el.style.removeProperty('transform'));
    siteSubMenus.forEach(el => Drupal.solo.slideUp(el, 500));
    setTimeout(() => {
      siteSubMenus.forEach(el => el.style.removeProperty('transform'));
    }, 550);
  };
  //click any where to close any submenu.
  document.addEventListener('click', (event) => {
    clickedHandler(() => {
      const navMenu = '.solo-inner .solo-menu .navigation__menubar';
      if (!event.target.closest(navMenu)) {
        resetSubMenus(siteSubMenus, svgIcons);
      }
    });
  });
  // Open menubar get called by dropdownTogglerButtonIsClicked();
  const openMenubar = (dropdownTogglerButton, subMenu) => {
    const navTagId = getNavTagId(dropdownTogglerButton);
    const subMenuClasses = getSubMenuClasses(navTagId);
    const rotated = getRotated(dropdownTogglerButton);
    subMenuClasses?.forEach((subMenuClass) => {
      if (subMenuClass !== subMenu) {
        hideSubMenus(subMenuClass);
        revertIcons(navTagId);
      }
    });
    rotated.style.transform = 'rotate(180deg)';
    openMenuHelper(dropdownTogglerButton, subMenu);
  }
  // Close menubar get called by dropdownTogglerButtonIsClicked();
  const closeMenubar = (dropdownTogglerButton, subMenu) => {
    const navTagId = getNavTagId(dropdownTogglerButton);
    const subMenuClasses = getSubMenuClasses(navTagId);
    const rotated = getRotated(dropdownTogglerButton);
    subMenuClasses?.forEach((subMenuClass) => {
      hideSubMenus(subMenuClass);
      revertIcons(navTagId);
    });
    closeMenuHelper(rotated, dropdownTogglerButton, subMenu);
  }
  // Open submenu get called by dropdownTogglerButtonIsClicked();
  const openSubMenu = (dropdownTogglerButton, subMenu) => {
    const [nestedSubMenus, nestedTogglers] = getDropdownElements(
      dropdownTogglerButton);
    const rotated = getRotated(dropdownTogglerButton);
    const verticalNav = subMenu.closest('.navigation-sidebar');
    // close all opened sibling menu
    nestedSubMenus.forEach((nestedSubMenu) => {
      if (nestedSubMenu !== subMenu) {
        hideSubMenus(nestedSubMenu);
      }
    });
    // revert all rotated icons
    nestedTogglers.forEach((nestedToggler) => {
      if (nestedToggler !== dropdownTogglerButton) {
        nestedToggler.style.removeProperty('transform');
      }
    });
    let arrowDirection = getArrowDirection(verticalNav);
    // rotate 90 only if it is bigger than 992px
    //  let arrowDirection = verticalNav != null ? 'rotate(-90deg)' : 'rotate(180deg)';
    rotated.style.transform = arrowDirection;
    openMenuHelper(dropdownTogglerButton, subMenu);
  }
  // Close submenu get called by dropdownTogglerButtonIsClicked();
  const closeSubMenu = (dropdownTogglerButton, subMenu) => {
    const rotated = getRotated(dropdownTogglerButton);
    closeMenuHelper(rotated, dropdownTogglerButton, subMenu);
  }
  // Toggler handle, according to the menu type functions will be called.
  const dropdownTogglerButtonIsClicked = (dropdownTogglerButton, subMenu) => {
    clickedHandler(() => {
      // Let get the menu type menubar or submenu.
      let isClassPresent = dropdownTogglerButton.parentElement.classList
        .contains('nav__menubar-item');
      if (isClassPresent) {
        if (!subMenu.classList.contains('toggled')) {
          openMenubar(dropdownTogglerButton, subMenu);
        } else {
          closeMenubar(dropdownTogglerButton, subMenu);
        }
      } else {
        if (!subMenu.classList.contains('toggled')) {
          openSubMenu(dropdownTogglerButton, subMenu);
        } else {
          closeSubMenu(dropdownTogglerButton, subMenu);
        }
      }
    });
  }
  // Add focus class once you click on menubar.
  siteMenuBars.forEach((siteMenuBar) => {
    siteMenuBar.addEventListener('click', (event) => {
      const sideMenu = ('navigation-sidebar');
      if (hasParentWithClass(siteMenuBar, sideMenu) || currentWidth <=
        992) {
        siteMenuBar.classList.remove('focus-in');
      } else {
        siteMenuBar.classList.add('focus-in');
      }
    });
  });


    function addRemoveListener(event) {
      const button = event.currentTarget;
      const subMenu = button.nextElementSibling;
      dropdownTogglerButtonIsClicked(button, subMenu);
    }

    function addEventListenerToButtons(buttons) {
      buttons.forEach((button) => {
        button.addEventListener('click', addRemoveListener);
      });
    }

    function removeEventListenerToButtons(buttons) {
      buttons.forEach((button) => {
        button.removeEventListener('click', addRemoveListener);
      });
    }


  // function disableClickForHover(menubars) {
  //   menubars.forEach((menubar) => {
  //     menubar.addEventListener('click', (event) => {
  //       event.preventDefault();
  //       event.stopPropagation();
  //     });
  //   });
  // }

    function menusHelper(currentWidth) {
        if (currentWidth >= 993) {
          // remove the first level menu styles.
          removesiteMenuBarsStyles(siteMenuBars);
          // menus.
          removeEventListenerToButtons(mmClickSmall);
          addEventListenerToButtons(mmClickBig);

          removeEventListenerToButtons(mmHoverSmall);
          removeEventListenerToButtons(navigationResponsiveHover);
        } else {
          // call the hover type only on small screen.
          removeEventListenerToButtons(mmClickBig);
          addEventListenerToButtons(mmClickSmall);

          addEventListenerToButtons(mmHoverSmall);
          addEventListenerToButtons(navigationResponsiveHover);
        }
    }

  Drupal.behaviors.menuAction = {
    attach: function (settings) {
      addEventListenerToButtons(navigationDefault);
      addEventListenerToButtons(navigationResponsiveClick);
      addEventListenerToButtons(navigationSidebar);
      // We only call main menu click and hvoer type when hover is disabled.
      currentWidth = getCurrentWidth();
      menusHelper(currentWidth)

      window.addEventListener('resize', () => {
        currentWidth = getCurrentWidth();
        menusHelper(currentWidth)
        // reset menu styles on resize.
        resetSubMenus(siteSubMenus, svgIcons);
      });


    }
  };
})(Drupal);
