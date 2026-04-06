#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'EOF'
Usage: ./scripts/tag-release.sh [--push]

Creates a git tag from the module version defined in:
  internautenproductai/internautenproductai.php

Options:
  --push    Push the created tag to origin
  -h, --help  Show this help
EOF
}

push_tag=false

while [[ $# -gt 0 ]]; do
  case "$1" in
    --push)
      push_tag=true
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage >&2
      exit 1
      ;;
  esac
done

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
MODULE_FILE="${REPO_ROOT}/internautenproductai/internautenproductai.php"

if [[ ! -f "${MODULE_FILE}" ]]; then
  echo "Module file not found: ${MODULE_FILE}" >&2
  exit 1
fi

if ! git -C "${REPO_ROOT}" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "No git repository found at ${REPO_ROOT}" >&2
  exit 1
fi

version="$(grep -E "version[[:space:]]*=[[:space:]]*'[^']+'" "${MODULE_FILE}" | head -n 1 | sed -E "s/.*'([^']+)'.*/\1/")"

if [[ -z "${version}" ]]; then
  echo "Could not read module version from ${MODULE_FILE}" >&2
  exit 1
fi

if [[ ! "${version}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Invalid version format: ${version}" >&2
  echo "Expected semantic version like 1.2.3" >&2
  exit 1
fi

tag="v${version}"

if [[ -n "$(git -C "${REPO_ROOT}" status --porcelain)" ]]; then
  echo "Working tree is not clean. Commit or stash your changes before tagging." >&2
  exit 1
fi

if git -C "${REPO_ROOT}" rev-parse "${tag}" >/dev/null 2>&1; then
  echo "Tag ${tag} already exists."
  exit 0
fi

git -C "${REPO_ROOT}" tag -a "${tag}" -m "Release ${tag}"
echo "Created tag ${tag} from module version ${version}."

if [[ "${push_tag}" == true ]]; then
  git -C "${REPO_ROOT}" push origin "${tag}"
  echo "Pushed ${tag} to origin."
else
  echo "Run './scripts/tag-release.sh --push' to push the tag to origin."
fi
