#!/usr/bin/env php
<?php
/**
 * LimeSurvey survey responses export via LimeSurvey Remote API.
 * 
 * LimeSurvey API documentation:
 * 	http://manual.limesurvey.org/RemoteControl_2_API
 * function "export_responses"
 * 	http://manual.limesurvey.org/RemoteControl_2_API#export_responses
 * 
 * @author cyrille@giquello.fr
 * @license http://en.wikipedia.org/wiki/WTFPL
 */

// Require https://github.com/weberhofer/jsonrpcphp
require_once(__DIR__.'/jsonrpcphp/org/jsonrpcphp/JsonRPCClient.class.php');

function processOptions()
{
	define('OPT_DEFAULT_f','csv');
	define('OPT_DEFAULT_c','all');
	define('OPT_DEFAULT_h','full');
	define('OPT_DEFAULT_r','long');
	define('OPT_DEFAULT_o','.');

	$options = array(
		's:' => 'Required the service base url',
		'u:' => 'Required auth username',
		'p:' => 'Required auth password',
		'i:' => 'Required survey ID',
		'f:' => 'Optional export format : "pdf","csv","xls","doc","json" - default to "'.OPT_DEFAULT_f.'"',
		'c:' => 'Optional response completion status: "complete","incomplete" or "all" - defaults to "'.OPT_DEFAULT_c.'"',
		'h:' => 'Optional questions render : "code","full" or "abbreviated" - defaults to "'.OPT_DEFAULT_h.'"',
		'r:' => 'Optional responses render : "short", "long" - defaults to "'.OPT_DEFAULT_r.'"',
		'o:' => 'Optional output folder for exported file - defaults to "'.OPT_DEFAULT_o.'"'
	);

	$shortopts = implode('', array_keys($options) );
	//echo var_export($shortopts,true),"\n===========\n";
	$opts = getopt($shortopts);
	//echo var_export($opts,true),"\n===========\n";

	if( empty($opts) )
	{
		displayHelpAndDie('Missing required parameters', $options);
	}
	else if( empty($opts['s']))
	{
		displayHelpAndDie('Service base url is required', $options);
	}
	else if( empty($opts['u']))
	{
		displayHelpAndDie('Service auth username is required', $options);
	}
	else if( empty($opts['p']))
	{
		displayHelpAndDie('Service auth password is required', $options);
	}
	else if( empty($opts['i']))
	{
		displayHelpAndDie('Survey ID is required', $options);
	}

	if( empty($opts['f']))
	{
		$opts['f'] = OPT_DEFAULT_f;
	}
	else
	{
		if( ! in_array($opts['f'],array('pdf','csv','xls','doc','json')) )
			displayHelpAndDie('Unknow format "'.$opts['f'].'"', $options);
	}

	if( empty($opts['c']))
	{
		$opts['c'] = OPT_DEFAULT_c;
	}
	else
	{
		if( ! in_array($opts['c'],array('complete','incomplete','all')) )
			displayHelpAndDie('response completion status "'.$opts['c'].'"', $options);
	}
	
	if( empty($opts['h']))
	{
		$opts['h'] = OPT_DEFAULT_h;
	}
	else
	{
		if( ! in_array($opts['h'],array('code','full','abbreviated')) )
			displayHelpAndDie('Unknow questions render "'.$opts['h'].'"', $options);
	}

	if( empty($opts['r']))
	{
		$opts['r'] = OPT_DEFAULT_r;
	}
	else
	{
		if( ! in_array($opts['r'],array('short','long')) )
			displayHelpAndDie('Unknow response render "'.$opts['r'].'"', $options);
	}

	if( empty($opts['o']))
	{
		$opts['o'] = OPT_DEFAULT_o;
	}
	else
	{
		if( ! file_exists($opts['o']) )
			displayHelpAndDie('Output folder does not exists "'.$opts['o'].'"', $options);
	}

	return $opts ;
}

function displayHelpAndDie($msg, $opts)
{
	global $argv ;
	echo 'Error: ',$msg,"\n";
	echo 'Usage: ',basename($argv[0]),' -s http://www.limesurvey.org -u username -p secret -u 12345 [...]',"\n";
	foreach( $opts as $k=>$v )
	{
		echo ' -',$k,' ',$v,"\n";
	}
	die('Abort.'."\n");
}

$options = processOptions();

// instanciate a new JsonRpc client
$jsonRpc = new \org\jsonrpcphp\jsonRPCClient( $options['s'].'/admin/remotecontrol' );
// receive session key
$sessionKey= $jsonRpc->get_session_key( $options['u'], $options['p'] );

$res = $jsonRpc->export_responses($sessionKey, $options['i'], $options['f'], null, $options['c'], $options['h'], $options['r'] );

// release the session key
$jsonRpc->release_session_key( $sessionKey );

if( ! is_array($res) )
{
	$filename = $options['o'].DIRECTORY_SEPARATOR.'limesurvey_'.$options['i'].'_responses.'.$options['f'];
	file_put_contents( $filename, base64_decode( $res ) );
}
else
{
	// error
	die( 'ERROR: '. print_r($res, true ));
}


