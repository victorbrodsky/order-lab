#!/bin/sh
# wkhtmltopdf-wrapper.sh
#
# Wrapper for wkhtmltopdf required by knp-snappy >= 1.7, which validates the
# configured binary with is_executable() and therefore no longer accepts a
# compound command such as "xvfb-run wkhtmltopdf" or
# "xwfb-run -- /usr/local/bin/wkhtmltopdf".
#
# Set the site setting "wkhtmltopdfpath" / "wkhtmltopdfpathLinux" (or the
# wkhtmltopdfpath container parameter) to the absolute path of this script.
#
# Behavior:
#  1) Alma10 (xwayland-run installed):  xwfb-run -- wkhtmltopdf "$@"
#  2) Older distros (xvfb installed):  xvfb-run -a wkhtmltopdf "$@"
#  3) No virtual display needed:       wkhtmltopdf "$@"

WKHTMLTOPDF_BIN=""

for candidate in /usr/local/bin/wkhtmltopdf /usr/bin/wkhtmltopdf wkhtmltopdf; do
    if command -v "$candidate" >/dev/null 2>&1; then
        WKHTMLTOPDF_BIN="$candidate"
        break
    fi
done

if [ -z "$WKHTMLTOPDF_BIN" ]; then
    echo "wkhtmltopdf-wrapper.sh: wkhtmltopdf binary not found" >&2
    exit 1
fi

if command -v xwfb-run >/dev/null 2>&1; then
    #The -- separator is required to distinguish between xwfb-run options and the wkhtmltopdf command
    exec xwfb-run -- "$WKHTMLTOPDF_BIN" "$@"
elif command -v xvfb-run >/dev/null 2>&1; then
    #-a: use a free display number, avoids "kill: No such process" errors
    exec xvfb-run -a "$WKHTMLTOPDF_BIN" "$@"
else
    exec "$WKHTMLTOPDF_BIN" "$@"
fi
