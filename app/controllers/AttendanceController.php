<?php

use Controller\Controller;
use Carbon\Carbon;

/**
 * All rights reserved.
 * User: Dread Pirate Roberts
 * Date: 16-Nov-17
 * Time: 20:21
 */
class AttendanceController extends Controller {
	
	public static function store() {
		//check if uuid is already set
		
		if ( isset( $_COOKIE['uuid'] ) ) {
			return false;
			
		}
		
		$user = $_POST['user'];
		
		/*check if user has already reported
		&& check if the ip address has already made a previous **successful** request
		*/
		
		$rep = Builder::table( 'attendance' )
		              ->select( 'regno as reg', 'address as ip' )
		              ->where( "dui",Carbon::today())
		              ->get();
		
		$rep = json_decode( $rep, true );

		if($rep["response"]!=null && !$rep["error"]) //fix bug if there is no members in attendance :-)
		{
			foreach ( $rep["response"] as $member ) {
				
				if ( array_search( $_SERVER["REMOTE_ADDR"], $member ) == "ip" ||
				     array_search( $user, $member ) == "reg" ) {
					return false;
				}
			}
		}else {
			$c    = Builder::table( 'attendance' )
			               ->insert( $user, Carbon::today(), $_SERVER["REMOTE_ADDR"] )
			               ->into( 'regno', 'dui', 'address' );
			$resp = json_decode( $c, true );
			
			if ( is_numeric( $resp["response"] ) && ! $resp["error"] ) {
				//set the unique cookie
				$uid = rand( 1, 19 ) . $user;
				
				setcookie( "uuid", $uid, time() + 86400 * 1 );
				
				return true;
			}
		}
		
		return false; //in case uncaught error occurs
	}
	public static function getUser(){
		
		$c= Builder::table("members")->
		select("concat(firstname,' ',lastname) as name")
		->where('regno','=',"'".	$_POST['user']."'")->get();
		
		dd(json_decode($c,true));
	}
}