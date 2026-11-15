"""Moves !important declarations out of Solo's @layer blocks into an unlayered block that follows each layer.

Why: under CSS Cascade 5 a layered !important beats every unlayered !important, so a Solo !important kept inside
@layer solo.* would beat the site owner's typed custom CSS (unlayered). Before Solo was layered, every Solo
declaration was unlayered and the owner's typed !important won. Moving the declaration, unchanged, to an unlayered
block in the same file restores that winner. Normal declarations stay in their layer.

The layer named by LOCKED is skipped: its !important must beat everything (reduced motion, WCAG 2.3.3).

Used by build-breakpoints.py (the generated css/breakpoints files) and by the one-time conversion of the other
files. Standard library only. Idempotent: a file with no layered !important outside LOCKED is returned unchanged.
"""

import re

LOCKED = "solo.locked"
WRAPPERS = ("@media", "@supports", "@container")
IMPORTANT = re.compile(r"!\s*important\s*$", re.I)
COMMENT = re.compile(r"/\*.*?\*/", re.S)


class UnlayerError(Exception):
    """The CSS has a shape this script does not handle (it stops rather than guess)."""


def _skip_comment(t, i):
    j = t.find("*/", i + 2)
    if j < 0:
        raise UnlayerError("unterminated comment")
    return j + 2


def _skip_string(t, i):
    q = t[i]
    j = i + 1
    while j < len(t):
        if t[j] == "\\":
            j += 2
            continue
        if t[j] == q:
            return j + 1
        j += 1
    raise UnlayerError("unterminated string")


def _match_brace(t, i):
    """t[i] is '{'; returns the index of the matching '}'."""
    depth = 0
    j = i
    while j < len(t):
        c = t[j]
        if t.startswith("/*", j):
            j = _skip_comment(t, j)
            continue
        if c in "\"'":
            j = _skip_string(t, j)
            continue
        if c == "{":
            depth += 1
        elif c == "}":
            depth -= 1
            if depth == 0:
                return j
        j += 1
    raise UnlayerError("unbalanced braces")


def _gap_end(t, i, end):
    """Index of the first character after whitespace and comments."""
    while i < end:
        if t[i].isspace():
            i += 1
        elif t.startswith("/*", i):
            i = _skip_comment(t, i)
        else:
            break
    return i


def _prelude_end(t, i, end):
    """Index of the first '{', ';' or '}' outside strings, comments and parentheses."""
    depth = 0
    j = i
    while j < end:
        c = t[j]
        if t.startswith("/*", j):
            j = _skip_comment(t, j)
            continue
        if c in "\"'":
            j = _skip_string(t, j)
            continue
        if c == "(":
            depth += 1
        elif c == ")":
            depth -= 1
        elif depth == 0 and c in "{;}":
            return j
        j += 1
    return end


def _strip(s):
    return COMMENT.sub(" ", s)


def _parse(t, i, end):
    """Children of a container between i and end: dicts with gap/start/head/open/close/kind."""
    nodes = []
    while True:
        gap = i
        s = _gap_end(t, i, end)
        if s >= end:
            return nodes, i
        p = _prelude_end(t, s, end)
        if p >= end or t[p] == "}":
            raise UnlayerError("stray text at %d" % s)
        head = t[s:p].strip()
        if t[p] == ";":
            nodes.append(dict(kind="stmt", gap=gap, start=s, end=p + 1))
            i = p + 1
            continue
        close = _match_brace(t, p)
        node = dict(gap=gap, start=s, head=head, open=p, close=close, end=close + 1)
        if head.startswith("@layer"):
            node["kind"] = "layer"
            node["name"] = head[6:].strip()
            node["children"], _ = _parse(t, p + 1, close)
        elif head.startswith("@") and head.split()[0] in WRAPPERS:
            node["kind"] = "wrap"
            node["children"], _ = _parse(t, p + 1, close)
        elif head.startswith("@"):
            node["kind"] = "opaque"
        else:
            node["kind"] = "rule"
            if "{" in _strip(t[p + 1:close]):
                raise UnlayerError("nested rule inside %r" % head[:60])
        nodes.append(node)
        i = close + 1


def _decls(body):
    """Split a rule body into declaration pieces at ';' (outside strings, comments, parentheses)."""
    pieces, depth, cur, j = [], 0, [], 0
    while j < len(body):
        c = body[j]
        if body.startswith("/*", j):
            k = _skip_comment(body, j)
            cur.append(body[j:k])
            j = k
            continue
        if c in "\"'":
            k = _skip_string(body, j)
            cur.append(body[j:k])
            j = k
            continue
        if c == "(":
            depth += 1
        elif c == ")":
            depth -= 1
        if c == ";" and depth == 0:
            pieces.append("".join(cur))
            cur = []
        else:
            cur.append(c)
        j += 1
    pieces.append("".join(cur))
    return pieces


def _is_important(piece):
    return bool(IMPORTANT.search(_strip(piece).strip()))


def _has_decl(piece):
    return ":" in _strip(piece)


def _rule_text(indent, head, pieces):
    lines = [indent + head + " {"]
    for p in pieces:
        lines.append(indent + "  " + p.strip() + ";")
    lines.append(indent + "}")
    return "\n".join(lines)


def _indent_of(t, pos):
    ls = t.rfind("\n", 0, pos) + 1
    return t[ls:pos] if not t[ls:pos].strip() else ""


def _gap_comments(t, node):
    """The comments (not the blank space) written before a node."""
    return [c.group(0) for c in COMMENT.finditer(t[node["gap"]:node["start"]])]


def _collect(t, children, container_close):
    """Splits the children of a container. Returns (kept_inner, moved_items, changed).

    kept_inner is the text between the container's braces without the moved declarations (original gaps kept);
    moved_items is a list of texts (comments plus rule) in source order.
    """
    kept_parts, moved_items, changed, removed_before_first_kept = [], [], False, False
    for ch in children:
        k, m = _split(t, ch)
        if m is not None:
            changed = True
            moved_items.append("\n".join([_indent_of(t, ch["start"]) + c for c in _gap_comments(t, ch)] + [m]))
        if k is None:
            if not kept_parts:
                removed_before_first_kept = True
            continue
        gap = t[ch["gap"]:ch["start"]]
        if removed_before_first_kept and not kept_parts and "/*" not in gap:
            gap = "\n" + _indent_of(t, ch["start"])
        kept_parts.append(gap + k)
    trailing = t[children[-1]["end"]:container_close] if children else ""
    return "".join(kept_parts) + trailing, moved_items, changed


def _split(t, node):
    """Returns (kept, moved) text for one node inside a layer; None when nothing is left on that side."""
    kind = node["kind"]
    if kind in ("stmt", "opaque"):
        return t[node["start"]:node["end"]], None
    indent = _indent_of(t, node["start"])
    if kind == "rule":
        pieces = _decls(t[node["open"] + 1:node["close"]])
        imp = [p for p in pieces if _has_decl(p) and _is_important(p)]
        if not imp:
            return t[node["start"]:node["end"]], None
        normal = [p for p in pieces if _has_decl(p) and not _is_important(p)]
        kept = _rule_text(indent, node["head"], normal)[len(indent):] if normal else None
        return kept, _rule_text(indent, node["head"], imp)
    inner, items, changed = _collect(t, node["children"], node["close"])
    if not changed:
        return t[node["start"]:node["end"]], None
    kept = None
    if re.search(r"[{;]", _strip(inner)):
        kept = node["head"] + " {" + inner + "}"
    moved = indent + node["head"] + " {\n" + "\n\n".join(items) + "\n" + indent + "}"
    return kept, moved


def _dedent(text, n):
    return "\n".join(l[n:] if l[:n] == " " * n else l for l in text.split("\n"))


def split_important(t, locked=LOCKED):
    """Returns (new_text, moved_count). moved_count counts !important declarations moved out of layers."""
    nodes, _ = _parse(t, 0, len(t))
    for n in nodes:
        if n["kind"] == "wrap" and any(c["kind"] == "layer" for c in n["children"]):
            raise UnlayerError("@layer inside %s" % n["head"][:40])
    out, pos, moved_total = [], 0, 0
    for n in nodes:
        if n["kind"] != "layer" or n["name"] == locked:
            continue
        inner, items, changed = _collect(t, n["children"], n["close"])
        if not changed:
            continue
        level = len(_indent_of(t, n["children"][0]["start"])) if n["children"] else 0
        moved_text = _dedent("\n\n".join(items), level)
        moved_total += len(re.findall(r"!\s*important", _strip(moved_text), re.I))
        # end of the line that closes the layer block (keeps its "end @layer" comment)
        eol = t.find("\n", n["end"])
        eol = len(t) if eol < 0 else eol
        if t[n["end"]:eol].strip() and not COMMENT.fullmatch(t[n["end"]:eol].strip()):
            raise UnlayerError("text after the closing brace of @layer %s" % n["name"])
        header = ("/* unlayered: the !important declarations of @layer %s above, moved unchanged. A layered !important\n"
                  "   beats every unlayered one, so they stay unlayered and the site owner's typed !important keeps winning. */"
                  % n["name"])
        block = header + "\n" + moved_text
        out.append(t[pos:n["start"]])
        if re.search(r"[{;]", _strip(inner)):
            out.append(t[n["start"]:n["open"] + 1] + inner + t[n["close"]:eol])
            out.append("\n\n" + block)
        else:
            out.append(block)
        pos = eol
    out.append(t[pos:])
    return "".join(out), moved_total
