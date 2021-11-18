<?php
namespace Hkm_traits\AddOn;

use TeamsMailerSystem\Exception;
use TeamsMailerSystem\SMTP;
use TeamsMailerSystem\OAuth;

/**
 * 
 */
trait MailerClearTrait
{
    
   

    /**
     * Clear queued addresses of given kind.
     *
     * @param string $kind 'to', 'cc', or 'bcc'
     */
    public static function CLEAR_QUEUED_ADDRESSES($kind)
    {
        self::$RecipientsQueue = array_filter(
            self::$RecipientsQueue,
            static function ($params) use ($kind) {
                return $params[0] !== $kind;
            }
        );
    }

    /**
     * Clear all To recipients.
     */
    public static function CLEAR_ADDRESSES()
    {
        foreach (self::$to as $to) {
            unset(self::$all_recipients[strtolower($to[0])]);
        }
        self::$to = [];
        self::CLEAR_QUEUED_ADDRESSES('to');
    }

    /**
     * Clear all CC recipients.
     */
    public static function CLEAR_CCS()
    {
        foreach (self::$cc as $cc) {
            unset(self::$all_recipients[strtolower($cc[0])]);
        }
        self::$cc = [];
        self::CLEAR_QUEUED_ADDRESSES('cc');
    }

    /**
     * Clear all BCC recipients.
     */
    public static function CLEAR_BCCS()
    {
        foreach (self::$bcc as $bcc) {
            unset(self::$all_recipients[strtolower($bcc[0])]);
        }
        self::$bcc = [];
        self::CLEAR_QUEUED_ADDRESSES('bcc');
    }

    /**
     * Clear all ReplyTo recipients.
     */
    public static function CLEAR_REPLY_TOS()
    {
        self::$ReplyTo = [];
        self::$ReplyToQueue = [];
    }

    /**
     * Clear all recipient types.
     */
    public static function CLEAR_ALL_RECIPIENTS()
    {
        self::$to = [];
        self::$cc = [];
        self::$bcc = [];
        self::$all_recipients = [];
        self::$RecipientsQueue = [];
    }

    /**
     * Clear all filesystem, string, and binary attachments.
     */
    public static function CLEAR_ATTACHMENTS()
    {
        self::$attachment = [];
    }

    /**
     * Clear all custom headers.
     */
    public static function CLEAR_CUSTOM_HEADERS()
    {
        self::$CustomHeader = [];
    }

    public static function RESET()
    {
        self::CLEAR_CUSTOM_HEADERS();
        self::CLEAR_ATTACHMENTS();
        self::CLEAR_ALL_RECIPIENTS();
        self::CLEAR_REPLY_TOS();
        self::CLEAR_BCCS();
        self::CLEAR_CCS();
        self::CLEAR_ADDRESSES();
        self::$smtp::QUIT();
    }
}
