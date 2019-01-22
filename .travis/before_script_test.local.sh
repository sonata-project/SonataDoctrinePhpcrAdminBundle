#!/usr/bin/env bash
set -ev

TESTING_SCRIPTS_DIR=vendor/symfony-cmf/testing/bin
CONSOLE=${TESTING_SCRIPTS_DIR}/console

export KERNEL_CLASS="Sonata\\DoctrinePHPCRAdminBundle\\Tests\\Fixtures\\App\\Kernel"
echo '+++ create PHPCR +++'
${CONSOLE} doctrine:phpcr:init:dbal --drop --force -vvv
${CONSOLE} doctrine:phpcr:repository:init -vvv
