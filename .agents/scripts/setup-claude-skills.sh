#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
CLAUDE_DIR="$ROOT_DIR/.claude"
SOURCE_DIR="$ROOT_DIR/.agents/skills"
TARGET_LINK="$CLAUDE_DIR/skills"

mkdir -p "$CLAUDE_DIR"

if [ -L "$TARGET_LINK" ] || [ -e "$TARGET_LINK" ]; then
  rm -rf "$TARGET_LINK"
fi

ln -sfn ../.agents/skills "$TARGET_LINK"

echo "Created symlink: .claude/skills -> ../.agents/skills"
