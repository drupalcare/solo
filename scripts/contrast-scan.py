"""Contrast scan of Solo's predefined schemes (static, from the CSS files).

Adopted from the d11 scratch scanner (solo_contrast.py) and kept here so it has one home.
Models each region: its backgrounds (every --r-background and --r-background-image the scheme sets for it, gradient stops
counted separately, worst one wins) against its body text, headings, links and link hover,
plus menu, buttons, tab hover. Text needs 4.5:1. Control borders (selectify/inputs) need
3:1 (WCAG 1.4.11) against the field and the main background.

Usage:
  contrast-scan.py [DIR]           scan DIR (default css/predefined). Exit 1 on any TEXT failure.
                                   Border (3:1) findings are counted and listed; they fail only with --strict.
  contrast-scan.py --control       negative control: plants a bad scheme in a temp dir and exits 0
                                   only if the scanner turns RED on it (and stays green on the real one).
  contrast-scan.py --baseline FILE write the per-scheme findings to FILE (for planning/).
Also fails when the scan examined fewer than MIN_CHECKS probes (a scanner that matches nothing is not green).
"""
import re, sys, os, glob, shutil, tempfile

WHITE = '#ffffff'

def hexrgb(h):
    h = h.lstrip('#')
    if len(h) in (3, 4):
        h = ''.join(c * 2 for c in h[:3])
    return tuple(int(h[i:i + 2], 16) for i in (0, 2, 4))

def lum(c):
    def ch(v):
        v /= 255
        return v / 12.92 if v <= 0.04045 else ((v + 0.055) / 1.055) ** 2.4
    r, g, b = c
    return 0.2126 * ch(r) + 0.7152 * ch(g) + 0.0722 * ch(b)

def ratio(a, b):
    la, lb = lum(a), lum(b)
    return (max(la, lb) + 0.05) / (min(la, lb) + 0.05)

def parse(text):
    text = re.sub(r'/\*.*?\*/', '', text, flags=re.S)
    blocks = []
    for sel, body in re.findall(r'([^{}]+)\{([^{}]*)\}', text):
        vs = dict(re.findall(r'(--[\w-]+)\s*:\s*([^;]+);', body))
        sels = [s.strip() for s in re.split(r',(?![^()]*\))', sel)]
        blocks.append((sels, vs))
    return blocks

def colors(val, env, depth=0):
    """All concrete colors in a value, resolving var()."""
    if val is None or depth > 6:
        return []
    out = []
    for m in re.finditer(r'#[0-9a-fA-F]{3,8}\b|var\((--[\w-]+)[^)]*\)|rgba?\(([^)]*)\)', val):
        if m.group(0).startswith('#'):
            out.append(hexrgb(m.group(0)))
        elif m.group(1):
            name = m.group(1)
            if name == '--solo-white':
                out.append(hexrgb(WHITE))
            elif name in env:
                out += colors(env[name], env, depth + 1)
        else:
            parts = re.split(r'[\s,/]+', m.group(2).strip())
            try:
                out.append(tuple(int(float(p)) for p in parts[:3]))
            except ValueError:
                pass
    return out

REGIONS = {
    'header': ['.solo-header', 'header.solo-header'],
    'page title': ['solo-page-title'],
    'breadcrumb': ['.breadcrumb'],
    'highlighted': ['.solo-highlighted'],
    'welcome': ['.solo-welcome-text'],
    'top': ['.solo-container--top', '__inner--top'],
    'main': ['.solo-container--main', 'region-box--main'],
    'bottom': ['.solo-container--bottom', '__inner--bottom'],
    'footer': ['.solo-container--footer', '__inner--footer'],
    'footer menu': ['.solo-footer-menu__nav'],
    'copyright': ['.solo-copyright'],
    'search popup': ['.solo-fixed-search-block'],
    'login popup': ['.solo-popup-login-block__inner'],
}
SHARED_BG = {
    'main': '--sc-main-container-bg', 'top': '--sc-top-container-bg',
    'bottom': '--sc-bottom-container-bg', 'footer': '--sc-footer-container-bg',
    'footer menu': '--sc-footer-menu-bg', 'copyright': '--sc-copyright-bg',
    'search popup': '--sc-fixed-search-block-bg', 'login popup': '--sc-popup-login-block-inner-bg',
}
EXCLUDE = re.compile(r'selectify|solo-select|solo-radio|solo-check|solo-input|solo-textarea|button|tabs|menu-tree|main-nav|hamburger|animate-hover|__wrapper|login__open')

CHECKS = 0
MIN_CHECKS = 1000


def in_region(sel, keys):
    return any(k in sel for k in keys)

def scan(path):
    blocks = parse(open(path).read())
    env = {}
    for sels, vs in blocks:
        if any(s in (':root', '.solo-page') for s in sels):
            env.update(vs)
    page_tx = env.get('--r-text')
    rows = []

    def check(region, what, fg, bgs, need=4.5):
        global CHECKS
        fgc = colors(fg, env)
        if not fgc or not bgs:
            return
        CHECKS += 1
        worst = min((ratio(fgc[0], b), b) for b in bgs)
        if worst[0] < need:
            rows.append((region, what, fg.strip(), '#%02x%02x%02x' % worst[1], round(worst[0], 2), need))

    for region, keys in REGIONS.items():
        bg_vals, tx, lk, lkh, h1 = [], None, None, None, None
        for sels, vs in blocks:
            for s in sels:
                if not in_region(s, keys) or EXCLUDE.search(s):
                    continue
                hover = ':hover' in s
                is_a = re.search(r'\ba\b', s) is not None
                is_h = re.search(r'\bh[1-3]\b', s) is not None
                if '--r-background' in vs and not hover and not is_a:
                    bg_vals.append(vs['--r-background'])
                if '--r-background-image' in vs and not hover and not is_a:
                    bg_vals.append(vs['--r-background-image'])
                if '--r-text' in vs:
                    if is_a and hover:
                        lkh = vs['--r-text']
                    elif is_a:
                        lk = vs['--r-text']
                    elif is_h:
                        h1 = vs['--r-text']
                    elif not hover:
                        tx = vs['--r-text']
                if '--r-link' in vs and not hover:
                    lk = lk or vs['--r-link']
                if '--r-link-hover' in vs:
                    lkh = lkh or vs['--r-link-hover']
                if '--r-heading' in vs:
                    h1 = h1 or vs['--r-heading']
        if not bg_vals and region in SHARED_BG and SHARED_BG[region] in env:
            bg_vals = [env[SHARED_BG[region]]]
        if region == 'header' and not bg_vals and '--solo-accent-background' in env:
            bg_vals = [env['--solo-accent-background']]
        bgs = [c for v in bg_vals for c in colors(v, env)]
        body = tx or page_tx
        check(region, 'text', body, bgs)
        if region not in ('breadcrumb',):
            check(region, 'heading', h1 or (body if tx else env.get('--sc-head-tx')), bgs)
        check(region, 'link', lk or (tx if tx and region in ('bottom', 'footer', 'footer menu', 'copyright', 'breadcrumb', 'page title') else env.get('--sc-link-tx')), bgs)
        check(region, 'link hover', lkh or env.get('--sc-link-hover-tx'), bgs)
        if region == 'header' and '--solo-accent-text' in env:
            check(region, 'header text', env['--solo-accent-text'], bgs)

    def pair(region, what, fgk, bgk, need=4.5):
        if fgk in env and bgk in env:
            check(region, what, env[fgk], colors(env[bgk], env), need)

    pair('menu', 'link', '--sc-menu-tx', '--sc-menu-bg')
    pair('menu', 'hover', '--sc-menu-hover-tx', '--sc-menu-hover-bg')
    pair('menu', 'open', '--sc-menu-open-tx', '--sc-menu-open-bg')
    pair('button', 'label', '--sc-button-tx', '--sc-button-bg')
    pair('button', 'hover label', '--sc-button-hover-tx', '--sc-button-hover-bg')
    pair('tabs', 'hover label', '--sc-tab-hover-tx', '--sc-tab-hover-bg')
    pair('pager', 'hover label', '--solo-accent-text', '--solo-accent-background')
    pair('dropdown', 'option hover', '--r-menu-text-hover', '--r-menu-background-hover')
    # Form control border against the main content background (non-text, 3:1).
    ctl = None
    for sels, vs in blocks:
        if any('.selectify:where' in s for s in sels) and '--r-border' in vs:
            ctl = vs['--r-border']
    if ctl:
        check('form controls', 'border (3:1)', ctl, [hexrgb(WHITE)] + colors(env.get('--sc-main-container-bg'), env), 3.0)
    return rows

def run(path, quiet=False):
    """Returns (text_failures, border_failures, per-scheme lines)."""
    text = border = 0
    lines = []
    for f in sorted(glob.glob(os.path.join(path, '*.css'))):
        rows = scan(f)
        name = os.path.basename(f)[5:-4]
        t = [r for r in rows if r[0] != 'form controls']
        b = [r for r in rows if r[0] == 'form controls']
        text += len(t)
        border += len(b)
        lines.append(f'## {name}: text {len(t)}, border {len(b)}')
        for r in rows:
            lines.append(f'  {r[0]:13} {r[1]:13} {r[2]:22} on {r[3]}  {r[4]}:1 (needs {r[5]})')
    return text, border, lines


def control(path):
    """Plant a scheme whose menu text equals its menu background and whose page text equals its page background."""
    d = tempfile.mkdtemp()
    try:
        src = sorted(glob.glob(os.path.join(path, '*.css')))[0]
        css = open(src).read()
        bg = re.search(r'--sc-menu-bg:\s*([^;]+);', css).group(1)
        css = re.sub(r'(--sc-menu-tx:)\s*[^;]+;', r'\1 ' + bg + ';', css)
        css += '\n.solo-container--main { --r-background: #ffffff; --r-text: #fefefe; }\n'
        open(os.path.join(d, 'solo-planted-bad.css'), 'w').write(css)
        text, _, lines = run(d)
        print('control: planted scheme text failures =', text)
        for l in lines[:6]:
            print(' ', l)
        return text >= 2
    finally:
        shutil.rmtree(d)


if __name__ == '__main__':
    args = sys.argv[1:]
    here = os.path.dirname(os.path.abspath(__file__))
    default = os.path.join(os.path.dirname(here), 'css', 'predefined')
    baseline = None
    if '--baseline' in args:
        i = args.index('--baseline')
        baseline = args[i + 1]
        del args[i:i + 2]
    strict = '--strict' in args
    ctl = '--control' in args
    args = [a for a in args if not a.startswith('--')]
    path = args[0] if args else default
    text, border, lines = run(path)
    schemes = len(lines) and sum(1 for l in lines if l.startswith('## '))
    border_schemes = sum(1 for l in lines if l.startswith('## ') and not l.endswith('border 0'))
    summary = (f'SCAN: {schemes} schemes, {CHECKS} probes, text failures {text}, '
               f'border findings {border} in {border_schemes} schemes')
    if baseline:
        open(baseline, 'w').write('\n'.join(lines) + '\n' + summary + '\n')
    if ctl:
        print('real scan:', summary)
        real_green = text == 0 and CHECKS >= MIN_CHECKS
        red = control(path)
        print('CONTROL:', 'OK (real scan green, planted scheme red)' if (real_green and red) else
              f'BROKEN (real_green={real_green}, planted_red={red})')
        sys.exit(0 if (real_green and red) else 1)
    for l in lines:
        if not l.startswith('## ') or not l.endswith('text 0, border 0'):
            print(l)
    print(summary)
    if CHECKS < MIN_CHECKS:
        print(f'FAIL: only {CHECKS} probes ran (minimum {MIN_CHECKS}); the scanner is not matching the markup')
        sys.exit(2)
    sys.exit(1 if text or (strict and border) else 0)
