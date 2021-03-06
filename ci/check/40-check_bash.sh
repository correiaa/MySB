#!/bin/bash
# ----------------------------------
#  __/\\\\____________/\\\\___________________/\\\\\\\\\\\____/\\\\\\\\\\\\\___
#   _\/\\\\\\________/\\\\\\_________________/\\\/////////\\\_\/\\\/////////\\\_
#	_\/\\\//\\\____/\\\//\\\____/\\\__/\\\__\//\\\______\///__\/\\\_______\/\\\_
#	 _\/\\\\///\\\/\\\/_\/\\\___\//\\\/\\\____\////\\\_________\/\\\\\\\\\\\\\\__
#	  _\/\\\__\///\\\/___\/\\\____\//\\\\\________\////\\\______\/\\\/////////\\\_
#	   _\/\\\____\///_____\/\\\_____\//\\\____________\////\\\___\/\\\_______\/\\\_
#		_\/\\\_____________\/\\\__/\\_/\\\______/\\\______\//\\\__\/\\\_______\/\\\_
#		 _\/\\\_____________\/\\\_\//\\\\/______\///\\\\\\\\\\\/___\/\\\\\\\\\\\\\/__
#		  _\///______________\///___\////__________\///////////_____\/////////////_____
#			By toulousain79 ---> https://github.com/toulousain79/
#
######################################################################
#
#	Copyright (c) 2013 toulousain79 (https://github.com/toulousain79/)
#	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
#	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
#	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
#	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#	--> Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
#
##################### FIRST LINE #####################################

if [ -z "${vars}" ] || [ "${vars}" -eq 0 ]; then
    # shellcheck source=ci/check/00-load_vars.bsh
    . "/builds/${CI_PROJECT_PATH}/ci/check/00-libs.sh"
else
    nReturn=${nReturn}
fi

gfnCopyProject

sFilesListSh="$(grep -IRl "\(#\!/bin/\|shell\=\)sh" --exclude-dir ".git" --exclude-dir ".vscode" --exclude "funcs_*" "${sDirToScan}/")"
if [ -n "${sFilesListSh}" ]; then
    echo && echo -e "${CBLUE}*** Check Syntax with Shellcheck (sh) ***${CEND}"
    for sFile in ${sFilesListSh}; do
        if ! shellcheck -s sh -f tty -S error -S warning -e SC2154 "${sFile}"; then
            echo -e "${CYELLOW}${sFile}:${CEND} ${CRED}Failed${CEND}"
            nReturn=$((nReturn + 1))
        else
            echo -e "${CYELLOW}${sFile}:${CEND} ${CGREEN}Passed${CEND}"
        fi
    done
fi

sFilesListBash="$(grep -IRl "\(#\!/bin/\|shell\=\)bash" --exclude-dir ".git" --exclude-dir ".vscode" --exclude-dir ".vscode" "${sDirToScan}/")"
if [ -n "${sFilesListBash}" ]; then
    echo && echo -e "${CBLUE}*** Check Syntax with Shellcheck (bash) ***${CEND}"
    for sFile in ${sFilesListBash}; do
        if ! shellcheck -s bash -f tty -S error -S warning -e SC2154 "${sFile}"; then
            echo -e "${CYELLOW}${sFile}:${CEND} ${CRED}Failed${CEND}"
            nReturn=$((nReturn + 1))
        else
            echo -e "${CYELLOW}${sFile}:${CEND} ${CGREEN}Passed${CEND}"
        fi
    done
fi

sFuncsList="$(grep -R -h -E "^[A-Za-z]+[A-Za-z0-9]*(\(\)\ \{)" "${sDirToScan}/inc/" | cut -d '(' -f 1 | sort)"
if [ -n "${sFuncsList}" ]; then
    echo && echo -e "${CBLUE}*** Check for orphan functions ***${CEND}"
    for func in ${sFuncsList}; do
        [ "${func}" == "gfnIblocklistXmlGenerate" ] && continue
        nCount=$(grep -R "${func}" "${sDirToScan}/" | wc -l)
        case "${nCount}" in
            1)
                echo -e "${CYELLOW}${func}:${CEND} ${CRED}Failed${CEND}"
                nReturn=$((nReturn + 1))
                ;;
            *)
                echo -e "${CYELLOW}${func}:${CEND} ${CGREEN}Passed${CEND}"
                ;;
        esac
    done
fi

sFilesListSh="$(grep -IRl "\(#\!/bin/\|shell\=\)sh" --exclude-dir ".git" --exclude-dir ".vscode" --exclude-dir "ci" "${sDirToScan}/")"
sFilesListBash="$(grep -IRl "\(#\!/bin/\|shell\=\)bash" --exclude-dir ".git" --exclude-dir ".vscode" --exclude-dir "ci" "${sDirToScan}/")"
sFilesList="${sFilesListSh} ${sFilesListBash}"
if [ -n "${sFilesList}" ]; then
    echo && echo -e "${CBLUE}*** Check scripts with 'set -n' ***${CEND}"
    for file in ${sFilesList}; do
        sed -i '/includes_before/d' "${file}"
        sed -i '/includes_after/d' "${file}"
        sed -i '/#!\/bin\/bash/d' "${file}"
        sed -i '1iset -n' "${file}"
        echo "set +n" >>"${file}"
        dos2unix "${file}" &>/dev/null
        if (bash "${file}"); then
            echo -e "${CYELLOW}${file}:${CEND} ${CGREEN}Passed${CEND}"
        else
            echo -e "${CYELLOW}${file}:${CEND} ${CRED}Failed${CEND}"
            nReturn=$((nReturn + 1))
        fi
    done
fi

export nReturn

##################### LAST LINE ######################################
