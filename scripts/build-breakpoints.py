#!/usr/bin/env python3
"""Builds the 15 per-breakpoint Solo CSS files from three sources.

A media query cannot read var(), so each breakpoint value has to be written
into the CSS. One source per family keeps the value in one place:

  scripts/breakpoints/menu-mn.css.src    -> css/breakpoints/solo-menu-breakpoints-mn-<bp>.css
  scripts/breakpoints/global-sb.css.src  -> css/breakpoints/solo-global-breakpoints-sb-<bp>.css
  scripts/breakpoints/layout-sb.css.src  -> css/layout/solo-layout-breakpoints-sb-<bp>.css

Source format: plain CSS, line by line.
  {{MIN}} / {{MAX}}   replaced by the breakpoint's min-width / max-width value.
  @@a,b|<line>        the line exists only in the listed breakpoints (576, 768,
                      992, 1200, 1400). A line with no prefix exists in all five.
Generated files are written exactly as the source defines them; the variants
that are not just a changed value are kept as they shipped in 2.0.

Usage:
  build-breakpoints.py            write the 15 files (only the ones that differ)
  build-breakpoints.py --check    exit 1 when any generated file differs from
                                  its source (drift check); writes nothing
"""

import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "scripts" / "breakpoints"

# The five collapse breakpoints in px: the one owner of the values. The rem pair is derived
# (px / 16, max-width = min - 0.00125rem), the form apex's css.md "Layout Collapse Breakpoints"
# table lists (35.99875rem ... 87.49875rem), so a range query never overlaps its neighbour.
PX = (576, 768, 992, 1200, 1400)


def _rem(value: float) -> str:
    return ("%.5f" % value).rstrip("0").rstrip(".") + "rem"


BREAKPOINTS = {str(px): (_rem(px / 16), _rem(px / 16 - 0.00125)) for px in PX}

FAMILIES = {
    "menu-mn.css.src": "css/breakpoints/solo-menu-breakpoints-mn-{bp}.css",
    "global-sb.css.src": "css/breakpoints/solo-global-breakpoints-sb-{bp}.css",
    "layout-sb.css.src": "css/layout/solo-layout-breakpoints-sb-{bp}.css",
}


def render(source: str, bp: str) -> str:
    """Returns the CSS of one breakpoint from a family source."""
    min_value, max_value = BREAKPOINTS[bp]
    out = []
    for line in source.split("\n")[:-1]:
        if line.startswith("@@"):
            tags, _, line = line[2:].partition("|")
            if bp not in tags.split(","):
                continue
        out.append(line.replace("{{MIN}}", min_value).replace("{{MAX}}", max_value))
    return "\n".join(out) + "\n"


def main() -> int:
    check = "--check" in sys.argv[1:]
    drift = 0
    written = 0
    for src_name, target in FAMILIES.items():
        source = (SRC / src_name).read_text(encoding="utf-8")
        for bp in BREAKPOINTS:
            path = ROOT / target.format(bp=bp)
            want = render(source, bp)
            have = path.read_text(encoding="utf-8") if path.exists() else None
            if have == want:
                continue
            if check:
                drift += 1
                print("DRIFT: " + str(path.relative_to(ROOT)))
            else:
                path.write_text(want, encoding="utf-8")
                written += 1
                print("wrote " + str(path.relative_to(ROOT)))
    if check:
        print("breakpoints: " + ("OK, 15 files match their sources" if not drift else str(drift) + " file(s) drifted"))
        return 1 if drift else 0
    print("breakpoints: " + str(written) + " file(s) written")
    return 0


if __name__ == "__main__":
    sys.exit(main())
