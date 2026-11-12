#!/usr/bin/env python3
"""The complete Solo library set, for the checkers: static yml plus the generated libraries.

Solo's libraries are no longer all written in solo.libraries.yml. Only solo-global and solo-menu are;
the rest are built at runtime by solo_utilities from shipped data. This module is the Python twin of
that build, so the checkers see the same set Drupal does:

  solo.libraries.yml                  the static libraries (parsed with a small reader, stdlib only)
  fonts/solo-fonts.json               "fonts" (font-<slug>-external, font-<slug>-local) and "icons" (Font Awesome)
  schemes/*.yml                       one library per predefined color scheme (scheme-<name>)
  scripts/breakpoints/breakpoints.json  breakpoint-page-sb-*, breakpoint-menu-mn-*
  libraries/solo-components.json      "libraries" (components, features) and "globs" (file patterns)

PHP twin: solo_utilities/src/Asset/SoloLibraryDefinitions.php. Same sources, same validation, same
defaults (CSS_DIRECTORIES below mirrors the PHP constant of the same name). Change one, change the
other; `--dump` prints the normalized set so the two can be compared.

Renamed libraries: the previous id of every renamed library stays one major version as a deprecated
alias (a library with "deprecated" and "dependencies" on the new id), built from the same data as the
new library, never typed twice: a component record carries "was", a glob rule carries "prefix" and
"strip", and fonts, schemes and breakpoints derive the previous id from the same slug or width. The
only aliases written by hand are the three libraries in solo.libraries.yml. The set is ordered: static
libraries first, then fonts, icons, schemes, breakpoints, components, globs. A static library wins
over a built one of the same id, key by key (the PHP merge is `$static + $built`).

Usage (python3 -I):
  solo_libraries.py --dump [--theme DIR] [--static-only | --built-only]
Exit 0 and the JSON on stdout; exit 1 and the problems on stderr when a source is malformed.
"""
import argparse
import glob
import json
import os
import posixpath
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))

# PHP twin: SoloLibraryDefinitions::FONT_CATEGORIES, SCHEME_WEIGHT, CSS_DIRECTORIES, CSS_CATEGORIES, META_KEYS.
FONT_CATEGORIES = {'global': ('global', -200), 'heading': ('head', -150), 'special': ('special', -100), 'site-name': ('site-name', -50)}
SCHEME_WEIGHT = -9
CSS_DIRECTORIES = {
    'css/base': ('base', -200),
    'css/layout': ('layout', 200),
    'css/components': ('component', 0),
    'css/theme': ('theme', 200),
    'css/debug': ('theme', None),
}
CSS_CATEGORIES = ('base', 'layout', 'component', 'state', 'theme')
META_KEYS = ('remote', 'version', 'Version', 'license', 'header')
# PHP twin: SoloLibraryDefinitions::EXTENSION, ALIAS_MESSAGE.
EXTENSION = 'solo'
ALIAS_MESSAGE = 'The %library_id% asset library is deprecated in solo 2.0.0 and is removed from solo 3.0.0. Use {use} instead.'

RE_SLUG = r'[a-z0-9]+(?:-[a-z0-9]+)*'
RE_ID = r'[a-z0-9]+(?:[-_][a-z0-9]+)*'
RE_QUERY = r'[A-Za-z0-9+:;,@._-]+'
RE_DEP = r'[a-z0-9_]+/[A-Za-z0-9_.-]+'
RE_REL = r'[A-Za-z0-9_-]+(?:/[A-Za-z0-9_.-]+)*'
RE_HTTPS = r'https://[A-Za-z0-9._~:/?#@!$&=+%;,-]+'
RE_HTTPS_META = r'https://[A-Za-z0-9._~:/?#@!$&=+%-]+'
RE_PUBLIC = r'public://[A-Za-z0-9_-]+(?:/[A-Za-z0-9_.-]+)*'
RE_GLOB = r'css/[A-Za-z0-9_-]+(?:/[A-Za-z0-9_*.-]+)*'


def font_library(slug, local):
    """PHP twin: SoloLibraryDefinitions::fontLibrary()."""
    return 'font-%s-%s' % (slug, 'local' if local else 'external')


def scheme_library(scheme_id):
    """PHP twin: SoloLibraryDefinitions::schemeLibrary(). The stored scheme id keeps its solo- prefix."""
    return 'scheme-' + (scheme_id[5:] if scheme_id.startswith('solo-') else scheme_id)


def breakpoint_library(family, value):
    """PHP twin: SoloLibraryDefinitions::breakpointLibrary(); family is page or menu, value is sb-576 or mn-576."""
    return 'breakpoint-%s-%s' % (family, value)


def alias_definition(new_ids):
    """The deprecated alias of a renamed library: it only depends on the new id(s)."""
    use = ' and '.join('%s/%s' % (EXTENSION, n) for n in new_ids)
    return {
        'deprecated': ALIAS_MESSAGE.replace('{use}', use),
        'dependencies': ['%s/%s' % (EXTENSION, n) for n in new_ids],
    }


def add_alias(aliases, old, new):
    if old != new and new not in aliases.setdefault(old, []):
        aliases[old].append(new)


def m(pattern, value):
    return isinstance(value, str) and re.fullmatch(pattern, value) is not None


def is_int(v):
    return type(v) is int


def is_list(v):
    """PHP array_is_list(): a list, or an empty JSON object (an empty PHP array)."""
    return isinstance(v, list) or (isinstance(v, dict) and not v)


def is_map(v):
    """A non-empty PHP array that is not a list."""
    return isinstance(v, dict) and bool(v)


# ---------------------------------------------------------------- static yml
def _scalar(s):
    s = s.strip()
    if len(s) >= 2 and s[0] == s[-1] and s[0] in '\'"':
        return s[1:-1]
    if s in ('true', 'false'):
        return s == 'true'
    if re.fullmatch(r'-?\d+', s):
        return int(s)
    if re.fullmatch(r'-?\d+\.\d+', s):
        return float(s)
    return s


def _flow(s, i=0):
    """Parse '{ k: v, k: { ... } }' from s[i]; returns (dict, next index)."""
    assert s[i] == '{'
    i += 1
    out = {}
    while True:
        while s[i] in ' ,':
            i += 1
        if s[i] == '}':
            return out, i + 1
        if s[i] in '\'"':
            j = s.index(s[i], i + 1)
            key = s[i + 1:j]
            i = j + 1
        else:
            j = i
            while s[j] != ':':
                j += 1
            key = s[i:j].strip()
            i = j
        while s[i] in ': ':
            i += 1
        if s[i] == '{':
            out[key], i = _flow(s, i)
        else:
            j = i
            while s[j] not in ',}':
                j += 1
            out[key] = _scalar(s[i:j])
            i = j


def _split_key(text):
    """'key: rest' or 'key:' -> (key, rest); a quoted key may hold colons, a URL key has no ': '."""
    if text[0] in '\'"':
        j = text.index(text[0], 1)
        key, rest = text[1:j], text[j + 1:]
        return key, rest[1:].strip() if rest.startswith(':') else rest.strip()
    mm = re.match(r'(.*?):(?:\s+(.*))?$', text)
    return mm.group(1), (mm.group(2) or '').strip()


def parse_yml(text):
    """The subset of YAML solo.libraries.yml uses: nested mappings, '- ' lists, inline { } maps, comments."""
    lines = []
    for raw in text.splitlines():
        if raw.strip() and not raw.lstrip().startswith('#'):
            lines.append((len(raw) - len(raw.lstrip()), raw.strip()))

    def block(i, indent):
        if lines[i][1].startswith('- '):
            out = []
            while i < len(lines) and lines[i][0] == indent and lines[i][1].startswith('- '):
                out.append(_scalar(lines[i][1][2:]))
                i += 1
            return out, i
        out = {}
        while i < len(lines) and lines[i][0] == indent:
            key, rest = _split_key(lines[i][1])
            i += 1
            if rest.startswith('{'):
                out[key] = _flow(rest)[0]
            elif rest:
                out[key] = _scalar(rest)
            elif i < len(lines) and lines[i][0] > indent:
                out[key], i = block(i, lines[i][0])
            else:
                out[key] = None
        return out, i

    return block(0, 0)[0] if lines else {}


def static_libraries(path):
    """The libraries written in a *.libraries.yml, normalized (a bare file entry has empty options)."""
    with open(path, encoding='utf-8') as fh:
        libs = parse_yml(fh.read())
    for lib in libs.values():
        for files in (lib.get('css') or {}).values():
            for f in files:
                files[f] = files[f] or {}
        for f in lib.get('js') or {}:
            lib['js'][f] = lib['js'][f] or {}
    return libs


# ---------------------------------------------------------------- built libraries (the PHP twin)
def _read_json(path, label, problems):
    try:
        with open(path, encoding='utf-8') as fh:
            data = json.load(fh)
    except OSError:
        problems.append('%s is missing or unreadable.' % label)
        return {}
    except ValueError:
        problems.append('%s is not valid JSON.' % label)
        return {}
    if not isinstance(data, (dict, list)):
        problems.append('%s is not valid JSON.' % label)
        return {}
    return data


def _is_rel(p):
    return m(RE_REL, p) and '..' not in p


def _is_asset(p):
    return '..' not in p and (_is_rel(p) or m(RE_HTTPS, p) or m(RE_PUBLIC, p))


def _fonts(rows, problems, aliases):
    if not isinstance(rows, list):
        problems.append('fonts/solo-fonts.json has no "fonts" list.')
        return {}
    libs = {}
    for index, row in enumerate(rows):
        g = row.get if isinstance(row, dict) else (lambda k: None)
        slug, category, query, local = g('slug'), g('category'), g('query'), g('local')
        if (not m(RE_SLUG, slug) or not isinstance(category, str) or category not in FONT_CATEGORIES
                or not m(RE_QUERY, query) or not isinstance(local, bool)):
            problems.append('fonts/solo-fonts.json entry %s is malformed and was skipped.' % index)
            continue
        directory, weight = FONT_CATEGORIES[category]
        sheet = 'fonts/css/%s/solo-%s.css' % (directory, slug)
        url = 'https://fonts.googleapis.com/css2?family=%s&display=swap' % query
        libs[font_library(slug, False)] = {'css': {'base': {
            url: {'type': 'external', 'minified': True, 'weight': weight},
            sheet: {'weight': weight},
        }}}
        add_alias(aliases, 'solo-gfex-' + slug, font_library(slug, False))
        if local:
            libs[font_library(slug, True)] = {'css': {'base': {
                'fonts/font-face/google-font-%s.css' % slug: {'weight': weight},
                sheet: {'weight': weight},
            }}}
            add_alias(aliases, 'solo-gfin-' + slug, font_library(slug, True))
    return libs


def _scheme_record(path):
    """id, css and dependencies of a scheme record: its top-level keys only (a full YAML read is not needed)."""
    rec = {}
    key = None
    with open(path, encoding='utf-8') as fh:
        for line in fh:
            line = line.rstrip('\n')
            if not line.strip() or line.lstrip().startswith('#'):
                continue
            if not line.startswith(' '):
                mm = re.match(r'([\w-]+):\s*(.*)$', line)
                key = mm.group(1) if mm else None
                if mm and mm.group(2):
                    rec[key] = [] if mm.group(2) == '[]' else _scalar(mm.group(2))
                elif mm:
                    rec[key] = []
            elif key and re.match(r'\s+- ', line) and isinstance(rec.get(key), list):
                rec[key].append(_scalar(line.strip()[2:]))
    return rec


def _schemes(theme, problems, aliases):
    libs = {}
    for path in sorted(glob.glob(os.path.join(theme, 'schemes', '*.yml'))):
        rec = _scheme_record(path)
        id_, css, deps = rec.get('id'), rec.get('css'), rec.get('dependencies', [])
        if (not m(RE_SLUG, id_) or not (isinstance(css, str) and _is_rel(css))
                or not isinstance(deps, list) or any(not m(RE_DEP, d) for d in deps)):
            problems.append('schemes/%s is malformed and was skipped.' % os.path.basename(path))
            continue
        lib = {}
        if deps:
            lib['dependencies'] = deps
        lib['css'] = {'theme': {css: {'weight': SCHEME_WEIGHT}}}
        libs[scheme_library(id_)] = lib
        add_alias(aliases, id_, scheme_library(id_))
    return libs


def _breakpoints(theme, problems, aliases):
    widths = _read_json(os.path.join(theme, 'scripts/breakpoints/breakpoints.json'), 'scripts/breakpoints/breakpoints.json', problems)
    if not is_list(widths):
        problems.append('scripts/breakpoints/breakpoints.json is not a JSON list.')
        return {}
    libs = {}
    for index, w in enumerate(widths):
        if not is_int(w) or w < 1:
            problems.append('scripts/breakpoints/breakpoints.json entry %s is malformed and was skipped.' % index)
            continue
        libs[breakpoint_library('page', 'sb-%d' % w)] = {'css': {
            'layout': {'css/layout/solo-page-sb-%d.css' % w: {'weight': -100}},
            'theme': {'css/breakpoints/solo-page-sb-%d.css' % w: {'weight': 200}},
        }}
        add_alias(aliases, 'solo-global-breakpoints-sb-%d' % w, breakpoint_library('page', 'sb-%d' % w))
        libs[breakpoint_library('menu', 'mn-%d' % w)] = {'css': {
            'theme': {'css/breakpoints/solo-menu-mn-%d.css' % w: {'weight': 200}},
        }}
        add_alias(aliases, 'solo-menu-breakpoints-mn-%d' % w, breakpoint_library('menu', 'mn-%d' % w))
    return libs


def _meta(meta):
    if not is_map(meta) or set(meta) - set(META_KEYS):
        return None
    for key, v in meta.items():
        if key == 'remote':
            ok = m(RE_HTTPS_META, v)
        elif key in ('version', 'Version'):
            ok = m(r'[A-Za-z0-9._-]+', v) or is_int(v) or type(v) is float
        elif key == 'header':
            ok = isinstance(v, bool)
        else:
            ok = (is_map(v) and not set(v) - {'name', 'url', 'gpl-compatible'}
                  and m(r'[A-Za-z0-9. -]+', v.get('name')) and m(RE_HTTPS_META, v.get('url'))
                  and isinstance(v.get('gpl-compatible'), bool))
        if not ok:
            return None
    return dict(meta)


def _file_entry(entry, css):
    if isinstance(entry, str):
        entry = {'path': entry}
    path = entry.get('path') if isinstance(entry, dict) else None
    if not isinstance(path, str) or not _is_asset(path):
        return None
    allowed = {'path', 'weight', 'type', 'minified', 'preprocess', 'attributes'}
    if css:
        allowed.add('category')
    if set(entry) - allowed:
        return None
    category, weight = CSS_DIRECTORIES.get(posixpath.dirname(path), ('base', None)) if css else ('', None)
    if entry.get('category') is not None:
        if entry['category'] not in CSS_CATEGORIES:
            return None
        category = entry['category']
    options = {}
    if 'weight' not in entry and weight is not None:
        options['weight'] = weight
    for key, v in entry.items():
        if key in ('path', 'category'):
            continue
        if key == 'weight':
            ok = v is None or is_int(v)
        elif key == 'type':
            ok = v in ('external', 'file')
        elif key in ('minified', 'preprocess'):
            ok = isinstance(v, bool)
        else:
            # Flags (True) or plain strings such as an integrity hash and crossorigin (Font Awesome CDN entry).
            ok = is_map(v) and all(m(r'[a-z-]+', k) and (isinstance(x, bool) or (isinstance(x, str) and m(r'[A-Za-z0-9+/=._:-]+', x)))
                                   for k, x in v.items())
        if not ok:
            return None
        if key == 'weight' and v is None:
            continue
        options[key] = v
    return path, category, options


def _files(entries, css):
    if not isinstance(entries, list) or not entries:
        return None
    out = {}
    for entry in entries:
        f = _file_entry(entry, css)
        if f is None:
            return None
        path, category, options = f
        target = out.setdefault(category, {}) if css else out
        if path in target:
            return None
        target[path] = options
    return out


def _record(record):
    lib = {}
    if record.get('meta') is not None:
        meta = _meta(record['meta'])
        if meta is None:
            return None
        lib = meta
    for key, css in (('css', True), ('js', False)):
        if record.get(key) is not None:
            files = _files(record[key], css)
            if files is None:
                return None
            lib[key] = files
    if record.get('dependencies') is not None:
        d = record['dependencies']
        if not (isinstance(d, list) and d and all(m(RE_DEP, x) for x in d)):
            return None
        lib['dependencies'] = d
    if set(record) - {'id', 'was', 'meta', 'css', 'js', 'dependencies'} or not (set(lib) & {'css', 'js', 'dependencies'}):
        return None
    return lib


def _records(records, label, problems, aliases):
    if not isinstance(records, list):
        problems.append('%s has no list.' % label)
        return {}
    libs = {}
    for index, record in enumerate(records):
        id_ = record.get('id') if isinstance(record, dict) else None
        if not m(RE_ID, id_):
            problems.append('%s entry %s has no valid id and was skipped.' % (label, index))
            continue
        lib = _record(record)
        if lib is None:
            problems.append('%s entry %s (%s) is malformed and was skipped.' % (label, index, id_))
            continue
        if id_ in libs:
            problems.append('%s defines %s twice; the second was skipped.' % (label, id_))
            continue
        # PHP twin: SoloLibraryDefinitions::previousIds(). One previous id, or a non-empty list when
        # several old libraries merged into this one.
        raw = record.get('was')
        was = [] if raw is None else [raw] if isinstance(raw, str) else raw
        if not isinstance(was, list) or (raw is not None and not was) or not all(m(RE_ID, w) for w in was):
            problems.append('%s entry %s (%s) has an invalid "was" and was skipped.' % (label, index, id_))
            continue
        libs[id_] = lib
        for old in was:
            add_alias(aliases, old, id_)
    return libs


def _globs(theme, rules, problems, aliases):
    if not isinstance(rules, list):
        problems.append('libraries/solo-components.json has no "globs" list.')
        return {}
    libs = {}
    for index, rule in enumerate(rules):
        g = rule.get if isinstance(rule, dict) else (lambda k, d=None: None)
        id_, pattern, category, weight = g('id'), g('pattern'), g('category'), g('weight')
        prefix, strip = g('prefix') or '', g('strip') or ''
        first = g('first', {}) if isinstance(rule, dict) else None
        meta = _meta(rule['meta']) if isinstance(rule, dict) and rule.get('meta') is not None else {}
        if (not m(RE_GLOB, pattern) or '..' in pattern
                or category not in CSS_CATEGORIES or not is_int(weight)
                or (id_ is not None and not m(RE_ID, id_))
                or not isinstance(first, dict) or any(not is_int(w) for w in first.values())
                or not isinstance(prefix, str) or not (prefix == '' or m(r'[a-z0-9-]+', prefix))
                or not isinstance(strip, str) or not (strip == '' or m(r'[a-z0-9-]+', strip))
                or meta is None):
            problems.append('libraries/solo-components.json globs entry %s is malformed and was skipped.' % index)
            continue
        paths = sorted(os.path.relpath(f, theme) for f in glob.glob(os.path.join(theme, pattern)))
        if not paths:
            problems.append('libraries/solo-components.json globs entry %s matched no file.' % index)
            continue
        if id_ is None:
            for path in paths:
                name = posixpath.basename(path)
                name = name[:-4] if name.endswith('.css') else name
                if not m(RE_ID, name):
                    problems.append('%s is not a valid library name and was skipped.' % path)
                    continue
                new = prefix + (name[len(strip):] if strip and name.startswith(strip) else name)
                if not m(RE_ID, new):
                    problems.append('%s is not a valid library name and was skipped.' % path)
                    continue
                libs[new] = {'css': {category: {path: {'weight': weight}}}}
                add_alias(aliases, name, new)
            continue
        ordered = []
        for path in first:
            if path in paths:
                ordered.append(path)
            else:
                problems.append('%s is listed first in globs entry %s but does not exist.' % (path, index))
        ordered += [p for p in paths if p not in ordered]
        libs[id_] = dict(meta, css={category: {p: {'weight': first.get(p, weight)} for p in ordered}})
    return libs


def built_available(theme):
    """True when the tree has the generated-library data (an older revision has the full yml only)."""
    return os.path.isfile(os.path.join(theme, 'libraries', 'solo-components.json'))


def built_libraries(theme, problems):
    fonts = _read_json(os.path.join(theme, 'fonts/solo-fonts.json'), 'fonts/solo-fonts.json', problems)
    comps = _read_json(os.path.join(theme, 'libraries/solo-components.json'), 'libraries/solo-components.json', problems)
    fonts = fonts if isinstance(fonts, dict) else {}
    comps = comps if isinstance(comps, dict) else {}
    libs = {}
    aliases = {}
    for part in (
        _fonts(fonts.get('fonts'), problems, aliases),
        _records(fonts.get('icons'), 'fonts/solo-fonts.json icons', problems, aliases),
        _schemes(theme, problems, aliases),
        _breakpoints(theme, problems, aliases),
        _records(comps.get('libraries'), 'libraries/solo-components.json libraries', problems, aliases),
        _globs(theme, comps.get('globs'), problems, aliases),
    ):
        for name, definition in part.items():
            if name in libs:
                problems.append('Library %s is defined twice; the second definition was skipped.' % name)
                continue
            libs[name] = definition
    # The previous ids, last: an alias never replaces a library.
    for old, new_ids in aliases.items():
        if old in libs:
            problems.append('Library %s is defined twice; the deprecated alias was skipped.' % old)
            continue
        libs[old] = alias_definition(new_ids)
    return libs


def libraries(theme, problems=None, static=True, built=True):
    """The ordered library set of a Solo tree: static first, then built (a static key wins, as `$static + $built`).

    A tree without libraries/solo-components.json (an older revision) is read from its full yml alone.
    """
    problems = [] if problems is None else problems
    out = {}
    yml = os.path.join(theme, 'solo.libraries.yml')
    if static and os.path.isfile(yml):
        out.update(static_libraries(yml))
    if built and built_available(theme):
        for name, definition in built_libraries(theme, problems).items():
            out[name] = {**out.get(name, {}), **{k: v for k, v in definition.items() if k not in out.get(name, {})}}
    return out


def load(theme):
    """libraries() for a checker: a malformed source stops the check with the problems named."""
    problems = []
    libs = libraries(theme, problems)
    if problems:
        sys.exit('solo_libraries: %d problem(s) reading %s\n  %s' % (len(problems), theme, '\n  '.join(problems)))
    return libs


def css_entries(libs):
    """Every stylesheet entry in set order: (library, category, file, weight or 0 when none)."""
    for name, lib in libs.items():
        for category, files in (lib.get('css') or {}).items():
            for f, options in files.items():
                yield name, category, f, options.get('weight') or 0


def main():
    ap = argparse.ArgumentParser(description=__doc__.split('\n')[0])
    ap.add_argument('--theme', default=os.path.dirname(HERE))
    ap.add_argument('--dump', action='store_true', help='print the normalized, order-preserving JSON')
    ap.add_argument('--static-only', action='store_true')
    ap.add_argument('--built-only', action='store_true')
    a = ap.parse_args()
    problems = []
    libs = libraries(a.theme, problems, static=not a.built_only, built=not a.static_only)
    for p in problems:
        print(p, file=sys.stderr)
    if a.dump:
        print(json.dumps(libs, indent=1, ensure_ascii=False))
    else:
        print('solo_libraries: %d libraries, %d problems' % (len(libs), len(problems)))
    return 1 if problems else 0


if __name__ == '__main__':
    sys.exit(main())
