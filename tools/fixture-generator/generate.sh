#!/usr/bin/env bash
set -euo pipefail

# Regenerates classes, class definitions and Container XML for every supported Pimcore line.
# No database or Pimcore install needed; see tests/fixtures/README.md.

cd "$(dirname "$0")"
root="$(cd ../.. && pwd)"

for line in 12.3 2026; do
    COMPOSER="composer-${line}.json" composer install --no-interaction --no-scripts --quiet
    out="${root}/tests/fixtures/classes/${line}"
    definitions="${root}/tests/fixtures/definitions/${line}"
    rm -rf "${out}" "${definitions}"
    php generate.php "vendor-${line}" "${out}" "${definitions}"
    php generate-container.php "vendor-${line}" "${root}/tests/fixtures/container/generated/${line}.xml"
done
