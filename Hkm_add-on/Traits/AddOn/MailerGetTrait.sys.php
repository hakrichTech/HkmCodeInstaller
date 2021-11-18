<?php
namespace Hkm_traits\AddOn;

use TeamsMailerSystem\Exception;
use TeamsMailerSystem\SMTP;
use TeamsMailerSystem\OAuth;

/**
 * 
 */
trait MailerGetTrait
{
    /**
     * Get an instance to use for SMTP operations.
     * Override this function to load your own SMTP implementation,
     * or set one with setSMTPInstance.
     *
     * @return SMTP
     */
    public static function GET_SMTP_INSTANCE()
    {
        if (!is_object(self::$smtp)) {
            self::$smtp = new SMTP();
        }

        return self::$smtp;
    }

    /**
      * Return the Message-ID header of the last email.
     * Technically this is the value from the last time the headers were created,
     * but it's also the message ID of the last sent message except in
     * pathological cases.
     *
     * @return string
     */
    public static function GET_LAST_MESSAGE_ID()
    {
        return self::$lastMessageID;
    }
    /**
     * Returns all custom headers.
     *
     * @return array
     */
    public static function GET_CUSTOM_HEADERS()
    {
        return self::$CustomHeader;
    }

     /**
     * Return the array of attachments.
     *
     * @return array
     */
    public static function GET_ATTACHMENTS()
    {
        return self::$attachment;
    }

     /**
     * Get the message MIME type headers.
     *
     * @return string
     */
    public static function GET_MAIL_MIME()
    {
        $result = '';
        $ismultipart = true;
        switch (self::$message_type) {
            case 'inline':
                $result .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';',static::$LE);
                $result .= self::TEXT_LINE(' boundary="' . self::$boundary[1] . '"');
                break;
            case 'attach':
            case 'inline_attach':
            case 'alt_attach':
            case 'alt_inline_attach':
                $result .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_MIXED . ';',static::$LE);
                $result .= self::TEXT_LINE(' boundary="' . self::$boundary[1] . '"');
                break;
            case 'alt':
            case 'alt_inline':
                $result .= hkm_headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';',static::$LE);
                $result .= self::TEXT_LINE(' boundary="' . self::$boundary[1] . '"');
                break;
            default:
                //Catches case 'plain': and case '':
                $result .= self::TEXT_LINE('Content-Type: ' . self::$ContentType . '; charset=' . self::$CharSet);
                $ismultipart = false;
                break;
        }
        //RFC1341 part 5 says 7bit is assumed if not specified
        if (static::ENCODING_7BIT !== self::$Encoding) {
            //RFC 2045 section 6.4 says multipart MIME parts may only use 7bit, 8bit or binary CTE
            if ($ismultipart) {
                if (static::ENCODING_8BIT === self::$Encoding) {
                    $result .= hkm_headerLine('Content-Transfer-Encoding', static::ENCODING_8BIT,static::$LE);
                }
                //The only remaining alternatives are quoted-printable and base64, which are both 7bit compatible
            } else {
                $result .= hkm_headerLine('Content-Transfer-Encoding', self::$Encoding,static::$LE);
            }
        }

        return $result;
    }

    /**
     * Return the current line break format string.
     *
     * @return string
     */
    public  static function GET_LE()
    {
        return static::$LE;
    }


    /**
     * Allows for public read access to 'to' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public static function GET_TO_ADDRESSES()
    {
        return self::$to;
    }

    /**
     * Allows for public static read access to 'cc' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public static function GET_CC_ADDRESSES()
    {
        return self::$cc;
    }

    /**
     * Allows for public static read access to 'bcc' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public static function GET_BCC_ADDRESSES()
    {
        return self::$bcc;
    }

    /**
     * Allows for public static read access to 'ReplyTo' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public static function GET_REPLY_TO_ADDRESSES()
    {
        return self::$ReplyTo;
    }

    /**
     * Allows for public static read access to 'all_recipients' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public static function GET_ALL_RECIPIENT_ADDRESSES()
    {
        return self::$all_recipients;
    }

     /**
     * Get the OAuth instance.
     *
     * @return OAuth
     */
    public static function GET_OAUTH()
    {
        return self::$oauth;
    }


    /**
     * Return the start of a message boundary.
     *
     * @param string $boundary
     * @param string $CharSet
     * @param string $contentType
     * @param string $encoding
     *
     * @return string
     */
    protected static function GET_BOUNDARY($boundary, $CharSet, $contentType, $encoding)
    {
        $result = '';
        if ('' === $CharSet) {
            $CharSet = self::$CharSet;
        }
        if ('' === $contentType) {
            $contentType = self::$ContentType;
        }
        if ('' === $encoding) {
            $encoding = self::$Encoding;
        }
        $result .= self::TEXT_LINE('--' . $boundary);
        $result .= sprintf('Content-Type: %s; charset=%s', $contentType, $CharSet);
        $result .= static::$LE;
        //RFC1341 part 5 says 7bit is assumed if not specified
        if (static::ENCODING_7BIT !== $encoding) {
            $result .= hkm_headerLine('Content-Transfer-Encoding', $encoding,static::$LE);
        }
        $result .= static::$LE;

        return $result;
    }


    /**
     * Returns the whole MIME message.
     * Includes complete headers and body.
     * Only valid post preSend().
     *
     * @see PHPMailer::preSend()
     *
     * @return string
     */
    public static function GET_SENT_MIME_MESSAGE()
    {
        return static::stripTrailingWSP(self::$MIMEHeader . self::$mailHeader) .
            static::$LE . static::$LE . self::$MIMEBody;
    }

    /**
     * Get the array of strings for the current language.
     *
     * @return array
     */
    public static function GET_TRANSLATIONS()
    {
        if (empty(self::$language)) {
            self::SET_LANGUAGE(); // Set the default language.
        }

        return self::$language;
    }
}
