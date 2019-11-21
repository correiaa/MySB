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

if [ -z "${vars}" ] || [ "$vars" -eq 0 ]; then
    # shellcheck source=ci/scripts/00-load_vars.bsh
    source "$(dirname "$0")/00-load_vars.bsh"
fi
if [[ -n ${1} ]]; then
    rm -rf /tmp/shellcheck_scan
    if [[ -d ${1} ]]; then
        rsync -av --exclude '.git' "${1}" /tmp/shellcheck_scan
        sDirToScan="/tmp/shellcheck_scan"
    else
        echo -e "${CYELLOW}${1} not a valid directory:${CEND} ${CRED}Failed${CEND}"
        exit
    fi
else
    if [[ -f /.dockerenv ]]; then
        if [[ -n ${CI_PROJECT_PATH} ]]; then
            sDirToScan="/builds/${CI_PROJECT_PATH}"
        else
            echo -e "${CYELLOW}Secret Variable \$CI_PROJECT_PATH:${CEND} ${CRED}Failed${CEND}"
            exit
        fi
    else
        echo -e "${CYELLOW}You are not in 'project_validation' images:${CEND} ${CRED}Failed${CEND}"
        exit
    fi
fi

export sDirToScan

##################### LAST LINE ######################################
