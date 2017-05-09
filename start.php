<?php
ini_set('display_errors', 'on');
use Workerman\Worker;

if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension.\n");
}

if(!extension_loaded('posix'))
{
    exit("Please install posix extension.\n");
}

define('GLOBAL_START', 1);

require_once __DIR__ . '/vendor/autoload.php';

foreach(glob(__DIR__.'/Applications/*/start*.php') as $start_file)
{
    require_once $start_file;
}
Worker::runAll();
