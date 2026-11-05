#!/usr/bin/env python3
"""Solo CSS budget ratchet: declarations above 0,2,0, with an id, with !important. Counts may fall, never rise.

Thousands of Solo selectors are above 0,2,0 by design (the module keeps its exceptions), so a lint rule cannot be
all-or-nothing. This script counts them per css/ folder and compares the counts with scripts/css-budget.json.
The numbers come from check-winners.py (same parser, same specificity, same file universe as `check-winners.py
--hist`); this file only counts and compares, it holds no CSS rules of its own.

  check-css-budget.py            compare the tree with css-budget.json; exit 1 when any count rises
  check-css-budget.py --update   rewrite css-budget.json from the tree. Refused when a count would rise,
                                 unless --allow-increase (the deliberate, reviewed way to accept a rise)
  check-css-budget.py --root DIR count another Solo checkout

The data file is generated; never edit it by hand. Exit 0: no count rose. Exit 1: a count rose (or a folder
gained counts the budget did not know). Exit 2: nothing was checked (no CSS found) or the budget file is unreadable.
The run prints how many files and declarations it read, so a green run that checked nothing cannot pass for a pass.
"""
import argparse
import glob
import importlib.util
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
SOLO = os.path.dirname(HERE)
BUDGET = os.path.join(HERE, 'css-budget.json')
FOLDERS = ('base', 'layout', 'components', 'breakpoints', 'theme', 'predefined', 'w3-css')
METRICS = ('over_020', 'with_id', 'important')


def load_winners():
    """check-winners.py has a hyphen in its name, so it is loaded by path (one parser, not a copy)."""
    spec = importlib.util.spec_from_file_location('check_winners', os.path.join(HERE, 'check-winners.py'))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def measure(root):
    """Same universe as check-winners.py hist(); the folder is the first path part under css/."""
    cw = load_winners()
    files = sorted(glob.glob(root + '/css/components/*.css') + glob.glob(root + '/css/base/*.css') +
                   glob.glob(root + '/css/layout/*.css') + glob.glob(root + '/css/breakpoints/solo-*-breakpoints-*.css') +
                   glob.glob(root + '/css/theme/*.css') + glob.glob(root + '/css/predefined/*.css') +
                   glob.glob(root + '/css/w3-css/*.css'))
    folders = {f: {'files': 0, 'declarations': 0, 'over_020': 0, 'with_id': 0, 'important': 0} for f in FOLDERS}
    for path in files:
        part = path.split('/css/')[1].split('/')[0]
        row = folders[part]
        row['files'] += 1
        with open(path, encoding='utf-8') as fh:
            decls = cw.parse(fh.read())
        for (_ln, _ctx, sel, _p, _v, imp) in decls:
            sp = cw.specificity(sel)
            row['declarations'] += 1
            row['over_020'] += sp > (0, 2, 0)
            row['with_id'] += bool(sp[0])
            row['important'] += bool(imp)
    return folders


def totals(folders):
    return {k: sum(r[k] for r in folders.values()) for k in ('files', 'declarations') + METRICS}


def rises(folders, base):
    out = []
    for name, row in folders.items():
        old = base.get(name, {})
        for m in METRICS:
            if row[m] > old.get(m, 0):
                out.append('%s %s: %d -> %d' % (name, m, old.get(m, 0), row[m]))
    return out


def main():
    ap = argparse.ArgumentParser(description=__doc__.split('\n')[0])
    ap.add_argument('--root', default=SOLO)
    ap.add_argument('--budget', default=BUDGET)
    ap.add_argument('--update', action='store_true')
    ap.add_argument('--allow-increase', action='store_true')
    a = ap.parse_args()

    folders = measure(a.root)
    t = totals(folders)
    print('read %d CSS files, %d declarations; over-0,2,0 %d, with-id %d, important %d'
          % (t['files'], t['declarations'], t['over_020'], t['with_id'], t['important']))
    if t['files'] == 0 or t['declarations'] == 0:
        print('RESULT: FAIL (checked nothing: no Solo CSS under %s/css)' % a.root)
        return 2

    old = None
    if os.path.exists(a.budget):
        try:
            with open(a.budget, encoding='utf-8') as fh:
                old = json.load(fh)['folders']
        except (ValueError, KeyError) as e:
            print('RESULT: FAIL (budget file unreadable: %s)' % e)
            return 2

    if a.update:
        if old is not None and not a.allow_increase:
            bad = rises(folders, old)
            if bad:
                print('REFUSED: --update would raise a count (use --allow-increase to accept it on purpose):')
                for b in bad:
                    print('  ' + b)
                return 1
        data = {'generated_by': 'scripts/check-css-budget.py --update',
                'note': 'Generated. Never edit by hand. Counts may fall, never rise.',
                'folders': folders}
        with open(a.budget, 'w', encoding='utf-8') as fh:
            json.dump(data, fh, indent=2, sort_keys=True)
            fh.write('\n')
        print('wrote %s' % a.budget)
        return 0

    if old is None:
        print('RESULT: FAIL (no budget file %s; run with --update once)' % a.budget)
        return 2
    bad = rises(folders, old)
    better = [n for n, r in folders.items() for m in METRICS if r[m] < old.get(n, {}).get(m, 0)]
    for b in bad:
        print('RISE ' + b)
    if better and not bad:
        print('improved in %s: run --update to lock the lower counts' % ', '.join(sorted(set(better))))
    print('RESULT: %s' % ('FAIL (a count rose)' if bad else 'PASS'))
    return 1 if bad else 0


if __name__ == '__main__':
    sys.exit(main())
