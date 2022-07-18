<?php


// MISC ADNMIN INCLUDES

/**
 * Displays the given administration message.
 *
 * @since 2.1.0
 *
 */
function show_message( $message ) {
	// if ( is_hkm_error( $message ) ) {
	// 	if ( $message->get_error_data() && is_string( $message->get_error_data() ) ) {
	// 		$message = $message->get_error_message() . ': ' . $message->get_error_data();
	// 	} else {
	// 		$message = $message->get_error_message();
	// 	}
	// }
	// echo "<p>$message</p>\n";
	// // hkm_ob_end_flush_all();
	// flush();
}

// PLUGGABLE FILE

if ( ! function_exists( 'hkm_create_nonce' ) ) :
	/**
	 * Creates a cryptographic token tied to a specific action, user, user session,
	 * and window of time.
	 *
	 * @since 2.0.3
	 * @since 4.0.0 Session tokens were integrated with nonce creation
	 *
	 * @param string|int $action Scalar value to add context to the nonce.
	 * @return string The token.
	 */
	function hkm_create_nonce( $action = -1 ) {
		// $user = hkm_get_current_user();
		// $uid  = (int) $user->ID;
		// if ( ! $uid ) {
		// 	/** This filter is documented in wp-includes/pluggable.php */
		// 	$uid = hkm_apply_filters( 'nonce_user_logged_out', $uid, $action );
		// }

		// $token = hkm_get_session_token();
		// $i     = hkm_nonce_tick();
		return "token";

		// return substr( hkm_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	}
endif;

// LINK TEMPLATE

/**
 * Retrieves a URL within the plugins or mu-plugins directory.
 *
 * Defaults to the plugins directory URL if no arguments are supplied.
 *
 * @since 2.6.0
 *
 * @param string $path   Optional. Extra path appended to the end of the URL, including
 *                       the relative directory if $plugin is supplied. Default empty.
 * @param string $plugin Optional. A full path to a file inside a plugin or mu-plugin.
 *                       The URL will be relative to its directory. Default empty.
 *                       Typically this is done by passing `__FILE__` as the argument.
 * @return string Plugins URL link with optional paths appended.
 */
function plugins_url( $path = '', $plugin = '' ) {

	$path          = hkm_normalize_path( $path );
	$plugin        = hkm_normalize_path( $plugin );
	$mu_plugin_dir = hkm_normalize_path( HKMMU_PLUGIN_DIR );

	if ( ! empty( $plugin ) && 0 === strpos( $plugin, $mu_plugin_dir ) ) {
		$url = HKMMU_PLUGIN_URL;
	} else {
		$url = HKM_PLUGIN_URL;
	}

	$url = set_url_scheme( $url );

	if ( ! empty( $plugin ) && is_string( $plugin ) ) {
		$folder = dirname( plugin_basename( $plugin ) );
		if ( '.' !== $folder ) {
			$url .= '/' . ltrim( $folder, '/' );
		}
	}

	if ( $path && is_string( $path ) ) {
		$url .= '/' . ltrim( $path, '/' );
	}
 
	/**
	 * Filters the URL to the plugins directory.
	 *
	 * @since 2.8.0
	 *
	 * @param string $url    The complete URL to the plugins directory including scheme and path.
	 * @param string $path   Path relative to the URL to the plugins directory. Blank string
	 *                       if no path is specified.
	 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin
	 *                       is specified.
	 */
	return hkm_apply_filters( 'plugins_url', $url, $path, $plugin );
}

// LOAD FILE

/**
 * Checks whether the given variable is a WordPress Error.
 *
 * Returns whether `$thing` is an instance of the `WP_Error` class.
 *
 * @since 2.1.0
 *
 * @param mixed $thing The variable to check.
 * @return bool Whether the variable is an instance of WP_Error.
 */
// function is_hkm_error( $thing ) {
// 	// $is_hkm_error = ( $thing instanceof Hkm_Error );

// 	// if ( $is_hkm_error ) {
// 	// 	/**
// 	// 	 * Fires when `is_hkm_error()` is called and its parameter is an instance of `WP_Error`.
// 	// 	 *
// 	// 	 * @since 5.6.0
// 	// 	 *
// 	// 	 * @param Hkm_Error $thing The error object passed to `is_hkm_error()`.
// 	// 	 */
// 	// 	hkm_do_action( 'is_hkm_error_instance', $thing );
// 	// }

// 	// return $is_hkm_error;
// 	return false;
// }

// OPTION FILE

/**
 * Retrieves an option value based on an option name.
 *
 * If the option does not exist, and a default value is not provided,
 * boolean false is returned. This could be used to check whether you need
 * to initialize an option during installation of a plugin, however that
 * can be done better by using add_option() which will not overwrite
 * existing options.
 *
 * Not initializing an option and using boolean `false` as a return value
 * is a bad practice as it triggers an additional database query.
 *
 * The type of the returned value can be different from the type that was passed
 * when saving or updating the option. If the option value was serialized,
 * then it will be unserialized when it is returned. In this case the type will
 * be the same. For example, storing a non-scalar value like an array will
 * return the same array.
 *
 * In most cases non-string scalar and null values will be converted and returned
 * as string equivalents.
 *
 * Exceptions:
 * 1. When the option has not been saved in the database, the `$default` value
 *    is returned if provided. If not, boolean `false` is returned.
 * 2. When one of the Options API filters is used: {@see 'pre_option_{$option}'},
 *    {@see 'default_option_{$option}'}, or {@see 'option_{$option}'}, the returned
 *    value may not match the expected type.
 * 3. When the option has just been saved in the database, and get_option()
 *    is used right after, non-string scalar and null values are not converted to
 *    string equivalents and the original type is returned.
 *
 * Examples:
 *
 * When adding options like this: `add_option( 'my_option_name', 'value' );`
 * and then retrieving them with `get_option( 'my_option_name' );`, the returned
 * values will be:
 *
 * `false` returns `string(0) ""`
 * `true`  returns `string(1) "1"`
 * `0`     returns `string(1) "0"`
 * `1`     returns `string(1) "1"`
 * `'0'`   returns `string(1) "0"`
 * `'1'`   returns `string(1) "1"`
 * `null`  returns `string(0) ""`
 *
 * When adding options with non-scalar values like
 * `add_option( 'my_array', array( false, 'str', null ) );`, the returned value
 * will be identical to the original as it is serialized before saving
 * it in the database:
 *
 *    array(3) {
 *        [0] => bool(false)
 *        [1] => string(3) "str"
 *        [2] => NULL
 *    }
 *
 * @since 1.5.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $option  Name of the option to retrieve. Expected to not be SQL-escaped.
 * @param mixed  $default Optional. Default value to return if the option does not exist.
 * @return mixed Value of the option. A value of any type may be returned, including
 *               scalar (string, boolean, float, integer), null, array, object.
 *               Scalar and null values will be returned as strings as long as they originate
 *               from a database stored option value. If there is no option in the database,
 *               boolean `false` is returned.
 */
function get_option( $option, $default = false ) {
	// global $wpdb;

	// if ( is_scalar( $option ) ) {
	// 	$option = trim( $option );
	// }

	// if ( empty( $option ) ) {
	// 	return false;
	// }

	// /*
	//  * Until a proper _deprecated_option() function can be introduced,
	//  * redirect requests to deprecated keys to the new, correct ones.
	//  */
	// $deprecated_keys = array(
	// 	'blacklist_keys'    => 'disallowed_keys',
	// 	'comment_whitelist' => 'comment_previously_approved',
	// );

	// if ( ! hkm_installing() && isset( $deprecated_keys[ $option ] ) ) {
	// 	_deprecated_argument(
	// 		__FUNCTION__,
	// 		'5.5.0',
	// 		sprintf(
	// 			/* translators: 1: Deprecated option key, 2: New option key. */
	// 			__( 'The "%1$s" option key has been renamed to "%2$s".' ),
	// 			$option,
	// 			$deprecated_keys[ $option ]
	// 		)
	// 	);
	// 	return get_option( $deprecated_keys[ $option ], $default );
	// }

	// /**
	//  * Filters the value of an existing option before it is retrieved.
	//  *
	//  * The dynamic portion of the hook name, `$option`, refers to the option name.
	//  *
	//  * Returning a truthy value from the filter will effectively short-circuit retrieval
	//  * and return the passed value instead.
	//  *
	//  * @since 1.5.0
	//  * @since 4.4.0 The `$option` parameter was added.
	//  * @since 4.9.0 The `$default` parameter was added.
	//  *
	//  * @param mixed  $pre_option The value to return instead of the option value. This differs
	//  *                           from `$default`, which is used as the fallback value in the event
	//  *                           the option doesn't exist elsewhere in get_option().
	//  *                           Default false (to skip past the short-circuit).
	//  * @param string $option     Option name.
	//  * @param mixed  $default    The fallback value to return if the option does not exist.
	//  *                           Default false.
	//  */
	// $pre = hkm_apply_filters( "pre_option_{$option}", false, $option, $default );

	// if ( false !== $pre ) {
	// 	return $pre;
	// }

	// if ( defined( 'WP_SETUP_CONFIG' ) ) {
	// 	return false;
	// }

	// // Distinguish between `false` as a default, and not passing one.
	// $passed_default = func_num_args() > 1;

	// if ( ! hkm_installing() ) {
	// 	// Prevent non-existent options from triggering multiple queries.
	// 	$notoptions = hkm_cache_get( 'notoptions', 'options' );

	// 	if ( isset( $notoptions[ $option ] ) ) {
	// 		/**
	// 		 * Filters the default value for an option.
	// 		 *
	// 		 * The dynamic portion of the hook name, `$option`, refers to the option name.
	// 		 *
	// 		 * @since 3.4.0
	// 		 * @since 4.4.0 The `$option` parameter was added.
	// 		 * @since 4.7.0 The `$passed_default` parameter was added to distinguish between a `false` value and the default parameter value.
	// 		 *
	// 		 * @param mixed  $default The default value to return if the option does not exist
	// 		 *                        in the database.
	// 		 * @param string $option  Option name.
	// 		 * @param bool   $passed_default Was `get_option()` passed a default value?
	// 		 */
	// 		return hkm_apply_filters( "default_option_{$option}", $default, $option, $passed_default );
	// 	}

	// 	$alloptions = hkm_load_alloptions();

	// 	if ( isset( $alloptions[ $option ] ) ) {
	// 		$value = $alloptions[ $option ];
	// 	} else {
	// 		$value = hkm_cache_get( $option, 'options' );

	// 		if ( false === $value ) {
	// 			$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );

	// 			// Has to be get_row() instead of get_var() because of funkiness with 0, false, null values.
	// 			if ( is_object( $row ) ) {
	// 				$value = $row->option_value;
	// 				hkm_cache_add( $option, $value, 'options' );
	// 			} else { // Option does not exist, so we must cache its non-existence.
	// 				if ( ! is_array( $notoptions ) ) {
	// 					$notoptions = array();
	// 				}

	// 				$notoptions[ $option ] = true;
	// 				hkm_cache_set( 'notoptions', $notoptions, 'options' );

	// 				/** This filter is documented in wp-includes/option.php */
	// 				return hkm_apply_filters( "default_option_{$option}", $default, $option, $passed_default );
	// 			}
	// 		}
	// 	}
	// } else {
	// 	$suppress = $wpdb->suppress_errors();
	// 	$row      = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );
	// 	$wpdb->suppress_errors( $suppress );

	// 	if ( is_object( $row ) ) {
	// 		$value = $row->option_value;
	// 	} else {
	// 		/** This filter is documented in wp-includes/option.php */
	// 		return hkm_apply_filters( "default_option_{$option}", $default, $option, $passed_default );
	// 	}
	// }

	// // If home is not set, use siteurl.
	// if ( 'home' === $option && '' === $value ) {
	// 	return get_option( 'siteurl' );
	// }

	// if ( in_array( $option, array( 'siteurl', 'home', 'category_base', 'tag_base' ), true ) ) {
	// 	$value = untrailingslashit( $value );
	// }

	// /**
	//  * Filters the value of an existing option.
	//  *
	//  * The dynamic portion of the hook name, `$option`, refers to the option name.
	//  *
	//  * @since 1.5.0 As 'option_' . $setting
	//  * @since 3.0.0
	//  * @since 4.4.0 The `$option` parameter was added.
	//  *
	//  * @param mixed  $value  Value of the option. If stored serialized, it will be
	//  *                       unserialized prior to being returned.
	//  * @param string $option Option name.
	//  */
	// return hkm_apply_filters( "option_{$option}", maybe_unserialize( $value ), $option );
}




/**
 * Escaping for HTML blocks.
 *
 * @since 2.8.0
 *
 * @param string $text
 * @return string
 */
function esc_html( $text ) {
	$safe_text = hkm_check_invalid_utf8( $text );
	$safe_text = _hkm_specialchars( $safe_text, ENT_QUOTES );
	/**
	 * Filters a string cleaned and escaped for output in HTML.
	 *
	 * Text passed to esc_html() is stripped of invalid or special characters
	 * before output.
	 *
	 * @since 2.8.0
	 *
	 * @param string $safe_text The text after it has been escaped.
	 * @param string $text      The text prior to being escaped.
	 */
	return hkm_apply_filters( 'esc_html', $safe_text, $text );
}


/**
 * Escaping for HTML attributes.
 *
 * @since 2.8.0
 *
 * @param string $text
 * @return string
 */
function esc_attr( $text ) {
	$safe_text = hkm_check_invalid_utf8( $text );
	$safe_text = _hkm_specialchars( $safe_text, ENT_QUOTES );
	/**
	 * Filters a string cleaned and escaped for output in an HTML attribute.
	 *
	 * Text passed to esc_attr() is stripped of invalid or special characters
	 * before output.
	 *
	 * @since 2.0.6
	 *
	 * @param string $safe_text The text after it has been escaped.
	 * @param string $text      The text prior to being escaped.
	 */
	return hkm_apply_filters( 'attribute_escape', $safe_text, $text );
}
/**
 * Converts a number of special characters into their HTML entities.
 *
 * Specifically deals with: &, <, >, ", and '.
 *
 * $quote_style can be set to ENT_COMPAT to encode " to
 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
 *
 * @since 1.2.2
 * @since 5.5.0 `$quote_style` also accepts `ENT_XML1`.
 * @access private
 *
 * @param string       $string        The text which is to be encoded.
 * @param int|string   $quote_style   Optional. Converts double quotes if set to ENT_COMPAT,
 *                                    both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES.
 *                                    Converts single and double quotes, as well as converting HTML
 *                                    named entities (that are not also XML named entities) to their
 *                                    code points if set to ENT_XML1. Also compatible with old values;
 *                                    converting single quotes if set to 'single',
 *                                    double if set to 'double' or both if otherwise set.
 *                                    Default is ENT_NOQUOTES.
 * @param false|string $charset       Optional. The character encoding of the string. Default false.
 * @param bool         $double_encode Optional. Whether to encode existing HTML entities. Default false.
 * @return string The encoded text with HTML entities.
 */
function _hkm_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) ) {
		return '';
	}

	// Don't bother if there are no specialchars - saves some processing.
	if ( ! preg_match( '/[&<>"\']/', $string ) ) {
		return $string;
	}

	// Account for the previous behaviour of the function when the $quote_style is not an accepted value.
	if ( empty( $quote_style ) ) {
		$quote_style = ENT_NOQUOTES;
	} elseif ( ENT_XML1 === $quote_style ) {
		$quote_style = ENT_QUOTES | ENT_XML1;
	} elseif ( ! in_array( $quote_style, array( ENT_NOQUOTES, ENT_COMPAT, ENT_QUOTES, 'single', 'double' ), true ) ) {
		$quote_style = ENT_QUOTES;
	}

	// Store the site charset as a static to avoid multiple calls to hkm_load_alloptions().
	if ( ! $charset ) {
		static $_charset = null;
		// if ( ! isset( $_charset ) ) {
		// 	$alloptions = hkm_load_alloptions();
		// 	$_charset   = isset( $alloptions['blog_charset'] ) ? $alloptions['blog_charset'] : '';
		// }
		// $charset = $_charset;
		$charset = 'utf8';
	}

	if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ), true ) ) {
		$charset = 'UTF-8';
	}

	$_quote_style = $quote_style;

	if ( 'double' === $quote_style ) {
		$quote_style  = ENT_COMPAT;
		$_quote_style = ENT_COMPAT;
	} elseif ( 'single' === $quote_style ) {
		$quote_style = ENT_NOQUOTES;
	}

	if ( ! $double_encode ) {
		// Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
		// This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
		// $string = hkm_kses_normalize_entities( $string, ( $quote_style & ENT_XML1 ) ? 'xml' : 'html' );
	}

	$string = htmlspecialchars( $string, $quote_style, $charset, $double_encode );

	// Back-compat.
	if ( 'single' === $_quote_style ) {
		$string = str_replace( "'", '&#039;', $string );
	}

	return $string;
}
// FUNCTION FILE

if (!function_exists('hkm_fetch_Dir')) {
	function hkm_fetch_Dir($x, $WithfFiles = false, $third_party = false)
	{
		$array = [];
		if (is_dir($x)) {
			$files = scandir($x);
			foreach ($files as $file) {
				if ($file != '.' && $file != "..") {
					if ($third_party) {
						if (is_dir($x . "/" . $file)) {
							$array[$file . '\\'] = $x . "/" . $file;
						}
					} else {
						if ($WithfFiles) {
							if (is_dir($x . "/" . $file)) {
								$array = array_merge(hkm_fetch_Dir($x . "/" . $file, $WithfFiles), $array);
							} else {
								$array[] = $x . "/" . $file;
							}
						} else {
							if (is_dir($x . "/" . $file)) {
								$array[] = $x . "/" . $file;
								$array = array_merge(hkm_fetch_Dir($x . "/" . $file), $array);
							}
						}
					}
				}
			}
		}
		return $array;
	}
}





/**
 * Test if a given path is a stream URL
 *
 * @since 3.5.0
 *
 * @param string $path The resource path or URL.
 * @return bool True if the path is a stream URL.
 */
function hkm_is_stream( $path ) {
	$scheme_separator = strpos( $path, '://' );

	if ( false === $scheme_separator ) {
		// $path isn't a stream.
		return false;
	}

	$stream = substr( $path, 0, $scheme_separator );

	return in_array( $stream, stream_get_wrappers(), true );
}

/**
 * Normalize a filesystem path.
 *
 * On windows systems, replaces backslashes with forward slashes
 * and forces upper-case drive letters.
 * Allows for two leading slashes for Windows network shares, but
 * ensures that all other duplicate slashes are reduced to a single.
 *
 * @since 3.9.0
 * @since 4.4.0 Ensures upper-case drive letters on Windows systems.
 * @since 4.5.0 Allows for Windows network shares.
 * @since 4.9.7 Allows for PHP file wrappers.
 *
 * @param string $path Path to normalize.
 * @return string Normalized path.
 */
function hkm_normalize_path( $path ) {
	$wrapper = '';

	if ( hkm_is_stream( $path ) ) {
		list( $wrapper, $path ) = explode( '://', $path, 2 );

		$wrapper .= '://';
	}

	// Standardise all paths to use '/'.
	$path = str_replace( '\\', '/', $path );

	// Replace multiple slashes down to a singular, allowing for network shares having two slashes.
	$path = preg_replace( '|(?<=.)/+|', '/', $path );

	// Windows paths should uppercase the drive letter.
	if ( ':' === substr( $path, 1, 1 ) ) {
		$path = ucfirst( $path );
	}

	return $wrapper . $path;
}

/**
 * Updates the value of an option that was already added.
 *
 * You do not need to serialize values. If the value needs to be serialized,
 * then it will be serialized before it is inserted into the database.
 * Remember, resources cannot be serialized or added as an option.
 *
 * If the option does not exist, it will be created.

 * This function is designed to work with or without a logged-in user. In terms of security,
 * plugin developers should check the current user's capabilities before updating any options.
 *
 *
 *
 * @param string      $option   Name of the option to update. Expected to not be SQL-escaped.
 * @param mixed       $value    Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param string|bool $autoload Optional. Whether to load the option when WordPress starts up. For existing options,
 *                              `$autoload` can only be updated using `hkm_update_option()` if `$value` is also changed.
 *                              Accepts 'yes'|true to enable or 'no'|false to disable. For non-existent options,
 *                              the default value is 'yes'. Default null.
 * @return bool True if the value was updated, false otherwise.
 */
function hkm_update_option( $option, $value, $autoload = null ) {
	
	return true;
}


/**
 * Mark a function argument as deprecated and inform when it has been used.
 *
 * This function is to be used whenever a deprecated function argument is used.
 * Before this function is called, the argument must be checked for whether it was
 * used by comparing it to its default value or evaluating whether it is empty.
 * For example:
 *
 *     if ( ! empty( $deprecated ) ) {
 *         _deprecated_argument( __FUNCTION__, '3.0.0' );
 *     }
 *
 * There is a hook deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function used the deprecated
 * argument.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * @param string $function The function that was called.
 * @param string $version  The version of WordPress that deprecated the argument used.
 * @param string $message  Optional. A message regarding the change. Default empty.
 */
function _deprecated_argument( $function, $version, $message = '' ) {

	
}

/**
 * Marks a deprecated action or filter hook as deprecated and throws a notice.
 *
 * Use the {@see 'deprecated_hook_run'} action to get the backtrace describing where
 * the deprecated hook was called.
 *
 * Default behavior is to trigger a user error if `WP_DEBUG` is true.
 *
 * This function is called by the do_action_deprecated() and hkm_apply_filters_deprecated()
 * functions, and so generally does not need to be called directly.
 *
 * @since 4.6.0
 * @since 5.4.0 The error type is now classified as E_USER_DEPRECATED (used to default to E_USER_NOTICE).
 * @access private
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default empty.
 * @param string $message     Optional. A message regarding the change. Default empty.
 */
function _hkm_deprecated_hook( $hook, $version, $replacement = '', $message = '' ) {
	
}
/**
 * Retrieve URL with nonce added to URL query.
 *
 * @since 2.0.4
 *
 * @param string     $actionurl URL to add nonce action.
 * @param int|string $action    Optional. Nonce action name. Default -1.
 * @param string     $name      Optional. Nonce name. Default '_wpnonce'.
 * @return string Escaped URL with nonce action added.
 */
function hkm_nonce_url( $actionurl, $action = -1, $name = '_wpnonce' ) {
	$actionurl = str_replace( '&amp;', '&', $actionurl );
	return esc_html( add_query_arg( $name, hkm_create_nonce( $action ), $actionurl ) );
}

/**
 * Retrieves a modified URL query string.
 *
 * You can rebuild the URL and append query variables to the URL query by using this function.
 * There are two ways to use this function; either a single key and value, or an associative array.
 *
 * Using a single key and value:
 *
 *     add_query_arg( 'key', 'value', 'http://example.com' );
 *
 * Using an associative array:
 *
 *     add_query_arg( array(
 *         'key1' => 'value1',
 *         'key2' => 'value2',
 *     ), 'http://example.com' );
 *
 * Omitting the URL from either use results in the current URL being used
 * (the value of `$_SERVER['REQUEST_URI']`).
 *
 * Values are expected to be encoded appropriately with urlencode() or rawurlencode().
 *
 * Setting any query variable's value to boolean false removes the key (see remove_query_arg()).
 *
 * Important: The return value of add_query_arg() is not escaped by default. Output should be
 * late-escaped with esc_url() or similar to help prevent vulnerability to cross-site scripting
 * (XSS) attacks.
 *
 * @since 1.5.0
 * @since 5.3.0 Formalized the existing and already documented parameters
 *              by adding `...$args` to the function signature.
 *
 * @param string|array $key   Either a query variable key, or an associative array of query variables.
 * @param string       $value Optional. Either a query variable value, or a URL to act upon.
 * @param string       $url   Optional. A URL to act upon.
 * @return string New URL query string (unescaped).
 */
function add_query_arg( ...$args ) {
	if ( is_array( $args[0] ) ) {
		if ( count( $args ) < 2 || false === $args[1] ) {
			$uri = $_SERVER['REQUEST_URI'];
		} else {
			$uri = $args[1];
		}
	} else {
		if ( count( $args ) < 3 || false === $args[2] ) {
			$uri = $_SERVER['REQUEST_URI'];
		} else {
			$uri = $args[2];
		}
	}

	$frag = strstr( $uri, '#' );
	if ( $frag ) {
		$uri = substr( $uri, 0, -strlen( $frag ) );
	} else {
		$frag = '';
	}

	if ( 0 === stripos( $uri, 'http://' ) ) {
		$protocol = 'http://';
		$uri      = substr( $uri, 7 );
	} elseif ( 0 === stripos( $uri, 'https://' ) ) {
		$protocol = 'https://';
		$uri      = substr( $uri, 8 );
	} else {
		$protocol = '';
	}

	if ( strpos( $uri, '?' ) !== false ) {
		list( $base, $query ) = explode( '?', $uri, 2 );
		$base                .= '?';
	} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
		$base  = $uri . '?';
		$query = '';
	} else {
		$base  = '';
		$query = $uri;
	}

	hkm_parse_str( $query, $qs );
	$qs = urlencode_deep( $qs ); // This re-URL-encodes things that were already in the query string.
	if ( is_array( $args[0] ) ) {
		foreach ( $args[0] as $k => $v ) {
			$qs[ $k ] = $v;
		}
	} else {
		$qs[ $args[0] ] = $args[1];
	}

	foreach ( $qs as $k => $v ) {
		if ( false === $v ) {
			unset( $qs[ $k ] );
		}
	}

	$ret = build_query( $qs );
	$ret = trim( $ret, '?' );
	$ret = preg_replace( '#=(&|$)#', '$1', $ret );
	$ret = $protocol . $base . $ret . $frag;
	$ret = rtrim( $ret, '?' );
	$ret = str_replace( '?#', '#', $ret );
	return $ret;
}
/**
 * Mark something as being incorrectly called.
 *
 * There is a hook {@see 'doing_it_wrong_run'} that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if `WP_DEBUG` is true.
 *
 
 *
 * @param string $function The function that was called.
 * @param string $message  A message explaining what has been done incorrectly.
 * @param string $version  The version of WordPress where the message was added.
 */
function _hkm_doing_it_wrong( $function, $message, $version ) {

}

/**
 * Convert float number to format based on the locale.
 *
 * @since 2.3.0
 *
 * @global WP_Locale $hkm_locale WordPress date and time locale object.
 *
 * @param float $number   The number to convert based on locale.
 * @param int   $decimals Optional. Precision of the number of decimal places. Default 0.
 * @return string Converted number in string format.
 */
function number_format_i18n( $number, $decimals = 0 ) {
	global $hkm_locale;

	if ( isset( $hkm_locale ) ) {
		$formatted = number_format( $number, absint( $decimals ), $hkm_locale->number_format['decimal_point'], $hkm_locale->number_format['thousands_sep'] );
	} else {
		$formatted = number_format( $number, absint( $decimals ) );
	}

	/**
	 * Filters the number formatted based on the locale.
	 *
	 * @since 2.8.0
	 * @since 4.9.0 The `$number` and `$decimals` parameters were added.
	 *
	 * @param string $formatted Converted number in string format.
	 * @param float  $number    The number to convert based on locale.
	 * @param int    $decimals  Precision of the number of decimal places.
	 */
	return hkm_apply_filters( 'number_format_i18n', $formatted, $number, $decimals );
}