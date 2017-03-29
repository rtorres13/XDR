<?php
include 'xdr_API.php';
include "XDR_sign.php";

if(isset($_GET['username'])){
	$username = $_GET['username'];
}else{
	die("missing username");
}


/********* Generate Player Token *******************/
$signRequests = new XDR_sign();
$HTTP_method = "GET";
$expires = time() + 60;
$request_path = "/sas/embed_token/{$pcode}/{$embedCode}";
$request_body = "";

$parameters = array(
	'account_id' => $username
);

$token = $signRequests->generateURL($HTTP_method, $key, $secret, $expires, $request_path, $request_body, $parameters);

/************* Get Playhead Time *******************/
$ooyalaApi = new xdr_API($key, $secret);
$request_path = "/v2/cross_device_resume/accounts/{$username}/viewed_assets/{$embedCode}/playhead_info";

$json = $ooyalaApi->get($request_path);
$response = json_decode($json, true);
$playhead_seconds = $response['playhead_seconds'];

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ooyala XDR Demo</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>
	<script src='https://player.ooyala.com/v3/<?php echo $playerid ?>?version=769ffa5a122ea2168453e285cf720a1761ef92b8'></script>
	<script src="jquery.confirm.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="jquery.confirm.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
<style>
html, body {
    width: 100%;
    height: 100%;
    font-family: "Helvetica Neue", Helvetica, sans-serif;
    color: #444;
    -webkit-font-smoothing: antialiased;
    background: #f0f0f0;
	overflow-x:hidden;
	overflow-y:hidden;
}
#playerwrapper{
	width:640px;
	height:360px;
	margin:auto auto;
	margin-top:200px;
	border:1px solid black;
	-moz-box-shadow: 13px 13px 14px #666;
	-webkit-box-shadow: 13px 13px 14px #666;
			box-shadow: 13px 13px 14px #666;
}
</style>
</head>
<body>

<div id="playerwrapper"></div>

<script>

var token = '<?php echo $token ?>';
var contentid = '<?php echo $embedCode ?>';


OO.ready(function() {
	
	window.player = OO.Player.create('playerwrapper',contentid, {
		embedToken : token,
		onCreate: function(player) {
			player.mb.subscribe('*','myPage', function(eventName) {
				if(eventName === "playbackReady"){
					promptStartTime()
				}
			});
		}
	}); 
	
});


function promptStartTime(){
	var time = '<?php echo $playhead_seconds ?>';
		
	if (time > 0) {

		    $.confirm({
		        'title': 'Cross Device Resume',
		        'message': 'Pick up where you left off?',
		        'buttons': {
		            'Resume': {
		                'class': 'blue',
		                'action': function () {
		                    console.log('resume');
		                    window.player.play();
		                    window.player.seek(time);
		                }
		            },
		            'Beginning': {
		                'class': 'gray',
		                'action': function () {
		                    console.log('beginning');
		                    window.player.play();
		                }
		            }
		        }
		    });

	} else {
		window.player.play();
	}

}

</script>
</body>
</html>