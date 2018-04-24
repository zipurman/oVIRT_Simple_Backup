<?php
	/**
	 * This file is used to give this script some type of security
	 *
	 * To allow all IP addresses to access this tool enter the following below
	 * $allowed_ips = array();
	 *
	 * To control specific IP addresses only, use the following format:
	 * $allowed_ips = array(
	 *          array("ip"=>"^192\.168\.1\..*$", "allow"=>"yes",), // allow any ip with 192.168.1.*
	 *          array("ip"=>"^192\.168\.1\.5$", "allow"=>"no",), // only disallow 192.168.1.5
	 *          array("ip"=>"^192\.168\.220\.2$", "allow"=>"no",), // only disallow 192.168.220.1
	 *          array("ip"=>"^192\.168\.1\.[124]$", "allow"=>"no",), // only disallow 192.168.1.1 192.168.1.2 192.168.1.4
	 *          );
	 *  The above example used regular expression matching
	 *  ^ = beginning of IP
	 *  $ = end of IP
	 *  .* = all match
	 *  \. escapes periods in IP address
	 *
	 */

	$allowed_ips = array(
		array( "ip" => "/^10\.0\..*$/", "allow" => true, ),
		array( "ip" => "/^10\.1\..*$/", "allow" => true, ),
		array( "ip" => "/^2001\:.*470\:c156\:([:0-9a-fA-F]{0,4}){1,6}/", "allow" => true, ),
		array( "ip" => "/^2001\:.*470\:1f11\:.*10c\:([:0-9a-fA-F]{0,4}){1,5}/", "allow" => true, ),
		array( "ip" => "/^127\.0\.0\.1$/", "allow" => true, ),
	);