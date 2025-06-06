<?php

declare(strict_types=1);

/*
 *     This file is part of Auto Trader.
 *
 *     (c) James IT Services | Louis Hage <louis@jamesitservices.com>
 *
 *     Copyright 2000-2023, James IT Services Ltd
 *     All rights reserved.
 */

if (! isset($_POST)) {
    exit('No post');
}
$data = json_decode(file_get_contents('php://input'), false, 5);
if ('refs/heads/main' != $data->ref && 'refs/heads/master' != $data->ref) {
    exit('Not pushed to master');
}
exec('cd .. && git pull 2>&1', $output);
file_put_contents('git.log', $output);
var_dump($output);
