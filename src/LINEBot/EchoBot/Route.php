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
	    $app->get('/',function(\Slim\Http\Request $req, \Slim\Http\Response $res) use ($app){
			//$app->setCookie('foo', 'bar', '2 days');
			$this['cookies']->set('foo', 'bar');
	    });
	    $app->get('/training',function(\Slim\Http\Request $req, \Slim\Http\Response $res) use ($app){
		    //$cookies = $this->$app->getCookie('foo');
		    //echo $cookies;
			//require_once(__DIR__ . '/../../../public/datatrain.php');			
	    });
		/*
		if(!empty($_POST))
		{
			//database settings
			include "db_config.php";
			foreach($_POST as $field_name => $val)
			{
				//clean post values
				$field_userid = strip_tags(trim($field_name));
				$val = strip_tags(trim(mysql_real_escape_string($val)));
		
				//from the fieldname:user_id we need to get user_id
				$split_data = explode(':', $field_userid);
				$user_id = $split_data[1];
				$field_name = $split_data[0];
				if(!empty($user_id) && !empty($field_name) && !empty($val))
				{
					//update the values
					mysql_query("UPDATE user_details SET $field_name = '$val' WHERE user_id = $user_id") or mysql_error();
					echo "Updated";
				} else {
					echo "Invalid Requests";
				}
			}
		} else {
			echo "Invalid Requests";
		}
		*/
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

			//$src = print_r($events,true);

            foreach ($events as $event) {
	            
	            //$src = print_r($event,true);
	            
	            if ($event instanceof MessageEvent) {
                    if ($event instanceof TextMessage) {
						$conn = $this->db;
				    	$sql = "INSERT INTO message (text) VALUES ('".$event->getText().")";
						if ($conn->query($sql) === TRUE) {
							$logger->info('New record created successfully');
						} else {
							$logger->info("Error: " . $sql);
						}
						
						$sql = "SELECT * FROM phrase where phrase like '%".$event->getText()."%'";
						$result = $conn->query($sql);						
						if ($result->num_rows > 0) {
						    while($row = $result->fetch_assoc()) {
						        $sql1 = "SELECT * FROM relation where idphrase = '".$row["id"]."'";
								$result1 = $conn->query($sql1);						
								if ($result1->num_rows > 0) {
								    while($row1 = $result1->fetch_assoc()) {
								        $sql2 = "SELECT * FROM answer where id = '".$row1["idanswer"]."'";
										$result2 = $conn->query($sql2);						
										if ($result2->num_rows > 0) {
										    while($row2 = $result2->fetch_assoc()) {
											    if($row2["phrase"] != '|time|'){
													if (strpos($row2["phrase"], '|name|') == false) {
														$resp = $bot->replyText($event->getReplyToken(), $row2["phrase"]);
													}
													//else if (strpos($row2["phrase"], 'ya?') == true) {
													//	$resp = $bot->replyText($event->getReplyToken(), 'hehe, kok tau sih?');
													//}
													else{
														//$src = print_r($event,true);
														//$resp = $bot->replyText($event->getReplyToken(), $src);
														if($event->getType() == "user"){
															$resp = $bot->getProfile($event->getUserId());
															if ($resp->isSucceeded()) {
															    $profile = $resp->getJSONDecodedBody();
															    $kata = str_replace("|name|",$profile['displayName'],$row2["phrase"]);   
															    $resp = $bot->replyText($event->getReplyToken(), $kata." ".$event->getType());
															}
														}else{
															$kata2 = str_replace("|name|",'kamu',$row2["phrase"]);   
														    $resp = $bot->replyText($event->getReplyToken(), $kata2);
														}											
													}
												}else{
													$resp = $bot->replyText($event->getReplyToken(), 'Sekarang jam '.date("h:i:sa"));
											    }
										    }
										} else {
											//$resp = $bot->replyText($event->getReplyToken(), 'Aduh aku belum bisa jawab, pertanyaannya terlalu berat kak :( 3');
										}
								    }
								} else {
									//$resp = $bot->replyText($event->getReplyToken(), 'Aduh aku belum bisa jawab, pertanyaannya terlalu berat kak :( 2');
								}
						    }
						} else {
							//$resp = $bot->replyText($event->getReplyToken(), 'Aduh aku belum bisa jawab, pertanyaannya terlalu berat kak :( 1');
						}
						
						if ($event->getText() != "ingkah maneh rey"){
							//$resp = $bot->leaveRoom('<roomId>');
							//normal
							//$replyText = $event->getText();
							//$resp = $bot->replyText($event->getReplyToken(), $replyText);
						}else{
							//$replyTexts = $src;
							//$replyTexts = $event->getType()." ".$event->getUserId()." ".$event->getGroupId();
							
							if($event->getType()=='user'){
								$resp = $bot->leaveRoom($event->getUserId());	
								$replyTexts = $event->getUserId();
							}else if($event->getType()=='group'){
								$resp = $bot->leaveGroup($event->getGroupId());								
								$replyTexts = $event->getGroupId();
							}
							
								
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
