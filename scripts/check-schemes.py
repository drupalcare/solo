#!/usr/bin/env python3
"""Drift check: the scheme records (schemes/<id>.yml) against everything else.

Fails (exit 1) when any of these disagree with the records:
  Solo library set (solo_libraries.py)            one library per scheme, same CSS file
  ../solo_utilities/src/Form/SettingsOptions.php  predefinedTheme(): category|id => label
  images/schemes/                                 one <id>.webp per scheme (none.webp is the "None" option)
  css/predefined/                                 one CSS file per scheme, palette values equal to the record
Record shape follows the apex scheme sidecar (id, name, category, mode); mode is measured from the CSS
(apex color-scheme-contract.md section 5) and must equal the record.
Usage: check-schemes.py [--theme DIR] [--utilities DIR]
"""
import argparse
import re
import sys
from pathlib import Path

sys.dont_write_bytecode = True
sys.path.insert(0, str(Path(__file__).resolve().parent))
import solo_libraries  # noqa: E402  the full library set: static yml plus the generated libraries


def read_record(path):
    rec = {"categories": [], "palette": {}, "page": {}}
    section = None
    for raw in path.read_text().splitlines():
        if not raw.strip() or raw.lstrip().startswith("#"):
            continue
        if not raw.startswith(" "):
            key, _, val = raw.partition(":")
            val = val.strip()
            if val == "":
                section = key
            else:
                section = None
                rec[key] = val.strip('"')
        elif section == "categories":
            rec["categories"].append(raw.strip()[2:])
        elif section in ("palette", "page"):
            key, _, val = raw.strip().partition(": ")
            rec[section][key] = val.strip().strip('"')
    return rec


def css_blocks(text):
    text = re.sub(r"/\*.*?\*/", "", text, flags=re.S)
    root = re.search(r":root\s*\{([^{}]*)\}", text)
    page = re.search(r"\.solo-page\s*\{([^{}]*)\}", text)
    d = lambda m: {k: v.strip() for k, v in re.findall(r"(--[\w-]+)\s*:\s*([^;]+);", m.group(1))} if m else {}
    return d(root), d(page)


def luminance(hexcol):
    h = hexcol.lstrip("#")
    if len(h) in (3, 4):
        h = "".join(c * 2 for c in h[:3])
    out = []
    for i in (0, 2, 4):
        v = int(h[i:i + 2], 16) / 255
        out.append(v / 12.92 if v <= 0.04045 else ((v + 0.055) / 1.055) ** 2.4)
    return 0.2126 * out[0] + 0.7152 * out[1] + 0.0722 * out[2]


def tone(value):
    """light or dark by the mean luminance of the colour stops (0.179 is where white and black give equal contrast)."""
    stops = re.findall(r"#[0-9a-fA-F]{3,8}\b", value or "")
    if not stops:
        return None
    return "light" if sum(luminance(c) for c in stops) / len(stops) > 0.179 else "dark"


def measured_mode(palette):
    """Contract section 5: every region one tone is light or dark; a light reading path with dark nav and footer is mixed."""
    t = {
        "main": tone(palette.get("--sc-main-container-bg")),
        "nav": tone(palette.get("--sc-menu-bg")),
        "footer": tone(palette.get("--sc-footer-container-bg")),
    }
    tones = set(t.values())
    mode = tones.pop() if len(tones) == 1 and None not in tones else "mixed"
    return mode, t


def main():
    here = Path(__file__).resolve().parent.parent
    ap = argparse.ArgumentParser()
    ap.add_argument("--theme", default=str(here))
    ap.add_argument("--utilities", default=str(here.parent / "solo_utilities"))
    a = ap.parse_args()
    theme, util = Path(a.theme), Path(a.utilities)
    errs = []

    recs = {}
    for f in sorted((theme / "schemes").glob("*.yml")):
        r = read_record(f)
        if r.get("id") != f.stem:
            errs.append(f"record {f.name}: id '{r.get('id')}' differs from the file name")
        recs[f.stem] = r
    if not recs:
        errs.append("no records in schemes/")

    # Libraries: top-level keys whose css points at css/predefined/.
    libs = {}
    for name, _cat, f, _w in solo_libraries.css_entries(solo_libraries.load(str(theme))):
        m = re.fullmatch(r"css/predefined/[\w.-]+\.css", f)
        if m:
            libs[name] = f
    for id_, r in recs.items():
        if id_ not in libs:
            errs.append(f"{id_}: no library in the Solo library set")
        elif libs[id_] != r.get("css"):
            errs.append(f"{id_}: library CSS {libs[id_]} differs from record {r.get('css')}")
    for id_ in libs:
        if id_ not in recs:
            errs.append(f"{id_}: library in the Solo library set has no record")

    # SettingsOptions::predefinedTheme().
    php = (util / "src/Form/SettingsOptions.php").read_text()
    i = php.index("function predefinedTheme")
    body = php[i:php.index("function", i + 30)]
    opts = {}
    for cat, id_, lab in re.findall(r"'([a-z_]+)\|(solo-[a-z0-9-]+)'\s*=>\s*\$this->t\('((?:[^'\\]|\\.)*)'\)", body):
        o = opts.setdefault(id_, {"label": lab.replace("\\'", "'"), "cats": []})
        o["cats"].append(cat)
    for id_, r in recs.items():
        o = opts.get(id_)
        if not o:
            errs.append(f"{id_}: not in SettingsOptions::predefinedTheme()")
            continue
        if o["label"] != r.get("name"):
            errs.append(f"{id_}: label '{o['label']}' (SettingsOptions) differs from record '{r.get('name')}'")
        if r["categories"] and r.get("category") != r["categories"][0]:
            errs.append(f"{id_}: category '{r.get('category')}' is not the first of categories {r['categories']}")
        if sorted(o["cats"]) != sorted(r["categories"]):
            errs.append(f"{id_}: categories {sorted(o['cats'])} (SettingsOptions) differ from record {sorted(r['categories'])}")
    for id_ in opts:
        if id_ not in recs:
            errs.append(f"{id_}: in SettingsOptions::predefinedTheme() has no record")

    # Thumbnails.
    on_disk = {p.name for p in (theme / "images/schemes").glob("*.webp")}
    want = set()
    for id_, r in recs.items():
        t = r.get("thumbnail", "")
        want.add(Path(t).name)
        if not (theme / t).is_file():
            errs.append(f"{id_}: thumbnail {t} is missing")
    for n in sorted(on_disk - want - {"none.webp"}):
        errs.append(f"images/schemes/{n}: no record")
    if "none.webp" not in on_disk:
        errs.append("images/schemes/none.webp is missing")

    # CSS files and palette values.
    css_files = {p.name for p in (theme / "css/predefined").glob("*.css")}
    for n in sorted(css_files - {Path(r.get("css", "")).name for r in recs.values()}):
        errs.append(f"css/predefined/{n}: no record")
    for id_, r in recs.items():
        p = theme / r.get("css", "")
        if not p.is_file():
            errs.append(f"{id_}: CSS {r.get('css')} is missing")
            continue
        root, page = css_blocks(p.read_text())
        mode, _ = measured_mode(root)
        if r.get("mode") != mode:
            errs.append(f"{id_}: mode '{r.get('mode')}' differs from the mode measured in the CSS '{mode}'")
        for name, got, rec in (("palette", root, r["palette"]), ("page", page, r["page"])):
            if got != rec:
                bad = sorted(k for k in set(got) | set(rec) if got.get(k) != rec.get(k))
                errs.append(f"{id_}: {name} differs from CSS in {', '.join(bad[:4])}{'...' if len(bad) > 4 else ''}")

    for e in errs:
        print("FAIL", e)
    print(f"SCHEMES: {'FAIL' if errs else 'OK'} ({len(recs)} records, {len(libs)} libraries, {len(opts)} options, {len(on_disk)} thumbnails, {len(errs)} problems)")
    return 1 if errs else 0


sys.exit(main())
