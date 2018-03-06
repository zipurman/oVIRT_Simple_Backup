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
		array( "ip" => "/^192\.168\.1.*$/", "allow" => true, ),
		array( "ip" => "/^192\.168\.1\.5$/", "allow" => false, ),
		array( "ip" => "/^192\.168\.220\.2$/", "allow" => true, ),
	);