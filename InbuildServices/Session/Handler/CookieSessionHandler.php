<?php 
namespace Hkm_services\Session\Handler;

use Hkm_code\Vezirion\ServicesSystem;
use Hkm_services\Cookie\CookieJar;

class CookieSessionHandler implements \SessionHandlerInterface {

	/**
	 * The cookie jar instance.
	 *
	 * @var \Hkm_services\Cookie\CookieJar
	 */
	protected $cookie;


	/**
	 * Create a new cookie driven handler instance.
	 *
	 * @param  \Hkm_services\Cookie\CookieJar  $cookie
	 * @param  int  $minutes
	 * @return void
	 */
	public function __construct(CookieJar $cookie, $minutes)
	{
		$this->cookie = $cookie;
		$this->minutes = $minutes;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function open($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function close()
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function read($sessionId)
	{
		return ServicesSystem::REQUEST()::GET_COOKIE($sessionId) ?: '';
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function write($sessionId, $data)
	{
		$this->cookie->queue($sessionId, $data, $this->minutes);
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function destroy($sessionId)
	{
		$this->cookie->queue($this->cookie->forget($sessionId));
	} 

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function gc($lifetime)
	{
		return true;
	}


}
