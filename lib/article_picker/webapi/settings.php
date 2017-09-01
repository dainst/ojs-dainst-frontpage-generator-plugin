<?php
// system settings (required!)
$debugmode 		    = true;
$errorReporting 	= false;
$allowedIps		    = array();
$allowedSets 		= array('POST');
$serverclass 		= 'ojsinfoapi';

// settings for the api
$settings = array(
    "roleWhitelist" => array(
        'admin',
        'manager',
        'editor',
        'sectionEditor',
        'layoutEditor')
);
?>