/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal) => {

  'use strict';

  let isClicked = false;
  let currentWidth;
  const querySelectorElements = (selector) => document.querySelectorAll(selector) ?? null;
  const siteMenuBars = querySelectorElements('.solo-inner nav .navigation__menubar');
  const siteSubMenus = querySelectorElements('.solo-inner nav .navigation__menubar ul');
  const svgIcons = querySelectorElements('.solo-inner nav .navigation__menubar .toggler-icon>svg');

  const dropdownTogglerButtons = querySelectorElements('.solo-inner .navigation__menubar .dropdown-toggler');




const navigationSidebarTemplate = querySelectorElements('.navigation-sidebar>.navigation__sidebar_template .dropdown-toggler');
const navigationPrimaryTemplate = querySelectorElements('.navigation-primary-template>.navigation__primary_template .dropdown-toggler');
const navigationPrimary = querySelectorElements('#primary-menu .solo-inner .navigation__primary  .dropdown-toggler');
const navigationSidebar = querySelectorElements('#primary-sidebar-menu .solo-inner .navigation__sidebar .dropdown-toggler');




  const hamburgerIconButtons = querySelectorElements('.solo-inner .mobile-nav');

  // Get current width
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;

  // Function to handle the click, so don't click fast twice.
  const delay = (duration) => new Promise(resolve => setTimeout(resolve, duration));

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
    return document.querySelector(`.solo-inner #${menuBar} .navigation__menubar`);
  }
  Drupal.solo.getNavigationMenubarClass = getNavigationMenubarClass;

  const getSubMenuClasses = (subMenus) => {
    return document.querySelectorAll(`.solo-inner #${subMenus} .navigation__menubar ul`);
  }
  Drupal.solo.getSubMenuClasses = getSubMenuClasses;

  const hasParentWithClass = (element, className) => !!element.closest(`.${className}`);

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
    return (currentWidth > 992 && !verticalNav) ? 'rotate(-90deg)' : 'rotate(180deg)';
  }

  // Change the arrow direction on close.
  const revertIcons = (navId) => {
    let svgIcons = document.querySelectorAll(`.solo-inner #${navId} .toggler-icon svg`);
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

    const togglerSibling = dropdownTogglerButton.closest('.solo-inner ul');
    const nestedSubMenus = [...togglerSibling.querySelectorAll('.solo-inner ul')];
    const nestedTogglers = [...togglerSibling.querySelectorAll('.solo-inner ul .dropdown-toggler svg')];

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

    Drupal.solo.slideDown(subMenu);
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
      const navMenu = '.solo-inner nav .navigation__menubar';
      if (!event.target.closest(navMenu)) {
        resetSubMenus(siteSubMenus, svgIcons);
      }
    });
  });

  // This function is to keep the menubar open when changing the screen
  // resolution over 992 px and it is called in resize event listner.
  const showMenuBarIfOpened = (siteMenuBars) => {
    hamburgerIconButtons?.forEach((hamburgerIconButton) => {
      if (hamburgerIconButton.classList.contains('toggled')) {
        siteMenuBars.forEach(siteMenuBar => {
          Drupal.solo.slideDown(siteMenuBar);
        });
      }
    });
  }

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

    const dropdownTogglerButtonHeight = dropdownTogglerButton.offsetHeight - 1;
    subMenu.style.transform = `translateY(${dropdownTogglerButtonHeight}px)`;

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
    const [nestedSubMenus, nestedTogglers] = getDropdownElements(dropdownTogglerButton);
    const rotated = getRotated(dropdownTogglerButton);
    const verticalNav = subMenu.closest('#primary-sidebar-menu') ?? subMenu.closest('.navigation-sidebar');
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
      let isClassPresent = dropdownTogglerButton.parentElement.classList.contains('nav__menubar-item');

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

      const mainMenu = ('primary-sidebar-menu-inner');
      const sideMenu = ('navigation-sidebar');
      if (hasParentWithClass(siteMenuBar, mainMenu) || hasParentWithClass(siteMenuBar, sideMenu) || currentWidth < 992) {
        siteMenuBar.classList.remove('focus-in');
      } else {
        siteMenuBar.classList.add('focus-in');
      }

    });
  });


  // When a toggler is clicked, we need to know if it is submenu or menubar.
  dropdownTogglerButtons.forEach((dropdownTogglerButton) => {

    dropdownTogglerButton.addEventListener('click', (event) => {
      const subMenu = dropdownTogglerButton.nextElementSibling;
      const eventTarget = event.target;
      dropdownTogglerButtonIsClicked(dropdownTogglerButton, subMenu);
    });

  });
  navigationPrimary.forEach((dropdownTogglerButton) => {

    dropdownTogglerButton.addEventListener('mouseover', (event) => {
      const subMenu = dropdownTogglerButton.nextElementSibling;
      const eventTarget = event.target;
      dropdownTogglerButtonIsClicked(dropdownTogglerButton, subMenu);
    });

  });


  Drupal.behaviors.menuAction = {
    attach: function(settings) {

      currentWidth = getCurrentWidth();

      window.addEventListener('resize', () => {

        resetSubMenus(siteSubMenus, svgIcons);
        currentWidth = getCurrentWidth();

        if (currentWidth < 992) {
          showMenuBarIfOpened(siteMenuBars);
        } else {
          // To revert the navigation.
          removesiteMenuBarsStyles(siteMenuBars);
        }

      });

    }
  };

})(Drupal);
