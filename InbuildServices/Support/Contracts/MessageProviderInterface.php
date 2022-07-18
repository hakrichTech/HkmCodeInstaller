<?php namespace Hkm_services\Support\Contracts;

interface MessageProviderInterface {

	/**
	 * Get the messages for the instance.
	 *
	 * @return \Hkm_services\Support\MessageBag
	 */
	public function getMessageBag();

}
