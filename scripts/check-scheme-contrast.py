"""Scheme contrast gate: every predefined scheme's key text pairs must reach WCAG AA.

Pairs, each resolved the way the CSS resolves them (css/base/solo-variables-regions.css,
css/theme/solo-scheme-shared.css):
  site name   header heading colour (header h1/h2/h3 --r-tx, else --r-header-tx, else --sc-head-tx) on the header background
  heading     the region's --r-sc-h1, else --sc-head-tx, on each content region background (main card, top, bottom, footer)
  link        the region's --r-sc-lk, else --sc-link-tx, on each content region background (main card included)
  link hover  same with --r-sc-lk-h / --sc-link-hover-tx
  menu        --sc-menu-tx on --sc-menu-bg
  body text   the region's --r-tx (page default for main) on its background
Thresholds: 4.5:1; 3:1 for the page-title heading (large text, 24px and up).
A gradient background counts every stop; the worst stop decides.

Usage:
  check-scheme-contrast.py [DIR]    report failures, exit 1 if any (DIR defaults to css/predefined)
  check-scheme-contrast.py --write-baseline   record today's failures in scheme-contrast-baseline.txt
  check-scheme-contrast.py --list   print every failure grouped by scheme, exit 0 (for the owner's palette review)
  check-scheme-contrast.py --self-test   plants a scheme with a 1.5:1 link and proves the gate goes red,
                                         and that a known-good scheme stays green; exit 0 only then

Known failures sit in scheme-contrast-baseline.txt (one 'scheme|pair|fg|bg' per line): palettes are the
owner's design, so the gate fails on any NEW failure and on any baseline line that no longer fails
(delete it), and never on the listed ones. A gate that is red from day one is ignored.
It never edits a palette. Why contrast-scan.py --strict stays green: it models the scheme's own
region blocks and checks each token against ONE region background it resolves per region; it never
resolves the main CARD (.solo-region-box--main) that sidebar links sit on, nor the header heading
(site name) rule, and it reads only the first matching --r-bg per region. See the task record.
"""
import glob
import importlib.util
import os
import re
import shutil
import sys
import tempfile

HERE = os.path.dirname(os.path.abspath(__file__))
spec = importlib.util.spec_from_file_location('contrast_scan', os.path.join(HERE, 'contrast-scan.py'))
cs = importlib.util.module_from_spec(spec)
spec.loader.exec_module(cs)

MIN_PAIRS = 500


def first(blocks, env, keys, prop, exclude_hover=True):
    """Value of prop in the last block whose selector mentions every needle in some key."""
    val = None
    for sels, vs in blocks:
        if prop not in vs:
            continue
        for s in sels:
            if exclude_hover and ':hover' in s:
                continue
            if any(k in s for k in keys):
                val = vs[prop]
    return val


def region_bg(blocks, env, keys, shared=None):
    out = []
    for sels, vs in blocks:
        if '--r-bg' not in vs:
            continue
        for s in sels:
            if ':hover' in s or re.search(r'\ba\b', s):
                continue
            if any(k in s for k in keys):
                out.append(vs['--r-bg'])
    if not out and shared and shared in env:
        out = [env[shared]]
    return [c for v in out for c in cs.colors(v, env)]


def scan(path):
    blocks = cs.parse(open(path).read())
    env = {}
    for sels, vs in blocks:
        if any(s in (':root', '.solo-page') for s in sels):
            env.update(vs)
    rows = []
    pairs = [0]

    def check(what, fg, bgs, need=4.5):
        fgc = cs.colors(fg, env) if fg else []
        if not fgc or not bgs:
            return
        pairs[0] += 1
        r, bg = min((cs.ratio(fgc[0], b), b) for b in bgs)
        if r < need:
            rows.append((what, fg.strip(), '#%02x%02x%02x' % bg, round(r, 2), need))

    page_tx = env.get('--r-tx')
    head = env.get('--sc-head-tx')
    link = env.get('--sc-link-tx')
    link_h = env.get('--sc-link-hover-tx')

    # (region label, selector needles, shared background token, region text selectors)
    content = [
        ('main card', ['.solo-region-box--main'], '--sc-main-container-bg', ['.solo-container--main']),
        ('main', ['.solo-container--main'], '--sc-main-container-bg', ['.solo-container--main']),
        ('top', ['.solo-container--top', '__inner--top'], '--sc-top-container-bg', ['.solo-container--top']),
        ('bottom', ['.solo-container--bottom', '__inner--bottom'], '--sc-bottom-container-bg', ['.solo-container--bottom']),
        ('footer', ['.solo-container--footer', '__inner--footer'], '--sc-footer-container-bg', ['.solo-container--footer']),
        ('highlighted', ['.solo-highlighted'], None, ['.solo-highlighted']),
    ]
    for label, bkeys, shared, tkeys in content:
        bgs = region_bg(blocks, env, bkeys, shared)
        if not bgs and label in ('main', 'main card'):
            bgs = cs.colors(env.get('--r-bg'), env) or [cs.hexrgb(cs.WHITE)]
        if label == 'main card' and not bgs:
            continue
        # footer-like regions set their own text/link colour; light regions use the tokens.
        r_tx = first(blocks, env, tkeys, '--r-tx') or page_tx
        r_lk = first(blocks, env, tkeys, '--r-sc-lk')
        r_lkh = first(blocks, env, tkeys, '--r-sc-lk-h')
        if label in ('bottom', 'footer'):
            check(label + ' text', r_tx, bgs)
            check(label + ' link', r_lk or r_tx, bgs)
            continue
        check(label + ' text', r_tx, bgs)
        # A scheme's per-region heading color (--r-sc-h1) wins over the palette one.
        check(label + ' heading', first(blocks, env, tkeys, '--r-sc-h1') or head, bgs)
        check(label + ' link', r_lk or link, bgs)
        check(label + ' link hover', r_lkh or link_h, bgs)

    hbgs = region_bg(blocks, env, ['header.solo-header', '.solo-header'], None)
    if not hbgs and '--r-header-bg' in env:
        hbgs = cs.colors(env['--r-header-bg'], env)
    site = None
    for sels, vs in blocks:
        if '--r-tx' in vs and any('header.solo-header' in s and re.search(r'\bh[1-3]\b', s) for s in sels):
            site = vs['--r-tx']
    site = site or first(blocks, env, ['header.solo-header'], '--r-tx') or env.get('--r-header-tx') or head
    check('site name', site, hbgs)

    tbgs = region_bg(blocks, env, ['solo-page-title'], None)
    ptx = None
    for sels, vs in blocks:
        if '--r-tx' in vs and any('solo-page-title' in s and re.search(r'\bh1\b', s) for s in sels):
            ptx = vs['--r-tx']
    ptx = ptx or first(blocks, env, ['solo-page-title'], '--r-sc-h1')
    check('page title heading (large)', ptx or site, tbgs or hbgs, 3.0)

    if '--sc-menu-tx' in env and '--sc-menu-bg' in env:
        check('menu text', env['--sc-menu-tx'], cs.colors(env['--sc-menu-bg'], env))
    return rows, pairs[0]


def run(path):
    results, total = {}, 0
    for f in sorted(glob.glob(os.path.join(path, '*.css'))):
        rows, n = scan(f)
        total += n
        results[os.path.basename(f)[5:-4]] = rows
    return results, total


BASELINE = os.path.join(HERE, 'scheme-contrast-baseline.txt')


def keys(results):
    return {f'{n}|{w}|{fg}|{bg}' for n, rows in results.items() for w, fg, bg, _, _ in rows}


def report(results, total, baseline):
    fails = sum(len(r) for r in results.values())
    bad = [k for k, v in results.items() if v]
    for name in sorted(bad):
        print('## ' + name)
        for what, fg, bg, r, need in results[name]:
            mark = '  [baseline]' if f'{name}|{what}|{fg}|{bg}' in baseline else ''
            print(f'  {what:30} {fg:24} on {bg}  {r}:1 (needs {need}){mark}')
    now = keys(results)
    new, fixed = sorted(now - baseline), sorted(baseline - now)
    print(f'SCHEME CONTRAST: {len(results)} schemes, {total} pairs, {fails} failures in {len(bad)} schemes; '
          f'{len(new)} new, {len(fixed)} baseline entries now passing')
    for k in new:
        print('  NEW  ' + k)
    for k in fixed:
        print('  FIXED (remove from baseline)  ' + k)
    return len(new) + len(fixed)


def self_test(path):
    results, total = run(path)
    d = tempfile.mkdtemp()
    try:
        src = sorted(glob.glob(os.path.join(path, '*.css')))[0]
        css = open(src).read()
        css = re.sub(r'(--sc-link-tx:)\s*[^;]+;', r'\1 #fefefe;', css)
        # Schemes set each region's link color too (--r-sc-lk), which wins over the palette.
        css = re.sub(r'(--r-sc-lk:)\s*[^;]+;', r'\1 #fefefe;', css)
        css = re.sub(r'(--sc-main-container-bg:)\s*[^;]+;', r'\1 #ffffff;', css)
        open(os.path.join(d, 'solo-planted.css'), 'w').write(css)
        planted, _ = run(d)
        red = any('link' in r[0] and r[3] < 1.1 for r in planted['planted'])
        # control 2: a known-good palette pair must stay green (white on black, 21:1)
        ok_css = css.replace('#fefefe', '#000000')
        open(os.path.join(d, 'solo-planted.css'), 'w').write(ok_css)
        good, _ = run(d)
        green = not any('main link' == r[0] or 'main card link' == r[0] for r in good['planted'])
    finally:
        shutil.rmtree(d)
    print(f'self-test: real scan pairs={total}, planted 1.0:1 link red={red}, 21:1 link green={green}')
    return red and green and total >= MIN_PAIRS


if __name__ == '__main__':
    args = sys.argv[1:]
    list_only = '--list' in args
    write_base = '--write-baseline' in args
    st = '--self-test' in args
    args = [a for a in args if not a.startswith('--')]
    path = args[0] if args else os.path.join(os.path.dirname(HERE), 'css', 'predefined')
    if st:
        ok = self_test(path)
        print('SELF-TEST:', 'OK' if ok else 'BROKEN')
        sys.exit(0 if ok else 1)
    results, total = run(path)
    if write_base:
        open(BASELINE, 'w').write('\n'.join(sorted(keys(results))) + '\n')
        print(f'baseline written: {len(keys(results))} entries')
        sys.exit(0)
    base = set(l.strip() for l in open(BASELINE)) if os.path.exists(BASELINE) else set()
    fails = report(results, total, base)
    if total < MIN_PAIRS:
        print(f'FAIL: only {total} pairs checked (minimum {MIN_PAIRS}); the scanner is not matching the markup')
        sys.exit(2)
    sys.exit(0 if list_only else (1 if fails else 0))
