<?php
namespace Hkm_traits\AddOn;

use TeamsMailerSystem\Exception;
use TeamsMailerSystem\SMTP;
use TeamsMailerSystem\OAuth;


/**
 * 
 */
trait MailerAddTrait
{


    /**
     * Add an embedded (inline) attachment from a file.
     * This can include images, sounds, and just about any other document type.
     * These differ from 'regular' attachments in that they are intended to be
     * displayed inline with the message, not just attached for download.
     * This is used in HTML messages that embed the images
     * the HTML refers to using the $cid value.
     * Never use a user-supplied path to a file!
     *
     * @param string $path        Path to the attachment
     * @param string $hkmd         Content ID of the attachment; Use this to reference
     *                            the content when using an embedded image in HTML
     * @param string $name        Overrides the attachment name
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File MIME type
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool True on successfully adding an attachment
     */
    public static function ADD_EMBEDDED_IMAGE(
        $path,
        $hkmd,
        $name = '',
        $encoding = self::ENCODING_BASE64,
        $type = '',
        $disposition = 'inline'
    ) {
        try {
            if (!fileIsAccessible($path)) {
                throw new Exception(self::lang('file_access') . $path, self::STOP_CONTINUE);
            }

            //If a MIME type is not specified, try to work it out from the file name
            if ('' === $type) {
                $type = hkm_filename_to_type($path);
            }

            if (!self::VALIDATE_ENCODING($encoding)) {
                throw new Exception(self::lang('encoding') . $encoding);
            }

            $filename = (string) hkm_mb_pathinfo($path, PATHINFO_BASENAME);
            if ('' === $name) {
                $name = $filename;
            }

            //Append to $attachment array
            self::$attachment[] = [
                0 => $path,
                1 => $filename,
                2 => $name,
                3 => $encoding,
                4 => $type,
                5 => false, //isStringAttachment
                6 => $disposition,
                7 => $cid,
            ];
        } catch (Exception $exc) {
            self::SET_EROR($exc->getMessage());
            self::DEBUG($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }

            return false;
        }

        return true;
    }


    /**
     * Add an embedded stringified attachment.
     * This can include images, sounds, and just about any other document type.
     * If your filename doesn't contain an extension, be sure to set the $type to an appropriate MIME type.
     *
     * @param string $string      The attachment binary data
     * @param string $hkmd         Content ID of the attachment; Use this to reference
     *                            the content when using an embedded image in HTML
     * @param string $name        A filename for the attachment. If this contains an extension,
     *                            PHPMailer will attempt to set a MIME type for the attachment.
     *                            For example 'file.jpg' would get an 'image/jpeg' MIME type.
     * @param string $encoding    File encoding (see $Encoding), defaults to 'base64'
     * @param string $type        MIME type - will be used in preference to any automatically derived type
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool True on successfully adding an attachment
     */
    public static function ADD_STRING_EMBEDDED_IMAGE(
        $string,
        $cid,
        $name = '',
        $encoding = self::ENCODING_BASE64,
        $type = '',
        $disposition = 'inline'
    ) {
        try {
            //If a MIME type is not specified, try to work it out from the name
            if ('' === $type && !empty($name)) {
                $type = hkm_filename_to_type($name);
            }

            if (!self::VALIDATE_ENCODING($encoding)) {
                throw new Exception(self::lang('encoding') . $encoding);
            }

            //Append to $attachment array
            self::$attachment[] = [
                0 => $string,
                1 => $name,
                2 => $name,
                3 => $encoding,
                4 => $type,
                5 => true, //isStringAttachment
                6 => $disposition,
                7 => $hkmd,
            ];
        } catch (Exception $exc) {
            self::SET_ERROR($exc->getMessage());
            self::DEBUG($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }

            return false;
        }

        return true;
    }

     /**
     * Add an attachment from a path on the filesystem.
     * Never use a user-supplied path to a file!
     * Returns false if the file could not be found or read.
     * Explicitly *does not* support passing URLs; PHPMailer is not an HTTP client.
     * If you need to do that, fetch the resource yourself and pass it in via a local file or string.
     *
     * @param string $path        Path to the attachment
     * @param string $name        Overrides the attachment name
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        MIME type, e.g. `image/jpeg`; determined automatically from $path if not specified
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function ADD_ATTACHMENT(
        $path,
        $name = '',
        $encoding = self::ENCODING_BASE64,
        $type = '',
        $disposition = 'attachment'
    ) {
        try {
            if (!fileIsAccessible($path)) {
                throw new Exception(self::lang('file_access') . $path, self::STOP_CONTINUE);
            }

            //If a MIME type is not specified, try to work it out from the file name
            if ('' === $type) {
                $type = hkm_filename_to_type($path);
            }

            $filename = (string) hkm_mb_pathinfo($path, PATHINFO_BASENAME);
            if ('' === $name) {
                $name = $filename;
            }
            if (!self::VALIDATE_ENCODING($encoding)) {
                throw new Exception(self::lang('encoding') . $encoding);
            }

            self::$attachment[] = [
                0 => $path,
                1 => $filename,
                2 => $name,
                3 => $encoding,
                4 => $type,
                5 => false, //isStringAttachment
                6 => $disposition,
                7 => $name,
            ];
        } catch (Exception $exc) {
            self::SET_ERROR($exc->getMessage());
            self::DEBUG($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }

            return false;
        }

        return true;
    }

     /**
     * Format an address for use in a message header.
     *
     * @param array $addr A 2-element indexed array, element 0 containing an address, element 1 containing a name like
     *                    ['joe@example.com', 'Joe User']
     *
     * @return string
     */
    public static function ADDR_FORMAT($addr)
    {
        if (empty($addr[1])) { //No name provided
            return hkm_secureHeader($addr[0]);
        }

        return self::ENCODE_HEADER(secureHeader($addr[1]), 'phrase') .
            ' <' . hkm_secureHeader($addr[0]) . '>';
    }


    /**
     * Create recipient headers.
     *
     * @param string $type
     * @param array  $addr An array of recipients,
     *                     where each recipient is a 2-element indexed array with element 0 containing an address
     *                     and element 1 containing a name, like:
     *                     [['joe@example.com', 'Joe User'], ['zoe@example.com', 'Zoe User']]
     *
     * @return string
     */
    public static function ADDR_APPEND($type, $addr)
    {
        $addresses = [];
        foreach ($addr as $address) {
            $addresses[] = self::ADDR_FORMAT($address);
        }

        return $type . ': ' . implode(', ', $addresses) . static::$LE;
    }

    

    /**
     * Add a "CC" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public static function ADD_CC($address, $name = '')
    {
        return self::ADD_OR_ENQUEUE_AN_ADDRESS('cc', $address, $name);
    }

    /**
     * Add a "BCC" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public static function ADD_BCC($address, $name = '')
    {
        return self::ADD_OR_ENQUEUE_AN_ADDRESS('bcc', $address, $name);
    }

    /**
     * Add a "Reply-To" address.
     *
     * @param string $address The email address to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public static function ADD_REPLY_TO($address, $name = '')
    {
        return self::ADD_OR_ENQUEUE_AN_ADDRESS('Reply-To', $address, $name);
    }
    
    /**
     * Add a "To" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public static function ADD_ADDRESS($address, $name = '')
    {
        return self::ADD_OR_ENQUEUE_AN_ADDRESS('to', $address, $name);
    }


    /**
     * Add an address to one of the recipient arrays or to the ReplyTo array. Because TeamsMailer
     * can't validate addresses with an IDN without knowing the TeamsMailer::$CharSet (that can still
     * be modified after calling this function), addition of such addresses is delayed until send().
     * Addresses that have been added already return false, but do not throw exceptions.
     *
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    protected static function ADD_OR_ENQUEUE_AN_ADDRESS($kind, $address, $name)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
        self::$validator::CHECK($address,'is_email_valid',['is_email_valid'=>'invalid_address']);
        $error_message = self::$validator::GET_ERROR('check');
        if (empty($error_message)) {

           $pos = strrpos($address, '@');
           $params = [$kind, $address, $name];
             //Enqueue addresses with IDN until we know the TeamsMailer::$CharSet.
        if (idnSupported() && self::HAS_8BIT_CHARS(substr($address, ++$pos))) {
            if ('Reply-To' !== $kind) {
                if (!array_key_exists($address, self::$RecipientsQueue)) {
                    self::$RecipientsQueue[$address] = $params;

                    return true;
                }
            } elseif (!array_key_exists($address, self::$ReplyToQueue)) {
                self::$ReplyToQueue[$address] = $params;

                return true;
            }

            return false;
        }

        //Immediately add standard addresses without IDN.
        return call_user_func_array([self::$thiss, 'ADD_AN_ADDRESS'], $params);
        }

            self::SET_ERROR($error_message);
            self::DEBUG($error_message);
            if (self::$exceptions) {
                throw new Exception($error_message);
            }

        return false;

       
    }

    /**
     * Add an address to one of the recipient arrays or to the ReplyTo array.
     * Addresses that have been added already return false, but do not throw exceptions.
     *
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    protected  static function ADD_AN_ADDRESS($kind, $address, $name = '')
    {
        if (!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])) {
            $error_message = sprintf(
                '%s: %s',
                self::lang('Invalid recipient kind'),
                $kind
            );
            self::SET_ERROR($error_message);
            self::DEBUG($error_message);
            if (self::$exceptions) {
                throw new Exception($error_message);
            }

            return false;
        }
        if (!static::VALIDATE_ADDRESS($address,'is_email_valid')) {
            $error_message = sprintf(
                '%s (%s): %s',
                self::lang('invalid_address'),
                $kind,
                $address
            );
            self::SET_ERROR($error_message);
            self::DEBUG($error_message);
            if (self::$exceptions) {
                throw new Exception($error_message);
            }

            return false;
        }
        if ('Reply-To' !== $kind) {
            if (!array_key_exists(strtolower($address), self::$all_recipients)) {
                self::${$kind}[] = [$address, $name];
                self::$all_recipients[strtolower($address)] = true;

                return true;
            }
        } elseif (!array_key_exists(strtolower($address), self::$ReplyTo)) {
            self::$ReplyTo[strtolower($address)] = [$address, $name];

            return true;
        }

        return false;
    }


    /**
     * Add a custom header.
     * $name value can be overloaded to contain
     * both header name and value (name:value).
     *
     * @param string      $name  Custom header name
     * @param string|null $value Header value
     *
     * @throws Exception
     */
    public static function ADD_CUSTOM_HEADER($name, $value = null)
    {
        if (null === $value && strpos($name, ':') !== false) {
            //Value passed in as name:value
            list($name, $value) = explode(':', $name, 2);
        }
        $name = trim($name);
        $value = (null === $value) ? '' : trim($value);
        //Ensure name is not empty, and that neither name nor value contain line breaks
        if (empty($name) || strpbrk($name . $value, "\r\n") !== false) {
            if (self::$exceptions) {
                throw new Exception(self::lang('invalid_header'));
            }

            return false;
        }
        self::$CustomHeader[] = [$name, $value];
        return true;
    }

    /**
     * Add a string or binary attachment (non-filesystem).
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     *
     * @param string $string      String attachment data
     * @param string $filename    Name of the attachment
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File extension (MIME) type
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool True on successfully adding an attachment
     */
    public static function ADD_STRING_ATTACHMENT(
        $string,
        $filename,
        $encoding = self::ENCODING_BASE64,
        $type = '',
        $disposition = 'attachment'
    ) {
        try {
            //If a MIME type is not specified, try to work it out from the file name
            if ('' === $type) {
                $type = hkm_filename_to_type($filename);
            }

            if (!self::VALIDATE_ENCODING($encoding)) {
                throw new Exception(self::lang('encoding') . $encoding);
            }

            //Append to $attachment array
            self::$attachment[] = [
                0 => $string,
                1 => $filename,
                2 => hkm_mb_pathinfo($filename, PATHINFO_BASENAME),
                3 => $encoding,
                4 => $type,
                5 => true, //isStringAttachment
                6 => $disposition,
                7 => 0,
            ];
        } catch (Exception $exc) {
            self::SET_ERROR($exc->getMessage());
            self::DEBUG($exc->getMessage());
            if (self::$exceptions) {
                throw $exc;
            }

            return false;
        }

        return true;
    }
}
