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

class Connection
{
    public function register(\Slim\App $app)
    {
        $container = $app->getContainer();
        
        $container['db'] = function ($c) {
	        $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
			$server = $url["host"];
			$username = $url["user"];
			$password = $url["pass"];
			$db = substr($url["path"], 1);
			$conn = new mysqli($server, $username, $password, $db);
	        return $conn;
        };
    }
}
