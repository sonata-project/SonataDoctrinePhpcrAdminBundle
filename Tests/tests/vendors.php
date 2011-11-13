#!/usr/bin/env php
<?php

set_time_limit(0);

$vendorDir = __DIR__.'/../../vendor';
if (!is_dir($vendorDir)) {
  mkdir($vendorDir);
}

$deps = array(
    array('symfony', 'git://github.com/symfony/symfony.git', isset($_SERVER['SYMFONY_VERSION']) ? $_SERVER['SYMFONY_VERSION'] : 'origin/master', ''),
    array('knpmenu', 'git://github.com/knplabs/KnpMenu.git', 'origin/master', ''),
    array('Sonata/AdminBundle', 'git://github.com/sonata-project/SonataAdminBundle.git', 'origin/master', ''),
    array('gaufrette', 'git://github.com/knplabs/Gaufrette.git', 'origin/master',''),
    array('symfony-cmf', 'git://github.com/symfony-cmf/symfony-cmf.git', 'origin/master', 'submodule update --init --recursive')
    
);

foreach ($deps as $dep) {
    list($name, $url, $rev, $cmd) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone --quiet %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }

    if($cmd == '') {
        system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
    } else {
        system(sprintf('cd %s && git fetch origin && git reset --hard %s && git %s', escapeshellarg($installDir), escapeshellarg($rev), $cmd));
    }
}

//updating symfony-cmf stuff to latest version
system(sprintf('cd %s/symfony-cmf/vendor/doctrine-phpcr-odm && git checkout origin/master', $vendorDir));
system(sprintf('cd %s/symfony-cmf/vendor/doctrine-phpcr-odm/lib/vendor/jackalope && git checkout origin/master', $vendorDir));
system(sprintf('cd %s/symfony-cmf/vendor/doctrine-phpcr-odm/lib/vendor/jackalope/lib/phpcr && git checkout origin/master', $vendorDir));
