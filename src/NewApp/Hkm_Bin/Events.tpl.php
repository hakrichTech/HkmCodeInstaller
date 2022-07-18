<@php

namespace Config\Hkm_Bin;

use Hkm_code\Events\Events;
use Hkm_code\Vezirion\Services;
use Hkm_code\Exceptions\SystemException;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::ON('pre_system', function () {
	if (ENVIRONMENT !== 'testing')
	{
		if (ini_get('zlib.output_compression'))
		{
			throw SystemException::FOR_ENABLED_ZLIB_OUTPUT_COMPRESSION();
		}

		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		ob_start(function ($buffer) {
			return $buffer;
		});
	}

	/*
	 * --------------------------------------------------------------------
	 * Debug Toolbar Listeners.
	 * --------------------------------------------------------------------
	 * If you delete, they will no longer be collected.
	 */

	if (HKM_DEBUG && ! hkm_is_cli())
	{


		Events::ON('DBQuery', 'Hkm_code\Debug\Toolbar\Collectors\Database::COLLECT');
		Services::TOOLBAR()::RESPOND();
	}
});
