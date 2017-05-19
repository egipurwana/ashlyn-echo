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

use LINE\LINEBot\Event\BeaconDetectionEvent;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\JoinEvent;
use LINE\LINEBot\Event\LeaveEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\VideoMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\UnfollowEvent;

use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;

use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException;
use ReflectionClass;

class Route
{
    public function register(\Slim\App $app)
    {
	    $app->get('/',function(\Slim\Http\Request $req, \Slim\Http\Response $res){
							
	    });
        $app->post('/callback', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {
            $bot = $this->bot;
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
	            
	            if ($event instanceof MessageEvent) {
                    if ($event instanceof TextMessage) {
						$conn = $this->db;
						//".$signature."'
				    	$sql = "INSERT INTO message (text) VALUES ('".$event->getText().")";
						if ($conn->query($sql) === TRUE) {
							$logger->info('New record created successfully');
						} else {
							$logger->info("Error: " . $sql);
						}
						
						if ($event->getText() != "ingkah maneh rey"){
							//$resp = $bot->leaveRoom('<roomId>');
							$replyText = $event->getText();
							$resp = $bot->replyText($event->getReplyToken(), $replyText);
						}else{
							$replyTexts = $event->getTypes()." ".$event->getSourceIds();                
							$resp = $bot->replyText($event->getReplyToken(), $replyTexts);	
						}
                    } elseif ($event instanceof StickerMessage) {
	                    $stickerBuilder = new StickerMessageBuilder($event->getPackageId(), $event->getStickerId());
		                $resp = $bot->replyMessage($event->getReplyToken(),$stickerBuilder);
		                
		                //$replyText = "Kalo ga ngirim stiker 'absolutely state of the art' mending ga usah deh";                
						//$resp = $bot->replyText($event->getReplyToken(), $replyText);
                    } elseif ($event instanceof LocationMessage) {
		                $locBuilder = new LocationMessageBuilder('DenganSenangHati HQ', 'Jl. Bojong Wetan', '-6.891063', '107.632794');
		                $resp = $bot->replyMessage($event->getReplyToken(),$locBuilder);
		                
		                //$replyText = "Lokasi apa nih?";                
						//$resp = $bot->replyText($event->getReplyToken(), $replyText);
                    } elseif ($event instanceof ImageMessage) {
		                $imgBuilder = new ImageMessageBuilder('https://g-search4.alicdn.com/bao/uploaded/i3/TB1ygnzHVXXXXcoXFXXXXXXXXXX_!!0-item_pic.jpg_240x240.jpg','https://g-search4.alicdn.com/bao/uploaded/i3/TB1ygnzHVXXXXcoXFXXXXXXXXXX_!!0-item_pic.jpg_240x240.jpg');
						$resp = $bot->replyMessage($event->getReplyToken(),$imgBuilder);
		                
			            //$replyText = "Kirim gambarnya yang lebih okei dong";                
						//$resp = $bot->replyText($event->getReplyToken(), $replyText);
                    } elseif ($event instanceof AudioMessage) {
		                $audioBuilder = new AudioMessageBuilder('https://ashlyn-bot.herokuapp.com/public/sample.m4a',10000);
						$resp = $bot->replyMessage($event->getReplyToken(),$audioBuilder);
		                
		                //$replyText = "Suaranya bagus, tapi lebih bagus diem deh kayanya";                
						//$resp = $bot->replyText($event->getReplyToken(), $replyText);
                    } elseif ($event instanceof VideoMessage) {
		                $vidBuilder = new VideoMessageBuilder('https://clips.vorwaerts-gmbh.de/big_buck_bunny.mp4','https://s-media-cache-ak0.pinimg.com/originals/5c/21/ad/5c21ad4c0d9ef944369b01030119bfd7.jpg');
						$resp = $bot->replyMessage($event->getReplyToken(),$vidBuilder);
		                
		                //$replyText = "Duh kirimnya video yang lebih berguna dong";                
						//$resp = $bot->replyText($event->getReplyToken(), $replyText);
                    } else {
                        // Just in case...
                        $logger->info('Unknown message type has come');
                        continue;
                    }
                } elseif ($event instanceof UnfollowEvent) {
	                $replyText = "Kok Unfollow sih?"; ;                
					$resp = $bot->replyText($event->getReplyToken(), $replyText);
                } elseif ($event instanceof FollowEvent) {
	                $replyText = "Thanks udah follow!"; ;                
					$resp = $bot->replyText($event->getReplyToken(), $replyText);
                } elseif ($event instanceof JoinEvent) {
	                $replyText = "Thanks udah join!";               
					$resp = $bot->replyText($event->getReplyToken(), $replyText);
                } elseif ($event instanceof LeaveEvent) {
	                $replyText = "Kok Leave sih?";               
					$resp = $bot->replyText($event->getReplyToken(), $replyText);
                } elseif ($event instanceof PostbackEvent) {
	                $replyText = "Postback detected";                
					$resp = $bot->replyText($event->getReplyToken(), $replyText);
                } elseif ($event instanceof BeaconDetectionEvent) {
	                $replyText = "Beacon detected";                
					$resp = $bot->replyText($event->getReplyToken(), $replyText);
                } else {
                    // Just in case...
                    $logger->info('Unknown event type has come');
                    continue;
                }
	            
                /*if (!($event instanceof MessageEvent)) {
                    $logger->info('Non message event has come');
                    continue;
                }

                if (!($event instanceof TextMessage)) {
                    $logger->info('Non text message has come');
                    continue;
                }
                
		    	$conn = $this->db;
		    	$sql = "INSERT INTO message (text) VALUES ('".$event->getText()." ".$signature."')";
				if ($conn->query($sql) === TRUE) {
					$logger->info('New record created successfully');
				} else {
					$logger->info("Error: " . $sql);
				}
                
                $replyText = $event->getText();                
                $resp = $bot->replyText($event->getReplyToken(), $replyText);*/
            }

            $res->write('OK');
            return $res;
        });
    }
}
