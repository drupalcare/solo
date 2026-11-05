#!/usr/bin/env python3
"""Solo cascade contract check: who wins between the CSS sources on a Solo page.

Encodes the ranked list in planning/solo-cascade-contract.md as pair checks.
Static only: reads CSS, libraries.yml and ThemeCssBuilder.php; no site, no DOM.

Winner model (CSS Cascade 5): !important beats normal; for normal declarations
unlayered beats layered, for !important layered beats unlayered; then
specificity; then load order. Load order is Drupal's: group (module CSS 0,
theme CSS 100, LibraryDiscoveryParser.php:162), then category + weight
(common.inc:80-102), then file order, then line (AssetResolver::sort).

Two declarations "meet" when they set the same property and their subjects
share a class or id (aliases below). This is a heuristic: it ignores media
queries and DOM nesting, so a pair may never meet on a real page.

The generated library (region colours), the typed custom CSS file and the typed
inline box are module libraries that solo_utilities moves into their own group
after every theme stylesheet (ThemeLibraryRegistrar::GROUP, via hook_css_alter);
the group and weights are read from that class, not copied here. Typed CSS is
the site owner's own text, so P6 checks it by a twin: for every Solo, scheme and
generated declaration, the same selector, property and importance typed by the
owner must win (contract section 7, step 1). The inline box (inline.<hash>.css)
loads after the typed file, so its twin must also beat the typed file's twin.
Region selectors are classes (R1); P7 fails on an id or on a form above 0,2,0
that is not in ABOVE_020.

Usage: check-cascade.py [--solo DIR] [--lane DIR] [--list]
Exit 0: every pair holds or is a recorded exception. Exit 1: a winner flipped.
"""
import argparse, collections, os, re, sys

HERE = os.path.dirname(os.path.abspath(__file__))
CAT = {'base': -200, 'layout': -100, 'component': 0, 'state': 100, 'theme': 200}
ADMIN_LIBS = {'solo-form-theme-settings', 'solo-node-edit-form', 'solo-debug'}
ADMIN_FILES = ('css/theme/solo-ckeditor', 'css/debug/', 'css/components/solo-layout-builder.css')
SCHEME_FILES = ('css/predefined/', 'css/theme/solo-scheme-')
COMPONENT_FOLDERS = ('css/base/', 'css/layout/', 'css/components/', 'css/w3-css/')
# Custom properties and the projects allowed to set them (one owner each;
# Solo is the declared adapter for selectify's tokens).
OWNERS = {'--r-': {'solo', 'generated'}, '--vvj-': {'vvj'}, '--pb-': {'paragraphs_bundles'},
          '--selectify-': {'selectify', 'solo'}, '--uk-': {'utilikit'}, '--w3-': {'w3css_paragraphs'}}
VVJ = ['vvj_core', 'vvja', 'vvjb', 'vvjc', 'vvjf', 'vvjh', 'vvjl', 'vvjp', 'vvjr', 'vvjs', 'vvjt']
LANE = VVJ + ['paragraphs_bundles', 'selectify', 'w3css_paragraphs', 'utilikit']

# Pairs whose intended winner loses TODAY. Recorded so the check pins today's
# state and goes red on a NEW loser; each one is listed in the contract.
# {system_messages} is filled from ThemeCssBuilder::REGION_SELECTORS at run time,
# so the generated selector is never copied here.
EXCEPTION_TEMPLATES = {
    # Owner call: the "dark system messages" option forces its colours with
    # !important, so it beats the region colours set for system_messages.
    'generated:system_messages:12 {system_messages} {{--r-bg}} | css/components/solo-system-messages-dark.css:14 :where(.solo-page) .solo-system-messages {{--r-bg}}',
    'generated:system_messages:12 {system_messages} {{--r-tx}} | css/components/solo-system-messages-dark.css:14 :where(.solo-page) .solo-system-messages {{--r-tx}}',
}
EXCEPTIONS = {
    # The control border base rule (0,3,0) outranks the scheme rule (0,2,0).
    'css/theme/solo-scheme-control-border.css:10 .solo-page .solo-check {border-color} | css/base/solo-variables-regions.css:63 .solo-page .solo-check:not(.selectify-checkbox) {border-color}',
    'css/theme/solo-scheme-control-border.css:10 .solo-page .solo-radio {border-color} | css/base/solo-variables-regions.css:63 .solo-page .solo-radio:not(.selectify-radio) {border-color}',
    'css/theme/solo-scheme-control-border.css:10 .solo-page .solo-select {border-color} | css/base/solo-variables-regions.css:63 .solo-page .solo-select:not(.selectify-select) {border-color}',
}

# ---------------------------------------------------------------- parser
def strip_comments(t):
    return re.sub(r'/\*.*?\*/', lambda m: '\n' * m.group(0).count('\n'), t, flags=re.S)

def split_top(s, ch):
    parts, depth, cur = [], 0, []
    for c in s:
        if c in '([': depth += 1
        elif c in ')]': depth -= 1
        if c == ch and depth == 0: parts.append(''.join(cur)); cur = []
        else: cur.append(c)
    parts.append(''.join(cur)); return parts

OPAQUE = ('@keyframes', '@-webkit-keyframes', '@font-face', '@page', '@property', '@counter-style')

def parse(text):
    """Yield (line, at-rule chain, selector, property, value, important)."""
    t = strip_comments(text); out = []; stack = []; buf = []; line = 1; paren = 0; q = None
    for i, c in enumerate(t):
        if c == '\n': line += 1
        if q:
            buf.append(c)
            if c == q: q = None
            continue
        if c in '"\'': q = c; buf.append(c); continue
        if c == '(': paren += 1
        elif c == ')': paren -= 1
        if c == '{' and paren == 0:
            pre = ' '.join(''.join(buf).split()); buf = []
            if stack and stack[-1][0] == 'opaque' or pre.lower().startswith(OPAQUE): stack.append(('opaque',))
            elif pre.startswith('@'): stack.append(('at', pre))
            else: stack.append(('rule', pre, line))
        elif c == '}' and paren == 0:
            body = ''.join(buf); buf = []
            top = stack.pop() if stack else ('none',)
            if top[0] == 'rule':
                ctx = tuple(s[1] for s in stack if s[0] == 'at')
                for d in split_top(body, ';'):
                    if ':' not in d: continue
                    p, v = d.split(':', 1); p = p.strip().lower(); v = ' '.join(v.split())
                    imp = bool(re.search(r'!\s*important\s*$', v, re.I))
                    v = re.sub(r'\s*!\s*important\s*$', '', v, flags=re.I)
                    for s in split_top(top[1], ','):
                        out.append((top[2], ctx, ' '.join(s.split()), p, v, imp))
        elif c == ';' and paren == 0 and not (stack and stack[-1][0] == 'rule'):
            buf = []
        else:
            buf.append(c)
    return out

def specificity(sel):
    s = re.sub(r':where\((?:[^()]|\([^()]*\))*\)', '', sel); extra = [0, 0, 0]
    def mx(m):
        best = max(specificity(a) for a in split_top(m.group(2), ','))
        return '\x00%d,%d,%d\x00' % best
    while True:
        s2 = re.sub(r':(is|not|has|matches)\(((?:[^()]|\([^()]*\))*)\)', mx, s)
        if s2 == s: break
        s = s2
    for m in re.finditer(r'\x00(\d+),(\d+),(\d+)\x00', s):
        for k in range(3): extra[k] += int(m.group(k + 1))
    s = re.sub(r'\x00[\d,]+\x00', '', s)
    s = re.sub(r'\[[^\]]*\]', ' .x ', s)
    a = len(re.findall(r'#[\w-]+', s))
    pe = len(re.findall(r'::[\w-]+|:(?:before|after|first-line|first-letter)\b', s))
    s = re.sub(r'::[\w-]+(\([^)]*\))?|:(?:before|after|first-line|first-letter)\b', ' ', s)
    b = len(re.findall(r'\.[\w-]+', s)) + len(re.findall(r':[\w-]+', s))
    s = re.sub(r'#[\w-]+|\.[\w-]+|:[\w-]+(\([^)]*\))?', ' ', s)
    c = len(re.findall(r'(?<![\w-])[a-zA-Z][\w-]*', s)) + pe
    return (a + extra[0], b + extra[1], c + extra[2])

def subject(sel):
    last = re.split(r'\s*[>+~]\s*|\s+', sel.strip())[-1]
    last = re.sub(r'::?[\w-]+(\((?:[^()]|\([^()]*\))*\))?', '', last)
    toks = set(re.findall(r'[.#][\w-]+', last))
    # Alias an id with its class twin (#solo-header ~ .solo-header) and a
    # region box modifier with its block (needs checking against templates).
    for t in list(toks):
        if t.startswith('#'): toks.add('.' + t[1:])
        m = re.match(r'(\.solo-region-box__inner)--', t)
        if m: toks.add(m.group(1))
    if '#solo-page-wrapper' in toks: toks.add('.solo-page')
    return toks

# ---------------------------------------------------------------- sources
def libraries(path):
    """file -> (category weight, file index, library name) from a libraries.yml."""
    o = {}; lib = cat = None
    for i, ln in enumerate(open(path, encoding='utf-8')):
        m = re.match(r'^([\w.-]+):\s*$', ln)
        if m: lib = m.group(1); cat = None; continue
        m = re.match(r'^    (base|layout|component|state|theme):\s*$', ln)
        if m: cat = m.group(1); continue
        m = re.match(r'^      ([\w./-]+\.css):\s*\{(.*)\}', ln)
        if m and cat:
            w = re.search(r'weight:\s*(-?\d+)', m.group(2))
            o.setdefault(m.group(1), (CAT[cat] + (int(w.group(1)) if w else 0), i, lib))
    return o

def decl(src, f, ln, ctx, sel, p, v, imp, group, w, idx, lib):
    lay = [c for c in ctx if c.startswith('@layer ')]
    return dict(src=src, f=f, ln=ln, sel=sel, p=p, v=v, imp=imp, layered=bool(lay),
                media=tuple(c for c in ctx if not c.startswith('@layer ')),
                sp=specificity(sel), o=(group, w, idx, ln), sj=subject(sel), lib=lib)

def solo_decls(solo):
    out = []
    for f, (w, idx, lib) in libraries(os.path.join(solo, 'solo.libraries.yml')).items():
        if lib in ADMIN_LIBS or f.startswith(ADMIN_FILES): continue
        fp = os.path.join(solo, f)
        if not os.path.exists(fp): continue
        kind = 'scheme' if f.startswith(SCHEME_FILES) else 'component' if f.startswith(COMPONENT_FOLDERS) else 'solo'
        for r in parse(open(fp, encoding='utf-8').read()):
            out.append(dict(decl(kind, f, *r, 100, w, idx, lib)))
    return out

def registrar_order(lane):
    """(group, settings, custom file, inline box weights) from ThemeLibraryRegistrar.php."""
    reglib = open(os.path.join(lane, 'solo_utilities/src/Css/ThemeLibraryRegistrar.php'), encoding='utf-8').read()
    num = lambda name: int(re.search(name + r' = (-?\d+);', reglib).group(1))
    # The library lists all three files under the theme category.
    return (num('GROUP'), num('SETTINGS_WEIGHT') + CAT['theme'], num('CUSTOM_WEIGHT') + CAT['theme'],
            num('INLINE_WEIGHT') + CAT['theme'])

def region_selectors(lane):
    """(region, selector) pairs and colour properties, read from ThemeCssBuilder.php (the source of truth)."""
    php = open(os.path.join(lane, 'solo_utilities/src/Css/ThemeCssBuilder.php'), encoding='utf-8').read()
    reg = re.search(r'REGION_SELECTORS = \[(.*?)\];', php, re.S).group(1)
    props = re.search(r'COLOR_PROPERTIES = \[(.*?)\];', php, re.S).group(1)
    return re.findall(r"'([\w]+)' => '([^']+)'", reg), re.findall(r"'[\w]+' => '(--[\w-]+)'", props)

def generated_decls(lane, group, w):
    """Region colour rules ThemeCssBuilder writes when every colour is set."""
    reg, props = region_selectors(lane)
    return [decl('generated', 'generated:' + name, n, (), sel, p, '#123456', False, group, w, 0, 'solo_utilities')
            for n, (name, sel) in enumerate(reg) for p in props]

# R1: region selectors carry no id, and stay at or below 0,2,0 except where a
# named rival rule on the same element is stronger (owner contract section 7).
ABOVE_020 = {
    # 50 schemes: div.solo-page-title.solo-container--single (0,2,1);
    # solo-denim-twilight.css: .solo-page div.solo-page-title.solo-container--single (0,3,1).
    'page_title',
}

def region_form_check(lane):
    reg, _ = region_selectors(lane); bad = []
    for name, sel in reg:
        if '#' in sel: bad.append('%s %s carries an id' % (name, sel))
        if specificity(sel) > (0, 2, 0) and name not in ABOVE_020:
            bad.append('%s %s is above 0,2,0 with no recorded rival' % (name, sel))
    return 'P7 region selectors: class only, <= 0,2,0', len(reg) - len(bad), bad

def typed_check(name, targets, group, w, label='typed twin'):
    """Typed CSS twin of each target (same selector, property, importance,
    unlayered, loaded at weight w of the generated group) must win: the owner's
    last word."""
    held = 0; lost = []
    for d in targets:
        t = dict(d, src='typed', layered=False, o=(group, w, 0, d['ln']))
        if rank(t) > rank(d): held += 1
        else: lost.append('%s | %s' % (label, ident(d)))
    return name, held, sorted(set(lost))

def lane_decls(lane, project):
    out = []; root = os.path.join(lane, project)
    order = {}
    for d, ds, fs in os.walk(root, followlinks=True):
        ds[:] = [x for x in ds if x not in ('tests', 'node_modules', '.git', 'vendor')]
        for fn in fs:
            if fn.endswith('.libraries.yml'):
                base = os.path.relpath(d, root)
                for f, v in libraries(os.path.join(d, fn)).items():
                    order[os.path.normpath(os.path.join(base, f))] = v
    for f, (w, idx, lib) in order.items():
        fp = os.path.join(root, f)
        if not os.path.exists(fp): continue
        for r in parse(open(fp, encoding='utf-8', errors='replace').read()):
            out.append(decl(project, project + '/' + f, *r, 0, w, idx, lib))
    return out

# ---------------------------------------------------------------- cascade
def rank(d):
    lay = 0 if d['layered'] else 1
    return (1, 1 - lay, d['sp'], d['o']) if d['imp'] else (0, lay, d['sp'], d['o'])

def meet(a, b):
    return a['p'] == b['p'] and a['sj'] & b['sj'] and a['v'] != b['v']

def ident(d):
    return '%s:%s %s {%s}' % (d['f'], d['ln'], d['sel'], d['p'])

def pair_check(name, winners, losers, prop_filter=None):
    by = collections.defaultdict(list)
    for b in losers:
        if prop_filter and not b['p'].startswith(prop_filter): continue
        for t in b['sj']: by[(b['p'], t)].append(b)
    held = 0; lost = []; seen = set()
    for a in winners:
        if prop_filter and not a['p'].startswith(prop_filter): continue
        for t in a['sj']:
            for b in by.get((a['p'], t), ()):
                k = (id(a), id(b))
                if k in seen or not meet(a, b): continue
                seen.add(k)
                if rank(a) > rank(b): held += 1
                else: lost.append('%s | %s' % (ident(a), ident(b)))
    return name, held, sorted(set(lost))

def ownership(groups):
    bad = []
    for owner, ds in groups.items():
        for d in ds:
            for ns, allowed in OWNERS.items():
                if d['p'].startswith(ns) and owner not in allowed:
                    bad.append('%s sets %s owned by %s' % (ident(d), ns, '/'.join(sorted(allowed))))
    return 'P5 one owner per token namespace', 0, sorted(set(bad))

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--solo', default=os.path.dirname(HERE))
    ap.add_argument('--lane', default=os.path.dirname(os.path.dirname(HERE)))
    ap.add_argument('--list', action='store_true', help='print every losing pair')
    a = ap.parse_args()
    group, settings_w, custom_w, inline_w = registrar_order(a.lane)
    EXCEPTIONS.update(x.format(**dict(region_selectors(a.lane)[0])) for x in EXCEPTION_TEMPLATES)
    solo = solo_decls(a.solo); gen = generated_decls(a.lane, group, settings_w)
    lane = {p: lane_decls(a.lane, p) for p in LANE if os.path.isdir(os.path.join(a.lane, p))}
    schemes = [d for d in solo if d['src'] == 'scheme']
    comps = [d for d in solo if d['src'] == 'component']
    # Typed file twins, as P6a-c build them: the inline box must also beat these.
    typed_file = [dict(d, src='typed', layered=False, o=(group, custom_w, 0, d['ln']))
                  for d in solo + gen]
    vvj_layered = [d for p in VVJ for d in lane.get(p, []) if d['layered']]
    checks = [
        pair_check('P1 region colours beat schemes', gen, schemes, '--r-'),
        pair_check('P2 region colours beat other Solo CSS', gen, [d for d in solo if d['src'] != 'scheme'], '--r-'),
        pair_check('P3 schemes beat Solo components', schemes, comps),
        pair_check('P4 Solo beats vvj.* layers', solo, vvj_layered),
        ownership(dict([('solo', solo), ('generated', gen)] + [('vvj' if p in VVJ else p, lane[p]) for p in lane])),
        typed_check('P6a typed CSS beats Solo theme CSS', [d for d in solo if d['src'] != 'scheme'], group, custom_w),
        typed_check('P6b typed CSS beats schemes', schemes, group, custom_w),
        typed_check('P6c typed CSS beats generated', gen, group, custom_w),
        typed_check('P6d inline box beats typed file, Solo, schemes, generated',
                    solo + gen + typed_file, group, inline_w, 'inline twin'),
        region_form_check(a.lane),
    ]
    print('sources: solo %d decls (admin excluded), generated %d, lane %s' % (
        len(solo), len(gen), {p: len(v) for p, v in lane.items()}))
    status = 0
    for name, held, lost in checks:
        new = [x for x in lost if x not in EXCEPTIONS]
        print('%-42s held %6d  losing %5d  new %d' % (name, held, len(lost), len(new)))
        if a.list:
            for x in lost: print('    ' + x)
        if new: status = 1
    gone = EXCEPTIONS - {x for _, _, l in checks for x in l}
    if gone: print('recorded exceptions no longer losing (remove them):', len(gone))
    return status

if __name__ == '__main__':
    sys.exit(main())
