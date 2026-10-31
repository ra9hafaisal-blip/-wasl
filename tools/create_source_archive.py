from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile


ROOT = Path(__file__).resolve().parents[1]
ARCHIVE = ROOT / "output" / "Wasl_SourceCode.zip"
EXCLUDED_DIRS = {
    ".git", ".claude", "node_modules", "vendor", "tmp", "cache", "logs",
}
EXCLUDED_FILES = {".env", "database.sqlite", "database.sqlite-journal"}


def include(path: Path) -> bool:
    relative = path.relative_to(ROOT)
    if any(part in EXCLUDED_DIRS for part in relative.parts):
        return False
    if relative.parts[:3] == ("storage", "framework", "views"):
        return False
    if path.name in EXCLUDED_FILES or path.suffix.lower() in {".log", ".sqlite", ".db"}:
        return False
    if path.name == ARCHIVE.name:
        return False
    return True


def main() -> None:
    ARCHIVE.parent.mkdir(parents=True, exist_ok=True)
    files = sorted(path for path in ROOT.rglob("*") if path.is_file() and include(path))
    with ZipFile(ARCHIVE, "w", compression=ZIP_DEFLATED, compresslevel=7) as archive:
        for path in files:
            archive.write(path, path.relative_to(ROOT))
    print(f"Created {ARCHIVE} ({len(files)} files)")


if __name__ == "__main__":
    main()
