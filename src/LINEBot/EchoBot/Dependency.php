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

namespace LINE\LINEBot\EchoBot;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Automattic\WooCommerce\Client;


class Dependency
{
    public function register(\Slim\App $app)
    {
        $container = $app->getContainer();

        $container['logger'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Logger::DEBUG));
            return $logger;
        };
        
		$container['session'] = function ($c) {
		  return new \SlimSession\Helper;
		};
		
		$container['wcapi'] = function($c){
			$woocommerce = new Client(
			    'http://www.ecimol.com', 
			    'ck_f1365694ff4885fcc2d7299352a5cdac7766148c', 
			    'cs_289648a34305bbd59d9dedd5e1185ea3ecec7959',
			    [
			        'wp_api' => true,
			        'version' => 'wc/v1',
			    ]
			);

			return $woocommerce;
		};
        
        $container['bot'] = function ($c) {
            $settings = $c->get('settings');
            $channelSecret = $settings['bot']['channelSecret'];
            $channelToken = $settings['bot']['channelToken'];
            $apiEndpointBase = $settings['apiEndpointBase'];
            $bot = new LINEBot(new CurlHTTPClient($channelToken), [
                'channelSecret' => $channelSecret,
                'endpointBase' => $apiEndpointBase, // <= Normally, you can omit this
            ]);
            return $bot;
        };
        
    }
}
