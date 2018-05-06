#!/usr/bin/env sh
set -ev

RELEVANT_FILES=$(git diff --name-only HEAD upstream/${TRAVIS_BRANCH} -- '*.rst')

if [[ -z ${RELEVANT_FILES} ]]; then echo -n 'KO'; exit 0; fi;
