<?php
require_once(__DIR__.'/vendor/autoload.php');

if (php_sapi_name() != 'cli') {
    exit("Please run in cli! \n");
}

$getOpt = new \GetOpt\GetOpt([
    \GetOpt\Option::create('c', 'config', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setDescription('The path of config json file'),
    \GetOpt\Option::create('l', 'cache', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
        ->setDescription('is cache report?')
        ->setDefaultValue(true),
    \GetOpt\Option::create('?','help', \GetOpt\GetOpt::NO_ARGUMENT)
        ->setDescription('Show this help and quit'),
]);
$getOpt->process();

$config_path = $getOpt->getOption('config');
$save_cache = $getOpt->getOption('cache', true);

if (!$config_path) {
    exit(PHP_EOL. $getOpt->getHelpText());
}

$config = json_decode(file_get_contents($config_path), 1);
if (empty($config)) {
    exit("can not open config file,". $config_path);
}



$temp_dir = array_get($config, 'temp-dir');
$report_dir = $temp_dir . '/report';
$client = new \MMHK\GmailHelper(array_get($config, 'credentials-dir'), $temp_dir);

$q = array_get($config, 'gmail-query');
$limit = array_get($config, 'limit', 20);
$client->run($limit, $q);
$client->export_error_report($report_dir);
if (!$save_cache) {
    $client->clearTempFiles();
}
