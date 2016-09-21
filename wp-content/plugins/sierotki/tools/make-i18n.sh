#!/bin/bash
export LC_ALL="C"

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do
  TARGET="$(readlink "$SOURCE")"
  if [[ $SOURCE == /* ]]; then
    SOURCE="$TARGET"
  else
    DIR="$( dirname "$SOURCE" )"
    SOURCE="$DIR/$TARGET"
  fi
done
RDIR="$( dirname "$SOURCE" )"
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd ../ )"
DIR="$(dirname "${DIR}")"

NAME=$(echo ${DIR}|tr '/' ' '|awk '{print $NF}'|tr '-' '_')

POT=${DIR}/languages/${NAME}.pot

if [ ! -d ${DIR}/languages ]; then
    mkdir -p ${DIR}/languages
fi

php -e ${HOME}/docs/wordpress/i18n/makepot.php wp-plugin ${DIR} ${POT}
TMP=`tempfile`
sed -e 's/FULL NAME <EMAIL@ADDRESS>/Marcin Pietrzak <marcin@iworks.pl>/' ${POT} > ${TMP}
cp ${TMP} ${POT}
sed -e 's/FIRST AUTHOR <EMAIL@ADDRESS>/Marcin Pietrzak <marcin@iworks.pl>/' ${POT} > ${TMP}
cp ${TMP} ${POT}
sed -e 's/LANGUAGE <LL@li.org>/Marcin Pietrzak <marcin@iworks.pl>/' ${POT} > ${TMP}
cp ${TMP} ${POT}
rm ${TMP}

cd ${DIR}/languages

for ELEMENT in $(ls -1 *.po|sed -e 's/\.po//')
do
    echo ${DIR}/languages/${ELEMENT}.po
    msgmerge -U ${ELEMENT}.po ${NAME}.pot
    msgfmt --statistics -v ${ELEMENT}.po -o ${ELEMENT}.mo
    echo
done

