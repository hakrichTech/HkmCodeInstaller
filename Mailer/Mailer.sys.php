<?php
namespace TeamsMailerSystem;

use Hkm_code\CLI\CLI;
use Hkm_code\Vezirion\Services;
use Hkm_traits\AddOn\MailerSetTrait;
use Hkm_traits\AddOn\MailerGetTrait;
use Hkm_traits\AddOn\MailerAddTrait;
use Hkm_traits\AddOn\MailerClearTrait;
use Exception;

class Mailer 
{
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';

    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    const ICAL_METHOD_REQUEST = 'REQUEST';
    const ICAL_METHOD_PUBLISH = 'PUBLISH';
    const ICAL_METHOD_REPLY = 'REPLY';
    const ICAL_METHOD_ADD = 'ADD';
    const ICAL_METHOD_CANCEL = 'CANCEL';
    const ICAL_METHOD_REFRESH = 'REFRESH';
    const ICAL_METHOD_COUNTER = 'COUNTER';
     const ICAL_METHOD_DECLINECOUNTER = 'DECLINECOUNTER';

     /**
     * Email priority.
     * Options: null (default), 1 = High, 3 = Normal, 5 = low.
     * When null, the header is not set at all.
     *
     * @var int|null
     */
    public static $Priority;

    /**
     * The character set of the message.
     *
     * @var string
     */
    public static $CharSet = self::CHARSET_ISO88591;

    /**
     * The MIME Content-type of the message.
     *
     * @var string
     */
    public static $ContentType = self::CONTENT_TYPE_PLAINTEXT;

    /**
     * The message encoding.
     * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
     *
     * @var string
     */
    public static $Encoding = self::ENCODING_8BIT;

    /**
     * Holds the most recent mailer error message.
     *
     * @var string
     */
    public static $ErrorInfo = '';

    /**
     * The From email address for the message.
     *
     * @var string
     */
    public static $From = '';

    /**
     * The From name of the message.
     *
     * @var string
     */
    public static $FromName = '';

    /**
     * The envelope sender of the message.
     * This will usually be turned into a Return-Path header by the receiver,
     * and is the address that bounces will be sent to.
     * If not empty, will be passed via `-f` to sendmail or as the 'MAIL FROM' value over SMTP.
     *
     * @var string
     */
    public static $Sender = '';

    /**
     * The Subject of the message.
     *
     * @var string
     */
    public static $Subject = '';

    /**
     * An HTML or plain text message body.
     * If HTML then call isHTML(true).
     *
     * @var string
     */
    public static $Body = '';

    /**
     * The plain-text message body.
     * This body can be read by mail clients that do not have HTML email
     * capability such as mutt & Eudora.
     * Clients that can read HTML will view the normal Body.
     *
     * @var string
     */
    public static $AltBody = '';

    /**
     * An iCal message part body.
     * Only supported in simple alt or alt_inline message types
     * To generate iCal event structures, use classes like EasyPeasyICS or iCalcreator.
     *
     * @see http://sprain.ch/blog/downloads/php-class-easypeasyics-create-ical-files-with-php/
     * @see http://kigkonsult.se/iCalcreator/
     *
     * @var string
     */
    public static $Ical = '';

    /**
     * Value-array of "method" in Contenttype header "text/calendar"
     *
     * @var string[]
     */
    protected static $IcalMethods = [
        self::ICAL_METHOD_REQUEST,
        self::ICAL_METHOD_PUBLISH,
        self::ICAL_METHOD_REPLY,
        self::ICAL_METHOD_ADD,
        self::ICAL_METHOD_CANCEL,
        self::ICAL_METHOD_REFRESH,
        self::ICAL_METHOD_COUNTER,
        self::ICAL_METHOD_DECLINECOUNTER,
    ];

    /**
     * The complete compiled MIME message body.
     *
     * @var string
     */
    protected static $MIMEBody = '';

    /**
     * The complete compiled MIME message headers.
     *
     * @var string
     */
    protected static $MIMEHeader = '';

    /**
     * Extra headers that CHREATE_HEADER() doesn't fold in.
     *
     * @var string
     */
    protected static $mailHeader = '';

    /**
     * Word-wrap the message body to this number of chars.
     * Set to 0 to not wrap. A useful value here is 78, for RFC2822 section 2.1.1 compliance.
     *
     * @see static::STD_LINE_LENGTH
     *
     * @var int
     */
    public static $WordWrap = 0;

    /**
     * Which method to use to send mail.
     * Options: "mail", "sendmail", or "smtp".
     *
     * @var string
     */
    public static $Mailer = 'mail';

    /**
     * The path to the sendmail program.
     *
     * @var string
     */
    public static $Sendmail = '/usr/sbin/sendmail';

    /**
     * Whether mail() uses a fully sendmail-compatible MTA.
     * One which supports sendmail's "-oi -f" options.
     *
     * @var bool
     */
    public static $UseSendmailOptions = true;

    /**
     * The email address that a reading confirmation should be sent to, also known as read receipt.
     *
     * @var string
     */
    public static $ConfirmReadingTo = '';

    /**
     * The hostname to use in the Message-ID header and as default HELO string.
     * If empty, TeamsMailer attempts to find one with, in order,
     * $_SERVER['SERVER_NAME'], gethostname(), php_uname('n'), or the value
     * 'localhost.localdomain'.
     *
     * @see TeamsMailer::$Helo
     *
     * @var string
     */
    public static $Hostname = '';

    /**
     * An ID to be used in the Message-ID header.
     * If empty, a unique id will be generated.
     * You can set your own, but it must be in the format "<id@domain>",
     * as defined in RFC5322 section 3.6.4 or it will be ignored.
     *
     * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
     *
     * @var string
     */
    public static $MessageID = '';

    /**
     * The message Date to be used in the Date header.
     * If empty, the current date will be added.
     *
     * @var string
     */
    public static $MessageDate = '';

    /**
     * SMTP hosts.
     * Either a single hostname or multiple semicolon-delimited hostnames.
     * You can also specify a different port
     * for each host by using this format: [hostname:port]
     * (e.g. "smtp1.example.com:25;smtp2.example.com").
     * You can also specify encryption type, for example:
     * (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465").
     * Hosts will be tried in order.
     *
     * @var string
     */
    public static $Host = 'localhost';

    /**
     * The default SMTP server port.
     *
     * @var int
     */
    public static $Port = 25;

    /**
     * The SMTP HELO/EHLO name used for the SMTP connection.
     * Default is $Hostname. If $Hostname is empty, TeamsMailer attempts to find
     * one with the same method described above for $Hostname.
     *
     * @see TeamsMailer::$Hostname
     *
     * @var string
     */
    public static $Helo = '';

    /**
     * What kind of encryption to use on the SMTP connection.
     * Options: '', static::ENCRYPTION_STARTTLS, or static::ENCRYPTION_SMTPS.
     *
     * @var string
     */
    public static $SMTPSecure = '';

    /**
     * Whether to enable TLS encryption automatically if a server supports it,
     * even if `SMTPSecure` is not set to 'tls'.
     * Be aware that in PHP >= 5.6 this requires that the server's certificates are valid.
     *
     * @var bool
     */
    public static $SMTPAutoTLS = true;

    /**
     * Whether to use SMTP authentication.
     * Uses the Username and Password properties.
     *
     * @see TeamsMailer::$Username
     * @see TeamsMailer::$Password
     *
     * @var bool
     */
    public static $SMTPAuth = false;

    /**
     * Options array passed to stream_context_create when connecting via SMTP.
     *
     * @var array
     */
    public static $SMTPOptions = [];

    /**
     * SMTP username.
     *
     * @var string
     */
    public static $Username = '';

    /**
     * SMTP password.
     *
     * @var string
     */
    public static $Password = '';

    /**
     * SMTP auth type.
     * Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified.
     *
     * @var string
     */
    public static $AuthType = '';

    /**
     * An instance of the TeamsMailer OAuth class.
     *
     * @var OAuth
     */
    protected static $oauth;

    /**
     * The SMTP server timeout in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     *
     * @var int
     */
    public static $Timeout = 300;

    /**
     * Comma separated list of DSN notifications
     * 'NEVER' under no circumstances a DSN must be returned to the sender.
     *         If you use NEVER all other notifications will be ignored.
     * 'SUCCESS' will notify you when your mail has arrived at its destination.
     * 'FAILURE' will arrive if an error occurred during delivery.
     * 'DELAY'   will notify you if there is an unusual delay in delivery, but the actual
     *           delivery's outcome (success or failure) is not yet decided.
     *
     * @see https://tools.ietf.org/html/rfc3461 See section 4.1 for more information about NOTIFY
     */
    public static $dsn = '';

    /**
     * SMTP class debug output mode.
     * Debug output level.
     * Options:
     * @see SMTP::DEBUG_OFF: No output
     * @see SMTP::DEBUG_CLIENT: Client messages
     * @see SMTP::DEBUG_SERVER: Client and server messages
     * @see SMTP::DEBUG_CONNECTION: As SERVER plus connection status
     * @see SMTP::DEBUG_LOWLEVEL: Noisy, low-level data output, rarely needed
     *
     * @see SMTP::$do_debug
     *
     * @var int
     */
    private static $SMTPDebug = 0;

    /**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `error_log` Output to error log as configured in php.ini
     * By default TeamsMailer will use `echo` if run from a `cli` or `cli-server` SAPI, `html` otherwise.
     * Alternatively, you can provide a callable expecting two params: a message string and the debug level:
     *
     * ```php
     * $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
     * ```
     *
     * Alternatively, you can pass in an instance of a PSR-3 compatible logger, though only `debug`
     * level output is used:
     *
     * ```php
     * $mail->Debugoutput = new myPsr3Logger;
     * ```
     *
     * @see SMTP::$Debugoutput
     *
     * @var string|callable|\Psr\Log\LoggerInterface
     */
    public static $Debugoutput = 'echo';

    /**
     * Whether to keep the SMTP connection open after each message.
     * If this is set to true then the connection will remain open after a send,
     * and closing the connection will require an explicit call to smtpClose().
     * It's a good idea to use this if you are sending multiple messages as it reduces overhead.
     * See the mailing list example for how to use it.
     *
     * @var bool
     */
    public static $SMTPKeepAlive = false;

    /**
     * Whether to split multiple to addresses into multiple messages
     * or send them all in one message.
     * Only supported in `mail` and `sendmail` transports, not in SMTP.
     *
     * @var bool
     *
     * @deprecated 6.0.0 TeamsMailer isn't a mailing list manager!
     */
    public static $SingleTo = false;

    /**
     * Storage for addresses when SingleTo is enabled.
     *
     * @var array
     */
    protected static $SingleToArray = [];

    /**
     * Whether to generate VERP addresses on send.
     * Only applicable when sending via SMTP.
     *
     * @see https://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @see http://www.postfix.org/VERP_README.html Postfix VERP info
     *
     * @var bool
     */
    public static $do_verp = false;

    /**
     * Whether to allow sending messages with an empty body.
     *
     * @var bool
     */
    public static $AllowEmpty = false;

    /**
     * DKIM selector.
     *
     * @var string
     */
    public static $DKIM_selector = '';

    /**
     * DKIM Identity.
     * Usually the email address used as the source of the email.
     *
     * @var string
     */
    public static $DKIM_identity = '';

    /**
     * DKIM passphrase.
     * Used if your key is encrypted.
     *
     * @var string
     */
    public static $DKIM_passphrase = '';

    /**
     * DKIM signing domain name.
     *
     * @example 'example.com'
     *
     * @var string
     */
    public static $DKIM_domain = '';

    /**
     * DKIM Copy header field values for diagnostic use.
     *
     * @var bool
     */
    public static $DKIM_copyHeaderFields = true;

    /**
     * DKIM Extra signing headers.
     *
     * @example ['List-Unsubscribe', 'List-Help']
     *
     * @var array
     */
    public static $DKIM_extraHeaders = [];

    /**
     * DKIM private key file path.
     *
     * @var string
     */
    public static $DKIM_private = '';

    /**
     * DKIM private key string.
     *
     * If set, takes precedence over `$DKIM_private`.
     *
     * @var string
     */
    public static $DKIM_private_string = '';

    /**
     * Callback Action function name.
     *
     * The function that handles the result of the send email action.
     * It is called out by send() for each email sent.
     *
     * Value can be any php callable: http://www.php.net/is_callable
     *
     * Parameters:
     *   bool $result        result of the send action
     *   array   $to            email addresses of the recipients
     *   array   $cc            cc email addresses
     *   array   $bcc           bcc email addresses
     *   string  $subject       the subject
     *   string  $body          the email body
     *   string  $from          email address of sender
     *   string  $extra         extra information of possible use
     *                          "smtp_transaction_id' => last smtp transaction id
     *
     * @var string
     */
    public static $action_function = '';

    /**
     * What to put in the X-Mailer header.
     * Options: An empty string for TeamsMailer default, whitespace/null for none, or a string to use.
     *
     * @var string|null
     */
    public static $XMailer = '';

    /**
     * Which validator to use by default when validating email addresses.
     * May be a callable to inject your own validator, but there are several built-in validators.
     * The default validator uses PHP's FILTER_VALIDATE_EMAIL filter_var option.
     *
     * @see TeamsMailer::VALIDATE_ADDRESS()
     *
     * @var string|callable
     */
    public static $validator = 'php';

    /**
     * An instance of the SMTP sender class.
     *
     * @var SMTP
     */
    protected static $smtp;

    /**
     * The array of 'to' names and addresses.
     *
     * @var array
     */
    protected static $to = [];

    /**
     * The array of 'cc' names and addresses.
     *
     * @var array
     */
    protected static $cc = [];

    /**
     * The array of 'bcc' names and addresses.
     *
     * @var array
     */
    protected static $bcc = [];

    /**
     * The array of reply-to names and addresses.
     *
     * @var array
     */
    protected static $ReplyTo = [];

    /**
     * An array of all kinds of addresses.
     * Includes all of $to, $cc, $bcc.
     *
     * @see TeamsMailer::$to
     * @see TeamsMailer::$cc
     * @see TeamsMailer::$bcc
     *
     * @var array
     */
    protected static $all_recipients = [];

    /**
     * An array of names and addresses queued for validation.
     * In send(), valid and non duplicate entries are moved to $all_recipients
     * and one of $to, $cc, or $bcc.
     * This array is used only for addresses with IDN.
     *
     * @see TeamsMailer::$to
     * @see TeamsMailer::$cc
     * @see TeamsMailer::$bcc
     * @see TeamsMailer::$all_recipients
     *
     * @var array
     */
    protected static $RecipientsQueue = [];

    /**
     * An array of reply-to names and addresses queued for validation.
     * In send(), valid and non duplicate entries are moved to $ReplyTo.
     * This array is used only for addresses with IDN.
     *
     * @see TeamsMailer::$ReplyTo
     *
     * @var array
     */
    protected static $ReplyToQueue = [];

    /**
     * The array of attachments.
     *
     * @var array
     */
    protected static $attachment = [];

    /**
     * The array of custom headers.
     *
     * @var array
     */
    protected static $CustomHeader = [];

    /**
     * The most recent Message-ID (including angular brackets).
     *
     * @var string
     */
    protected static $lastMessageID = '';

    /**
     * The message's MIME type.
     *
     * @var string
     */
    protected static $message_type = '';

    /**
     * The array of MIME boundary strings.
     *
     * @var array
     */
    protected static $boundary = [];

    /**
     * The array of available text strings for the current language.
     *
     * @var array
     */
    protected static $language = [];

    /**
     * The number of errors encountered.
     *
     * @var int
     */
    protected static $error_count = 0;

    /**
     * The S/MIME certificate file path.
     *
     * @var string
     */
    protected static $sign_cert_file = '';

    /**
     * The S/MIME key file path.
     *
     * @var string
     */
    protected static $sign_key_file = '';

    /**
     * The optional S/MIME extra certificates ("CA Chain") file path.
     *
     * @var string
     */
    protected static $sign_extracerts_file = '';

    /**
     * The S/MIME password for the key.
     * Used only if the key is encrypted.
     *
     * @var string
     */
    protected static $sign_key_pass = '';

    /**
     * Whether to throw exceptions for errors.
     *
     * @var bool
     */
    protected static $exceptions = false;

    /**
     * Unique ID used for message ID and boundaries.
     *
     * @var string
     */
    protected static $uniqueid = '';

    /**
     * The TeamsMailer Version number.
     *
     * @var string
     */
    const VERSION = '6.5.1';

    /**
     * Error severity: message only, continue processing.
     *
     * @var int
     */
    const STOP_MESSAGE = 0;

    /**
     * Error severity: message, likely ok to continue processing.
     *
     * @var int
     */
    const STOP_CONTINUE = 1;

    /**
     * Error severity: message, plus full stop, critical error reached.
     *
     * @var int
     */
    const STOP_CRITICAL = 2;

    /**
     * The SMTP standard CRLF line break.
     * If you want to change line break format, change static::$LE, not this.
     */
    const CRLF = "\r\n";

    /**
     * "Folding White Space" a white space string used for line folding.
     */
    const FWS = ' ';

    /**
     * SMTP RFC standard line ending; Carriage Return, Line Feed.
     *
     * @var string
     */
    protected  static $LE = self::CRLF;

    /**
     * The maximum line length supported by mail().
     *
     * Background: mail() will sometimes corrupt messages
     * with headers headers longer than 65 chars, see #818.
     *
     * @var int
     */
    const MAIL_MAX_LINE_LENGTH = 63;

    /**
     * The maximum line length allowed by RFC 2822 section 2.1.1.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 998;

    /**
     * The lower maximum line length allowed by RFC 2822 section 2.1.1.
     * This length does NOT include the line break
     * 76 means that lines will be 77 or 78 chars depending on whether
     * the line break format is LF or CRLF; both are valid.
     *
     * @var int
     */
    const STD_LINE_LENGTH = 76;

    public static $thiss;
    

    /**
     * Constructor.
     *
     * @param bool $exceptions Should we throw external exceptions?
     */
    public  function __construct($exceptions = null)
    {
        self::$validator = Services::VALIDATION();
        self::$thiss = $this;
        if (null !== $exceptions) {
            self::$exceptions = (bool) $exceptions;
        }
        //Pick an appropriate debug output format automatically
        self::$Debugoutput = (strpos(PHP_SAPI, 'cli') !== false ? 'echo' : 'html');
    }

    use MailerSetTrait;
    use MailerGetTrait;
    use MailerAddTrait;
    use MailerClearTrait;

    /**
     * Output debugging info via a user-defined method.
     * Only generates output if debug output is enabled.
     *
     * @see TeamsMailer::$Debugoutput
     * @see TeamsMailer::$SMTPDebug
     *
     * @param string $str
     */
    public static function DEBUG($str)
    {
        if (self::$SMTPDebug <= 0) {
            return;
        }
        //Is this a PSR-3 logger?
        if (self::$Debugoutput instanceof \Psr\Log\LoggerInterface) {
            self::$Debugoutput->debug($str);

            return;
        }
        //Avoid clash with built-in function names
        if (is_callable(self::$Debugoutput) && !in_array(self::$Debugoutput, ['error_log', 'html', 'echo'])) {
             print "fun";
            call_user_func(self::$Debugoutput, $str, self::$SMTPDebug);

            return;
        }

        switch (self::$Debugoutput) {
            case 'error_log':

                //Don't output, just log
                /** @noinspection ForgottenDebugOutputInspection */
                error_log($str);
                break;
            case 'html':

                //Cleans up output a bit for a better looking, HTML-safe output
                echo htmlentities(
                    preg_replace('/[\r\n]+/', '', $str),
                    ENT_QUOTES,
                    'UTF-8'
                ), "<br>\n";
                break;
            case 'echo':
            default:

                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/m', "\n", $str);
                $time  = CLI::color(gmdate('Y-m-d H:i:s'),'white');
                $str = CLI::color(trim(str_replace("\n","\n\t",trim($str))),'green');
                 CLI::write($time."\t".$str);
        }
    }

    



      /**
     * Converts IDN in given email address to its ASCII form, also known as punycode, if possible.
     * Important: Address must be passed in same encoding as currently set in PHPMailer::$CharSet.
     * This function silently returns unmodified address if:
     * - No conversion is necessary (i.e. domain name is not an IDN, or is already in ASCII form)
     * - Conversion to punycode is impossible (e.g. required PHP functions are not available)
     *   or fails for any reason (e.g. domain contains characters not allowed in an IDN).
     *
     * @see PHPMailer::$CharSet
     *
     * @param string $address The email address to convert
     *
     * @return string The encoded address in ASCII form
     */
    public static function PUNY_ENCODE_ADDRESS($address)
    {
        //Verify we have required functions, CharSet, and at-sign.
        $pos = strrpos($address, '@');
        if (
            !empty(self::$CharSet) &&
            false !== $pos &&
            hkm_idnSupported()
        ) {
            $domain = substr($address, ++$pos);
            //Verify CharSet string is a valid one, and domain properly encoded in this CharSet.
            if (self::HAS_8BIT_CHARS($domain) && @mb_check_encoding($domain, self::$CharSet)) {
                //Convert the domain from whatever charset it's in to UTF-8
                $domain = mb_convert_encoding($domain, self::CHARSET_UTF8, self::$CharSet);
                //Ignore IDE complaints about this line - method signature changed in PHP 5.4
                $errorcode = 0;
                if (defined('INTL_IDNA_VARIANT_UTS46')) {
                    //Use the current punycode standard (appeared in PHP 7.2)
                    $punycode = idn_to_ascii($domain, $errorcode, \INTL_IDNA_VARIANT_UTS46);
                } elseif (defined('INTL_IDNA_VARIANT_2003')) {
                    //Fall back to this old, deprecated/removed encoding
                    $punycode = idn_to_ascii($domain, $errorcode, \INTL_IDNA_VARIANT_2003);
                } else {
                    //Fall back to a default we don't know about
                    $punycode = idn_to_ascii($domain, $errorcode);
                }
                if (false !== $punycode) {
                    return substr($address, 0, $pos) . $punycode;
                }
            }
        }

        return $address;
    }

     /**
     * Does a string contain any 8-bit chars (in any charset)?
     *
     * @param string $text
     *
     * @return bool
     */
    public static function HAS_8BIT_CHARS($text)
    {
        return (bool) preg_match('/[\x80-\xFF]/', $text);
    }


    /**
     * Validate encodings.
     *
     * @param string $encoding
     *
     * @return bool
     */
    protected static function VALIDATE_ENCODING($encoding)
    {
        return in_array(
            $encoding,
            [
                self::ENCODING_7BIT,
                self::ENCODING_QUOTED_PRINTABLE,
                self::ENCODING_BASE64,
                self::ENCODING_8BIT,
                self::ENCODING_BINARY,
            ],
            true
        );
    }
   

    /**
     * Initiate a connection to an SMTP server.
     * Returns false if the operation failed.
     *
     * @param array $options An array of options compatible with stream_context_create()
     *
     * @throws Exception
     *
     * @uses \TeamsMailer\TeamsMailer\SMTP
     *
     * @return bool
     */
    public static function SMTP_CONNECT($options = null)
    {
        if (null === self::$smtp) {
            self::$smtp = self::GET_SMTP_INSTANCE();
        }

        //If no options are provided, use whatever is set in the instance
        if (null === $options) {
            $options = self::$SMTPOptions;
        }

        //Already connected?
        if (self::$smtp::CONNECTED()) {
            return true;
        }

        self::$smtp::SET_TIMEOUT(self::$Timeout);
        self::$smtp::SET_DEBUG_LEVEL(self::$SMTPDebug);
        self::$smtp::SET_DEBUG_OUTPUT(self::$Debugoutput);
        self::$smtp::SET_VERP(self::$do_verp);
        $hosts = explode(';', self::$Host);
        $lastexception = null;

        foreach ($hosts as $hostentry) {
            $hostinfo = [];
            if (!preg_match('/^(?:(ssl|tls):\/\/)?(.+?)(?::(\d+))?$/',trim($hostentry),$hostinfo)) 
                {
                self::DEBUG(self::lang('invalid_hostentry') . ' ' . trim($hostentry));
                //Not a valid host entry
                continue;
            }
            //$hostinfo[1]: optional ssl or tls prefix
            //$hostinfo[2]: the hostname
            //$hostinfo[3]: optional port number
            //The host string prefix can temporarily override the current setting for SMTPSecure
            //If it's not specified, the default value is used

            self::$validator::CHECK($hostinfo[2],'is_valid_host',['is_valid_host'=>'invalid_host']);
            $er_msg = self::$validator::GET_ERROR('check');
            if (!empty($er_msg)) {
                self::DEBUG(self::lang('invalid_host') . ' ' . $hostinfo[2]);
                continue;
            }


            $prefix = '';
            $secure = self::$SMTPSecure;
            $tls = (static::ENCRYPTION_STARTTLS === self::$SMTPSecure);
            if ('ssl' === $hostinfo[1] || ('' === $hostinfo[1] && static::ENCRYPTION_SMTPS === self::$SMTPSecure)) {
                $prefix = 'ssl://';
                $tls = false; //Can't have SSL and TLS at the same time
                $secure = static::ENCRYPTION_SMTPS;
            } elseif ('tls' === $hostinfo[1]) {
                $tls = true;
                //TLS doesn't use a prefix
                $secure = static::ENCRYPTION_STARTTLS;
            }
            //Do we need the OpenSSL extension?
            $sslext = defined('OPENSSL_ALGO_SHA256');
            if (static::ENCRYPTION_STARTTLS === $secure || static::ENCRYPTION_SMTPS === $secure) {
                //Check for an OpenSSL constant rather than using extension_loaded, which is sometimes disabled
                if (!$sslext) {
                    throw new Exception(self::lang('extension_missing') . 'openssl', self::STOP_CRITICAL);
                }
            }
            $host = $hostinfo[2];
            $port = self::$Port;
            if (
                array_key_exists(3, $hostinfo) &&
                is_numeric($hostinfo[3]) &&
                $hostinfo[3] > 0 &&
                $hostinfo[3] < 65536
            ) {
                $port = (int) $hostinfo[3];
            }
            if (self::$smtp::CONNECT($prefix . $host, $port, self::$Timeout, $options)) {
                try {
                    if (self::$Helo) {
                        $hello = self::$Helo;
                    } else {
                        $hello = self::SERVER_HOSTNAME();
                    }
                    self::$smtp::HELLO($hello);
                    //Automatically enable TLS encryption if:
                    //* it's not disabled
                    //* we have openssl extension
                    //* we are not already using SSL
                    //* the server offers STARTTLS
                    if (self::$SMTPAutoTLS && $sslext && 'ssl' !== $secure && self::$smtp::GET_SERVER_EXT('STARTTLS')) {
                        $tls = true;
                    }
                    if ($tls) {
                        if (!self::$smtp::START_TLS()) {
                            throw new Exception(self::lang('connect_host'));
                        }
                        //We must resend EHLO after TLS negotiation
                        self::$smtp::HELLO($hello);
                    }
                    if (
                        self::$SMTPAuth && !self::$smtp::AUTHENTICATE(
                            self::$Username,
                            self::$Password,
                            self::$AuthType,
                            self::$oauth
                        )
                    ) {
                        throw new Exception(self::lang('authenticate'));
                    }

                    return true;
                } catch (Exception $exc) {
                    $lastexception = $exc;
                    self::DEBUG($exc->getMessage());
                    //We must have connected, but then failed TLS or Auth, so close connection nicely
                    self::$smtp::QUIT();
                }
            }
        }
        //If we get here, all connection attempts have failed, so close connection hard
        self::$smtp::CLOSE();
        //As we've caught all exceptions, just report whatever the last one was
        if (   self::$exceptions && null !== $lastexception) {
            throw $lastexception;
        }

        return false;
    }

      /**
     * Get the server hostname.
     * Returns 'localhost.localdomain' if unknown.
     *
     * @return string
     */
    protected static function SERVER_HOSTNAME()
    {
        $result = '';
        if (!empty(self::$Hostname)) {
            $result = self::$Hostname;
        } elseif (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) {
            $result = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $result = gethostname();
        } elseif (php_uname('n') !== false) {
            $result = php_uname('n');
        }
        self::$validator::CHECK($result,'is_valid_host',['is_valid_host'=>'localhost.localdomain']);
        $v = self::$validator::GET_ERROR('check');

        return !empty($v)?$v:$result;
    }

    /**
     * Check if an error occurred.
     *
     * @return bool True if an error did occur
     */
    public static function IS_ERROR()
    {
        return self::$error_count > 0;
    }

    /**
     * Check if an embedded attachment is present with this cid.
     *
     * @param string $cid
     *
     * @return bool
     */
    protected static function CID_EXISTS($cid)
    {
        foreach (self::$attachment as $attachment) {
            if ('inline' === $attachment[6] && $cid === $attachment[7]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets message type to HTML or plain.
     *
     * @param bool $isHtml True for HTML mode
     */
    public  static function IS_HTML($isHtml = true)
    {
        if ($isHtml) {
            self::$ContentType = static::CONTENT_TYPE_TEXT_HTML;
        } else {
            self::$ContentType = static::CONTENT_TYPE_PLAINTEXT;
        }
    }

    /**
     * Normalize line breaks in a string.
     * Converts UNIX LF, Mac CR and Windows CRLF line breaks into a single line break format.
     * Defaults to CRLF (for message bodies) and preserves consecutive breaks.
     *
     * @param string $text
     * @param string $breaktype What kind of line break to use; defaults to static::$LE
     *
     * @return string
     */
    public static function NOMALIZE_BREAKS($text, $breaktype = null)
    {
        if (null === $breaktype) {
            $breaktype = static::$LE;
        }
        //Normalise to \n
        $text = str_replace([self::CRLF, "\r"], "\n", $text);
        //Now convert LE as needed
        if ("\n" !== $breaktype) {
            $text = str_replace("\n", $breaktype, $text);
        }

        return $text;
    }

    /**
     * Create a message body from an HTML string.
     * Automatically inlines images and creates a plain-text version by converting the HTML,
     * overwriting any existing values in Body and AltBody.
     * Do not source $message content from user input!
     * $basedir is prepended when handling relative URLs, e.g. <img src="/images/a.png"> and must not be empty
     * will look for an image file in $basedir/images/a.png and convert it to inline.
     * If you don't provide a $basedir, relative paths will be left untouched (and thus probably break in email)
     * Converts data-uri images into embedded attachments.
     * If you don't want to apply these transformations to your HTML, just set Body and AltBody directly.
     *
     * @param string        $message  HTML message string
     * @param string        $basedir  Absolute path to a base directory to prepend to relative paths to images
     * @param bool|callable $advanced Whether to use the internal HTML to text converter
     *                                or your own custom converter
     * @return string The transformed message body
     *
     * @throws Exception
     *
     * @see TeamsMailer::html2text()
     */
    public static function MSG_HTML($message, $basedir = '', $advanced = false)
    {
        preg_match_all('/(?<!-)(src|background)=["\'](.*)["\']/Ui', $message, $images);
        if (array_key_exists(2, $images)) {
            if (strlen($basedir) > 1 && '/' !== substr($basedir, -1)) {
                //Ensure $basedir has a trailing /
                $basedir .= '/';
            }
            foreach ($images[2] as $imgindex => $url) {
                //Convert data URIs into embedded images
                //e.g. "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                $match = [];
                if (preg_match('#^data:(image/(?:jpe?g|gif|png));?(base64)?,(.+)#', $url, $match)) {
                    if (count($match) === 4 && static::ENCODING_BASE64 === $match[2]) {
                        $data = base64_decode($match[3]);
                    } elseif ('' === $match[2]) {
                        $data = rawurldecode($match[3]);
                    } else {
                        //Not recognised so leave it alone
                        continue;
                    }
                    //Hash the decoded data, not the URL, so that the same data-URI image used in multiple places
                    //will only be embedded once, even if it used a different encoding
                    $cid = substr(hash('sha256', $data), 0, 32) . '@Teamsmailer.0'; //RFC2392 S 2

                    if(self::CID_EXISTS($cid)) {
                        self::ADD_STRING_EMBEDDED_IMAGE(
                            $data,
                            $cid,
                            'embed' . $imgindex,
                            static::ENCODING_BASE64,
                            $match[1]
                        );
                    }
                    $message = str_replace(
                        $images[0][$imgindex],
                        $images[1][$imgindex] . '="cid:' . $cid . '"',
                        $message
                    );
                    continue;
                }
                if (
                    //Only process relative URLs if a basedir is provided (i.e. no absolute local paths)
                    !empty($basedir)
                    //Ignore URLs containing parent dir traversal (..)
                    && (strpos($url, '..') === false)
                    //Do not change urls that are already inline images
                    && 0 !== strpos($url, 'hkmd:')
                    //Do not change absolute URLs, including anonymous protocol
                    && !preg_match('#^[a-z][a-z0-9+.-]*:?//#i', $url)
                ) {
                    $filename = hkm_mb_pathinfo($url, PATHINFO_BASENAME);
                    $directory = dirname($url);
                    if ('.' === $directory) {
                        $directory = '';
                    }
                    //RFC2392 S 2
                    $cid = substr(hash('sha256', $url), 0, 32) . '@Teamsmailer.0';
                    if (strlen($basedir) > 1 && '/' !== substr($basedir, -1)) {
                        $basedir .= '/';
                    }
                    if (strlen($directory) > 1 && '/' !== substr($directory, -1)) {
                        $directory .= '/';
                    }
                    if (
                        self::ADD_EMBEDDED_IMAGE(
                            $basedir . $directory . $filename,
                            $cid,
                            $filename,
                            static::ENCODING_BASE64,
                            _mime_types((string) hkm_mb_pathinfo($filename, PATHINFO_EXTENSION))
                        )
                    ) {
                        $message = preg_replace(
                            '/' . $images[1][$imgindex] . '=["\']' . preg_quote($url, '/') . '["\']/Ui',
                            $images[1][$imgindex] . '="cid:' . $cid . '"',
                            $message
                        );
                    }
                }
            }
        }
        self::IS_HTML();
        //Convert all message body line breaks to LE, makes quoted-printable encoding work much better
        self::$Body = static::NOMALIZE_BREAKS($message);
        self::$AltBody = static::NOMALIZE_BREAKS(html2text($message,self::$CharSet, $advanced));
        if (!self::ALTERNATIVE_EXISTS()) {
            self::$AltBody = 'This is an HTML-only message. To view it, activate HTML in your email application.'
                . static::$LE;
        }

        return self::$Body;
    }
     /**
     * Check if this message has an alternative body set.
     *
     * @return bool
     */
    public static function ALTERNATIVE_EXISTS()
    {
        return !empty(self::$AltBody);
    }


    /**
     * Encode a file attachment in requested format.
     * Returns an empty string on failure.
     *
     * @param string $path     The full path to the file
     * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
     *
     * @return string
     */
    protected static function ENCODE_FILE($path, $encoding = self::ENCODING_BASE64)
    {
        try {
            if (!fileIsAccessible($path)) {
                throw new Exception(self::lang('file_open') . $path, self::STOP_CONTINUE);
            }
            $file_buffer = file_get_contents($path);
            if (false === $file_buffer) {
                throw new Exception(self::lang('file_open') . $path, self::STOP_CONTINUE);
            }
            $file_buffer = self::ENCODE_STRING($file_buffer, $encoding);

            return $file_buffer;
        } catch (Exception $exc) {
            self::SET_ERROR($exc->getMessage());
            self::DEBUG($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }

            return '';
        }
    }

    /**
     * Encode a string in requested format.
     * Returns an empty string on failure.
     *
     * @param string $str      The text to encode
     * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
     *
     * @throws Exception
     *
     * @return string
     */
    public static function ENCODE_STRING($str, $encoding = self::ENCODING_BASE64)
    {
        $encoded = '';
        switch (strtolower($encoding)) {
            case static::ENCODING_BASE64:
                $encoded = chunk_split(
                    base64_encode($str),
                    static::STD_LINE_LENGTH,
                    static::$LE
                );
                break;
            case static::ENCODING_7BIT:
            case static::ENCODING_8BIT:
                $encoded = self::NOMALIZE_BREAKS($str);
                //Make sure it ends with a line break
                if (substr($encoded, -(strlen(static::$LE))) !== static::$LE) {
                    $encoded .= static::$LE;
                }
                break;
            case static::ENCODING_BINARY:
                $encoded = $str;
                break;
            case static::ENCODING_QUOTED_PRINTABLE:
                $encoded = self::ENCODE_QP($str);
                break;
            default:
                self::SET_ERROR(self::lang('encoding') . $encoding);
                if (self::$exceptions) {
                    throw new Exception(self::lang('encoding') . $encoding);
                }
                break;
        }

        return $encoded;
    }

    /**
     * Encode a header value (not including its label) optimally.
     * Picks shortest of Q, B, or none. Result includes folding if needed.
     * See RFC822 definitions for phrase, comment and text positions.
     *
     * @param string $str      The header value to encode
     * @param string $position What context the string will be used in
     *
     * @return string
     */
    public static function ENCODE_HEADER($str, $position = 'text')
    {
        $matchcount = 0;
        switch (strtolower($position)) {
            case 'phrase':
                if (!preg_match('/[\200-\377]/', $str)) {
                    //Can't use addslashes as we don't know the value of magic_quotes_sybase
                    $encoded = addcslashes($str, "\0..\37\177\\\"");
                    if (($str === $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return $encoded;
                    }

                    return "\"$encoded\"";
                }
                $matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                break;
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $matchcount = preg_match_all('/[()"]/', $str, $matches);
            //fallthrough
            case 'text':
            default:
                $matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                break;
        }

        if (self::HAS_8BIT_CHARS($str)) {
            $CharSet = self::$CharSet;
        } else {
            $CharSet = static::CHARSET_ASCII;
        }

        //Q/B encoding adds 8 chars and the charset ("` =?<charset>?[QB]?<content>?=`").
        $overhead = 8 + strlen($CharSet);

        if ('mail' === self::$Mailer) {
            $maxlen = static::MAIL_MAX_LINE_LENGTH - $overhead;
        } else {
            $maxlen = static::MAX_LINE_LENGTH - $overhead;
        }

        //Select the encoding that produces the shortest output and/or prevents corruption.
        if ($matchcount > strlen($str) / 3) {
            //More than 1/3 of the content needs encoding, use B-encode.
            $encoding = 'B';
        } elseif ($matchcount > 0) {
            //Less than 1/3 of the content needs encoding, use Q-encode.
            $encoding = 'Q';
        } elseif (strlen($str) > $maxlen) {
            //No encoding needed, but value exceeds max line length, use Q-encode to prevent corruption.
            $encoding = 'Q';
        } else {
            //No reformatting needed
            $encoding = false;
        }

        switch ($encoding) {
            case 'B':
                if (self::HAS_MULTI_BYTES($str)) {
                    //Use a custom function which correctly encodes and wraps long
                    //multibyte strings without breaking lines within a character
                    $encoded = self::BASE64_ENCODE_WRAP_MB($str, "\n");
                } else {
                    $encoded = base64_encode($str);
                    $maxlen -= $maxlen % 4;
                    $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
                }
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $CharSet . "?$encoding?\\1?=", $encoded);
                break;
            case 'Q':
                $encoded = self::ENCODE_Q($str, $position);
                $encoded = self::WRAP_TEXT($encoded, $maxlen, true);
                $encoded = str_replace('=' . static::$LE, "\n", trim($encoded));
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $CharSet . "?$encoding?\\1?=", $encoded);
                break;
            default:
                return $str;
        }

        return trim(static::NOMALIZE_BREAKS($encoded));
    }


     /**
     * Encode a string in quoted-printable format.
     * According to RFC2045 section 6.7.
     *
     * @param string $string The text to encode
     *
     * @return string
     */
    public static function ENCODE_QP($string)
    {
        return static::NOMALIZE_BREAKS(quoted_printable_encode($string));
    }

    /**
     * Encode a string using Q encoding.
     *
     * @see http://tools.ietf.org/html/rfc2047#section-4.2
     *
     * @param string $str      the text to encode
     * @param string $position Where the text is going to be used, see the RFC for what that means
     *
     * @return string
     */
    public static function ENCODE_Q($str, $position = 'text')
    {
        //There should not be any EOL in the string
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $str);
        switch (strtolower($position)) {
            case 'phrase':
                //RFC 2047 section 5.3
                $pattern = '^A-Za-z0-9!*+\/ -';
                break;
            /*
             * RFC 2047 section 5.2.
             * Build $pattern without including delimiters and []
             */
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $pattern = '\(\)"';
            /* Intentional fall through */
            case 'text':
            default:
                //RFC 2047 section 5.1
                //Replace every high ascii, control, =, ? and _ characters
                $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
                break;
        }
        $matches = [];
        if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
            //If the string contains an '=', make sure it's the first thing we replace
            //so as to avoid double-encoding
            $eqkey = array_search('=', $matches[0], true);
            if (false !== $eqkey) {
                unset($matches[0][$eqkey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        //Replace spaces with _ (more readable than =20)
        //RFC 2047 section 4.2(2)
        return str_replace(' ', '_', $encoded);
    }

    /**
     * Check if a string contains multi-byte characters.
     *
     * @param string $str multi-byte text to wrap encode
     *
     * @return bool
     */
    public static function HAS_MULTI_BYTES($str)
    {
        if (function_exists('mb_strlen')) {
            return strlen($str) > mb_strlen($str, self::$CharSet);
        }

        //Assume no multibytes (we can't handle without mbstring functions anyway)
        return false;
    }

     /**
     * Encode and wrap long multibyte strings for mail headers
     * without breaking lines within a character.
     * Adapted from a function by paravoid.
     *
     * @see http://www.php.net/manual/en/function.mb-encode-mimeheader.php#60283
     *
     * @param string $str       multi-byte text to wrap encode
     * @param string $linebreak string to use as linefeed/end-of-line
     *
     * @return string
     */
    public static function BASE64_ENCODE_WRAP_MB($str, $linebreak = null)
    {
        $start = '=?' . self::$CharSet . '?B?';
        $end = '?=';
        $encoded = '';
        if (null === $linebreak) {
            $linebreak = static::$LE;
        }

        $mb_length = mb_strlen($str, self::$CharSet);
        //Each line must have length <= 75, including $start and $end
        $length = 75 - strlen($start) - strlen($end);
        //Average multi-byte ratio
        $ratio = $mb_length / strlen($str);
        //Base64 has a 4:3 ratio
        $avgLength = floor($length * $ratio * .75);

        $offset = 0;
        for ($i = 0; $i < $mb_length; $i += $offset) {
            $lookBack = 0;
            do {
                $offset = $avgLength - $lookBack;
                $chunk = mb_substr($str, $i, $offset, self::$CharSet);
                $chunk = base64_encode($chunk);
                ++$lookBack;
            } while (strlen($chunk) > $length);
            $encoded .= $chunk . $linebreak;
        }

        //Chomp the last linefeed
        return substr($encoded, 0, -strlen($linebreak));
    }

    /**
     * Word-wrap message.
     * For use with mailers that do not automatically perform wrapping
     * and for quoted-printable encoded messages.
     * Original written by philippe.
     *
     * @param string $message The message to wrap
     * @param int    $length  The line length to wrap to
     * @param bool   $qp_mode Whether to run in Quoted-Printable mode
     *
     * @return string
     */
    public static function WRAP_TEXT($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', static::$LE);
        } else {
            $soft_break = static::$LE;
        }
        //If utf-8 encoding is used, we will need to make sure we don't
        //split multibyte characters when we wrap
        $is_utf8 = static::CHARSET_UTF8 === strtolower(self::$CharSet);
        $lelen = strlen(static::$LE);
        $crlflen = strlen(static::$LE);

        $message = static::NOMALIZE_BREAKS($message);
        //Remove a trailing line break
        if (substr($message, -$lelen) === static::$LE) {
            $message = substr($message, 0, -$lelen);
        }

        //Split message into lines
        $lines = explode(static::$LE, $message);
        //Message will be rebuilt in here
        $message = '';
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $buf = '';
            $firstword = true;
            foreach ($words as $word) {
                if ($qp_mode && (strlen($word) > $length)) {
                    $space_left = $length - strlen($buf) - $crlflen;
                    if (!$firstword) {
                        if ($space_left > 20) {
                            $len = $space_left;
                            if ($is_utf8) {
                                $len = hkm_utf8CharBoundary($word, $len);
                            } elseif ('=' === substr($word, $len - 1, 1)) {
                                --$len;
                            } elseif ('=' === substr($word, $len - 2, 1)) {
                                $len -= 2;
                            }
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', static::$LE);
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while ($word !== '') {
                        if ($length <= 0) {
                            break;
                        }
                        $len = $length;
                        if ($is_utf8) {
                            $len = hkm_utf8CharBoundary($word, $len);
                        } elseif ('=' === substr($word, $len - 1, 1)) {
                            --$len;
                        } elseif ('=' === substr($word, $len - 2, 1)) {
                            $len -= 2;
                        }
                        $part = substr($word, 0, $len);
                        $word = (string) substr($word, $len);

                        if ($word !== '') {
                            $message .= $part . sprintf('=%s', static::$LE);
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    if (!$firstword) {
                        $buf .= ' ';
                    }
                    $buf .= $word;

                    if ('' !== $buf_o && strlen($buf) > $length) {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
                $firstword = false;
            }
            $message .= $buf . static::$LE;
        }

        return $message;
    }

     /**
     * Assemble message headers.
     *
     * @return string The assembled headers
     */
    public static function CREATE_HEADER()
    {
        
        $result = '';

        $result .= hkm_headerLine('Date', '' === self::$MessageDate ? hkm_rfcDate() : self::$MessageDate,static::$LE);

        //The To header is created automatically by mail(), so needs to be omitted here
        if ('mail' !== self::$Mailer) {
            if (self::$SingleTo) {
                foreach (self::$to as $toaddr) {
                    self::$SingleToArray[] = self::ADDR_FORMAT($toaddr);
                }
            } elseif (count(self::$to) > 0) {
                $result .= self::ADDR_APPEND('To', self::$to);
            } elseif (count(self::$cc) === 0) {
                $result .= hkm_headerLine('To', 'undisclosed-recipients:;',static::$LE);
            }
        }
        $result .= self::ADDR_APPEND('From', [[trim(self::$From), self::$FromName]]);

        //sendmail and mail() extract Cc from the header before sending
        if (count(self::$cc) > 0) {
            $result .= self::ADDR_APPEND('Cc', self::$cc);
        }

        //sendmail and mail() extract Bcc from the header before sending
        if (
            (
                'sendmail' === self::$Mailer || 'qmail' === self::$Mailer || 'mail' === self::$Mailer
            )
            && count(self::$bcc) > 0
        ) {
            $result .= self::ADDR_APPEND('Bcc', self::$bcc);
        }

        if (count(self::$ReplyTo) > 0) {
            $result .= self::ADDR_APPEND('Reply-To', self::$ReplyTo);
        }

        //mail() sets the subject itself
        if ('mail' !== self::$Mailer) {
            $result .= hkm_headerLine('Subject', self::ENCODE_HEADER(secureHeader(self::$Subject)),static::$LE);
        }

        //Only allow a custom message ID if it conforms to RFC 5322 section 3.6.4
        //https://tools.ietf.org/html/rfc5322#section-3.6.4
        if (
            '' !== self::$MessageID &&
            preg_match(
                '/^<((([a-z\d!#$%&\'*+\/=?^_`{|}~-]+(\.[a-z\d!#$%&\'*+\/=?^_`{|}~-]+)*)' .
                '|("(([\x01-\x08\x0B\x0C\x0E-\x1F\x7F]|[\x21\x23-\x5B\x5D-\x7E])' .
                '|(\\[\x01-\x09\x0B\x0C\x0E-\x7F]))*"))@(([a-z\d!#$%&\'*+\/=?^_`{|}~-]+' .
                '(\.[a-z\d!#$%&\'*+\/=?^_`{|}~-]+)*)|(\[(([\x01-\x08\x0B\x0C\x0E-\x1F\x7F]' .
                '|[\x21-\x5A\x5E-\x7E])|(\\[\x01-\x09\x0B\x0C\x0E-\x7F]))*\])))>$/Di',
                self::$MessageID
            )
        ) {
            self::$lastMessageID = self::$MessageID;
        } else {
            self::$lastMessageID = sprintf('<%s@%s>', self::$uniqueid, self::SERVER_HOSTNAME());
        }
        $result .= hkm_headerLine('Message-ID', self::$lastMessageID, static::$LE);
        if (null !== self::$Priority) {
            $result .= hkm_headerLine('X-Priority', self::$Priority,static::$LE);
        }
        if ('' === self::$XMailer) {
            $result .= hkm_headerLine(
                'X-Mailer',
                'PHPMailer ' . self::VERSION . ' (https://github.com/PHPMailer/PHPMailer)',
                static::$LE
            );
        } else {
            $myXmailer = trim(self::$XMailer);
            if ($myXmailer) {
                $result .= hkm_headerLine('X-Mailer', $myXmailer,static::$LE);
            }
        }

        if ('' !== self::$ConfirmReadingTo) {
            $result .= hkm_headerLine('Disposition-Notification-To', '<' . self::$ConfirmReadingTo . '>',static::$LE);
        }

        //Add custom headers
        foreach (self::$CustomHeader as $header) {
            $result .= hkm_headerLine(
                trim($header[0]),
                self::ENCODE_HEADER(trim($header[1])),
                static::$LE
            );
        }
        if (!self::$sign_key_file) {
            $result .= hkm_headerLine('MIME-Version', '1.0',static::$LE);
            $result .= self::GET_MAIL_MIME();
        }

        return $result;
    }

    /**
     * Return a formatted mail line.
     *
     * @param string $value
     *
     * @return string
     */
    public static function TEXT_LINE($value)
    {
        return $value . static::$LE;
    }


    /**
     * Check if an inline attachment is present.
     *
     * @return bool
     */
    public static function INLINE_IMAGE_EXISTS()
    {
        foreach (self::$attachment as $attachment) {
            if ('inline' === $attachment[6]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an attachment (non-inline) is present.
     *
     * @return bool
     */
    public static function ATTACHMENT_EXISTS()
    {
        foreach (self::$attachment as $attachment) {
            if ('attachment' === $attachment[6]) {
                return true;
            }
        }

        return false;
    }

     /**
     * Check that a string looks like an email address.
     * Validation patterns supported:
     * * `auto` Pick best pattern automatically;
     * * `pcre8` Use the squiloople.com pattern, requires PCRE > 8.0;
     * * `pcre` Use old PCRE implementation;
     * * `php` Use PHP built-in FILTER_VALIDATE_EMAIL;
     * * `html5` Use the pattern given by the HTML5 spec for 'email' type form input elements.
     * * `noregex` Don't use a regex: super fast, really dumb.
     * Alternatively you may pass in a callable to inject your own validator, for example:
     *
     * ```php
     * PHPMailer::VALIDATE_ADDRESS('user@example.com', function($address) {
     *     return (strpos($address, '@') !== false);
     * });
     * ```
     *
     * You can also set the PHPMailer::$validator static to a callable, allowing built-in methods to use your validator.
     *
     * @param string          $address       The email address to check
     * @param string|callable $patternselect Which pattern to use
     *
     * @return bool
     */
    public static function VALIDATE_ADDRESS($address, $patternselect = null)
    {
        if (null === $patternselect) {
            $patternselect = static::$validator;
        }
        //Don't allow strings as callables, see SECURITY.md and CVE-2021-3603
        if (is_callable($patternselect) && !is_string($patternselect)) {
            return call_user_func($patternselect, $address);
        }
        //Reject line breaks in addresses; it's valid RFC5322, but not RFC5321
        if (strpos($address, "\n") !== false || strpos($address, "\r") !== false) {
            return false;
        }
        switch ($patternselect) {
            case 'pcre': //Kept for BC
            case 'pcre8':
                /*
                 * A more complex and more permissive version of the RFC5322 regex on which FILTER_VALIDATE_EMAIL
                 * is based.
                 * In addition to the addresses allowed by filter_var, also permits:
                 *  * dotless domains: `a@b`
                 *  * comments: `1234 @ local(blah) .machine .example`
                 *  * quoted elements: `'"test blah"@example.org'`
                 *  * numeric TLDs: `a@b.123`
                 *  * unbracketed IPv4 literals: `a@192.168.0.1`
                 *  * IPv6 literals: 'first.last@[IPv6:a1::]'
                 * Not all of these will necessarily work for sending!
                 *
                 * @see       http://squiloople.com/2009/12/20/email-address-validation/
                 * @copyright 2009-2010 Michael Rushton
                 * Feel free to use and redistribute this code. But please keep this copyright notice.
                 */
                return (bool) preg_match(
                    '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
                    '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
                    '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
                    '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                    '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
                    '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                    '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
                    $address
                );
            case 'html5':
                /*
                 * This is the pattern used in the HTML5 spec for validation of 'email' type form input elements.
                 *
                 * @see https://html.spec.whatwg.org/#e-mail-state-(type=email)
                 */
                return (bool) preg_match(
                    '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
                    '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
                    $address
                );
            case 'php':
            default:
                return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
        }
    }

    /**
     * Send mail using the PHP mail() function.
     *
     * @see http://www.php.net/manual/en/book.mail.php
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     *
     * @return bool
     */
    protected static function MAIL_SEND($header, $body)
    {
        hkm_stripTrailingWSP($header) . static::$LE . static::$LE;

        $toArr = [];
        foreach (self::$to as $toaddr) {
            $toArr[] = self::ADDR_FORMAT($toaddr);
        }
        $to = implode(', ', $toArr);

        $params = null;
        //This sets the SMTP envelope sender which gets turned into a return-path header by the receiver
        //A space after `-f` is optional, but there is a long history of its presence
        //causing problems, so we don't use one
        //Exim docs: http://www.exim.org/exim-html-current/doc/html/spec_html/ch-the_exim_command_line.html
        //Sendmail docs: http://www.sendmail.org/~ca/email/man/sendmail.html
        //Qmail docs: http://www.qmail.org/man/man8/qmail-inject.html
        //Example problem: https://www.drupal.org/node/1057954
        //CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
        if (empty(self::$Sender) && !empty(ini_get('sendmail_from'))) {
            //PHP config has a sender address we can use
            self::$Sender = ini_get('sendmail_from');
        }
        if (!empty(self::$Sender) && static::VALIDATE_ADDRESS(self::$Sender)) {
            if (isShellSafe(self::$Sender)) {
                $params = sprintf('-f%s', self::$Sender);
            }
            $old_from = ini_get('sendmail_from');
            ini_set('sendmail_from', self::$Sender);
        }
        $result = false;
        if (self::$SingleTo && count($toArr) > 1) {
            foreach ($toArr as $toAddr) {
                $result = self::MAIL_PASS_THRU($toAddr, self::$Subject, $body, $header, $params);
                $addrinfo = static::PARSE_ADDRESSES($toAddr, true, self::$CharSet);
                self::DO_CALLBACK(
                    $result,
                    [[$addrinfo['address'], $addrinfo['name']]],
                    self::$cc,
                    self::$bcc,
                    self::$Subject,
                    $body,
                    self::$From,
                    []
                );
            }
        } else {
            $result = self::MAIL_PASS_THRU($to, self::$Subject, $body, $header, $params);
            self::DO_CALLBACK($result, self::$to, self::$cc, self::$bcc, self::$Subject, $body, self::$From, []);
        }
        if (isset($old_from)) {
            ini_set('sendmail_from', $old_from);
        }
        if (!$result) {
            throw new Exception(self::lang('instantiate'), self::STOP_CRITICAL);
        }

        return true;
    }

    /**
     * Send mail via SMTP.
     * Returns false if there is a bad MAIL FROM, RCPT, or DATA input.
     *
     * @see PHPMailer::setSMTPInstance() to use a different class.
     *
     * @uses \PHPMailer\PHPMailer\SMTP
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     *
     * @return bool
     */
    protected static function SMTP_SEND($header, $body)
    {
        hkm_stripTrailingWSP($header) . static::$LE . static::$LE;
        $bad_rcpt = [];
        if (!self::SMTP_CONNECT(self::$SMTPOptions)) {
            throw new Exception(self::lang('smtp_connect_failed'), self::STOP_CRITICAL);
        }
        //Sender already validated in PRE_SEND()
        if ('' === self::$Sender) {
            $smtp_from = self::$From;
        } else {
            $smtp_from = self::$Sender;
        }
        if (!self::$smtp::MAIL($smtp_from)) {
            self::SET_ERROR(self::lang('from_failed') . $smtp_from . ' : ' . implode(',', self::$smtp::GET_ERROR()));
            throw new Exception(self::$ErrorInfo, self::STOP_CRITICAL);
        }

        $callbacks = [];
        //Attempt to send to all recipients
        foreach ([self::$to, self::$cc, self::$bcc] as $togroup) {
            foreach ($togroup as $to) {
                if (!self::$smtp::RECIPIENT($to[0], self::$dsn)) {
                    $error = self::$smtp::GET_ERROR();
                    $bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
                    $isSent = false;
                } else {
                    $isSent = true;
                }

                $callbacks[] = ['issent' => $isSent, 'to' => $to[0], 'name' => $to[1]];
            }
        }

        //Only send the DATA command if we have viable recipients
        if ((count(self::$all_recipients) > count($bad_rcpt)) && !self::$smtp::DATA($header . $body)) {
            throw new Exception(self::lang('data_not_accepted'), self::STOP_CRITICAL);
        }

        $smtp_transaction_id = self::$smtp::GET_LAST_TRANSACTION_ID();

        if (self::$SMTPKeepAlive) {
            self::$smtp::RESET();
        } else {
            self::$smtp::QUIT();
            self::$smtp::CLOSE();
        }

        foreach ($callbacks as $cb) {
            self::DO_CALLBACK(
                $cb['issent'],
                [[$cb['to'], $cb['name']]],
                [],
                [],
                self::$Subject,
                $body,
                self::$From,
                ['smtp_transaction_id' => $smtp_transaction_id]
            );
        }

        //Create error message for any bad addresses
        if (count($bad_rcpt) > 0) {
            $errstr = '';
            foreach ($bad_rcpt as $bad) {
                $errstr .= $bad['to'] . ': ' . $bad['error'];
            }
            throw new Exception(self::lang('recipients_failed') . $errstr, self::STOP_CONTINUE);
        }

        return true;
    }

    /**
     * Close the active SMTP session if one exists.
     */
    public  static function SMTP_CLOSE()
    {
        if ((null !== self::$smtp) && self::$smtp::CONNECTED()) {
            self::$smtp::QUIT();
            self::$smtp::CLOSE();
        }
    }


    /**
     * Set the public and private key files and password for S/MIME signing.
     *
     * @param string $cert_filename
     * @param string $key_filename
     * @param string $key_pass            Password for private key
     * @param string $extracerts_filename Optional path to chain certificate
     */
    public static function SIGN($cert_filename, $key_filename, $key_pass, $extracerts_filename = '')
    {
        self::$sign_cert_file = $cert_filename;
        self::$sign_key_file = $key_filename;
        self::$sign_key_pass = $key_pass;
        self::$sign_extracerts_file = $extracerts_filename;
    }

     /**
     * Quoted-Printable-encode a DKIM header.
     *
     * @param string $txt
     *
     * @return string
     */
    public static function DKIM_QP($txt)
    {
        $line = '';
        $len = strlen($txt);
        for ($i = 0; $i < $len; ++$i) {
            $ord = ord($txt[$i]);
            if (((0x21 <= $ord) && ($ord <= 0x3A)) || $ord === 0x3C || ((0x3E <= $ord) && ($ord <= 0x7E))) {
                $line .= $txt[$i];
            } else {
                $line .= '=' . sprintf('%02X', $ord);
            }
        }

        return $line;
    }

    /**
     * Generate a DKIM signature.
     *
     * @param string $signHeader
     *
     * @throws Exception
     *
     * @return string The DKIM signature value
     */
    public static function DKIM_SIGN($signHeader)
    {
        if (!defined('PKCS7_TEXT')) {
            if (self::$exceptions) {
                throw new Exception(self::lang('extension_missing') . 'openssl');
            }

            return '';
        }
        $privKeyStr = !empty(self::$DKIM_private_string) ?
            self::$DKIM_private_string :
            file_get_contents(self::$DKIM_private);
        if ('' !== self::$DKIM_passphrase) {
            $privKey = openssl_pkey_get_private($privKeyStr, self::$DKIM_passphrase);
        } else {
            $privKey = openssl_pkey_get_private($privKeyStr);
        }
        if (openssl_sign($signHeader, $signature, $privKey, 'sha256WithRSAEncryption')) {
            if (\PHP_MAJOR_VERSION < 8) {
                openssl_pkey_free($privKey);
            }

            return base64_encode($signature);
        }
        if (\PHP_MAJOR_VERSION < 8) {
            openssl_pkey_free($privKey);
        }

        return '';
    }

    /**
     * Generate a DKIM canonicalization header.
     * Uses the 'relaxed' algorithm from RFC6376 section 3.4.2.
     * Canonicalized headers should *always* use CRLF, regardless of mailer setting.
     *
     * @see https://tools.ietf.org/html/rfc6376#section-3.4.2
     *
     * @param string $signHeader Header
     *
     * @return string
     */
    public static function DKIM_HEADER_C($signHeader)
    {
        //Normalize breaks to CRLF (regardless of the mailer)
        $signHeader = static::NOMALIZE_BREAKS($signHeader, self::CRLF);
        //Unfold header lines
        //Note PCRE \s is too broad a definition of whitespace; RFC5322 defines it as `[ \t]`
        //@see https://tools.ietf.org/html/rfc5322#section-2.2
        //That means this may break if you do something daft like put vertical tabs in your headers.
        $signHeader = preg_replace('/\r\n[ \t]+/', ' ', $signHeader);
        //Break headers out into an array
        $lines = explode(self::CRLF, $signHeader);
        foreach ($lines as $key => $line) {
            //If the header is missing a :, skip it as it's invalid
            //This is likely to happen because the explode() above will also split
            //on the trailing LE, leaving an empty line
            if (strpos($line, ':') === false) {
                continue;
            }
            list($heading, $value) = explode(':', $line, 2);
            //Lower-case header name
            $heading = strtolower($heading);
            //Collapse white space within the value, also convert WSP to space
            $value = preg_replace('/[ \t]+/', ' ', $value);
            //RFC6376 is slightly unclear here - it says to delete space at the *end* of each value
            //But then says to delete space before and after the colon.
            //Net result is the same as trimming both ends of the value.
            //By elimination, the same applies to the field name
            $lines[$key] = trim($heading, " \t") . ':' . trim($value, " \t");
        }

        return implode(self::CRLF, $lines);
    }

    /**
     * Generate a DKIM canonicalization body.
     * Uses the 'simple' algorithm from RFC6376 section 3.4.3.
     * Canonicalized bodies should *always* use CRLF, regardless of mailer setting.
     *
     * @see https://tools.ietf.org/html/rfc6376#section-3.4.3
     *
     * @param string $body Message Body
     *
     * @return string
     */
    public static function DKIM_BODY_C($body)
    {
        if (empty($body)) {
            return self::CRLF;
        }
        //Normalize line endings to CRLF
        $body = static::NOMALIZE_BREAKS($body, self::CRLF);

        //Reduce multiple trailing line breaks to a single one
    hkm_stripTrailingWSP($body) . self::CRLF;
    }

    /**
     * Create the DKIM header and body in a new message header.
     *
     * @param string $headers_line Header lines
     * @param string $subject      Subject
     * @param string $body         Body
     *
     * @throws Exception
     *
     * @return string
     */
    public static function DKIM_ADD($headers_line, $subject, $body)
    {
        $DKIMsignatureType = 'rsa-sha256'; //Signature & hash algorithms
        $DKIMcanonicalization = 'relaxed/simple'; //Canonicalization methods of header & body
        $DKIMquery = 'dns/txt'; //Query method
        $DKIMtime = time();
        //Always sign these headers without being asked
        //Recommended list from https://tools.ietf.org/html/rfc6376#section-5.4.1
        $autoSignHeaders = [
            'from',
            'to',
            'cc',
            'date',
            'subject',
            'reply-to',
            'message-id',
            'content-type',
            'mime-version',
            'x-mailer',
        ];
        if (stripos($headers_line, 'Subject') === false) {
            $headers_line .= 'Subject: ' . $subject . static::$LE;
        }
        $headerLines = explode(static::$LE, $headers_line);
        $currentHeaderLabel = '';
        $currentHeaderValue = '';
        $parsedHeaders = [];
        $headerLineIndex = 0;
        $headerLineCount = count($headerLines);
        foreach ($headerLines as $headerLine) {
            $matches = [];
            if (preg_match('/^([^ \t]*?)(?::[ \t]*)(.*)$/', $headerLine, $matches)) {
                if ($currentHeaderLabel !== '') {
                    //We were previously in another header; This is the start of a new header, so save the previous one
                    $parsedHeaders[] = ['label' => $currentHeaderLabel, 'value' => $currentHeaderValue];
                }
                $currentHeaderLabel = $matches[1];
                $currentHeaderValue = $matches[2];
            } elseif (preg_match('/^[ \t]+(.*)$/', $headerLine, $matches)) {
                //This is a folded continuation of the current header, so unfold it
                $currentHeaderValue .= ' ' . $matches[1];
            }
            ++$headerLineIndex;
            if ($headerLineIndex >= $headerLineCount) {
                //This was the last line, so finish off this header
                $parsedHeaders[] = ['label' => $currentHeaderLabel, 'value' => $currentHeaderValue];
            }
        }
        $copiedHeaders = [];
        $headersToSignKeys = [];
        $headersToSign = [];
        foreach ($parsedHeaders as $header) {
            //Is this header one that must be included in the DKIM signature?
            if (in_array(strtolower($header['label']), $autoSignHeaders, true)) {
                $headersToSignKeys[] = $header['label'];
                $headersToSign[] = $header['label'] . ': ' . $header['value'];
                if (self::$DKIM_copyHeaderFields) {
                    $copiedHeaders[] = $header['label'] . ':' . //Note no space after this, as per RFC
                        str_replace('|', '=7C', self::DKIM_QP($header['value']));
                }
                continue;
            }
            //Is this an extra custom header we've been asked to sign?
            if (in_array($header['label'], self::$DKIM_extraHeaders, true)) {
                //Find its value in custom headers
                foreach (self::$CustomHeader as $customHeader) {
                    if ($customHeader[0] === $header['label']) {
                        $headersToSignKeys[] = $header['label'];
                        $headersToSign[] = $header['label'] . ': ' . $header['value'];
                        if (self::$DKIM_copyHeaderFields) {
                            $copiedHeaders[] = $header['label'] . ':' . //Note no space after this, as per RFC
                                str_replace('|', '=7C', self::DKIM_QP($header['value']));
                        }
                        //Skip straight to the next header
                        continue 2;
                    }
                }
            }
        }
        $copiedHeaderFields = '';
        if (self::$DKIM_copyHeaderFields && count($copiedHeaders) > 0) {
            //Assemble a DKIM 'z' tag
            $copiedHeaderFields = ' z=';
            $first = true;
            foreach ($copiedHeaders as $copiedHeader) {
                if (!$first) {
                    $copiedHeaderFields .= static::$LE . ' |';
                }
                //Fold long values
                if (strlen($copiedHeader) > self::STD_LINE_LENGTH - 3) {
                    $copiedHeaderFields .= substr(
                        chunk_split($copiedHeader, self::STD_LINE_LENGTH - 3, static::$LE . self::FWS),
                        0,
                        -strlen(static::$LE . self::FWS)
                    );
                } else {
                    $copiedHeaderFields .= $copiedHeader;
                }
                $first = false;
            }
            $copiedHeaderFields .= ';' . static::$LE;
        }
        $headerKeys = ' h=' . implode(':', $headersToSignKeys) . ';' . static::$LE;
        $headerValues = implode(static::$LE, $headersToSign);
        $body = self::DKIM_BODY_C($body);
        //Base64 of packed binary SHA-256 hash of body
        $DKIMb64 = base64_encode(pack('H*', hash('sha256', $body)));
        $ident = '';
        if ('' !== self::$DKIM_identity) {
            $ident = ' i=' . self::$DKIM_identity . ';' . static::$LE;
        }
        //The DKIM-Signature header is included in the signature *except for* the value of the `b` tag
        //which is appended after calculating the signature
        //https://tools.ietf.org/html/rfc6376#section-3.5
        $dkimSignatureHeader = 'DKIM-Signature: v=1;' .
            ' d=' . self::$DKIM_domain . ';' .
            ' s=' . self::$DKIM_selector . ';' . static::$LE .
            ' a=' . $DKIMsignatureType . ';' .
            ' q=' . $DKIMquery . ';' .
            ' t=' . $DKIMtime . ';' .
            ' c=' . $DKIMcanonicalization . ';' . static::$LE .
            $headerKeys .
            $ident .
            $copiedHeaderFields .
            ' bh=' . $DKIMb64 . ';' . static::$LE .
            ' b=';
        //Canonicalize the set of headers
        $canonicalizedHeaders = self::DKIM_HEADER_C(
            $headerValues . static::$LE . $dkimSignatureHeader
        );
        $signature = self::DKIM_SIGN($canonicalizedHeaders);
        $signature = trim(chunk_split($signature, self::STD_LINE_LENGTH - 3, static::$LE . self::FWS));

        return static::NOMALIZE_BREAKS($dkimSignatureHeader . $signature);
    }



    /**
     * Detect if a string contains a line longer than the maximum line length
     * allowed by RFC 2822 section 2.1.1.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function HAS_LINE_LONGER_THAN_MAX($str)
    {
        return (bool) preg_match('/^(.{' . (self::MAX_LINE_LENGTH + strlen(static::$LE)) . ',})/m', $str);
    }

     /**
     * Attach all file, string, and binary attachments to the message.
     * Returns an empty string on failure.
     *
     * @param string $disposition_type
     * @param string $boundary
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function ATTACH_ALL($disposition_type, $boundary)
    {
        //Return text of body
        $mime = [];
        $cidUniq = [];
        $incl = [];

        //Add all attachments
        foreach (self::$attachment as $attachment) {
            //Check if it is a valid disposition_filter
            if ($attachment[6] === $disposition_type) {
                //Check for string attachment
                $string = '';
                $path = '';
                $bString = $attachment[5];
                if ($bString) {
                    $string = $attachment[0];
                } else {
                    $path = $attachment[0];
                }

                $inclhash = hash('sha256', serialize($attachment));
                if (in_array($inclhash, $incl, true)) {
                    continue;
                }
                $incl[] = $inclhash;
                $name = $attachment[2];
                $encoding = $attachment[3];
                $type = $attachment[4];
                $disposition = $attachment[6];
                $cid = $attachment[7];
                if ('inline' === $disposition && array_key_exists($cid, $cidUniq)) {
                    continue;
                }
                $cidUniq[$cid] = true;

                $mime[] = sprintf('--%s%s', $boundary, static::$LE);
                //Only include a filename property if we have one
                if (!empty($name)) {
                    $mime[] = sprintf(
                        'Content-Type: %s; name=%s%s',
                        $type,
                        hkm_quotedString(self::ENCODE_HEADER(secureHeader($name))),
                        static::$LE
                    );
                } else {
                    $mime[] = sprintf(
                        'Content-Type: %s%s',
                        $type,
                        static::$LE
                    );
                }
                //RFC1341 part 5 says 7bit is assumed if not specified
                if (static::ENCODING_7BIT !== $encoding) {
                    $mime[] = sprintf('Content-Transfer-Encoding: %s%s', $encoding, static::$LE);
                }

                //Only set Content-IDs on inline attachments
                if ((string) $cid !== '' && $disposition === 'inline') {
                    $mime[] = 'Content-ID: <' . self::ENCODE_HEADER(secureHeader($cid)) . '>' . static::$LE;
                }

                //Allow for bypassing the Content-Disposition header
                if (!empty($disposition)) {
                    $encoded_name = self::ENCODE_HEADER(secureHeader($name));
                    if (!empty($encoded_name)) {
                        $mime[] = sprintf(
                            'Content-Disposition: %s; filename=%s%s',
                            $disposition,
                            hkm_quotedString($encoded_name),
                            static::$LE . static::$LE
                        );
                    } else {
                        $mime[] = sprintf(
                            'Content-Disposition: %s%s',
                            $disposition,
                            static::$LE . static::$LE
                        );
                    }
                } else {
                    $mime[] = static::$LE;
                }

                //Encode as string attachment
                if ($bString) {
                    $mime[] = self::ENCODE_STRING($string, $encoding);
                } else {
                    $mime[] = self::ENCODE_FILE($path, $encoding);
                }
                if (self::IS_ERROR()) {
                    return '';
                }
                $mime[] = static::$LE;
            }
        }

        $mime[] = sprintf('--%s--%s', $boundary, static::$LE);

        return implode('', $mime);
    }

    /**
     * Call mail() in a safe_mode-aware fashion.
     * Also, unless sendmail_path points to sendmail (or something that
     * claims to be sendmail), don't pass params (not a perfect fix,
     * but it will do).
     *
     * @param string      $to      To
     * @param string      $subject Subject
     * @param string      $body    Message Body
     * @param string      $header  Additional Header(s)
     * @param string|null $params  Params
     *
     * @return bool
     */
    private static function MAIL_PASS_THRU($to, $subject, $body, $header, $params)
    {
        //Check overloading of mail function to avoid double-encoding
        if (ini_get('mbstring.func_overload') & 1) {
            $subject = hkm_secureHeader($subject);
        } else {
            $subject = self::ENCODE_HEADER(secureHeader($subject));
        }
        $seder = self::$Sender;
        //Calling mail() with null params breaks
        self::DEBUG('Sending with mail()');
        self::DEBUG('Sendmail path: ' . ini_get('sendmail_path'));
        self::DEBUG("Envelope sender: {$seder}");
        self::DEBUG("To: {$to}");
        self::DEBUG("Subject: {$subject}");
        self::DEBUG("Headers: {$header}");
        if (!self::$UseSendmailOptions || null === $params) {
            $result = @mail($to, $subject, $body, $header);
        } else {
            self::DEBUG("Additional params: {$params}");
            $result = @mail($to, $subject, $body, $header, $params);
        }
        self::DEBUG('Result: ' . ($result ? 'true' : 'false'));
        return $result;
    }

    

    /**
     * Send messages using SMTP.
     */
    public static function IS_SMTP()
    {
        self::$Mailer = 'smtp';
    }

    /**
     * Send messages using PHP's mail() function.
     */
    public static function IS_MAIL()
    {
        self::$Mailer = 'mail';
    }

    /**
     * Send messages using $Sendmail.
     */
    public static function IS_SENDMAIL()
    {
        $ini_sendmail_path = ini_get('sendmail_path');

        if (false === stripos($ini_sendmail_path, 'sendmail')) {
            self::$Sendmail = '/usr/sbin/sendmail';
        } else {
            self::$Sendmail = $ini_sendmail_path;
        }
        self::$Mailer = 'sendmail';
    }

    /**
     * Send messages using qmail.
     */
    public static function IS_QMAIL()
    {
        $ini_sendmail_path = ini_get('sendmail_path');

        if (false === stripos($ini_sendmail_path, 'qmail')) {
            self::$Sendmail = '/var/qmail/bin/qmail-inject';
        } else {
            self::$Sendmail = $ini_sendmail_path;
        }
        self::$Mailer = 'qmail';
    }


   


    /**
     * Perform a callback.
     *
     * @param bool   $isSent
     * @param array  $to
     * @param array  $cc
     * @param array  $bcc
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param array  $extra
     */
    protected static function DO_CALLBACK($isSent, $to, $cc, $bcc, $subject, $body, $from, $extra)
    {
        if (!empty(self::$action_function) && is_callable(self::$action_function)) {
            call_user_func(self::$action_function, $isSent, $to, $cc, $bcc, $subject, $body, $from, $extra);
        }
    }

     /**
     * Set or reset instance properties.
     * You should avoid this function - it's more verbose, less efficient, more error-prone and
     * harder to debug than setting properties directly.
     * Usage Example:
     * `$mail->set('SMTPSecure', static::ENCRYPTION_STARTTLS);`
     *   is the same as:
     * `$mail->SMTPSecure = static::ENCRYPTION_STARTTLS;`.
     *
     * @param string $name  The property name to set
     * @param mixed  $value The value to set the property to
     *
     * @return bool
     */
    public static function SET($name, $value = '')
    {
        if (property_exists(self::$thiss, $name)) {
            self::$$name = $value;

            return true;
        }
        self::SET_ERROR(self::lang('variable_set') . $name);

        return false;
    }



    /**
     * Parse and validate a string containing one or more RFC822-style comma-separated email addresses
     * of the form "display name <address>" into an array of name/address pairs.
     * Uses the imap_rfc822_parse_adrlist function if the IMAP extension is available.
     * Note that quotes in the name part are removed.
     *
     * @see http://www.andrew.cmu.edu/user/agreen1/testing/mrbs/web/Mail/RFC822.php A more careful implementation
     *
     * @param string $addrstr The address list string
     * @param bool   $useimap Whether to use the IMAP extension to parse the list
     *
     * @return array
     */
    public static function PARSE_ADDRESSES($addrstr, $useimap = true, $CharSet = self::CHARSET_ISO88591)
    {
        $addresses = [];
        if ($useimap && function_exists('imap_rfc822_parse_adrlist')) {
            //Use this built-in parser if it's available
            $list = imap_rfc822_parse_adrlist($addrstr, '');
            // Clear any potential IMAP errors to get rid of notices being thrown at end of script.
            imap_errors();
            foreach ($list as $address) {
                if (
                    '.SYNTAX-ERROR.' !== $address->host &&
                    static::VALIDATE_ADDRESS($address->mailbox . '@' . $address->host)
                ) {
                    //Decode the name part if it's present and encoded
                    if (
                        property_exists($address, 'personal') &&
                        //Check for a Mbstring constant rather than using extension_loaded, which is sometimes disabled
                        defined('MB_CASE_UPPER') &&
                        preg_match('/^=\?.*\?=$/s', $address->personal)
                    ) {
                        $origCharset = mb_internal_encoding();
                        mb_internal_encoding($CharSet);
                        //Undo any RFC2047-encoded spaces-as-underscores
                        $address->personal = str_replace('_', '=20', $address->personal);
                        //Decode the name
                        $address->personal = mb_decode_mimeheader($address->personal);
                        mb_internal_encoding($origCharset);
                    }

                    $addresses[] = [
                        'name' => (property_exists($address, 'personal') ? $address->personal : ''),
                        'address' => $address->mailbox . '@' . $address->host,
                    ];
                }
            }
        } else {
            //Use this simpler parser
            $list = explode(',', $addrstr);
            foreach ($list as $address) {
                $address = trim($address);
                //Is there a separate name part?
                if (strpos($address, '<') === false) {
                    //No separate name, just use the whole thing
                    if (static::VALIDATE_ADDRESS($address)) {
                        $addresses[] = [
                            'name' => '',
                            'address' => $address,
                        ];
                    }
                } else {
                    list($name, $email) = explode('<', $address);
                    $email = trim(str_replace('>', '', $email));
                    $name = trim($name);
                    if (static::VALIDATE_ADDRESS($email)) {
                        //Check for a Mbstring constant rather than using extension_loaded, which is sometimes disabled
                        //If this name is encoded, decode it
                        if (defined('MB_CASE_UPPER') && preg_match('/^=\?.*\?=$/s', $name)) {
                            $origCharset = mb_internal_encoding();
                            mb_internal_encoding($CharSet);
                            //Undo any RFC2047-encoded spaces-as-underscores
                            $name = str_replace('_', '=20', $name);
                            //Decode the name
                            $name = mb_decode_mimeheader($name);
                            mb_internal_encoding($origCharset);
                        }
                        $addresses[] = [
                            //Remove any surrounding quotes and spaces from the name
                            'name' => trim($name, '\'" '),
                            'address' => $email,
                        ];
                    }
                }
            }
        }

        return $addresses;
    }

    

   

    

  

    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     *
     * @throws Exception
     *
     * @return bool false on error - See the ErrorInfo property for details of the error
     */
    public static function SEND()
    {
        try {
            if (!self::PRE_SEND()) {
                return false;
            }

            return self::POST_SEND();
        } catch (Exception $exc) {
            self::$mailHeader = '';
            self::SET_ERROR($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }

            return false;
        }
    }

    /**
     * Prepare a message for sending.
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function PRE_SEND()
    {
        if (
            'smtp' === self::$Mailer
            || ('mail' === self::$Mailer && (\PHP_VERSION_ID >= 80000 || stripos(PHP_OS, 'WIN') === 0))
        ) {
            //SMTP mandates RFC-compliant line endings
            //and it's also used with mail() on Windows
            static::SET_LE(self::CRLF);
        } else {
            //Maintain backward compatibility with legacy Linux command line mailers
            static::SET_LE(PHP_EOL);
        }
        //Check for buggy PHP versions that add a header with an incorrect line break
        if (
            'mail' === self::$Mailer
            && ((\PHP_VERSION_ID >= 70000 && \PHP_VERSION_ID < 70017)
                || (\PHP_VERSION_ID >= 70100 && \PHP_VERSION_ID < 70103))
            && ini_get('mail.add_x_header') === '1'
            && stripos(PHP_OS, 'WIN') === 0
        ) {
            trigger_error(self::lang('buggy_php'), E_USER_WARNING);
        }

        try {
            self::$error_count = 0; //Reset errors
            self::$mailHeader = '';

            //Dequeue recipient and Reply-To addresses with IDN
            foreach (array_merge(self::$RecipientsQueue, self::$ReplyToQueue) as $params) {
                $params[1] = self::PUNY_ENCODE_ADDRESS($params[1]);
                call_user_func_array([self::$thiss, 'ADD_AN_ADDRESS'], $params);
            }
            if (count(self::$to) + count(self::$cc) + count(self::$bcc) < 1) {
                throw new Exception(self::lang('provide_address'), self::STOP_CRITICAL);
            }

            //Validate From, Sender, and ConfirmReadingTo addresses
            foreach (['From', 'Sender', 'ConfirmReadingTo'] as $address_kind) {
                self::$$address_kind = trim(self::$$address_kind);
                if (empty(self::$$address_kind)) {
                    continue;
                }
                self::$$address_kind = self::PUNY_ENCODE_ADDRESS(self::$$address_kind);
                if (!static::VALIDATE_ADDRESS(self::$$address_kind)) {
                    $error_message = sprintf(
                        '%s (%s): %s',
                        self::lang('invalid_address'),
                        $address_kind,
                        self::$$address_kind
                    );
                    self::SET_ERROR($error_message);
                    self::DEBUG($error_message);
                    if (self::$exceptions) {
                        throw new Exception($error_message);
                    }

                    return false;
                }
            }

            //Set whether the message is multipart/alternative
            if (self::ALTERNATIVE_EXISTS()) {
                self::$ContentType = static::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
            }

            self::SET_MESSAGE_TYPE();
            //Refuse to send an empty message unless we are specifically allowing it
            if (!self::$AllowEmpty && empty(self::$Body)) {
                throw new Exception(self::lang('empty_message'), self::STOP_CRITICAL);
            }

            //Trim subject consistently
            self::$Subject = trim(self::$Subject);
            //Create body before headers in case body makes changes to headers (e.g. altering transfer encoding)
            self::$MIMEHeader = '';
            self::$MIMEBody = self::CREATE_BODY();
            //createBody may have added some headers, so retain them
            $tempheaders = self::$MIMEHeader;
            self::$MIMEHeader = self::CREATE_HEADER();
            self::$MIMEHeader .= $tempheaders;

            //To capture the complete message when using mail(), create
            //an extra header list which CHREATE_HEADER() doesn't fold in
            if ('mail' === self::$Mailer) {
                if (count(self::$to) > 0) {
                    self::$mailHeader .= self::ADDR_APPEND('To', self::$to);
                } else {
                    self::$mailHeader .= hkm_headerLine('To', 'undisclosed-recipients:;',static::$LE);
                }
                self::$mailHeader .= hkm_headerLine(
                    'Subject',
                    self::ENCODE_HEADER(secureHeader(self::$Subject)),static::$LE
                );
            }

            //Sign with DKIM if enabled
            if (
                !empty(self::$DKIM_domain)
                && !empty(self::$DKIM_selector)
                && (!empty(self::$DKIM_private_string)
                    || (!empty(self::$DKIM_private)
                        && hkm_isPermittedPath(self::$DKIM_private)
                        && file_exists(self::$DKIM_private)
                    )
                )
            ) {
                $header_dkim = self::$DKIM_Add(
                    self::$MIMEHeader . self::$mailHeader,
                    self::ENCODE_HEADER(secureHeader(self::$Subject)),
                    self::$MIMEBody
                );
                self::$MIMEHeader = hkm_stripTrailingWSP(self::$MIMEHeader) . static::$LE .
                    static::NOMALIZE_BREAKS($header_dkim) . static::$LE;
            }

            return true;
        } catch (Exception $exc) {
            self::SET_ERROR($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }

            return false;
        }
    }

    /**
     * Actually send a message via the selected mechanism.
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function POST_SEND()
    {
        try {
            //Choose the mailer and send through it
            switch (self::$Mailer) {
                case 'sendmail':
                case 'qmail':
                    return self::SENDMAIL_SEND(self::$MIMEHeader, self::$MIMEBody);
                case 'smtp':
                    return self::SMTP_SEND(self::$MIMEHeader, self::$MIMEBody);
                case 'mail':
                    return self::MAIL_SEND(self::$MIMEHeader, self::$MIMEBody);
                default:
                    $sendMethod = self::$Mailer . '_Send';
                    if (method_exists(self::$thiss, strtoupper($sendMethod))) {
                        return self::$$sendMethod(self::$MIMEHeader, self::$MIMEBody);
                    }

                    return self::MAIL_SEND(self::$MIMEHeader, self::$MIMEBody);
            }
        } catch (Exception $exc) {
            if (self::$Mailer === 'smtp' && self::$SMTPKeepAlive == true) {
                self::$smtp->reset();
            }
            self::SET_ERROR($exc->getMessage());
            self::DEBUG($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }
        }

        return false;
    }

    /**
     * Send mail using the $Sendmail program.
     *
     * @see PHPMailer::$Sendmail
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     *
     * @return bool
     */
    protected static function SENDMAIL_SEND($header, $body)
    {
        if (self::$Mailer === 'qmail') {
            self::DEBUG('Sending with qmail');
        } else {
            self::DEBUG('Sending with sendmail');
        }
        $header = hkm_stripTrailingWSP($header) . static::$LE . static::$LE;
        //This sets the SMTP envelope sender which gets turned into a return-path header by the receiver
        //A space after `-f` is optional, but there is a long history of its presence
        //causing problems, so we don't use one
        //Exim docs: http://www.exim.org/exim-html-current/doc/html/spec_html/ch-the_exim_command_line.html
        //Sendmail docs: http://www.sendmail.org/~ca/email/man/sendmail.html
        //Qmail docs: http://www.qmail.org/man/man8/qmail-inject.html
        //Example problem: https://www.drupal.org/node/1057954
        if (empty(self::$Sender) && !empty(ini_get('sendmail_from'))) {
            //PHP config has a sender address we can use
            self::$Sender = ini_get('sendmail_from');
        }
        //CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
        if (!empty(self::$Sender) && static::VALIDATE_ADDRESS(self::$Sender) &&hkm_isShellSafe(self::$Sender)) {
            if (self::$Mailer === 'qmail') {
                $sendmailFmt = '%s -f%s';
            } else {
                $sendmailFmt = '%s -oi -f%s -t';
            }
        } else {
            //allow sendmail to choose a default envelope sender. It may
            //seem preferable to force it to use the From header as with
            //SMTP, but that introduces new problems (see
            //<https://github.com/PHPMailer/PHPMailer/issues/2298>), and
            //it has historically worked this way.
            $sendmailFmt = '%s -oi -t';
        }

        $sendmail = sprintf($sendmailFmt, escapeshellcmd(self::$Sendmail), self::$Sender);
        self::DEBUG('Sendmail path: ' . self::$Sendmail);
        self::DEBUG('Sendmail command: ' . $sendmail);
        self::DEBUG('Envelope sender: ' . self::$Sender);
        self::DEBUG("Headers: {$header}");

        if (self::$SingleTo) {
            foreach (self::$SingleToArray as $toAddr) {
                $mail = @popen($sendmail, 'w');
                if (!$mail) {
                    throw new Exception(self::lang('execute') . self::$Sendmail, self::STOP_CRITICAL);
                }
                self::DEBUG("To: {$toAddr}");
                fwrite($mail, 'To: ' . $toAddr . "\n");
                fwrite($mail, $header);
                fwrite($mail, $body);
                $result = pclose($mail);
                $addrinfo = static::PARSE_ADDRESSES($toAddr, true, self::$CharSet);
                self::DO_CALLBACK(
                    ($result === 0),
                    [[$addrinfo['address'], $addrinfo['name']]],
                    self::$cc,
                    self::$bcc,
                    self::$Subject,
                    $body,
                    self::$From,
                    []
                );
                self::DEBUG("Result: " . ($result === 0 ? 'true' : 'false'));
                if (0 !== $result) {
                    throw new Exception(self::lang('execute') . self::$Sendmail, self::STOP_CRITICAL);
                }
            }
        } else {
            $mail = @popen($sendmail, 'w');
            if (!$mail) {
                throw new Exception(self::lang('execute') . self::$Sendmail, self::STOP_CRITICAL);
            }
            fwrite($mail, $header);
            fwrite($mail, $body);
            $result = pclose($mail);
            self::DO_CALLBACK(
                ($result === 0),
                self::$to,
                self::$cc,
                self::$bcc,
                self::$Subject,
                $body,
                self::$From,
                []
            );
            self::DEBUG("Result: " . ($result === 0 ? 'true' : 'false'));
            if (0 !== $result) {
                throw new Exception(self::lang('execute') . self::$Sendmail, self::STOP_CRITICAL);
            }
        }

        return true;
    }

    

    

    /**
     * Assemble the message body.
     * Returns an empty string on failure.
     *
     * @throws Exception
     *
     * @return string The assembled message body
     */
    public static function CREATE_BODY()
    {
        $body = '';
        //Create unique IDs and preset boundaries
        self::$uniqueid =HKM_GENERATE_ID();
        self::$boundary[1] = 'b1_' . self::$uniqueid;
        self::$boundary[2] = 'b2_' . self::$uniqueid;
        self::$boundary[3] = 'b3_' . self::$uniqueid;

        if (self::$sign_key_file) {
            $body .= self::GET_MAIL_MIME() . static::$LE;
        }

        self::SET_WORD_WRAP();

        $bodyEncoding = self::$Encoding;
        $bodyCharSet = self::$CharSet;
        //Can we do a 7-bit downgrade?
        if (static::ENCODING_8BIT === $bodyEncoding && !self::HAS_8BIT_CHARS(self::$Body)) {
            $bodyEncoding = static::ENCODING_7BIT;
            //All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
            $bodyCharSet = static::CHARSET_ASCII;
        }
        //If lines are too long, and we're not already using an encoding that will shorten them,
        //change to quoted-printable transfer encoding for the body part only
        if (static::ENCODING_BASE64 !== self::$Encoding && static::HAS_LINE_LONGER_THAN_MAX(self::$Body)) {
            $bodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }

        $altBodyEncoding = self::$Encoding;
        $altBodyCharSet = self::$CharSet;
        //Can we do a 7-bit downgrade?
        if (static::ENCODING_8BIT === $altBodyEncoding && !self::HAS_8BIT_CHARS(self::$AltBody)) {
            $altBodyEncoding = static::ENCODING_7BIT;
            //All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
            $altBodyCharSet = static::CHARSET_ASCII;
        }
        //If lines are too long, and we're not already using an encoding that will shorten them,
        //change to quoted-printable transfer encoding for the alt body part only
        if (static::ENCODING_BASE64 !== $altBodyEncoding && static::HAS_LINE_LONGER_THAN_MAX(self::$AltBody)) {
            $altBodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        //Use this as a preamble in all multipart message types
        $mimepre = 'This is a multi-part message in MIME format.' . static::$LE . static::$LE;
        switch (self::$message_type) {
            case 'inline':
                $body .= $mimepre;
                $body .= self::GET_BOUNDARY(self::$boundary[1], $bodyCharSet, '', $bodyEncoding);
                $body .= self::ENCODE_STRING(self::$Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('inline', self::$boundary[1]);
                break;
            case 'attach':
                $body .= $mimepre;
                $body .= self::GET_BOUNDARY(self::$boundary[1], $bodyCharSet, '', $bodyEncoding);
                $body .= self::ENCODE_STRING(self::$Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('attachment', self::$boundary[1]);
                break;
            case 'inline_attach':
                $body .= $mimepre;
                $body .= self::TEXT_LINE('--' . self::$boundary[1]);
                $body .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';',static::$LE);
                $body .= self::TEXT_LINE(' boundary="' . self::$boundary[2] . '";');
                $body .= self::TEXT_LINE(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= self::GET_BOUNDARY(self::$boundary[2], $bodyCharSet, '', $bodyEncoding);
                $body .= self::ENCODE_STRING(self::$Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('inline', self::$boundary[2]);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('attachment', self::$boundary[1]);
                break;
            case 'alt':
                $body .= $mimepre;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[1],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[1],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$Body, $bodyEncoding);
                $body .= static::$LE;
                if (!empty(self::$Ical)) {
                    $method = static::ICAL_METHOD_REQUEST;
                    foreach (static::$IcalMethods as $imethod) {
                        if (stripos(self::$Ical, 'METHOD:' . $imethod) !== false) {
                            $method = $imethod;
                            break;
                        }
                    }
                    $body .= self::GET_BOUNDARY(
                        self::$boundary[1],
                        '',
                        static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method,
                        ''
                    );
                    $body .= self::ENCODE_STRING(self::$Ical, self::$Encoding);
                    $body .= static::$LE;
                }
                $body .= self::END_BOUNDARY(self::$boundary[1]);
                break;
            case 'alt_inline':
                $body .= $mimepre;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[1],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= self::TEXT_LINE('--' . self::$boundary[1]);
                $body .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';',static::$LE);
                $body .= self::TEXT_LINE(' boundary="' . self::$boundary[2] . '";');
                $body .= self::TEXT_LINE(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[2],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('inline', self::$boundary[2]);
                $body .= static::$LE;
                $body .= self::END_BOUNDARY(self::$boundary[1]);
                break;
            case 'alt_attach':
                $body .= $mimepre;
                $body .= self::TEXT_LINE('--' . self::$boundary[1]);
                $body .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';',static::$LE);
                $body .= self::TEXT_LINE(' boundary="' . self::$boundary[2] . '"');
                $body .= static::$LE;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[2],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[2],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$Body, $bodyEncoding);
                $body .= static::$LE;
                if (!empty(self::$Ical)) {
                    $method = static::ICAL_METHOD_REQUEST;
                    foreach (static::$IcalMethods as $imethod) {
                        if (stripos(self::$Ical, 'METHOD:' . $imethod) !== false) {
                            $method = $imethod;
                            break;
                        }
                    }
                    $body .= self::GET_BOUNDARY(
                        self::$boundary[2],
                        '',
                        static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method,
                        ''
                    );
                    $body .= self::ENCODE_STRING(self::$Ical, self::$Encoding);
                }
                $body .= self::END_BOUNDARY(self::$boundary[2]);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('attachment', self::$boundary[1]);
                break;
            case 'alt_inline_attach':
                $body .= $mimepre;
                $body .= self::TEXT_LINE('--' . self::$boundary[1]);
                $body .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';',static::$LE);
                $body .= self::TEXT_LINE(' boundary="' . self::$boundary[2] . '"');
                $body .= static::$LE;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[2],
                    $altBodyCharSet,
                    static::CONTENT_TYPE_PLAINTEXT,
                    $altBodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= self::TEXT_LINE('--' . self::$boundary[2]);
                $body .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';',static::$LE);
                $body .= self::TEXT_LINE(' boundary="' . self::$boundary[3] . '";');
                $body .= self::TEXT_LINE(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                $body .= static::$LE;
                $body .= self::GET_BOUNDARY(
                    self::$boundary[3],
                    $bodyCharSet,
                    static::CONTENT_TYPE_TEXT_HTML,
                    $bodyEncoding
                );
                $body .= self::ENCODE_STRING(self::$Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('inline', self::$boundary[3]);
                $body .= static::$LE;
                $body .= self::END_BOUNDARY(self::$boundary[2]);
                $body .= static::$LE;
                $body .= self::ATTACH_ALL('attachment', self::$boundary[1]);
                break;
            default:
                //Catch case 'plain' and case '', applies to simple `text/plain` and `text/html` body content types
                //Reset the `Encoding` property in case we changed it for line length reasons
                self::$Encoding = $bodyEncoding;
                $body .= self::ENCODE_STRING(self::$Body, self::$Encoding);
                break;
        }

        if (self::IS_ERROR()) {
            $body = '';
            if (self::$exceptions) {
                throw new Exception(self::lang('empty_message'), self::STOP_CRITICAL);
            }
        } elseif (self::$sign_key_file) {
            try {
                if (!defined('PKCS7_TEXT')) {
                    throw new Exception(self::lang('extension_missing') . 'openssl');
                }

                $file = tempnam(sys_get_temp_dir(), 'srcsign');
                $signed = tempnam(sys_get_temp_dir(), 'mailsign');
                file_put_contents($file, $body);

                //Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
                if (empty(self::$sign_extracerts_file)) {
                    $sign = @openssl_pkcs7_sign(
                        $file,
                        $signed,
                        'file://' . realpath(self::$sign_cert_file),
                        ['file://' . realpath(self::$sign_key_file), self::$sign_key_pass],
                        []
                    );
                } else {
                    $sign = @openssl_pkcs7_sign(
                        $file,
                        $signed,
                        'file://' . realpath(self::$sign_cert_file),
                        ['file://' . realpath(self::$sign_key_file), self::$sign_key_pass],
                        [],
                        PKCS7_DETACHED,
                        self::$sign_extracerts_file
                    );
                }

                @unlink($file);
                if ($sign) {
                    $body = file_get_contents($signed);
                    @unlink($signed);
                    //The message returned by openssl contains both headers and body, so need to split them up
                    $parts = explode("\n\n", $body, 2);
                    self::$MIMEHeader .= $parts[0] . static::$LE . static::$LE;
                    $body = $parts[1];
                } else {
                    @unlink($signed);
                    throw new Exception(self::lang('signing') . openssl_error_string());
                }
            } catch (Exception $exc) {
                $body = '';
                if (self::$exceptions) {
                    throw $exc;
                }
            }
        }

        return $body;
    }


    /**
     * Return the end of a message boundary.
     *
     * @param string $boundary
     *
     * @return string
     */
    protected static function END_BOUNDARY($boundary)
    {
        return static::$LE . '--' . $boundary . '--' . static::$LE;
    }


     /**
     * Get an error message in the current language.
     *
     * @param string $key
     *
     * @return string
     */
    protected static function hkm_lang($key)
    {
        if (count(self::$language) < 1) {
            self::SET_LANGUAGE(); //Set the default language
        }

        if (array_key_exists($key, self::$language)) {
            if ('smtp_connect_failed' === $key) {
                //Include a link to troubleshooting docs on SMTP connection failure.
                //This is by far the biggest cause of support questions
                //but it's usually not PHPMailer's fault.
                return self::$language[$key] . ' https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';
            }

            return self::$language[$key];
        }

        //Return the key as a fallback
        return $key;
    }

    /**
     * Destructor.
     */
    public  function __destruct()
    {
        //Close any open SMTP connection nicely
        $this::SMTP_CLOSE();
    }

}

