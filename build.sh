#!/usr/bin/bash
shopt -s extglob
#
# Copyright (c) 2025 Jukka V. Lehtonen (jukka.lehtonen@abo.fi)
#
function usage_and_exit ()
{
    echo "Usage: build.sh options"
    echo "Options:"
    echo "-d data,             Path to data files"
    echo "-m misc,             Path to misc pages"
    echo "-b bench,            Path to benchmark pages"
    echo "-v molstar,          Path to Mol* files"
    echo "-n dbname,           Name of SQL database"
    echo "-u dbuser,           SQL username"
    echo "-p dbpasswd,         SQL password"
    echo "-h|--help,           This text."
    exit 1
}

TEMP=$(getopt -o d:m:b:v:n:u:p:h -l help -n 'build.sh' -- "$@")
if [ $? != 0 ] ; then echo "Terminating..." >&2 ; exit 1 ; fi
eval set -- "$TEMP"

DATAPATH=""
MISCPATH=""
BMPATH=""
MOLSTAR=""
DBNAME="syngap"
DBUSER=""
DBPW=""
while true ; do
        case "$1" in
                -d) DATAPATH="$2" ; shift 2 ;;
                -m) MISCPATH="$2" ; shift 2 ;;
                -b) BMPATH="$2"   ; shift 2 ;;
                -v) MOLSTAR="$2"  ; shift 2 ;;
                -n) DBNAME="$2"   ; shift 2 ;;
                -u) DBUSER="$2"   ; shift 2 ;;
                -p) DBPW="$2"     ; shift 2 ;;
                -h|--help) usage_and_exit ;;
                --) shift ; break ;;
                *) echo "Internal error!" ; exit 1 ;;
        esac
done

# Must have all vars
[[ -n "${DATAPATH}" && -n "${MOLSTAR}" && -n "${DBUSER}"  && -n "${DBPW}" ]] || usage_and_exit

rsync -av src/{index.html,styles,sites,table} build/

rsync -av --exclude=index.html ${MOLSTAR}/build/examples/basic-wrapper/ build/viewer/
rsync -av ${MOLSTAR}/build/examples/basic-wrapper/index.html build/viewer/index.php
cp src/viewer/table.php build/viewer/table.php

rsync -av src/sql/export_csv.php build/sql/
sed "{s/DBNAME/${DBNAME}/; s/DBUSER/${DBUSER}/; s/DBPW/${DBPW}/}" src/sql/connect.php > build/sql/connect.php

rsync -a --stats --exclude=/mutations.csv ${DATAPATH}/ build/data/
rsync -a --stats ${MISCPATH}/ build/misc/

[[ -n "${BMPATH}" ]] && rsync -a --stats ${BMPATH}/ build/benchmark/

# mutations.csv has been created with query:
# SELECT DISTINCT point_mutation(variant) FROM syngap ORDER BY point_mutation(variant);
#
mkdir -p build/images/amino_acids_mutants
if [[ -f  ${DATAPATH}/mutations.csv ]] ; then
while read X
do
    echo ${X}
    [[ -f build/images/amino_acids_mutants/${X}.png ]] || convert +append src/images/${X%?}.png src/images/arrow.png src/images/${X:1}.png build/images/amino_acids_mutants/${X}.png
done < ${DATAPATH}/mutations.csv
fi

rsync -av src/images/icons/ build/images/icons/

exit 0
