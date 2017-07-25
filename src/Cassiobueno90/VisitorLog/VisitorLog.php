<?php namespace Cassiobueno90\VisitorLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Cassiobueno90\VisitorLog\Useragent;
use Cassiobueno90\VisitorLog\Visitor;

class VisitorLog extends Model {
	protected $table = 'visitors_log';
	protected $primaryKey = 'id';
	
	public static function storelog()
	{
		if(!Session::has('visitor_log_sid'))
		    return false;

		$sid = Session::get('visitor_log_sid');

		$visitor = Visitor::find($sid);		
		if(!$visitor)
			return false;

		$log = new VisitorLog();

		$log->ip = $visitor->ip;
		$log->page = $visitor->page;
		$log->useragent = $visitor->useragent;
		$log->user = $visitor->user;

		$log->save();

	}
	
}
