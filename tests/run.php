#!/usr/bin/env php
<?php

// Find test libraries.
$libraries = array();
foreach (new DirectoryIterator(dirname(__DIR__) . "/tests") as $entry)
{
    if (!$entry->isDot()) $libraries  []= $entry->getPathname();
    unset($entry); // prevent leak into global scope.
}

// Append test libraries to include path.
set_include_path(
    implode(PATH_SEPARATOR,
        array_merge(
            array_filter(explode(PATH_SEPARATOR, get_include_path())),
            $libraries
        )
    )
);

require(dirname(__DIR__) . "/vendor/phpunit/phpunit.php");
