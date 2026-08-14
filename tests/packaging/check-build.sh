#!/usr/bin/env bash
# Checks the built WordPress.org file set (release/svn, from `npx grunt wporg`)
# against two failure classes CI has already caught for real:
#
#  - dev-only paths leaking into the build: the bare "!external" Gruntfile
#    glob shipped external/ to wp.org until it was tightened, because a bare
#    directory negation does not exclude the directory's children;
#  - shipped PHP files executable directly without a guard: the unguarded
#    templates answered direct HTTP requests with a fatal-error page leaking
#    the server's absolute filesystem path.
#
# Usage: tests/packaging/check-build.sh [build-dir]   (default: release/svn)
set -u
BUILD_DIR="${1:-release/svn}"
fail=0
err() { echo "FAIL: $*" >&2; fail=1; }

[ -d "$BUILD_DIR" ] || { echo "Build dir '$BUILD_DIR' missing — run 'npx grunt wporg' first" >&2; exit 2; }

# Dev-only paths that must never ship.
for p in external documentation tests .github node_modules Gruntfile.js package.json package-lock.json CHANGELOG.md CODEOWNERS; do
  [ -e "$BUILD_DIR/$p" ] && err "dev-only path shipped: $p"
done

# Shipped essentials that must be present.
for p in draw-attention.php readme.txt uninstall.php index.php admin public includes languages LICENSE.txt; do
  [ -e "$BUILD_DIR/$p" ] || err "expected shipped path missing: $p"
done

# Every shipped own-code PHP file must be safe to execute directly: a
# WPINC-style guard dies before any code runs, so direct php-cli execution
# yields no output and no fatal. Side-effect-free class definitions also pass;
# Plugin Check's own scan covers guard *presence* — this catches the runtime
# behavior. Vendored libraries under public/includes/lib/ are upstream's to
# police, same scoping as the Plugin Check workflow.
while IFS= read -r -d '' f; do
  rel="${f#"$BUILD_DIR"/}"
  case "$rel" in public/includes/lib/*) continue ;; esac
  out=$(php -d error_reporting=E_ALL -d display_errors=1 "$f" 2>&1)
  rc=$?
  if [ $rc -ne 0 ]; then
    err "direct execution fatals (exit $rc): $rel — $(echo "$out" | head -1)"
  elif [ -n "$out" ]; then
    err "direct execution produces output: $rel — $(echo "$out" | head -1)"
  fi
done < <(find "$BUILD_DIR" -name '*.php' -print0)

if [ $fail -eq 0 ]; then
  echo "OK: build contents + direct-access sweep passed for $BUILD_DIR"
fi
exit $fail
