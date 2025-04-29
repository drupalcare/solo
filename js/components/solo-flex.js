/**
 * @file
 * SoloFlex dynamic utility classes behavior.
 */
(function(Drupal, once) {
  'use strict';
  Drupal.behaviors.soloFlex = {
    attach(context) {
      const rules = {
        p: {
          css: 'padding',
          sides: true
        },
        m: {
          css: 'margin',
          sides: true
        },
        b: {
          css: 'borderWidth',
          sides: true
        },
        br: {
          css: 'borderRadius',
          sides: false
        },
        bg: {
          css: 'backgroundColor',
          sides: false,
          isColor: true
        },
        c: {
          css: 'color',
          sides: false,
          isColor: true
        },
        bdc: {
          css: 'borderColor',
          sides: false,
          isColor: true
        },
        w: {
          css: 'width',
          sides: false,
          allowPercent: true
        },
        h: {
          css: 'height',
          sides: false,
          allowPercent: true
        },
        fs: {
          css: 'fontSize',
          sides: false
        },
        g: {
          css: 'gap',
          sides: false
        },
        o: {
          css: 'opacity',
          sides: false,
          isDecimal: true
        },
        z: {
          css: 'zIndex',
          sides: false
        },
        ta: {
          css: 'textAlign',
          sides: false,
          isKeyword: true
        },
        d: {
          css: 'display',
          sides: false,
          isKeyword: true
        },
        po: {
          css: 'position',
          sides: false,
          isKeyword: true
        },
        bgs: {
          css: 'backgroundSize',
          sides: false,
          isKeyword: true
        },
        r: {
          css: 'rotate',
          sides: false,
          isTransform: 'rotate'
        },
        s: {
          css: 'scale',
          sides: false,
          isTransform: 'scale'
        },
        t: {
          css: 'top',
          sides: false
        },
        l: {
          css: 'left',
          sides: false
        },
        rpos: {
          css: 'right',
          sides: false
        },
        btm: {
          css: 'bottom',
          sides: false
        },
      };
      const breakpoints = {
        sm: 576,
        md: 768,
        lg: 992,
        xl: 1200,
        xxl: 1400,
      };
      const resettableProperties = {
        padding: true,
        margin: true,
        borderWidth: true,
        borderRadius: true,
        backgroundColor: true,
        color: true,
        borderColor: true,
        width: true,
        height: true,
        fontSize: true,
        gap: true,
        opacity: true,
        zIndex: true,
        textAlign: true,
        display: true,
        position: true,
        backgroundSize: true,
        top: true,
        left: true,
        right: true,
        bottom: true,
        transform: true,
      };

      function applyClasses(context) {
        const currentWidth = window.innerWidth;
        const elements = once('soloFlex', '.solo-flex', context);
        elements.forEach(el => {
          const soloFlexProperties = new Set();
          el.classList.forEach(className => {
            if (className === 'solo-flex') {
              return;
            }
            let match;
            let isResponsive = false;
            let bpPrefix = '';
            let pureClass = className;
            // Check responsive prefix
            for (const bp in breakpoints) {
              if (className.startsWith(`${bp}-`)) {
                isResponsive = true;
                bpPrefix = bp;
                pureClass = className.substring(bp.length + 1);
                break;
              }
            }
            if (isResponsive && currentWidth > breakpoints[bpPrefix]) {
              return;
            }
            for (const prefix in rules) {
              const rule = rules[prefix];
              if (rule.sides && (match = pureClass.match(new RegExp(`^${prefix}-(r|l|t|b)-(\\d+)$`)))) {
                const side = {
                  r: 'Right',
                  l: 'Left',
                  t: 'Top',
                  b: 'Bottom'
                } [match[1]];
                soloFlexProperties.add(`${rule.css}${side}`);
                el.style[`${rule.css}${side}`] = `${match[2]}px`;
              } else if (rule.isKeyword && (match = pureClass.match(new RegExp(`^${prefix}-(\\w+)$`)))) {
                soloFlexProperties.add(rule.css);
                el.style[rule.css] = match[1];
              } else if (rule.isDecimal && (match = pureClass.match(new RegExp(`^${prefix}-(\\d+)$`)))) {
                soloFlexProperties.add(rule.css);
                const value = parseInt(match[1], 10) / 100;
                el.style[rule.css] = value.toString();
              } else if (rule.isTransform && (match = pureClass.match(new RegExp(`^${prefix}-(\\d+)$`)))) {
                soloFlexProperties.add('transform');
                if (!el.style.transform) {
                  el.style.transform = '';
                }
                if (rule.isTransform === 'rotate') {
                  el.style.transform += ` rotate(${match[1]}deg)`;
                }
                if (rule.isTransform === 'scale') {
                  const scaleValue = parseInt(match[1], 10) / 100;
                  el.style.transform += ` scale(${scaleValue})`;
                }
              } else if (!rule.isColor && (match = pureClass.match(new RegExp(`^${prefix}-(\\d+)(p?)$`)))) {
                soloFlexProperties.add(rule.css);
                const value = match[1];
                const isPercent = match[2] === 'p';
                if (isPercent && rule.allowPercent) {
                  el.style[rule.css] = `${value}%`;
                } else {
                  el.style[rule.css] = `${value}px`;
                }
              } else if (rule.isColor && (match = pureClass.match(new RegExp(`^${prefix}-([0-9a-fA-F]{6})$`)))) {
                soloFlexProperties.add(rule.css);
                el.style[rule.css] = `#${match[1]}`;
              } else if (rule.isColor && (match = pureClass.match(new RegExp(`^${prefix}-([0-9a-fA-F]{3})$`)))) {
                soloFlexProperties.add(rule.css);
                const hex = match[1].split('').map(ch => ch + ch).join('');
                el.style[rule.css] = `#${hex}`;
              }
            }
          });
          // Now reset properties that SoloFlex would manage, but no classes matched for them
          for (const cssProp in resettableProperties) {
            if (!soloFlexProperties.has(cssProp)) {
              el.style[cssProp] = '';
            }
          }
        });
      }
      // Initial run
      applyClasses(context);
      // Re-run on window resize (with debounce)
      let resizeTimeout;
      window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          applyClasses(context);
        }, 150);
      });
    }
  };
})(Drupal, once);
