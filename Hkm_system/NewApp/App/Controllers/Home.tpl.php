<@php

namespace {AppNamespace}\Controllers;

class Home extends BaseController
{
	public static function INDEX()
	{
		return hkm_view('welcome_message');
	}
}
