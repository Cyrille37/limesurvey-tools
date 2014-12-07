#!/usr/bin/env php
<?php

// Require https://github.com/weberhofer/jsonrpcphp
require_once(__DIR__.'/jsonrpcphp/org/jsonrpcphp/JsonRPCClient.class.php');

# adjust this one to your actual LimeSurvey URL & account

define( 'LS_BASEURL', 'http://limesurvey.org');
define( 'LS_USER', 'username' );
define( 'LS_PASSWORD', 'secret' );

// the survey to process
$survey_id=932462;

// instanciate a new client
$myJSONRPCClient = new \org\jsonrpcphp\jsonRPCClient( LS_BASEURL.'/admin/remotecontrol' );

// receive session key
$sessionKey= $myJSONRPCClient->get_session_key( LS_USER, LS_PASSWORD );

// receive all ids and info of groups belonging to a given survey
echo '===================================',"\n";
echo 'list_groups',"\n";
$groups = $myJSONRPCClient->list_groups( $sessionKey, $survey_id );
print_r($groups, null );

echo '===================================',"\n";
echo 'get_survey_properties',"\n";
$res = $myJSONRPCClient->get_survey_properties($sessionKey, $survey_id, array('startdate', 'active', 'publicstatistics', 'publicgraphs'));
print_r($res, null );

echo '===================================',"\n";
echo 'get_summary',"\n";
$res = $myJSONRPCClient->get_summary($sessionKey, $survey_id, 'all');
print_r($res, null );

// release the session key
$myJSONRPCClient->release_session_key( $sessionKey );
