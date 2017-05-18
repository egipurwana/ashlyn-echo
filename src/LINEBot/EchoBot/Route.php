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
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException;

class Route
{
    public function register(\Slim\App $app)
    {
	    $app->get('/',function(\Slim\Http\Request $req, \Slim\Http\Response $res){

		    	echo 'haha';
		    	
		    	$conn = $this->db;
		    	$sql = "INSERT INTO message (text) VALUES ('huhuy')";
				if ($conn->query($sql) === TRUE) {
					echo'haha';
					//$logger->info('New record created successfully');
				} else {
					echo'huhu';
					//$logger->info("Error: " . $sql);
				}
		    	
		    	echo 'huhu';
		    	/*$sql = "INSERT INTO heroku_4d31cca975d0dde.message (text) VALUES ('huhuy')";
				if ($conn->query($sql) === TRUE) {
					echo'haha';//$logger->info('New record created successfully');
				} else {
					echo'huhu';
					//$logger->info("Error: " . $sql);
				}*/
		    
				/*
				$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
				$server = $url["host"];
				$username = $url["user"];
				$password = $url["pass"];
				$db = substr($url["path"], 1);
				$conn = new mysqli($server, $username, $password, $db);
				//$sql = "INSERT INTO heroku_4d31cca975d0dde.message (text) VALUES ('".$event->getText()."')";
				$sql = "INSERT INTO heroku_4d31cca975d0dde.message (text) VALUES ('huhuy')";
				if ($conn->query($sql) === TRUE) {
					//$logger->info('New record created successfully');
				} else {
					//$logger->info("Error: " . $sql);
				}
				*/
				//$conn->close();
			
				
	    });
        $app->post('/callback', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {
            /** @var \LINE\LINEBot $bot */
            $bot = $this->bot;
            /** @var \Monolog\Logger $logger */
            $logger = $this->logger;

            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }

            // Check request with signature and parse request
            try {
                $events = $bot->parseEventRequest($req->getBody(), $signature[0]);
            } catch (InvalidSignatureException $e) {
                return $res->withStatus(400, 'Invalid signature');
            } catch (UnknownEventTypeException $e) {
                return $res->withStatus(400, 'Unknown event type has come');
            } catch (UnknownMessageTypeException $e) {
                return $res->withStatus(400, 'Unknown message type has come');
            } catch (InvalidEventRequestException $e) {
                return $res->withStatus(400, "Invalid event request");
            }

            foreach ($events as $event) {
                if (!($event instanceof MessageEvent)) {
                    $logger->info('Non message event has come');
                    continue;
                }

                if (!($event instanceof TextMessage)) {
                    $logger->info('Non text message has come');
                    continue;
                }
                /*$sql = "SELECT id, firstname, lastname FROM MyGuests";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
				    while($row = $result->fetch_assoc()) {
				        echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
				    }
				} else {
				    echo "0 results";
				}
				try {
					$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
					$server = $url["host"];
					$username = $url["user"];
					$password = $url["pass"];
					$db = substr($url["path"], 1);
					$conn = new mysqli($server, $username, $password, $db);
					//$sql = "INSERT INTO heroku_4d31cca975d0dde.message (text) VALUES ('".$event->getText()."')";
					$sql = "INSERT INTO heroku_4d31cca975d0dde.message (text) VALUES ('huhuy')";
					if ($conn->query($sql) === TRUE) {
						//$logger->info('New record created successfully');
					} else {
						//$logger->info("Error: " . $sql);
					}
					//$conn->close();
				}catch ($e) {
					//$replyText = $event->getText();
	                //$resp = $bot->replyText($event->getReplyToken(), $replyText." Tapi ada error di engine");
	            }
				*/

				/*$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
				$server = $url["host"];
				$username = $url["user"];
				$password = $url["pass"];
				$db = substr($url["path"], 1);
				$conn = new mysqli($server, $username, $password, $db);
				*/
				
                $replyText = $event->getText();
                $logger->info('Reply text: ' . $replyText);
                $resp = $bot->replyText($event->getReplyToken(), $replyText);
                $logger->info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
            }

            $res->write('OK');
            return $res;
        });
    }
}
