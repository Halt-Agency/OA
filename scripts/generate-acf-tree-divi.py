#!/usr/bin/env python3
import json
from pathlib import Path


def load_json(path: Path):
    with path.open('r', encoding='utf-8') as f:
        return json.load(f)


def join_key(parts):
    return '_'.join([p for p in parts if p])


def walk_fields(fields, prefix, lines):
    for field in fields or []:
        name = field.get('name') or ''
        label = field.get('label') or ''
        ftype = field.get('type') or ''

        if ftype == 'group':
            new_prefix = prefix + [name] if name else prefix
            if label:
                lines.append(f"- {join_key(new_prefix)} (group) — {label}")
            walk_fields(field.get('sub_fields'), new_prefix, lines)
            continue

        if ftype == 'repeater':
            key_prefix = join_key(prefix + [name])
            label_text = f" — {label}" if label else ''
            lines.append(f"- {key_prefix} (repeater){label_text}")
            # Repeater subfields are stored as {repeater}_{row}_{subfield}
            for sub in field.get('sub_fields') or []:
                sub_name = sub.get('name') or ''
                sub_label = sub.get('label') or ''
                sub_type = sub.get('type') or ''
                if sub_name:
                    lines.append(
                        f"  - {key_prefix}_0_{sub_name} ({sub_type}) — row 0{(' — ' + sub_label) if sub_label else ''}"
                    )
            continue

        full_key = join_key(prefix + [name])
        if not full_key:
            continue
        label_text = f" — {label}" if label else ''
        lines.append(f"- {full_key} ({ftype}){label_text}")


def main():
    repo_root = Path(__file__).resolve().parents[1]
    acf_dir = repo_root / 'acf-json'
    out_path = repo_root / 'ACF-TREE-DIVI.md'

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

    lines = [
        "# ACF Field Keys for Divi",
        "",
        "Generated from acf-json/*.json. Use these keys in Divi dynamic content.",
        "Group subfields are flattened with underscores (e.g., page_content_hero_heading).",
        "Repeaters are shown with a row 0 example (e.g., repeater_0_subfield).",
        "",
    ]

    for title, filename, fields in groups:
        lines.append(f"## {title}")
        lines.append(f"Source: {filename}")
        lines.append("")
        if not fields:
            lines.append("- (no fields)")
            lines.append("")
            continue
        group_lines = []
        walk_fields(fields, [], group_lines)
        if group_lines:
            lines.extend(group_lines)
        else:
            lines.append("- (no fields)")
        lines.append("")

    out_path.write_text("\n".join(lines), encoding='utf-8')


if __name__ == '__main__':
    main()
