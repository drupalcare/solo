#!/usr/bin/env python3
"""Rebuild tests/fixtures/solo1-freeze/*.txt from the Solo 1.x release tags.

The lists record what Solo 1 sites and sub-themes can rely on. Solo 2 must
keep every entry; tests/src/Unit/Solo1FreezeTest.php fails when one is gone.

Source: git objects only (git show TAG:path, git ls-tree). No checkout, no
network. Union over TAGS; each line is "<entry><TAB><first tag it appeared in>".

Usage: python3 scripts/generate-solo1-freeze.py [--check]
  --check  rebuild in memory and exit 1 when the files on disk differ.

What the method cannot see: see planning/solo-step2-result.md.
"""

import re
import subprocess
import sys
from pathlib import Path

TAGS = ["1.0.35", "1.0.36", "1.0.37", "1.0.38", "1.0.39"]
ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "tests" / "fixtures" / "solo1-freeze"
NAMESPACE = "Drupal\\solo\\Hook\\"


def git(*args: str) -> str:
    result = subprocess.run(
        ["git", "-C", str(ROOT), *args],
        check=True,
        capture_output=True,
        text=True,
    )
    return result.stdout


def show(tag: str, path: str) -> str:
    return git("show", f"{tag}:{path}")


def tree(tag: str) -> list[str]:
    return git("ls-tree", "-r", "--name-only", f"{tag}^{{commit}}").splitlines()


def hooks(tag: str, files: list[str]) -> set[str]:
    """Every method declared in src/Hook/*.php, as Class::method."""
    found = set()
    for path in files:
        if not re.fullmatch(r"src/Hook/[A-Za-z0-9_]+\.php", path):
            continue
        source = show(tag, path)
        class_match = re.search(r"^class\s+([A-Za-z0-9_]+)", source, re.M)
        if class_match is None:
            continue
        for method in re.findall(
            r"^\s*(?:public\s+|protected\s+|private\s+)?(?:static\s+)?function\s+([A-Za-z0-9_]+)\s*\(",
            source,
            re.M,
        ):
            found.add(f"{NAMESPACE}{class_match.group(1)}::{method}")
    return found


def twig_paths(tag: str, files: list[str]) -> set[str]:
    """Every literal @solo/... string in any .twig file."""
    found = set()
    for path in files:
        if not path.endswith(".twig"):
            continue
        source = show(tag, path)
        # A literal path ends in a file extension; a string cut short by a
        # Twig "~" concatenation is a prefix and is handled below.
        found.update(re.findall(r"@solo/[A-Za-z0-9_./-]*\.(?:twig|svg)", source))
        # "@solo/<prefix>" ~ key ~ "<suffix>": every file at this tag that
        # matches prefix*suffix is a possible target. An inference, labelled
        # in the result file.
        for prefix, suffix in re.findall(
            r'"@solo/([A-Za-z0-9_./-]*)"\s*~[^"\n]*"([A-Za-z0-9_.]*)"', source
        ):
            for candidate in files:
                if candidate.startswith(prefix) and candidate.endswith(suffix):
                    found.add(f"@solo/{candidate}")
    return found


def top_level_keys(source: str) -> set[str]:
    return set(re.findall(r"^([A-Za-z0-9_.-]+):", source, re.M))


def regions(tag: str) -> set[str]:
    block = re.search(r"^regions:\n((?:[ \t]+.*\n|\n)+)", show(tag, "solo.info.yml"), re.M)
    if block is None:
        raise SystemExit(f"{tag}: no regions block")
    return set(re.findall(r"^  ([A-Za-z0-9_]+):", block.group(1), re.M))


def schema_keys(tag: str) -> set[str]:
    """Direct children of solo.settings: mapping (indent 4)."""
    return set(re.findall(r"^    ([A-Za-z0-9_.-]+):", show(tag, "config/schema/solo.schema.yml"), re.M))


COLLECTORS = {
    "hooks": lambda tag, files: hooks(tag, files),
    "twig-paths": lambda tag, files: twig_paths(tag, files),
    "regions": lambda tag, files: regions(tag),
    "libraries": lambda tag, files: top_level_keys(show(tag, "solo.libraries.yml")),
    "schema-keys": lambda tag, files: schema_keys(tag),
}


def build() -> dict[str, tuple[dict[str, str], dict[str, int]]]:
    refs = git("for-each-ref", "refs/tags", "--format=%(refname:short)").split()
    missing = [t for t in TAGS if t not in refs]
    if missing:
        raise SystemExit(f"missing tags: {missing}")
    first: dict[str, dict[str, str]] = {name: {} for name in COLLECTORS}
    per_tag: dict[str, dict[str, int]] = {name: {} for name in COLLECTORS}
    for tag in TAGS:
        files = tree(tag)
        for name, collect in COLLECTORS.items():
            entries = collect(tag, files)
            per_tag[name][tag] = len(entries)
            for entry in entries:
                first[name].setdefault(entry, tag)
    return {name: (first[name], per_tag[name]) for name in COLLECTORS}


def render(entries: dict[str, str]) -> str:
    return "".join(f"{entry}\t{tag}\n" for entry, tag in sorted(entries.items()))


def main() -> int:
    check = "--check" in sys.argv
    result = build()
    OUT.mkdir(parents=True, exist_ok=True)
    drift = 0
    for name, (entries, per_tag) in result.items():
        target = OUT / f"{name}.txt"
        text = render(entries)
        if check:
            if not target.exists() or target.read_text() != text:
                print(f"DRIFT {target.name}")
                drift += 1
        else:
            target.write_text(text)
        print(f"{name}: union {len(entries)}; per tag {per_tag}")
    return 1 if drift else 0


if __name__ == "__main__":
    sys.exit(main())
