#!/usr/bin/env python3
"""Fails when a removed 1.x region variable name is still used in Solo's files.

One owner: solo_utilities/config/map/solo-variable-map.yml (`removed:` lists every old name). An old name
may appear only in the archive (css/base/solo-region-legacy.css), the generated README table, the
CHANGELOG and the docs; anywhere else it is a leftover that the archive would hide.

Usage:
  check-region-vars.py              exit 1 and list file:line for every old name outside the allow-list
  check-region-vars.py --self-test  plant an old name in a copy of the tree and prove the check goes red,
                                    and a new name plus an allow-listed file stay green
"""

import re
import shutil
import sys
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
MAP = ROOT.parent / "solo_utilities" / "config" / "map" / "solo-variable-map.yml"
SUFFIXES = {".css", ".src", ".yml", ".twig", ".js", ".php", ".theme", ".py", ".json", ".txt"}
SKIP_DIRS = {".git", "tests", "__pycache__", "node_modules", ".claude", "vendor"}
# Files that may name an old name: the archive, the doc that lists them, the history, this tool's siblings.
ALLOW = {
    "css/base/solo-region-legacy.css",
    "README.md",
    "CHANGELOG.md",
    "scripts/build-region-legacy.py",
    "scripts/check-region-vars.py",
}
ALLOW_DIRS = ("docs/",)


def old_names(map_text):
    """The `removed:` keys of the map."""
    names, section = [], None
    for raw in map_text.splitlines():
        if raw.lstrip().startswith("#") or not raw.strip():
            continue
        if raw.rstrip() in ("roles:", "removed:"):
            section = raw.strip()[:-1]
            continue
        m = re.match(r"  '(--[a-z0-9-]+)':", raw)
        if section == "removed" and m:
            names.append(m.group(1))
    return names


def scan(root, names):
    """Returns (path, line number, name) for each old name outside the allow-list."""
    pattern = re.compile(
        r"(?<![A-Za-z0-9_-])(" + "|".join(re.escape(n) for n in sorted(names, key=len, reverse=True)) + r")(?![A-Za-z0-9_-])"
    )
    found = []
    for path in sorted(root.rglob("*")):
        if not path.is_file() or path.suffix not in SUFFIXES:
            continue
        rel = path.relative_to(root).as_posix()
        if set(rel.split("/")) & SKIP_DIRS or rel in ALLOW or rel.startswith(ALLOW_DIRS):
            continue
        for number, line in enumerate(path.read_text(errors="replace").splitlines(), 1):
            for m in pattern.finditer(line):
                found.append((rel, number, m.group(1)))
    return found


def self_test():
    names = old_names(MAP.read_text())
    failures = 0
    if len(names) < 15:
        print("FAIL: the map yields only %d old names" % len(names))
        failures += 1
    with tempfile.TemporaryDirectory() as tmp:
        copy = Path(tmp) / "solo"
        (copy / "css").mkdir(parents=True)
        (copy / "css" / "base").mkdir()
        cases = (
            ("css/a.css", ".a { color: var(--r-tx); }\n", True),
            ("css/b.css", ".a { color: var(--r-text); --r-text-x: 1; }\n", False),
            ("css/c.css", ".a { --r-tx-lk: red; }\n", True),
            ("css/base/solo-region-legacy.css", ".a { --r-tx: var(--r-text); }\n", False),
            ("CHANGELOG.md", "mentions --r-bg\n", False),
        )
        for rel, text, must_fail in cases:
            (copy / rel).write_text(text)
            hits = [h for h in scan(copy, names) if h[0] == rel]
            ok = bool(hits) == must_fail
            print("%s: %s (%s)" % ("ok" if ok else "FAIL", rel, "flagged" if hits else "clean"))
            failures += 0 if ok else 1
        shutil.rmtree(copy)
    print("self-test: %d failures" % failures)
    return 1 if failures else 0


def main(argv):
    if "--self-test" in argv:
        return self_test()
    names = old_names(MAP.read_text())
    if not names:
        print("check-region-vars: no old names read from %s" % MAP)
        return 1
    found = scan(ROOT, names)
    for rel, number, name in found:
        print("%s:%d: old region variable %s" % (rel, number, name))
    print("check-region-vars: %d old names in the map, %d uses outside the archive" % (len(names), len(found)))
    return 1 if found else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
