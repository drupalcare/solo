#!/usr/bin/env python3
"""Static cascade-winner guard for Solo CSS: did a selector or !important edit change who wins?

Compares the Solo tree at a git revision (default HEAD, extracted with `git archive` into a temp
dir) with the working tree. Declarations are paired by file and position, so selectors may change
but the property sequence of each file must not. For every declaration whose selector or
!important changed, every other declaration that shares a subject class/id/type and the same
property (or shorthand family) with a different value is checked: if the winner between the two
differs before and after, that is a CHANGED WINNER and the script exits 1.

Universe: every CSS file in solo.libraries.yml (schemes included, css/debug excluded); core
modules, core/misc and modules/contrib of a Drupal docroot (symlinks followed, apex-named paths
skipped); Claro only against solo-form-theme-settings; ThemeCssBuilder's generated region rules
synthesised from solo_utilities. Order key: importance, layer (external @layer honoured; Solo is
compared unlayered on both sides), specificity, load order.

Usage (python3 -I):
  check-winners.py                      HEAD vs working tree; exit 0 = no changed winner
  check-winners.py --base REV           another baseline revision
  check-winners.py --before DIR --after DIR   compare two extracted trees (control / red proof)
  check-winners.py --hist [--list] [DIR]      specificity histogram of components, base, layout, breakpoints, theme, predefined, w3-css
  check-winners.py --stable [...]       pair by stable_key (library, media, selector with ids as class twins and
                                        :where() unwrapped, property, value) so rules may be reordered; every
                                        Solo declaration is judged; a one-to-one value edit is paired, not judged.
                                        Positional pairing stays the default/control.
  check-winners.py --util-before PHP    also judge the generated region rules (ThemeCssBuilder REGION_SELECTORS)
                                        against a baseline copy of ThemeCssBuilder.php; without it they are fixed rivals.
Exit: 0 clean, 1 changed winners, 2 cannot pair (positional: a file's declaration list changed shape;
--stable: a declaration added or removed).
Limits: no markup and no media exclusivity across families, so it over-reports; inline styles and
site custom CSS are not modelled. A pass is evidence, not a browser check.
"""
import argparse
import collections
import glob
import os
import re
import subprocess
import sys
import tempfile

SOLO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
UTIL = os.path.join(os.path.dirname(SOLO), 'solo_utilities', 'src', 'Css', 'ThemeCssBuilder.php')
DOCROOT = '/Users/alaahaddad/Sites/demo/web'
EXT_DIRS = ('core/modules', 'core/misc', 'modules/contrib', 'core/themes/claro')
ADMIN_ONLY = {'solo-form-theme-settings'}
CAT = {'base': -200, 'layout': -100, 'component': 0, 'state': 100, 'theme': 200}
PX = ('576', '768', '992', '1200', '1400')
FAM = {'menu-mn': 'css/breakpoints/solo-menu-breakpoints-mn-{bp}.css',
       'global-sb': 'css/breakpoints/solo-global-breakpoints-sb-{bp}.css',
       'layout-sb': 'css/layout/solo-layout-breakpoints-sb-{bp}.css'}
GEN = {v.format(bp=b): (k, b) for k, v in FAM.items() for b in PX}
# id -> class the same element always carries (templates/layout/page.html.twig:84, partials/_header:3,7,23,
# _primary-menu:3, _primary-sidebar-menu:3,6, _fixed-search-block:3-4, _main-regions:35,42, _system-messages:3,
# preloader:6-7). Each class is on that one element only in Solo markup.
IDS = {'#solo-page-wrapper': '.solo-page', '#solo-header': '.solo-header', '#solo-primary-menu': '.solo-primary-menu',
       '#solo-sidebar-button-open': '.solo-sidebar-menu__open', '#solo-sidebar-button-close': '.solo-sidebar-menu__close',
       '#solo-search-button-open': '.solo-fixed-search__open', '#solo-fixed-search-block': '.solo-fixed-search',
       '#solo-primary-sidebar-menu': '.solo-primary-sidebar-menu', '#solo-main-container-inner': '.solo-container__inner--main',
       '#solo-system-messages': '.solo-system-messages', '#solo-preloader': '.solo-preloader'}
OPAQUE = ('@keyframes', '@-webkit-keyframes', '@font-face', '@page', '@property', '@counter-style')


# ---- CSS parsing -------------------------------------------------------------------------------
def strip_comments(t):
    out = []; i = 0; n = len(t)
    while i < n:
        if t.startswith('/*', i):
            j = t.find('*/', i + 2); j = n if j < 0 else j + 2
            out.append(''.join(c if c == '\n' else ' ' for c in t[i:j])); i = j
        elif t[i] in '"\'':
            q = t[i]; j = i + 1
            while j < n and t[j] != q:
                j += 2 if t[j] == '\\' else 1
            out.append(t[i:j + 1]); i = j + 1
        else:
            out.append(t[i]); i += 1
    return ''.join(out)


def split_top(s, ch):
    parts = []; depth = 0; cur = []; q = None
    for c in s:
        if q:
            cur.append(c)
            if c == q: q = None
            continue
        if c in '"\'': q = c
        elif c in '([': depth += 1
        elif c in ')]': depth -= 1
        if c == ch and depth == 0:
            parts.append(''.join(cur)); cur = []
        else:
            cur.append(c)
    parts.append(''.join(cur))
    return parts


def parse(text):
    """Return (line, at-rule context, selector, property, value, important) per declaration."""
    t = strip_comments(text); rules = []; stack = []; buf = []; i = 0; n = len(t); line = 1; q = None; paren = 0

    def flush(body, sel, ctx, bline):
        for d in split_top(body, ';'):
            if ':' not in d: continue
            p, v = d.split(':', 1); p = p.strip().lower(); v = ' '.join(v.split())
            if not p: continue
            imp = bool(re.search(r'!\s*important\s*$', v, re.I))
            v = re.sub(r'\s*!\s*important\s*$', '', v, flags=re.I)
            for s in split_top(sel, ','):
                rules.append((bline, ctx, ' '.join(s.split()), p, v, imp))
    while i < n:
        c = t[i]
        if c == '\n': line += 1
        if q:
            buf.append(c)
            if c == '\\' and i + 1 < n: buf.append(t[i + 1]); i += 2; continue
            if c == q: q = None
            i += 1; continue
        if c in '"\'': q = c; buf.append(c); i += 1; continue
        if c == '(': paren += 1
        if c == ')': paren -= 1
        if c == '{' and paren == 0:
            pre = ' '.join(''.join(buf).split()); buf = []
            if stack and stack[-1][0] == 'opaque':
                stack.append(('opaque',)); i += 1; continue
            if pre.startswith('@'):
                stack.append(('opaque',) if pre.lower().startswith(OPAQUE) else ('at', pre))
            else:
                stack.append(('rule', pre, line))
            i += 1; continue
        if c == '}' and paren == 0:
            body = ''.join(buf); buf = []
            top = stack.pop() if stack else ('none',)
            if top[0] == 'rule':
                flush(body, top[1], tuple(s[1] for s in stack if s[0] == 'at'), top[2])
            i += 1; continue
        if c == ';' and paren == 0 and not (stack and stack[-1][0] == 'rule'):
            buf = []; i += 1; continue
        buf.append(c); i += 1
    return rules


def specificity(sel):
    s = re.sub(r':where\((?:[^()]|\([^()]*\))*\)', '', sel)

    def mx(m):
        best = (0, 0, 0)
        for a in split_top(m.group(2), ','):
            best = max(best, specificity(a))
        return '\x00%d,%d,%d\x00' % best
    extra = [0, 0, 0]
    while True:
        s2 = re.sub(r':(is|not|has|matches)\(((?:[^()]|\([^()]*\))*)\)', mx, s)
        if s2 == s: break
        s = s2
    for m in re.finditer(r'\x00(\d+),(\d+),(\d+)\x00', s):
        for k in range(3): extra[k] += int(m.group(k + 1))
    s = re.sub(r'\x00[\d,]+\x00', '', s)
    s = re.sub(r'\[[^\]]*\]', lambda m: ' .x ', s)
    a = len(re.findall(r'#[\w-]+', s))
    pe = len(re.findall(r'::[\w-]+|:(?:before|after|first-line|first-letter)\b', s))
    s = re.sub(r'::[\w-]+(\([^)]*\))?|:(?:before|after|first-line|first-letter)\b', ' ', s)
    b = len(re.findall(r'\.[\w-]+', s)) + len(re.findall(r':[\w-]+', s))
    s = re.sub(r'#[\w-]+|\.[\w-]+|:[\w-]+(\([^)]*\))?', ' ', s)
    c = len(re.findall(r'(?<![\w-])[a-zA-Z][\w-]*', s)) + pe
    return (a + extra[0], b + extra[1], c + extra[2])


def split_complex(sel):
    out = []; cur = ''; depth = 0; comb = ''; i = 0; s = sel.strip()
    while i < len(s):
        c = s[i]
        if c in '([': depth += 1
        if c in ')]': depth -= 1
        if depth == 0 and c in ' >+~':
            j = i; cb = ' '
            while j < len(s) and s[j] in ' >+~':
                if s[j] != ' ': cb = s[j]
                j += 1
            out.append((cur, comb)); cur = ''; comb = cb; i = j; continue
        cur += c; i += 1
    out.append((cur, comb))
    return out


# ---- the trees -------------------------------------------------------------------------------
def order(root):
    """css file -> (category weight + weight, file index, library), from solo.libraries.yml."""
    o = {}; lib = cat = None; idx = 0
    for ln in open(os.path.join(root, 'solo.libraries.yml'), encoding='utf-8'):
        m = re.match(r'^([\w-]+):\s*$', ln)
        if m: lib = m.group(1); continue
        m = re.match(r'^    (base|layout|component|state|theme):\s*$', ln)
        if m: cat = m.group(1); continue
        m = re.match(r'^      (css/\S+\.css):\s*\{(.*)\}', ln)
        if m:
            w = re.search(r'weight:\s*(-?\d+)', m.group(2)); w = int(w.group(1)) if w else 0
            o.setdefault(m.group(1), (CAT[cat] + w, idx, lib)); idx += 1
    return o


def decls_tree(root):
    out = []
    for f, (w, i, lib) in order(root).items():
        if f.startswith('css/debug/') or not os.path.exists(os.path.join(root, f)): continue
        for n, (ln, ctx, sel, p, v, imp) in enumerate(parse(open(os.path.join(root, f), encoding='utf-8').read())):
            out.append(dict(f=f, n=n, ln=ln, ctx=ctx, sel=sel, p=p, v=v, imp=imp, lay=False, o=(w, i, ln),
                            sp=specificity(sel), lib=lib))
    return out


def external(docroot):
    out = []
    for sub in EXT_DIRS:
        for d, ds, fs in os.walk(os.path.join(docroot, sub), followlinks=True):
            ds[:] = sorted(x for x in ds if 'apex' not in x and x not in ('tests', 'node_modules', 'js'))
            for fn in sorted(fs):
                if not fn.endswith('.css') or 'apex' in fn: continue
                p = os.path.join(d, fn)
                try:
                    rs = parse(open(p, encoding='utf-8', errors='replace').read())
                except Exception:
                    continue
                for n, (ln, ctx, sel, pr, v, imp) in enumerate(rs):
                    out.append(dict(f=p.split('/web/', 1)[-1], n=n, ln=ln, ctx=ctx, sel=sel, p=pr, v=v, imp=imp,
                                    lay=any(c.startswith('@layer') for c in ctx), o=(-10 ** 6, 0, n),
                                    sp=specificity(sel), lib='claro' if '/themes/claro' in p else 'ext'))
    return out


def region_rules(php_path):
    """(region, selector) pairs and --r-* properties from ThemeCssBuilder's REGION_SELECTORS and COLOR_PROPERTIES."""
    php = open(php_path, encoding='utf-8').read()
    reg = re.search(r'REGION_SELECTORS = \[(.*?)\];', php, re.S).group(1)
    props = re.search(r'COLOR_PROPERTIES = \[(.*?)\];', php, re.S).group(1)
    return re.findall(r"'(\w+)' => '([^']+)'", reg), re.findall(r"'\w+' => '(--r-[\w-]+)'", props)


def generated(php_path, before_path=None):
    """The region colour rules, every colour set. With before_path (a baseline ThemeCssBuilder.php), each rule is
    paired with its baseline by region and property and judged as a changed selector (subjects: both selectors)."""
    if not os.path.exists(php_path): return []

    def rules(path):
        regs, props = region_rules(path)
        return {(name, p): dict(f='generated:' + name, n=k, ln=k, ctx=(), sel=sel, p=p, v='GEN', imp=False, lay=False,
                                o=(10 ** 6, 0, k), sp=specificity(sel), lib='generated')
                for k, (name, sel) in enumerate(regs) for p in props}
    after = rules(php_path)
    if before_path:
        before = rules(before_path)
        if set(before) != set(after):
            sys.exit('CANNOT PAIR generated rules: regions or properties differ from %s' % before_path)
        for k, d in after.items():
            d['b'] = before[k]; d['chg'] = True; d['tk'] = tokens(before[k]['sel']) | tokens(d['sel'])
    return list(after.values())


# ---- comparison ------------------------------------------------------------------------------
def tokens(sel):
    comp = split_complex(sel)[-1][0]
    comp = re.sub(r'::?[\w-]+(\((?:[^()]|\([^()]*\))*\))?', '', comp)
    t = set(re.findall(r'[.#][\w-]+', comp))
    m = re.match(r'[a-zA-Z][\w-]*', comp)
    if m: t.add(m.group(0).lower())
    for k, v in IDS.items():
        if k in t: t.add(v)
    return t


def rootp(p): return '' if p.startswith('--') else p.split('-')[0]


def conflict(a, b):
    return a == b or (not a.startswith('--') and not b.startswith('--') and (b.startswith(a + '-') or a.startswith(b + '-')))


def key(d):
    lay = d.get('lay', False)
    return (d['imp'], (1 if lay else 0) if d['imp'] else (0 if lay else 1), d['sp'], d['o'])


def pair_trees(before, after):
    """Attach each after-declaration to its before twin; return files that cannot be paired."""
    bf = collections.defaultdict(list); af = collections.defaultdict(list)
    for d in before: bf[d['f']].append(d)
    for d in after: af[d['f']].append(d)
    bad = sorted(f for f in set(bf) | set(af) if [x['p'] for x in bf[f]] != [x['p'] for x in af[f]])
    vals = 0
    for f, ds in af.items():
        if f in bad: continue
        for d, b in zip(ds, bf[f]):
            d['b'] = b; d['chg'] = (b['sel'], b['imp']) != (d['sel'], d['imp']); vals += b['v'] != d['v']
    return bad, vals


def unwrap_where(sel):
    """Drop every ':where(' and its matching ')', keeping the argument: ':where(li).x' -> 'li.x'."""
    out = []; i = 0
    while True:
        j = sel.find(':where(', i)
        if j < 0: out.append(sel[i:]); break
        out.append(sel[i:j]); k = j + 7; depth = 1; m = k
        while m < len(sel) and depth:
            depth += {'(': 1, ')': -1}.get(sel[m], 0); m += 1
        if depth: out.append(sel[j:]); break  # unbalanced: keep as written
        out.append(sel[k:m - 1]); i = m
    return ''.join(out)


def norm_sel(sel):
    """A selector with the flatten rewrites undone: ids as their class twins (IDS), ':where()' unwrapped."""
    s = unwrap_where(sel)
    for k, v in IDS.items():
        s = re.sub(re.escape(k) + r'(?![\w-])', v, s)
    return ' '.join(s.replace('>', ' > ').replace('+', ' + ').replace('~', ' ~ ').split())


def norm_media(ctx):
    """An at-rule prelude with media range syntax mapped back to the legacy min-width / max-width form, so a
    value-identical range rewrite keeps its stable_key: '(a <= width <= b)' and '(width >= a)' / '(width <= b)'."""
    c = re.sub(r'\(\s*([\d.]+(?:px|rem|em))\s*<=\s*width\s*<=\s*([\d.]+(?:px|rem|em))\s*\)', r'(min-width:\1) and (max-width:\2)', ctx)
    c = re.sub(r'\(\s*width\s*>=\s*([\d.]+(?:px|rem|em))\s*\)', r'(min-width:\1)', c)
    c = re.sub(r'\(\s*width\s*<=\s*([\d.]+(?:px|rem|em))\s*\)', r'(max-width:\1)', c)
    c = re.sub(r'\(\s*(min|max)-width\s*:\s*', r'(\1-width:', c)
    return re.sub(r'^@media\s*\(', '@media (', c)


def stable_key(d):
    """Identity that survives a flatten rewrite and a reorder: library, media, normalised selector, property, value.
    !important is not part of the key, so removing it pairs with its baseline and is judged as a change."""
    return (d['lib'], tuple(norm_media(c) for c in d['ctx']), norm_sel(d['sel']), d['p'], d['v'])


def pair_stable(before, after):
    """--stable: attach each after-declaration to its before twin by stable_key, so rules may move within and across
    files of one library. Every Solo declaration is judged (chg=True), because a reorder changes winners without
    changing a selector. Equal keys (exact duplicates) pair in load order. Leftovers pair a second time on the key
    without its value, one to one only: a value edit, counted and not judged (as in positional mode). Returns
    (unpaired declarations, value edits)."""
    def match(bs, as_, kf):
        bk = collections.defaultdict(list); ak = collections.defaultdict(list)
        for d in sorted(bs, key=lambda x: x['o']): bk[kf(d)].append(d)
        for d in sorted(as_, key=lambda x: x['o']): ak[kf(d)].append(d)
        lb = []; la = []; n = 0
        for k in set(bk) | set(ak):
            for d, b in zip(ak[k], bk[k]):
                d['b'] = b; d['chg'] = True; n += 1
            lb += bk[k][len(ak[k]):]; la += ak[k][len(bk[k]):]
        return lb, la, n
    lb, la, _ = match(before, after, stable_key)
    lb, la, vals = match(lb, la, lambda d: stable_key(d)[:-1])
    bad = sorted('%s %s:%d %r {%s: %s}' % (side, x['f'], x['ln'], x['sel'], x['p'], x['v'])
                 for side, xs in (('removed', lb), ('added', la)) for x in xs)
    return bad, vals


def compare(after, ext):
    for d in after:
        d['tk'] = tokens(d['b']['sel'])
    for e in ext:
        if 'b' not in e:
            e['b'] = e; e['chg'] = False; e['tk'] = tokens(e['sel'])
    grp = collections.defaultdict(list)
    for d in after + ext:
        for t in d['tk']: grp[(t, rootp(d['p']))].append(d)
    flips = []; seen = set(); n = 0
    for g in grp.values():
        for a in (d for d in g if d['chg']):
            for b in g:
                if a is b or a['b']['v'] == b['b']['v'] or not conflict(a['p'], b['p']): continue
                if b['chg'] and id(b) < id(a): continue
                if b['lib'] == 'claro' and a['lib'] not in ADMIN_ONLY: continue
                if a['f'] in GEN and b['f'] in GEN:
                    (na, pa), (nb, pb) = GEN[a['f']], GEN[b['f']]
                    if (na == 'menu-mn') == (nb == 'menu-mn') and pa != pb: continue  # one library per family per page
                k = (id(a), id(b))
                if k in seen: continue
                seen.add(k); n += 1
                kb = (key(a['b']), key(b['b'])); ka = (key(a), key(b))
                if kb[0] == kb[1] or ka[0] == ka[1]: continue
                if (kb[0] > kb[1]) != (ka[0] > ka[1]): flips.append((a, b, ka[0] > ka[1]))
    return n, flips


def git_tree(rev, dest):
    data = subprocess.run(['git', '-C', SOLO, 'archive', rev, 'css', 'solo.libraries.yml'], check=True, capture_output=True).stdout
    subprocess.run(['tar', '-x', '-C', dest], input=data, check=True)
    return dest


def run(before_root, after_root, docroot, show, stable=False, util_before=None):
    before = decls_tree(before_root); after = decls_tree(after_root)
    bad, vals = (pair_stable if stable else pair_trees)(before, after)
    after = [d for d in after if 'b' in d]
    ext = external(docroot) + generated(UTIL, util_before)
    if util_before:
        print('generated region rules judged against %s' % util_before)
    n, flips = compare(after, ext)
    changed = sum(d['chg'] for d in after)
    print('before %s | after %s | pairing %s' % (before_root, after_root, 'stable' if stable else 'positional'))
    if stable:
        print('solo decls paired %d (all judged), value edits (not judged) %d, unpaired %d, external %d'
              % (len(after), vals, len(bad), len(ext)))
    else:
        print('solo decls %d, changed selector/important %d, value edits (not judged) %d, external %d'
              % (len(after), changed, vals, len(ext)))
    print('pairs %d, CHANGED WINNERS %d' % (n, len(flips)))
    for f in bad[:show] if stable else bad:
        print(('UNPAIRED: ' if stable else 'CANNOT PAIR (declaration list changed shape): ') + f)
    for a, b, aw in flips[:show]:
        print('  %s:%d %r %s imp=%s  vs  %s:%d %r imp=%s -> now wins: %s'
              % (a['f'], a['ln'], a['sel'], a['p'], a['imp'], b['f'], b['ln'], b['sel'], b['imp'], 'first' if aw else 'second'))
    return 2 if bad else (1 if flips else 0)


def hist(root, listing):
    files = sorted(glob.glob(root + '/css/components/*.css') + glob.glob(root + '/css/base/*.css') +
                   glob.glob(root + '/css/layout/*.css') + glob.glob(root + '/css/breakpoints/solo-*-breakpoints-*.css') +
                   glob.glob(root + '/css/theme/*.css') + glob.glob(root + '/css/predefined/*.css') +
                   glob.glob(root + '/css/w3-css/*.css'))
    per = collections.Counter(); over = collections.Counter(); ids = imps = n = 0
    for f in files:
        part = f.split('/css/')[1].split('/')[0]
        for (ln, ctx, sel, p, v, imp) in parse(open(f, encoding='utf-8').read()):
            n += 1; sp = specificity(sel)
            ids += bool(sp[0]); imps += imp
            if sp > (0, 2, 0):
                over[(sp, sel, part)] += 1; per[part] += 1
    print('files %d, declarations %d, over-0,2,0 %d, with-id %d, important %d' % (len(files), n, sum(over.values()), ids, imps))
    for part in ('base', 'layout', 'components', 'breakpoints', 'theme', 'predefined', 'w3-css'):
        print('  %-11s over-0,2,0 declarations %d' % (part, per[part]))
    if listing:
        for (sp, sel, part), c in sorted(over.items(), key=lambda x: x[0][0], reverse=True): print(sp, c, part, sel)


def main():
    ap = argparse.ArgumentParser(description=__doc__.split('\n')[0])
    ap.add_argument('--base', default='HEAD'); ap.add_argument('--before'); ap.add_argument('--after', default=SOLO)
    ap.add_argument('--docroot', default=DOCROOT); ap.add_argument('--show', type=int, default=20)
    ap.add_argument('--hist', nargs='?', const=SOLO); ap.add_argument('--list', action='store_true')
    ap.add_argument('--stable', action='store_true', help='pair by stable_key instead of file and position')
    ap.add_argument('--util-before', help='baseline ThemeCssBuilder.php: judge the generated region rules against it')
    a = ap.parse_args()
    if a.hist:
        hist(a.hist, a.list); return 0
    if a.before:
        return run(a.before, a.after, a.docroot, a.show, a.stable, a.util_before)
    with tempfile.TemporaryDirectory() as tmp:
        return run(git_tree(a.base, tmp), a.after, a.docroot, a.show, a.stable, a.util_before)


if __name__ == '__main__':
    sys.exit(main())
