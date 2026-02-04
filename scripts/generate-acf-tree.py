#!/usr/bin/env python3
import json
from pathlib import Path


def load_json(path: Path):
    with path.open('r', encoding='utf-8') as f:
        return json.load(f)


def fmt_field(field, indent=0):
    name = field.get('name') or '(no name)'
    label = field.get('label') or ''
    ftype = field.get('type') or ''
    line = f"- {name} ({ftype})"
    if label and label != name:
        line += f" — {label}"
    lines = [("  " * indent) + line]
    sub_fields = field.get('sub_fields') or []
    for sub in sub_fields:
        lines.extend(fmt_field(sub, indent + 1))
    return lines


def main():
    repo_root = Path(__file__).resolve().parents[1]
    acf_dir = repo_root / 'acf-json'
    out_path = repo_root / 'ACF-TREE.md'

    if not acf_dir.exists():
        raise SystemExit(f"acf-json not found at {acf_dir}")

    groups = []
    for path in sorted(acf_dir.glob('*.json')):
        try:
            data = load_json(path)
        except Exception:
            continue
        if not isinstance(data, dict):
            continue
        title = data.get('title') or path.stem
        fields = data.get('fields') or []
        groups.append((title, path.name, fields))

    lines = ["# ACF Field Tree", "", "Generated from acf-json/*.json.", ""]
    for title, filename, fields in groups:
        lines.append(f"## {title}")
        lines.append(f"Source: {filename}")
        lines.append("")
        if not fields:
            lines.append("- (no fields)")
            lines.append("")
            continue
        for field in fields:
            lines.extend(fmt_field(field, 0))
        lines.append("")

    out_path.write_text("\n".join(lines), encoding='utf-8')


if __name__ == '__main__':
    main()
