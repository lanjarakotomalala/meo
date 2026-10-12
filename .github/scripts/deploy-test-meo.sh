#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${TEST_MEO_SFTP_KEY:-}" ]]; then
  echo "Missing test.meo.fr deployment key" >&2
  exit 1
fi

key_file="${RUNNER_TEMP}/test-meo-deploy-key"
known_hosts="${RUNNER_TEMP}/test-meo-known-hosts"
batch_file="${RUNNER_TEMP}/test-meo-sftp-batch"
live_html="${RUNNER_TEMP}/test-meo-live-index.html"
trap 'rm -f -- "$key_file" "$known_hosts" "$batch_file" "$live_html"' EXIT
umask 077
printf '%s\n' "$TEST_MEO_SFTP_KEY" > "$key_file"

# Pinned public host key of nc3199.nexylan.net:2121, checked on 2026-10-09.
printf '%s\n' '[nc3199.nexylan.net]:2121 ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIEQGp8AVcj8RoZobKKzKaz+PS/rKLUT7eLQHnXljoy1H' > "$known_hosts"

printf '%s\n' \
  'cd /var/www/test.meo.fr/htdocs' \
  '-mkdir assets' \
  '-mkdir data' > "$batch_file"

count=0
while IFS= read -r -d '' source; do
  relative="${source#cro-preview/}"
  case "$relative" in
    .htaccess | *.html | *.css | *.js)
      [[ "$relative" != */* ]] || continue
      ;;
    assets/*)
      [[ "$relative" =~ ^assets/[A-Za-z0-9._-]+\.(svg|png|ico|webp|woff2|otf|jpg|jpeg)$ ]] || continue
      ;;
    data/*)
      [[ "$relative" =~ ^data/[A-Za-z0-9._-]+\.json$ ]] || continue
      ;;
    *) continue ;;
  esac
  [[ -f "$source" ]] || { echo "Missing tracked file: $source" >&2; exit 1; }
  [[ "$relative" =~ ^[A-Za-z0-9._/-]+$ ]] || { echo "Unsafe path: $relative" >&2; exit 1; }
  if [[ "$relative" == index.html ]]; then
    continue
  fi
  printf 'put "%s" "%s"\n' "$source" "$relative" >> "$batch_file"
  ((count += 1))
done < <(git ls-files -z -- cro-preview)

# Publish the entry point last, after its styles, scripts, data and images.
printf '%s\n' 'put "cro-preview/index.html" "index.html"' >> "$batch_file"
((count += 1))
echo "Uploading $count preview files to test.meo.fr"

sftp -q -b "$batch_file" -P 2121 \
  -o BatchMode=yes \
  -o IdentitiesOnly=yes \
  -o StrictHostKeyChecking=yes \
  -o "UserKnownHostsFile=$known_hosts" \
  -i "$key_file" \
  test-fd6ed3@nc3199.nexylan.net

curl --fail --silent --show-error --location --retry 5 --retry-delay 2 \
  "https://test.meo.fr/?deploy=${GITHUB_SHA}" -o "$live_html"
if ! cmp -s cro-preview/index.html "$live_html"; then
  echo "The live homepage does not match the deployed Git revision" >&2
  exit 1
fi
echo "Verified https://test.meo.fr/ against ${GITHUB_SHA}"
