<?php
namespace Hkm_traits\AddOn;

use TeamsMailerSystem\Exception;
use TeamsMailerSystem\SMTP;
use Hkm_code\Vezirion\FileLocator;
use TeamsMailerSystem\OAuth;


/**
 * 
 */
trait MailerSetTrait
{
     /**
     * Provide an instance to use for SMTP operations.
     *
     * @return SMTP
     */
    public static function SET_SMTP_INSTANCE(SMTP $smtp)
    {
        self::$smtp = $smtp;

        return self::$smtp;
    }
    public static function SET_DEBUG(int $v = 0)
    {
        self::$SMTPDebug = $v;
        return self::$thiss;
    }
     /**
     * Add an error message to the error container.
     *
     * @param string $msg
     */
    protected static function SET_ERROR($msg)
    {
        ++self::$error_count;
        if ('smtp' === self::$Mailer && null !== self::$smtp) {
            $lasterror = self::$smtp::GET_ERROR();
            // if (!empty($lasterror['error'])) {

            //     $msg .= self::$lang('smtp_error') . $lasterror['error'];
            //     if (!empty($lasterror['detail'])) {
            //         $msg .= ' ' . self::$lang('smtp_detail') . $lasterror['detail'];
            //     }
            //     if (!empty($lasterror['smtp_code'])) {
            //         $msg .= ' ' . self::$lang('smtp_code') . $lasterror['smtp_code'];
            //     }
            //     if (!empty($lasterror['smtp_code_ex'])) {
            //         $msg .= ' ' . self::$lang('smtp_code_ex') . $lasterror['smtp_code_ex'];
            //     }
            // }
        }
        self::$ErrorInfo = $msg;
    }

      /**
     * Set the message type.
     * PHPMailer only supports some preset message types, not arbitrary MIME structures.
     */
    protected static function SET_MESSAGE_TYPE()
    {
        $type = [];
        if (self::ALTERNATIVE_EXISTS()) {
            $type[] = 'alt';
        }
        if (self::INLINE_IMAGE_EXISTS()) {
            $type[] = 'inline';
        }
        if (self::ATTACHMENT_EXISTS()) {
            $type[] = 'attach';
        }
        self::$message_type = implode('_', $type);
        if ('' === self::$message_type) {
            //The 'plain' message_type refers to the message having a single body element, not that it is plain-text
            self::$message_type = 'plain';
        }
    }

     /**
     * Set the line break format string, e.g. "\r\n".
     *
     * @param string $le
     */
    protected static function SET_LE($le)
    {
        static::$LE = $le;
    }

     /**
     * Set an OAuth instance.
     */
    public static function SET_OAUTH(OAuth $oauth)
    {
        self::$oauth = $oauth;
    }


    /**
     * Apply word wrapping to the message body.
     * Wraps the message body to the number of chars set in the WordWrap property.
     * You should only do this to plain-text bodies as wrapping HTML tags may break them.
     * This is called automatically by createBody(), so you don't need to call it yourself.
     */
    public static function SET_WORD_WRAP()
    {
        if (self::$WordWrap < 1) {
            return;
        }

        switch (self::$message_type) {
            case 'alt':
            case 'alt_inline':
            case 'alt_attach':
            case 'alt_inline_attach':
                self::$AltBody = self::WRAP_TEXT(self::$AltBody, self::$WordWrap);
                break;
            default:
                self::$Body = self::WRAP_TEXT(self::$Body, self::$WordWrap);
                break;
        }
    }


    /**
     * Set the language for error messages.
     * The default language is English.
     *
     * @param string $langcode  ISO 639-1 2-character language code (e.g. French is "fr")
     *                          Optionally, the language code can be enhanced with a 4-character
     *                          script annotation and/or a 2-character country annotation.
     * @param string $lang_path Path to the language file directory, with trailing separator (slash).D
     *                          Do not set this from user input!
     *
     * @return bool Returns true if the requested language was loaded, false otherwise.
     */
    public static function SET_LANGUAGE($langcode = 'en', $filesLang = '')
    {
        //Backwards compatibility for renamed language codes
        $renamed_langcodes = [
            'br' => 'pt_br',
            'cz' => 'cs',
            'dk' => 'da',
            'no' => 'nb',
            'se' => 'sv',
            'rs' => 'sr',
            'tg' => 'tl',
            'am' => 'hy',
        ];

        if (array_key_exists($langcode, $renamed_langcodes)) {
            $langcode = $renamed_langcodes[$langcode];
        }

        //Define full set of translatable strings in English
        $PHPMAILER_LANG = [
            'authenticate' => 'SMTP Error: Could not authenticate.',
            'buggy_php' => 'Your version of PHP is affected by a bug that may result in corrupted messages.' .
                ' To fix it, switch to sending using SMTP, disable the mail.add_x_header option in' .
                ' your php.ini, switch to MacOS or Linux, or upgrade your PHP to version 7.0.17+ or 7.1.3+.',
            'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
            'data_not_accepted' => 'SMTP Error: data not accepted.',
            'empty_message' => 'Message body empty',
            'encoding' => 'Unknown encoding: ',
            'execute' => 'Could not execute: ',
            'extension_missing' => 'Extension missing: ',
            'file_access' => 'Could not access file: ',
            'file_open' => 'File Error: Could not open file: ',
            'from_failed' => 'The following From address failed: ',
            'instantiate' => 'Could not instantiate mail function.',
            'invalid_address' => 'Invalid address: ',
            'invalid_header' => 'Invalid header name or value',
            'invalid_hostentry' => 'Invalid hostentry: ',
            'invalid_host' => 'Invalid host: ',
            'mailer_not_supported' => ' mailer is not supported.',
            'provide_address' => 'You must provide at least one recipient email address.',
            'recipients_failed' => 'SMTP Error: The following recipients failed: ',
            'signing' => 'Signing Error: ',
            'smtp_code' => 'SMTP code: ',
            'smtp_code_ex' => 'Additional SMTP info: ',
            'smtp_connect_failed' => 'SMTP connect() failed.',
            'smtp_detail' => 'Detail: ',
            'smtp_error' => 'SMTP server error: ',
            'variable_set' => 'Cannot set or reset variable: ',
        ];

        
        if (empty($filesLang)) {
            //Calculate an absolute path so it can work if CWD is not here
            $filesLang = FileLocator::LIST_FILES('TeamsMailerSystem/language');
        }
        

        //Validate $langcode
        $foundlang = true;
        $langcode  = strtolower($langcode);
        if (
            !preg_match('/^(?P<lang>[a-z]{2})(?P<script>_[a-z]{4})?(?P<country>_[a-z]{2})?$/', $langcode, $matches)
            && $langcode !== 'en'
        ) {
            $foundlang = false;
            $langcode = 'en';
        }

        //There is no English translation file
        if ('en' !== $langcode) {
            $langcodes = [];
            if (!empty($matches['script']) && !empty($matches['country'])) {
                $langcodes[] = $matches['lang'] . $matches['script'] . $matches['country'];
            }
            if (!empty($matches['country'])) {
                $langcodes[] = $matches['lang'] . $matches['country'];
            }
            if (!empty($matches['script'])) {
                $langcodes[] = $matches['lang'] . $matches['script'];
            }
            $langcodes[] = $matches['lang'];

            //Try and find a readable language file for the requested language.
            $foundFile = false;
            foreach ($langcodes as $code) {
                $lang_fi = hkm_get_file($filesLang,$code);
                if ($lang_fi[0]) {
                    $lang_file = $lang_fi[1];
                }else{
                    $lang_file = '';
                }

                if (fileIsAccessible($lang_file)) {
                    $foundFile = true;
                    break;
                }
            }

            if ($foundFile === false) {
                $foundlang = false;
            } else {
                $lines = file($lang_file);
                foreach ($lines as $line) {
                    //Translation file lines look like this:
                    //$PHPMAILER_LANG['authenticate'] = 'SMTP-Fehler: Authentifizierung fehlgeschlagen.';
                    //These files are parsed as text and not PHP so as to avoid the possibility of code injection
                    //See https://blog.stevenlevithan.com/archives/match-quoted-string
                    $matches = [];
                    if (
                        preg_match(
                            '/^\$PHPMAILER_LANG\[\'([a-z\d_]+)\'\]\s*=\s*(["\'])(.+)*?\2;/',
                            $line,
                            $matches
                        ) &&
                        //Ignore unknown translation keys
                        array_key_exists($matches[1], $PHPMAILER_LANG)
                    ) {
                        //Overwrite language-specific strings so we'll never have missing translation keys.
                        $PHPMAILER_LANG[$matches[1]] = (string)$matches[3];
                    }
                }
            }
        }
        self::$language = $PHPMAILER_LANG;

        return $foundlang; //Returns false if language not found
    }

    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool   $auto    Whether to also set the Sender address, defaults to true
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function SET_FROM($address, $name = '', $auto = true)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
        //Don't validate now addresses with IDN. Will be done in send().
        $pos = strrpos($address, '@');
        if (
            (false === $pos)
            || ((!self::HAS_8BIT_CHARS(substr($address, ++$pos)) || !idnSupported())
            && !static::VALIDATE_ADDRESS($address))
        ) {
            $error_message = sprintf(
                '%s (From): %s',
                self::lang('invalid_address'),
                $address
            );
            self::SET_ERROR($error_message);
            self::DEBUG($error_message);
            if (self::$exceptions) {
                throw new Exception($error_message);
            }

            return false;
        }
        self::$From = $address;
        self::$FromName = $name;
        if ($auto && empty(self::$Sender)) {
            self::$Sender = $address;
        }

        return true;
    }
}
