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
	
	public function CallAPI($method, $url, $data = false)
	{
	    $curl = curl_init();
	
	    switch ($method)
	    {
	        case "POST":
	            curl_setopt($curl, CURLOPT_POST, 1);	
	            if ($data)
	                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	            break;
	        case "PUT":
	            curl_setopt($curl, CURLOPT_PUT, 1);
	            break;
	        default:
	            if ($data)
	                $url = sprintf("%s?%s", $url, http_build_query($data));
	    }
	
	    // Optional Authentication:
	    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    $result = curl_exec($curl);
	    curl_close($curl);
	
	    return $result;
	}
	
    public function register(\Slim\App $app)
    { 		
	    $app->get('/',function(\Slim\Http\Request $req, \Slim\Http\Response $res) use ($app){
			echo 'Welcome to ashlyn :)';
	    });
	    $app->get('/training',function(\Slim\Http\Request $req, \Slim\Http\Response $res) use ($app){
			//require_once(__DIR__ . '/../../../public/datatrain.php');
			
			$responses = self::CallAPI("GET", "https://quark.timeshift.tech/imageSearch/imagesearch/api?url=https://s3-ap-southeast-1.amazonaws.com/ashlyn/Uda336c76d0bab90d19e2fd3ae4313e3d-gambar-2017255.jpg");
			$ismatch = json_decode($responses);
			
			//print_r($ismatch);
			//echo '<br>';
			//echo '<br>';
			
			$array = json_decode(json_encode($ismatch),true);
			print_r($array);
			
			echo '<br>';
			echo '<br>';
			
			echo $array['matches']['match0']['name'];
			echo $array['matches']['match1']['name'];
			
			/*echo count($ismatch->{'matches'});
			echo $ismatch->{'matches'}->{'match0'};
			echo $ismatch->{'matches'}->{'match0'}->{'name'};
			echo $ismatch->{'matches'}->{'match0'}->{'price'};
			echo count($ismatch);*/
			
	    });
        $app->post('/callback', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {
            $bot = $this->bot;
            $logger = $this->logger;
            $session = $this->session;
			$conn = $this->db;
			
            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }

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
	            if ($event instanceof MessageEvent) {
                    if ($event instanceof TextMessage) {
						
				    	$sqlxxx = "INSERT INTO message (text) VALUES ('".$event->getText()."')";
						if ($conn->query($sqlxxx) === TRUE) {
							$logger->info('New record created successfully');
						} else {
							$logger->info("Error: " . $sqlxxx);
						}
						
						if ($event->getText() == "keluar kamu ashlyn"){
							if($event->getType()=='group'){
								$resp = $bot->leaveGroup($event->getGroupId());								
								$replyTexts = $event->getGroupId();
							}
						}
						
						$sql = "SELECT * FROM trainer where iduser = '".$event->getUserId()."'";
						$result = $conn->query($sql);						
						if ($result->num_rows > 0) {
							if ($event->getText() != "training end"){							
								while($row = $result->fetch_assoc()) {
								    $trainingmode = true;
								    $question = $row["training_mode"];
								    $trainerid = $row["iduser"];
								    $questionid = $row["idquestion"];
								    $answerid = $row["idanswer"];
							    }
							}else{
								$sql = "DELETE FROM trainer WHERE iduser = '".$event->getUserId()."'";
								if ($conn->query($sql) === TRUE) {
									$resp = $bot->replyText($event->getReplyToken(), "Terima kasih udah ngajarin aku, semoga besok aku segera jadi lebih baik :*");
								} else {
									$resp = $bot->replyText($event->getReplyToken(), "Aku belum mau pisah sama kamu, coba lebih keras lagi");
								}						
								
								//$resp = $bot->replyText($event->getReplyToken(), "MODE TRAINING SUDAH BERAKHIR, TERIMA KASIH!");
								$trainingmode = false;
								$question = 1;
							}
						    
						}
						
						if($trainingmode == true){
							if($question == 1){
								$sqlxxx = "INSERT IGNORE INTO phrase (phrase) VALUES ('".$event->getText()."')";
								if ($conn->query($sqlxxx) === TRUE) {
									$sqltrain = "SELECT * FROM phrase where phrase = '".$event->getText()."'";
									$result = $conn->query($sqltrain);
									if ($result->num_rows > 0) {
										while($row = $result->fetch_assoc()) {									
											
											try {
												$textBuilderResp = new TextMessageBuilder('Hmm');
												$response = $bot->pushMessage($event->getUserId(),$textBuilderResp);
												$deb = print_r($response->getRawBody(),true);
												$resp = $bot->replyText($event->getReplyToken(),"Hmms, aku harus jawab apa mas? ".$event->getUserId()." xx ".$deb);
											} catch (Exception $e) {
												$resp = $bot->replyText($event->getReplyToken(),$e->getMessage());
											} 
											
											$sqlxxy = "UPDATE trainer SET training_mode = 0, idquestion= ".$row['id']." WHERE iduser = '".$event->getUserId()."'";
											$result = $conn->query($sqlxxy);
										}
									}
									$row = $result->fetch_row();									
								} else {
									$resp = $bot->replyText($event->getReplyToken(),"Pertanyaan enggak masuk ".$sqlxxx);
								}
							}else if($question == 0){
								$sqlxxx = "INSERT INTO answer (phrase) VALUES ('".$event->getText()."')";
								if ($conn->query($sqlxxx) === TRUE) {
									$sqltrain = "SELECT * FROM answer where phrase = '".$event->getText()."'";
									$result = $conn->query($sqltrain);
									if ($result->num_rows > 0) {
										while($row = $result->fetch_assoc()) {
											$resp = $bot->replyText($event->getReplyToken(),"Okei, Aku mengerti sekarang. Pertanyaan lain dong");//.$row["id"]);

											$sqlxxy = "UPDATE trainer SET training_mode = 1, idanswer= ".$row['id']." WHERE iduser = '".$event->getUserId()."'";
											$result = $conn->query($sqlxxy);
		
											$sqlxxxx = "INSERT INTO relation (idphrase,idanswer) VALUES (".$questionid.",".$row['id'].")";
											if ($conn->query($sqlxxxx) === TRUE) {
												$imgBuilder = new TextMessageBuilder('Okei, Aku mengerti sekarang. Pertanyaan lain dong!');
												$resp = $bot->pushMessage($trainerid,$imgBuilder);
											}
										}
									}
								} else {
									$resp = $bot->replyText($event->getReplyToken(),"Jawaban enggak masuk ".$sqlxxx);
								}
							}
						}else{
							if ($event->getText() != "training start"){
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
															else{
																if($event->getType() == "user"){
																	$resp = $bot->getProfile($event->getUserId());
																	if ($resp->isSucceeded()) {
																	    $profile = $resp->getJSONDecodedBody();
																	    $kata = str_replace("|name|",$profile['displayName'],$row2["phrase"]);   
																	    $resp = $bot->replyText($event->getReplyToken(), $kata);
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
													//not found
												}
										    }
										} else {
											$resp = $bot->replyText($event->getReplyToken(), "Jawabnya apa sih? aku lupa");
											//not found
										}
								    }
								} else {
									$resp = $bot->replyText($event->getReplyToken(),$session->training);
									//not found
								}
							}else{
								$sql = "INSERT INTO trainer (iduser) VALUES ('".$event->getUserId()."')";
								if ($conn->query($sql) === TRUE) {
									$resp = $bot->replyText($event->getReplyToken(), "Terima kasih sudah mau jadi trainer aku mas, ayo mulai dengan pertanyaan pertama!");
								} else {
									$resp = $bot->replyText($event->getReplyToken(), "Maaf gagal, coba lagi");
								}
							}
						}	
                    } elseif ($event instanceof StickerMessage) {
	                    $stickerBuilder = new StickerMessageBuilder($event->getPackageId(), $event->getStickerId());
		                $resp = $bot->replyMessage($event->getReplyToken(),$stickerBuilder);
                    } elseif ($event instanceof LocationMessage) {
		                $locBuilder = new LocationMessageBuilder('DenganSenangHati HQ', 'Jl. Bojong Wetan', '-6.891063', '107.632794');
		                $resp = $bot->replyMessage($event->getReplyToken(),$locBuilder);
                    } elseif ($event instanceof ImageMessage) {
		                
		                $s3 = \Aws\S3\S3Client::factory();
						$bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');
		                //$upload = $s3->upload($bucket, $_FILES['userfile']['name'], fopen($_FILES['userfile']['tmp_name'], 'rb'), 'public-read');
		                //$upload = $s3->putObject($string, $bucketName, $uploadName, S3::ACL_PUBLIC_READ);
						//$upload->get('ObjectURL')
						
                    	$response = $bot->getMessageContent($event->getPackageId());
 						if ($response->isSucceeded()) {
 						    $date = date("YGi");
 						    $upload = $s3->upload('ashlyn', $event->getUserId().'-gambar-'.$date.'.jpg', $response->getRawBody(), 'public-read');
 						    $responsex = $upload->get('ObjectURL');
 						} else {
 						    $responsex = 'error';
 						} 						
 						
 						$responses = self::CallAPI("GET", "https://quark.timeshift.tech/imageSearch/imagesearch/api?url=".$responsex);
 						$ismatch = json_decode($responses);
 						
 						$src = print_r($ismatch,true);
 						$resp = $bot->replyText($event->getReplyToken(), $src);
 						
 						//$result = $ismatch->{'matches'}.length()+" ";
 						/*$result += $ismatch->{'matches'}->{'match0'}+" ";
 						$result += $ismatch->{'matches'}->{'match0'}->{'name'}+" ";
 						$result += $ismatch->{'matches'}->{'match0'}->{'price'}+" ";*/
 						
 						//$resp = $bot->replyText($event->getReplyToken(), $result);
 						
 						/*
 						if (isset($ismatch->{'is_matched'}) && $ismatch->{'is_matched'} == 1){
	 						//$vidBuilder = new ImageMessageBuilder($responsex, $ismatch->{'closest_match'});
	 						//$response = $bot->pushMessage($event->getUserId(),$vidBuilder);
	 						
 							$resp = $bot->replyText($event->getReplyToken(),  "Yang ini bukan? ".$ismatch->{'closest_match'}." nama produknya : ".$ismatch->{'name'}." harga : ".$ismatch->{'price'}." deskripsi : ".$ismatch->{'description'});
 						}else{
	 						$resp = $bot->replyText($event->getReplyToken(), "Gambar apaan tuh?");
 						}
 						*/
 						
                    } elseif ($event instanceof AudioMessage) {
		                $audioBuilder = new AudioMessageBuilder('https://ashlyn-bot.herokuapp.com/public/sample.m4a',10000);
						$resp = $bot->replyMessage($event->getReplyToken(),$audioBuilder);
                    } elseif ($event instanceof VideoMessage) {
		                $vidBuilder = new VideoMessageBuilder('https://clips.vorwaerts-gmbh.de/big_buck_bunny.mp4','https://s-media-cache-ak0.pinimg.com/originals/5c/21/ad/5c21ad4c0d9ef944369b01030119bfd7.jpg');
						$resp = $bot->replyMessage($event->getReplyToken(),$vidBuilder);
                    } else {
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
	                $replyText = "Hai apa kabar?";               
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
                    $logger->info('Unknown event type has come');
                    continue;
                }
			}
		   
            $res->write('OK');
            return $res;
        });
    }
}
