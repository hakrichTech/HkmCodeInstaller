<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;

class Email extends BaseVezirion
{
	/**
	 * @var string
	 */
	public static $fromEmail;

	/**
	 * @var string
	 */
	public static $fromName;

	/**
	 * @var string
	 */
	public static $recipients;

	/**
	 * The "user agent"
	 *
	 * @var string
	 */
	public static $userAgent = 'hakrichteam';

	/**
	 * The mail sending protocol: mail, sendmail, smtp
	 *
	 * @var string
	 */
	public static $protocol = 'mail';

	/**
	 * The server path to Sendmail.
	 *
	 * @var string
	 */
	public static $mailPath = '/usr/sbin/sendmail';

	/**
	 * SMTP Server Address
	 *
	 * @var string
	 */
	public static $SMTPHost;

	/**
	 * SMTP Username
	 *
	 * @var string
	 */
	public static $SMTPUser;

	/**
	 * SMTP Password
	 *
	 * @var string
	 */
	public static $SMTPPass;

	/**
	 * SMTP Port
	 *
	 * @var integer
	 */
	public static $SMTPPort = 25;

	/**
	 * SMTP Timeout (in seconds)
	 *
	 * @var integer
	 */
	public static $SMTPTimeout = 5;

	/**
	 * Enable persistent SMTP connections
	 *
	 * @var boolean
	 */
	public static $SMTPKeepAlive = false;

	/**
	 * SMTP Encryption. Either tls or ssl
	 *
	 * @var string
	 */
	public static $SMTPCrypto = 'tls';

	/**
	 * Enable word-wrap
	 *
	 * @var boolean
	 */
	public static $wordWrap = true;

	/**
	 * Character count to wrap at
	 *
	 * @var integer
	 */
	public static $wrapChars = 76;

	/**
	 * Type of mail, either 'text' or 'html'
	 *
	 * @var string
	 */
	public static $mailType = 'text';

	/**
	 * Character set (utf-8, iso-8859-1, etc.)
	 *
	 * @var string
	 */
	public static $charset = 'UTF-8';

	/**
	 * Whether to validate the email address
	 *
	 * @var boolean
	 */
	public static $validate = false;

	/**
	 * Email Priority. 1 = highest. 5 = lowest. 3 = normal
	 *
	 * @var integer
	 */
	public static $priority = 3;

	/**
	 * Newline character. (Use “\r\n” to comply with RFC 822)
	 *
	 * @var string
	 */
	public static $CRLF = "\r\n";

	/**
	 * Newline character. (Use “\r\n” to comply with RFC 822)
	 *
	 * @var string
	 */
	public static $newline = "\r\n";

	/**
	 * Enable BCC Batch Mode.
	 *
	 * @var boolean
	 */
	public static $BCCBatchMode = false;

	/**
	 * Number of emails in each BCC batch
	 *
	 * @var integer
	 */
	public static $BCCBatchSize = 200;

	/**
	 * Enable notify message from server
	 *
	 * @var boolean
	 */ 
	public static $DSN = false;

}
