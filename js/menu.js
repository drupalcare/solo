/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {

  'use strict';

  let isClicked = false;
  let currentWidth;
  let origOffsetY;

  const querySelectorElements = (selector) => document.querySelectorAll(selector) ?? null;
  const siteMenuBars = querySelectorElements('.d-inner nav .navigation__menubar');
  const siteSubMenus = querySelectorElements('.d-inner nav .navigation__menubar ul');
  const svgIcons = querySelectorElements('.d-inner nav .navigation__menubar .toggler-icon>svg');
  const dropdownTogglerButtons = querySelectorElements('.d-inner .navigation__menubar .dropdown-toggler');
  const hamburgerIconButtons = querySelectorElements('.d-inner .mobile-nav');

  // Get current width
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;

  // Apply background color to all site submenus. The region background color
  // will be used. Ex. if the submenu in header region then the heaser background
  // color will be applied to this submenu.
  const getParentBg = (el) => {
    const closestParent = el.closest('.page-wrapper>div') ?? el.closest('.page-wrapper>header');
    if (closestParent) {
      let parentBg = window.getComputedStyle(closestParent).backgroundColor;
      return parentBg;
    }
  }
  siteSubMenus.forEach(el => el.style.backgroundColor = getParentBg(el));

  // Apply static position to the main menu on scroll so it will be sticky
  // on the top.
  const mainNavigation = document.querySelector('#main-navigation-h');
  if (mainNavigation && mainNavigation.querySelector('.d-inner .navigation__menubar')) {
    origOffsetY = mainNavigation.offsetTop;
  }

  const scrollWindow = () => {
    if (mainNavigation) {
      mainNavigation.classList.toggle('w3-sticky', window.scrollY >
        origOffsetY);
    }
  };

  // Function to handle the click, so don't click fast twice.
  const clickedHandler = (callback) => {
    if (!isClicked) {
      isClicked = true;
      callback();
      setTimeout(() => {
        isClicked = false;
      }, 500);
    }
  }

  const hideSubMenus = (childElement) => {
    Drupal.solo.slideUp(childElement, 300);
  }

  const getNavigationMenubarClass = (menuBar) => {
    return document.querySelector(`.d-inner #${menuBar} .navigation__menubar`);
  }

  const getSubMenuClasses = (subMenus) => {
    return document.querySelectorAll(`.d-inner #${subMenus} .navigation__menubar ul`);
  }

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
  const revertIcon = (navId) => {
    let svgIcons = document.querySelectorAll(`.d-inner #${navId} .toggler-icon svg`);
    svgIcons.forEach((svgIcon) => {
      svgIcon.style.removeProperty('transform');
    });
  }

  // click anywhere to close the nav
  const removesiteMenuBarsStyles = (siteMenuBars) => {
    siteMenuBars.forEach((siteMenuBar) => {
      siteMenuBar.removeAttribute('style');

    });
  }

  const getDropdownElements = (dropdownTogglerButton) => {

    const togglerSibling = dropdownTogglerButton.closest('.d-inner ul');
    const nestedSubMenus = [...togglerSibling.querySelectorAll('.d-inner ul')];
    const nestedTogglers = [...togglerSibling.querySelectorAll('.d-inner ul .dropdown-toggler svg')];

    return [nestedSubMenus, nestedTogglers];
  }

  // This function handle the attributes
  const closeMenuHelper = (rotated, dropdownTogglerButton, subMenu) => {

    rotated.style.removeProperty('transform');
    dropdownTogglerButton.setAttribute('aria-expanded', 'false');
    dropdownTogglerButton.setAttribute('aria-hidden', 'true');
    Drupal.solo.slideUp(subMenu, 400);

  }

  const openMenuHelper = (dropdownTogglerButton, subMenu) => {

    Drupal.solo.slideDown(subMenu);
    dropdownTogglerButton.setAttribute('aria-expanded', 'true');
    dropdownTogglerButton.setAttribute('aria-hidden', 'false');

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
      const navMenu = '.d-inner nav .navigation__menubar';
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
        revertIcon(navTagId);
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
      revertIcon(navTagId);
    });

    closeMenuHelper(rotated, dropdownTogglerButton, subMenu);

  }

  // Open submenu get called by dropdownTogglerButtonIsClicked();
  const openSubMenu = (dropdownTogglerButton, subMenu) => {
    const [nestedSubMenus, nestedTogglers] = getDropdownElements(dropdownTogglerButton);
    const rotated = getRotated(dropdownTogglerButton);
    const verticalNav = subMenu.closest('#main-navigation-v') ?? null;
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
      let isClassPresent = dropdownTogglerButton.parentElement.classList.contains('navigation__menubar-item');

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

  // When a toggler is clicked, we need to know if it is submenu or menubar.
  dropdownTogglerButtons.forEach((dropdownTogglerButton) => {
    dropdownTogglerButton.addEventListener('click', (event) => {
      const subMenu = dropdownTogglerButton.nextElementSibling;
      const eventTarget = event.target;
      dropdownTogglerButtonIsClicked(dropdownTogglerButton, subMenu);
    });
  });

  //////////////////////////////////////////////////////////////////////
  // Steps when click hamburger icon.
  // hamburgerIconIsClicked >> closeMobileMenuHandler >> closeMobileMenu
  // hamburgerIconIsClicked >> openMobileMenuHandler >> openMobileMenu
  const openMobileMenu = navTagId => {
    const navigationMenubarClass = getNavigationMenubarClass(navTagId);
    const subMenuClasses = getSubMenuClasses(navTagId);

    subMenuClasses?.forEach((subMenu) => {
      hideSubMenus(subMenu);
      revertIcon(navTagId);
    });
    Drupal.solo.slideDown(navigationMenubarClass, 'flex');

  };

  const closeMobileMenu = navTagId => {
    const navigationMenubarClass = getNavigationMenubarClass(navTagId);
    const subMenuClasses = getSubMenuClasses(navTagId);

    subMenuClasses?.forEach((subMenu) => {
      hideSubMenus(subMenu);
      revertIcon(navTagId);
    });

    Drupal.solo.slideUp(navigationMenubarClass, 400);

  };

  const getMobileNavType = (hamburgerIcon) => {
    const hamburgerIconChild = hamburgerIcon.children[0];
    const navTagId = hamburgerIcon.parentElement.classList.contains('responsive-navigation') ?
      hamburgerIcon.closest('nav').id :
      hamburgerIcon.nextElementSibling.id;
    return [hamburgerIconChild, navTagId];
  };

  const hamburgerIconIsClicked = (hamburgerIcon) => {
    const [hamburgerIconChild, navTagId] = getMobileNavType(hamburgerIcon);

    if (!hamburgerIcon.classList.contains('toggled')) {
      hamburgerIconChild.setAttribute('aria-expanded', 'true');
      hamburgerIconChild.setAttribute('aria-hidden', 'false');
      hamburgerIcon.classList.add('toggled');
      openMobileMenu(navTagId);
    } else {
      hamburgerIconChild.setAttribute('aria-expanded', 'false');
      hamburgerIconChild.setAttribute('aria-hidden', 'true');
      hamburgerIcon.classList.remove('toggled');
      closeMobileMenu(navTagId);
    }
  };

  const adIdToMenubars = (hamburgerIcon) => {
    const [hamburgerIconChild, navTagId] = getMobileNavType(hamburgerIcon);
    let ariaControl = document.querySelector(`#${navTagId} .navigation__menubar`).getAttribute('id');
    if (currentWidth <= 992) {
      hamburgerIconChild.setAttribute('aria-controls', ariaControl);
    } else {
      hamburgerIconChild.removeAttribute('aria-controls');
    }
  };

  // Hamburger icon is clicked
  hamburgerIconButtons.forEach((hamburgerIcon) => {
    adIdToMenubars(hamburgerIcon);
    hamburgerIcon.addEventListener('click', () => {
      clickedHandler(() => {
        hamburgerIconIsClicked(hamburgerIcon);
      });
    });
  });

  ////////////////////////////////////////////////
  // Main menu vertical nav start Close or Open.
  const navClickListener = (selector, callback) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener('click', callback);
    }
  };

  // Close nav button found in page.html.twig in vertical menu region.
  navClickListener('#main-navigation-v #close-nav', () => {
    const verticalNav = document.getElementById('main-navigation-v');
    let cosBtns = document.querySelectorAll('.cos-btn');
    cosBtns?.forEach((cosBtn) => {
      cosBtn.setAttribute('aria-expanded', 'false');
      cosBtn.setAttribute('aria-hidden', 'true');

    })

    verticalNav.style.display = 'none';
    const subMenus = document.querySelectorAll(
      '#main-navigation-v .navigation__menubar li ul.sub__menu');
    subMenus.forEach(hideSubMenus);
  });

  // Open nav button found in page.html.twig in header region.
  navClickListener('#open-nav-inner', () => {

    let cosBtns = document.querySelectorAll('.cos-btn');
    cosBtns?.forEach((cosBtn) => {
      cosBtn.setAttribute('aria-expanded', 'true');
      cosBtn.setAttribute('aria-hidden', 'false');

    })

    const verticalNav = document.getElementById('main-navigation-v');
    verticalNav.style.display = 'flex';
  });

  Drupal.behaviors.menuAction = {
    attach: function(settings) {

      currentWidth = getCurrentWidth();
      window.addEventListener('scroll', scrollWindow);
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

})(Drupal, once);
