<?php

require __DIR__ . "/../vendor/autoload.php";

use Rmdevx\TestSessionMembers\Db;
use Rmdevx\TestSessionMembers\Duplicator;

try {

    $duplicator = new Duplicator(
        new Db($_ENV["MYSQL_DATABASE"], $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"])
    );

    $duplicator->correctMemberRecords();

} catch (Exception $e) {
    print_r($e);
}