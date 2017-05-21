<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
 
date_default_timezone_set('Asia/Jakarta');
 
use LINE\LINEBot\EchoBot\Dependency;
use LINE\LINEBot\EchoBot\Route;
use LINE\LINEBot\EchoBot\Setting;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/LINEBot/EchoBot/Connection.php';

$setting = Setting::getSetting();
$app = new Slim\App($setting);

$app->add(new \Slim\Middleware\Session([
  'name' => 'ashlyn_session',
  'lifetime' => '1 hour',
  'secure' => true
]));

(new Connection())->register($app);
(new Dependency())->register($app);
(new Route())->register($app);

$app->run();
