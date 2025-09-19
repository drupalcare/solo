/**
 * Solo Menu Diagnostic Tool - User Version
 *
 * Instructions:
 * 1. Open the website where menus sometimes fail
 * 2. Open browser console (F12)
 * 3. Paste this entire script and press Enter
 * 4. Click dropdown menus multiple times (especially ones that fail)
 * 5. After clicking menus 5-10 times, type: generateSoloReport()
 * 6. Copy the report and send to developer
 */

(function() {
  'use strict';

  console.log('%c🔍 Solo Menu Diagnostic Started', 'background: green; color: white; padding: 5px; font-size: 14px');
  console.log('Click dropdown menus that usually fail, then run: generateSoloReport()');

  // Initialize diagnostic
  window.soloDiagnostic = {
    startTime: Date.now(),
    environment: {},
    clickAttempts: [],
    errors: [],
    stateChecks: [],
    doubleClicks: 0
  };

  // Capture Environment
  window.soloDiagnostic.environment = {
    userAgent: navigator.userAgent,
    browser: (() => {
      const ua = navigator.userAgent;
      if (ua.includes('Firefox')) return 'Firefox';
      if (ua.includes('Chrome')) return 'Chrome';
      if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari';
      if (ua.includes('Edge')) return 'Edge';
      return 'Unknown';
    })(),
    isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
    screenWidth: window.innerWidth,
    screenHeight: window.innerHeight,
    timestamp: new Date().toISOString()
  };

  // Check for extensions
  setTimeout(() => {
    const extensions = [];
    if (document.querySelector('[data-grammarly-shadow-root]')) extensions.push('Grammarly');
    if (document.querySelector('[data-lastpass-root]')) extensions.push('LastPass');
    if (document.querySelector('#gdx-bubble-host')) extensions.push('Google Dictionary');
    if (window.___browserSync___) extensions.push('BrowserSync');
    window.soloDiagnostic.environment.extensions = extensions;
  }, 1000);

  // FIXED: Capture ALL clicks, then filter for menu-related elements
  let lastClickTime = 0;
  document.addEventListener('click', function(e) {
    const now = Date.now();
    const timeSinceLastClick = now - lastClickTime;

    // Check various ways to identify a menu button
    const isMenuButton =
      e.target.classList.contains('dropdown-toggler') ||
      e.target.closest('.dropdown-toggler') ||
      e.target.closest('button.dropdown-toggler') ||
      e.target.closest('.nav__menubar-item button') ||
      (e.target.tagName === 'BUTTON' && e.target.nextElementSibling?.classList.contains('sub__menu'));

    if (!isMenuButton) return;

    // Found a menu click!
    const button = e.target.closest('button') || e.target;
    const submenu = button.nextElementSibling;

    // Check if it's a double-click
    if (timeSinceLastClick < 300) {
      window.soloDiagnostic.doubleClicks++;
    }

    const clickData = {
      timestamp: now - window.soloDiagnostic.startTime,
      timeSinceLastClick: timeSinceLastClick,
      isDoubleClick: timeSinceLastClick < 300,
      buttonInfo: {
        tagName: button.tagName,
        className: button.className,
        id: button.id || 'no-id',
        innerText: button.innerText?.substring(0, 20)
      },
      submenuFound: !!submenu,
      submenuInfo: submenu ? {
        className: submenu.className,
        display: window.getComputedStyle(submenu).display,
        visibility: window.getComputedStyle(submenu).visibility,
        wasToggled: submenu.classList.contains('toggled')
      } : null,
      ariaExpanded: button.getAttribute('aria-expanded'),
      globalState: typeof state !== 'undefined' ? {
        exists: true,
        isClicked: state?.isClicked
      } : { exists: false }
    };

    // Check if menu opened/closed after 150ms
    setTimeout(() => {
      if (submenu) {
        clickData.afterClick = {
          isToggled: submenu.classList.contains('toggled'),
          display: window.getComputedStyle(submenu).display,
          ariaExpanded: button.getAttribute('aria-expanded')
        };

        // Determine if click worked
        clickData.clickWorked = clickData.submenuInfo.wasToggled !== clickData.afterClick.isToggled;

        if (!clickData.clickWorked) {
          console.warn('❌ Menu click failed:', button.innerText || button.className);
        }
      }
    }, 150);

    window.soloDiagnostic.clickAttempts.push(clickData);
    lastClickTime = now;

  }, true);

  // Capture errors
  window.addEventListener('error', function(e) {
    window.soloDiagnostic.errors.push({
      timestamp: Date.now() - window.soloDiagnostic.startTime,
      message: e.message,
      filename: e.filename,
      line: e.lineno
    });
  });

  // Check menu system state
  const checkMenuState = () => {
    const buttons = document.querySelectorAll('button.dropdown-toggler, .dropdown-toggler, .nav__menubar-item button');
    const stateCheck = {
      timestamp: Date.now() - window.soloDiagnostic.startTime,
      totalButtons: buttons.length,
      buttonsWithHandlers: 0,
      animations: {}
    };

    // Check handlers
    buttons.forEach(btn => {
      // Check various handler attachment methods
      if (btn.onclick ||
          btn.hasAttribute('data-solo-click-handler') ||
          (Drupal?.solo?.eventHandlers?.clickHandlers?.has &&
           Drupal.solo.eventHandlers.clickHandlers.has(btn))) {
        stateCheck.buttonsWithHandlers++;
      }
    });

    // Check animation settings
    stateCheck.animations = Drupal?.solo?.animations || { not_found: true };

    // Check for stuck global state
    if (typeof state !== 'undefined' && state?.isClicked === true) {
      stateCheck.globalStateStuck = true;
      console.error('⚠️ Global state.isClicked is stuck at TRUE - blocking all clicks!');
    }

    window.soloDiagnostic.stateChecks.push(stateCheck);
  };

  // Run state checks
  setTimeout(checkMenuState, 100);
  setTimeout(checkMenuState, 2000);

  // Generate report function
  window.generateSoloReport = () => {
    checkMenuState(); // Final check

    const failedClicks = window.soloDiagnostic.clickAttempts.filter(c => c.clickWorked === false);
    const successfulClicks = window.soloDiagnostic.clickAttempts.filter(c => c.clickWorked === true);

    const report = `
==========================================
SOLO MENU DIAGNOSTIC REPORT
==========================================
Date: ${new Date().toISOString()}
Browser: ${window.soloDiagnostic.environment.browser}
Screen: ${window.soloDiagnostic.environment.screenWidth}x${window.soloDiagnostic.environment.screenHeight}
Mobile: ${window.soloDiagnostic.environment.isMobile}
Extensions: ${window.soloDiagnostic.environment.extensions?.join(', ') || 'none detected'}

CLICK SUMMARY:
--------------
Total Menu Clicks: ${window.soloDiagnostic.clickAttempts.length}
Successful: ${successfulClicks.length}
Failed: ${failedClicks.length}
Double-clicks Detected: ${window.soloDiagnostic.doubleClicks}
JavaScript Errors: ${window.soloDiagnostic.errors.length}

MENU STATE:
-----------
Total Buttons Found: ${window.soloDiagnostic.stateChecks[window.soloDiagnostic.stateChecks.length - 1]?.totalButtons || 0}
Buttons with Handlers: ${window.soloDiagnostic.stateChecks[window.soloDiagnostic.stateChecks.length - 1]?.buttonsWithHandlers || 0}
Global State Stuck: ${window.soloDiagnostic.stateChecks[window.soloDiagnostic.stateChecks.length - 1]?.globalStateStuck ? 'YES - CRITICAL!' : 'No'}

ANIMATION DELAYS:
-----------------
${JSON.stringify(window.soloDiagnostic.stateChecks[0]?.animations || {}, null, 2)}

FAILED CLICK DETAILS:
--------------------
${failedClicks.length > 0 ? failedClicks.map((c, i) => `
Click #${i + 1}:
  Time: ${c.timestamp}ms
  Button: ${c.buttonInfo.innerText || c.buttonInfo.className}
  Double-click: ${c.isDoubleClick ? 'YES' : 'No'}
  Time since last: ${c.timeSinceLastClick}ms
  Global state blocked: ${c.globalState?.isClicked ? 'YES' : 'No'}
`).join('') : 'None - all clicks worked!'}

ERRORS:
-------
${window.soloDiagnostic.errors.map(e => `- ${e.message} at line ${e.line}`).join('\n') || 'None'}

==========================================
RAW DATA FOR DEVELOPER:
${JSON.stringify({
  clicks: window.soloDiagnostic.clickAttempts,
  state: window.soloDiagnostic.stateChecks,
  errors: window.soloDiagnostic.errors
}, null, 2)}
==========================================
`;

    // Copy to clipboard
    const textarea = document.createElement('textarea');
    textarea.value = report;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);

    console.log('%c📋 Report copied to clipboard!', 'color: green; font-size: 16px; font-weight: bold');
    console.log(report);

    // Show summary
    if (failedClicks.length > 0) {
      console.error(`❌ ${failedClicks.length} clicks failed. Check if user is double-clicking or clicking too fast.`);
    } else if (window.soloDiagnostic.clickAttempts.length === 0) {
      console.warn('⚠️ No menu clicks detected. Make sure to click dropdown menus before generating report.');
    } else {
      console.log('✅ All clicks worked successfully!');
    }

    return report;
  };

  // Pretty display function
  window.showReport = () => {
    const failed = window.soloDiagnostic.clickAttempts.filter(c => c.clickWorked === false);
    const successful = window.soloDiagnostic.clickAttempts.filter(c => c.clickWorked === true);

    console.log('%c═══════════════════════════════════════', 'color: #888');
    console.log('%c       SOLO MENU DIAGNOSTIC RESULTS     ', 'color: white; background: #333; font-weight: bold; padding: 5px');
    console.log('%c═══════════════════════════════════════', 'color: #888');

    // Status
    const status = failed.length === 0 ? '✅ ALL MENUS WORKING' : `❌ ${failed.length} CLICKS FAILED`;
    console.log(`%c${status}`, failed.length === 0 ? 'color: green; font-size: 16px; font-weight: bold' : 'color: red; font-size: 16px; font-weight: bold');

    // Summary table
    console.table({
      'Total Clicks': window.soloDiagnostic.clickAttempts.length,
      'Successful': successful.length,
      'Failed': failed.length,
      'Double-clicks': window.soloDiagnostic.doubleClicks,
      'Total Buttons': window.soloDiagnostic.stateChecks[0]?.totalButtons || 0,
      'With Handlers': window.soloDiagnostic.stateChecks[0]?.buttonsWithHandlers || 0
    });

    // Environment
    console.log('%cEnvironment:', 'font-weight: bold; color: blue');
    console.log(`Browser: ${window.soloDiagnostic.environment.browser}`);
    console.log(`Screen: ${window.soloDiagnostic.environment.screenWidth}x${window.soloDiagnostic.environment.screenHeight}`);
    console.log(`Extensions: ${window.soloDiagnostic.environment.extensions?.join(', ') || 'none'}`);

    // Animation delays
    console.log('%cAnimation Delays (ms):', 'font-weight: bold; color: blue');
    console.table(window.soloDiagnostic.stateChecks[0]?.animations || {});

    // Failed clicks details
    if (failed.length > 0) {
      console.log('%c⚠️ Failed Clicks:', 'font-weight: bold; color: red');
      failed.forEach((click, i) => {
        console.log(`${i+1}. Button: "${click.buttonInfo.innerText}"
   Double-click: ${click.isDoubleClick ? 'YES' : 'No'}
   Time since last: ${click.timeSinceLastClick}ms
   Blocked by state: ${click.globalState?.isClicked ? 'YES' : 'No'}`);
      });
    }

    console.log('%c═══════════════════════════════════════', 'color: #888');
  };

  // Show instructions
  console.log('%cInstructions:', 'font-weight: bold; color: blue');
  console.log('1. Click dropdown menus that usually fail (5-10 times)');
  console.log('2. Type: showReport() for pretty display');
  console.log('3. Type: generateSoloReport() to copy full report');
  console.log('4. Send the copied report to developer');

})();
