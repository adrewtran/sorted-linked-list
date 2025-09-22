#!/usr/bin/env bash
set -euo pipefail
vendor/bin/phpcs --standard=phpcs.xml src tests
