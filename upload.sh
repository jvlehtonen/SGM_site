#!/usr/bin/bash
shopt -s extglob
#
# Copyright (c) 2025 Jukka V. Lehtonen (jukka.lehtonen@abo.fi)
#
function usage_and_exit ()
{
    echo "Usage: upload.sh options"
    echo "Options:"
    echo "-d URL,              Path to httpd server"
    echo "-h|--help,           This text."
    exit 1
}

TEMP=$(getopt -o d:h -l help -n 'build.sh' -- "$@")
if [ $? != 0 ] ; then echo "Terminating..." >&2 ; exit 1 ; fi
eval set -- "$TEMP"

URL=""
while true ; do
        case "$1" in
                -d) URL="$2" ; shift 2 ;;
                -h|--help) usage_and_exit ;;
                --) shift ; break ;;
                *) echo "Internal error!" ; exit 1 ;;
        esac
done

# Must have URL
[[ -n "${URL}" ]] || usage_and_exit

rsync -aivC --stats --chmod=D2775,F664 --chown=:www-data --delete build/* ${URL}/
