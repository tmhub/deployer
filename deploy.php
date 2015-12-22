<?php
$deployPath = getcwd() . '/build';
if (!is_dir($deployPath)) {
    mkdir($deployPath);
}
localServer('local')
    ->env('deploy_path', $deployPath)
;

option(
    'package',
    null,
    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
    'Package to deploy. (Default: --package=tm/core:*)'
);

env('composer', function () {
    if (commandExist('composer')) {
        $composer = 'composer';
    } else {
        run("cd {{deploy_path}} && curl -sS https://getcomposer.org/installer | php");
        $composer = 'php composer.phar';
    }
    return $composer;
});

env('option_package', function () {

    $package = $default = ['tm/core:*'];
    if (input()->hasOption('package')) {
        $package = input()->getOption('package');

        if (empty($package)) {
            $package = $default;
        } else {
            $package = explode(',', $package);
        }
    }
    return $package;
});
task('deploy:cleanup', function () {
    //run("if [ ! -d {{deploy_path}} ]; then mkdir -p {{deploy_path}}; fi");
    run(
        "cd {{deploy_path}}"
        . " && rm -rf composer.lock composer.json htdocs"
    );
});

task('deploy:update_code', function () {
    $release = date('YmdHis');
    $composer = env('composer');
    run(
        "cd {{deploy_path}}"
        . " && $composer init -n  --name='tm/demo{$release}' --type='magento-module' -s dev"
        . " && $composer config repositories.firegento composer http://packages.firegento.com"
        . " && $composer config repositories.tmhub composer http://tmhub.github.io/packages/"
        . " && $composer config discard-changes true"
    );
    run("if [ ! -d {{deploy_path}}/htdocs ]; then mkdir -p {{deploy_path}}/htdocs; fi");

    if (!commandExist('jq')) {
        run("sudo apt-get install jq");
    }
    $jq = 'jq';
    $jqOptions = '';//'--indent 4';
    run(
        "cd {{deploy_path}}"
        . " && mv -f composer.json composer.json.old"
        // . " && $jq $jqOptions '.extra." . '"magento-root-dir" = "htdocs"'. "' composer.json.old  > composer.json"
        . " && $jq $jqOptions '.extra.magentorootdir = " . '"htdocs"' . "' composer.json.old | sed -r 's/magentorootdir/magento-root-dir/g' > composer.json"
        . " && mv -f composer.json composer.json.old"
        // . " && $jq $jqOptions '.extra." . '"magento-deploystrategy" = "copy"'. "' composer.json.old  > composer.json"
        . " && $jq $jqOptions '.extra.magentodeploystrategy = " . '"copy"' . "' composer.json.old | sed -r 's/magentodeploystrategy/magento-deploystrategy/g' > composer.json"
        . " && mv -f composer.json composer.json.old"
        // . " && $jq $jqOptions '.extra." . '"magento-force"'. " = true' composer.json.old  > composer.json"
        . " && $jq $jqOptions '.extra.magentoforce = true' composer.json.old | sed -r 's/magentoforce/magento-force/g' > composer.json"
        . " && rm composer.json.old"
    );
    $packages = [
        'symfony/console:2.4',
        'magento-hackathon/composer-command-integrator:*',
        'magento-hackathon/magento-composer-installer:*'
    ];
    foreach ($packages as $package) {
        run("cd {{deploy_path}} && $composer require -n --no-update $package");
    }
    run("cd {{deploy_path}} && $composer update");

    $packages = env('option_package');
    foreach ($packages as $package) {
        run("cd {{deploy_path}} && $composer require -n --no-update $package");
    }
    run("cd {{deploy_path}} && $composer update");

    $packages = env('option_package');
    $package = current($packages);
    $version = '';
    if (strstr($package, ':')) {
        list($package, $version) = explode(':', $package, 2);
    }
    list($vendor, $package) = explode('/', $package);

    if (empty($version) || '*' == $version) {
        $version = run("cd {{deploy_path}}/vendor/$vendor/$package && git describe --abbrev=0 --tags")->toString();
    }
    $filename = "$package-$version.zip";

    run("if [ ! -d {{deploy_path}}/bin ]; then mkdir -p {{deploy_path}}/bin; fi");
    if (!commandExist('zip')) {
        run("sudo apt-get install zip");
    }
    $zip = 'zip';

    run("cd {{deploy_path}}/htdocs  && $zip -r {{deploy_path}}/bin/$filename *");
});

/**
 * Main task
 */
task('deploy', [
    'deploy:cleanup',
    'deploy:update_code'
])->desc('Deploy magento-module using magento-composer-installer');

task('test', function () {
});
