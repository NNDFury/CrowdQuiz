<?php
// prevent the server from timing out
set_time_limit(0);

// include the web sockets server script (the server is started at the far bottom of this file)
require 'class.PHPWebSocket.php';
require 'game_controller.php';

// when a client sends data to the server
function wsOnMessage($clientID, $message, $messageLength, $binary) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	// check if message length is 0
	if ($messageLength == 0) {
		$Server->wsClose($clientID);
		return;
	}
	/*
	//The speaker is the only person in the room. Don't let them feel lonely.
	if ( sizeof($Server->wsClients) == 1 )
		$Server->wsSend($clientID, "There isn't anyone else in the room, but I'll still listen to you. --Your Trusty Server");
	else
		//Send the message to everyone but the person who said it
		foreach ( $Server->wsClients as $id => $client )
			if ( $id != $clientID )
				$Server->wsSend($id, "Visitor $clientID ($ip) said \"$message\"");
	//
	*/
	$arr = json_decode($message,true);
	global $game_controller;
	var_dump($arr);
	if($arr['id'] == 'message'){
	//Send the message to everyone but the person who said it
		foreach ( $Server->wsClients as $id => $client )
			if ( $id != $clientID )
				$text = "Visitor $clientID ($ip) said \"".$arr["text"]."\"";
				$arr = '{"id":"message","text":'.$text.'"}';
				$Server->wsSend($id, $arr);	
	}elseif ($arr['id'] == ConstantVariables::KEY_REQUEST_NOTIFY_ROLE) {
		
		//var_dump($game_controller);
		if($arr['is_admin'] == '1'){
			$game_controller->onAdminJoin($clientID);	
		}else{
			$game_controller->onUserJoin($clientID);	
		}
	} elseif($arr['id'] == ConstantVariables::KEY_REQUEST_NEXT_QUESTION) {
		$qid = intval($arr['qid']);
		$game_controller->nextQuestion($qid);
	} elseif($arr['id'] == ConstantVariables::KEY_REQUEST_SUBMIT_CHOICE) {
		$qid = intval($arr['qid']);
		$aid = intval($arr['aid']);
		$time = floatval($arr['time']);
		$answer = new AngelAnswer($qid, $aid, $time);
		$game_controller->submitChoice($clientID, $answer);
	} elseif($arr['id'] == ConstantVariables::KEY_REQUEST_SHOW_RESULT) {
		$qid = intval($arr['qid']);
		$game_controller->showResult($qid);
	} elseif($arr['id'] == ConstantVariables::KEY_REQUEST_RESTART_GAME) {
		$game_controller->restartGame();
	} elseif($arr['id'] == ConstantVariables::KEY_REQUEST_SHOW_RANKING) {
		$qid = intval($arr['qid']);
		$game_controller->showRanking($qid);
	}
}

// when a client connects
function wsOnOpen($clientID)
{
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "$ip ($clientID) has connected." );

	//Send a join notice to everyone but the person who joined
	foreach ( $Server->wsClients as $id => $client )
		if ( $id != $clientID )
			$Server->wsSend($id, "Visitor $clientID ($ip) has joined the room.");
}

// when a client closes or lost connection
function wsOnClose($clientID, $status) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "$ip ($clientID) has disconnected." );

	//Send a user left notice to everyone in the room
	foreach ( $Server->wsClients as $id => $client )
		$Server->wsSend($id, "Visitor $clientID ($ip) has left the room.");

	global $game_controller;
	$game_controller->onUserLeave($clientID);
}

function getServerAddress() {
if(array_key_exists('SERVER_ADDR', $_SERVER))
    return $_SERVER['SERVER_ADDR'];
elseif(array_key_exists('LOCAL_ADDR', $_SERVER))
    return $_SERVER['LOCAL_ADDR'];
elseif(array_key_exists('SERVER_NAME', $_SERVER))
    return gethostbyname($_SERVER['SERVER_NAME']);
else {
    // Running CLI
    if(stristr(PHP_OS, 'WIN')) {
        return gethostbyname(php_uname("n"));
    } else {
        $ifconfig = shell_exec('/sbin/ifconfig eth0');
        preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
        return $match[1];
    }
  }
}

// start the server
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
$game_controller = new GameController($Server);
//var_dump($game_controller);
// for other computers to connect, you will probably need to change this to your LAN IP or external IP,
// alternatively use: gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME']))
//$Server->wsStartServer('192.168.0.42', 9301);
$ipAddress = getServerAddress();
$port = "9301";
echo "Server start at ". $ipAddress.":".$port;
$Server->wsStartServer($ipAddress, $port);
?>