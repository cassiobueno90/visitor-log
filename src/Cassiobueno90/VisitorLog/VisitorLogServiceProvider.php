<?php namespace Cassiobueno90\VisitorLog;

use Illuminate\Support\ServiceProvider;
use Cassiobueno90\VisitorLog\Visitor;
use Cassiobueno90\VisitorLog\VisitorLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class VisitorLogServiceProvider extends ServiceProvider {
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cassiobueno90/visitor-log');
	}
	
    public function logstore()
    {
        var_dump($this);die;
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['router']->before(function ($request) {
			// First clear out all "old" visitors
			Visitor::clear();

			$page = Request::path();
			$ignore = Config::get('visitor-log::ignore');
			/*if(is_array($ignore) && in_array($page, $ignore)) 
			//We ignore this site
			    return;*/
			//UPDATED TO...
			if(is_array($ignore))
				foreach ($ignore as $key => $value){
					if(fnmatch($page, $value))
						return;
				}
			//END UPDATE - Now it accepts string matching with wildcard(*) in the config file

			$visitor = Visitor::getCurrent();
			
			if(!$visitor)
			{
				//We need to add a new user
				$visitor = new Visitor;
				$visitor->ip = Request::getClientIp();
				$visitor->useragent = Request::server('HTTP_USER_AGENT');
				$visitor->sid = str_random(25);
			}

			$user = null;
			$usermodel = strtolower(Config::get('visitor-log::usermodel'));
			if(($usermodel == "auth" || $usermodel == "laravel") && Auth::check())
			{
				$user = Auth::user()->idColaborador;
			}

			if($usermodel == "sentry" && class_exists('Cartalyst\Sentry\SentryServiceProvider') && Sentry::check())
			{
				$user = Sentry::getUser()->id;
			}

			//Save/Update the rest
			$visitor->user = $user;
			$visitor->page = $page;
			
			VisitorLog::storelog();

			$visitor->save();
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('visitor');
	}

}
