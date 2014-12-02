#!/bin/bash

CMD=`basename $0`
while getopts "d:a:" OPT
do
  case $OPT in
    "d") DATE="$OPTARG" ;;
    "a") AREA="$OPTARG" ;;
  esac
done


exec php -q index.php --type=getshop --date=${DATE} --area=${AREA}
exit;
