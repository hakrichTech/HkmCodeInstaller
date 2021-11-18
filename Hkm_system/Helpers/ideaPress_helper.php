<?php

/**
 * Retrieve metadata from a file.
 *
 * Searches for metadata in the first 8 KB of a file, such as a plugin or theme.
 * Each piece of metadata must be on its own line. Fields can not span multiple
 * lines, the value will get cut at the end of the first line.
 *
 * If the file data is not within that first 8 KB, then the author should correct
 * their plugin file and move the data headers to the top.
 *
 * @link https://codex.wordpress.org/File_Header
 *
 * @since 2.9.0
 *
 * @param string $file            Absolute path to the file.
 * @param array  $default_headers List of headers, in the format `array( 'HeaderKey' => 'Header Name' )`.
 * @param string $context         Optional. If specified adds filter hook {@see 'extra_$context_headers'}.
 *                                Default empty.
 * @return string[] Array of file header values keyed by header name.
 */
function hkm_getFileData( $file, $default_headers, $context = '' ) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	if ( $fp ) {
		// Pull only the first 8 KB of the file in.
		$file_data = fread( $fp, 8 * KB_IN_BYTES );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );
	} else {
		$file_data = '';
	}

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

	/**
	 * Filters extra file headers by context.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to
	 * the context where extra headers might be loaded.
	 *
	 * @since 2.9.0
	 *
	 * @param array $extra_context_headers Empty array by default.
	 */
	$extra_headers = $context ? hkm_applyFilters( "extra_{$context}_headers", array() ) : array();
	if ( $extra_headers ) {
		$extra_headers = array_combine( $extra_headers, $extra_headers ); // Keys equal values.
		$all_headers   = array_merge( $extra_headers, (array) $default_headers );
	} else {
		$all_headers = $default_headers;
	}

	foreach ( $all_headers as $field => $regex ) {
		if ( preg_match( '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$all_headers[ $field ] = _cleanupHeaderComment( $match[1] );
		} else {
			$all_headers[ $field ] = '';
		}
	}

	return $all_headers;
}


/**
 * Strip close comment and close php tags from file headers used by HkmCode.
 *
 * @since 2.8.0
 * @access private
 *
 * @see https://core.trac.wordpress.org/ticket/8497
 *
 * @param string $str Header comment to clean up.
 * @return string
 */
function _cleanupHeaderComment( $str ) {
	return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
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
 * @since 3.0.0
 * @since 5.4.0 This function is no longer marked as "private".
 * @since 5.4.0 The error type is now classified as E_USER_DEPRECATED (used to default to E_USER_NOTICE).
 *
 * @param string $function The function that was called.
 * @param string $version  The version of WordPress that deprecated the argument used.
 * @param string $message  Optional. A message regarding the change. Default empty.
 */
function _hkm_deprecated_argument( $function, $version, $message = '' ) {

	/**
	 * Fires when a deprecated argument is called.
	 *
	 * @since 3.0.0
	 *
	 * @param string $function The function that was called.
	 * @param string $message  A message regarding the change.
	 * @param string $version  The version of WordPress that deprecated the argument used.
	 */
	hkm_do_action( 'deprecated_argument_run', $function, $message, $version );

	/**
	 * Filters whether to trigger an error for deprecated arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $trigger Whether to trigger the error for deprecated arguments. Default true.
	 */
	if ( HKM_DEBUG && hkm_applyFilters( 'deprecated_argument_trigger_error', true ) ) {
		if ( function_exists( '__' ) ) {
			if ( $message ) {
				trigger_error(
					sprintf(
						/* translators: 1: PHP function name, 2: Version number, 3: Optional message regarding the change. */
						__( '%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s! %3$s' ),
						$function,
						$version,
						$message
					),
					E_USER_DEPRECATED
				);
			} else {
				trigger_error(
					sprintf(
						/* translators: 1: PHP function name, 2: Version number. */
						__( '%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s with no alternative available.' ),
						$function,
						$version
					),
					E_USER_DEPRECATED
				);
			}
		} else {
			if ( $message ) {
				trigger_error(
					sprintf(
						'%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s! %3$s',
						$function,
						$version,
						$message
					),
					E_USER_DEPRECATED
				);
			} else {
				trigger_error(
					sprintf(
						'%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s with no alternative available.',
						$function,
						$version
					),
					E_USER_DEPRECATED
				);
			}
		}
	}
}

/**
 * Retrieve the translation of $text.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * *Note:* Don't use translate() directly, use __() or related functions.
 *
 * @since 2.2.0
 * @since 5.5.0 Introduced gettext-{$domain} filter.
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string Translated text.
 */
function hkm_translate( $text, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation  = $translations->translate( $text );

	/**
	 * Filters text with its translation.
	 *
	 * @since 2.0.11
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Text to translate.
	 * @param string $domain      Text domain. Unique identifier for retrieving translated strings.
	 */
	$translation = hkm_applyFilters( 'gettext', $translation, $text, $domain );

	/**
	 * Filters text with its translation for a domain.
	 *
	 * The dynamic portion of the hook, `$domain`, refers to the text domain.
	 *
	 * @since 5.5.0
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Text to translate.
	 * @param string $domain      Text domain. Unique identifier for retrieving translated strings.
	 */
	$translation = hkm_applyFilters( "gettext_{$domain}", $translation, $text, $domain );

	return $translation;
}


/**
 * Return the Translations instance for a text domain.
 *
 * If there isn't one, returns empty Translations instance.
 *
 * @since 2.8.0
 *
 * @global MO[] $l10n
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return Translations|NOOP_Translations A Translations instance.
 */
function hkm_get_translations_for_domain( $domain ) {
	global $l10n;
	if ( isset( $l10n[ $domain ] ) || ( _load_textdomain_just_in_time( $domain ) && isset( $l10n[ $domain ] ) ) ) {
		return $l10n[ $domain ];
	}

	static $noop_translations = null;
	if ( null === $noop_translations ) {
		$noop_translations = new NOOP_Translations;
	}

	return $noop_translations;
}


/**
 * Loads plugin and theme textdomains just-in-time.
 *
 * When a textdomain is encountered for the first time, we try to load
 * the translation file from `wp-content/languages`, removing the need
 * to call load_plugin_texdomain() or load_theme_texdomain().
 *
 * @since 4.6.0
 * @access private
 *
 * @see get_translations_for_domain()
 * @global MO[] $l10n_unloaded An array of all text domains that have been unloaded again.
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return bool True when the textdomain is successfully loaded, false otherwise.
 */
function _load_textdomain_just_in_time( $domain ) {
	global $l10n_unloaded;

	$l10n_unloaded = (array) $l10n_unloaded;

	// Short-circuit if domain is 'default' which is reserved for core.
	if ( 'default' === $domain || isset( $l10n_unloaded[ $domain ] ) ) {
		return false;
	}

	$translation_path = _get_path_to_translation( $domain );
	if ( false === $translation_path ) {
		return false;
	}

	return load_textdomain( $domain, $translation_path );
}


/**
 * Gets the path to a translation file for loading a textdomain just in time.
 *
 * Caches the retrieved results internally.
 *
 * @since 4.7.0
 * @access private
 *
 * @see _load_textdomain_just_in_time()
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @param bool   $reset  Whether to reset the internal cache. Used by the switch to locale functionality.
 * @return string|false The path to the translation file or false if no translation file was found.
 */
function _get_path_to_translation( $domain, $reset = false ) {
	static $available_translations = array();

	if ( true === $reset ) {
		$available_translations = array();
	}

	if ( ! isset( $available_translations[ $domain ] ) ) {
		$available_translations[ $domain ] = _get_path_to_translation_from_lang_dir( $domain );
	}

	return $available_translations[ $domain ];
}


/**
 * Gets the path to a translation file in the languages directory for the current locale.
 *
 * Holds a cached list of available .mo files to improve performance.
 *
 * @since 4.7.0
 * @access private
 *
 * @see _get_path_to_translation()
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return string|false The path to the translation file or false if no translation file was found.
 */
function _get_path_to_translation_from_lang_dir( $domain ) {
	static $cached_mofiles = null;

	if ( null === $cached_mofiles ) {
		$cached_mofiles = array();

		$locations = array(
			HKM_LANG_DIR . '/plugins',
			HKM_LANG_DIR . '/themes',
		);

		foreach ( $locations as $location ) {
			$mofiles = glob( $location . '/*.mo' );
			if ( $mofiles ) {
				$cached_mofiles = array_merge( $cached_mofiles, $mofiles );
			}
		}
	}

	$locale = determine_locale();
	$mofile = "{$domain}-{$locale}.mo";

	$path = HKM_LANG_DIR . '/plugins/' . $mofile;
	if ( in_array( $path, $cached_mofiles, true ) ) {
		return $path;
	}

	$path = HKM_LANG_DIR . '/themes/' . $mofile;
	if ( in_array( $path, $cached_mofiles, true ) ) {
		return $path;
	}

	return false;
}



/**
 * Determine the current locale desired for the request.
 *
 * @since 5.0.0
 *
 * @global string $pagenow
 *
 * @return string The determined locale.
 */
function determine_locale() {
	/**
	 * Filters the locale for the current request prior to the default determination process.
	 *
	 * Using this filter allows to override the default logic, effectively short-circuiting the function.
	 *
	 * @since 5.0.0
	 *
	 * @param string|null $locale The locale to return and short-circuit. Default null.
	 */
	$determined_locale = hkm_applyFilters( 'pre_determine_locale', null );

	if ( ! empty( $determined_locale ) && is_string( $determined_locale ) ) {
		return $determined_locale;
	}

	$determined_locale = get_locale();
	/**
	 * Filters the locale for the current request.
	 *
	 * @since 5.0.0
	 *
	 * @param string $locale The locale.
	 */
	return hkm_applyFilters( 'determine_locale', $determined_locale );
}

/**
 * Retrieves the current locale.
 *
 * If the locale is set, then it will filter the locale in the {@see 'locale'}
 * filter hook and return the value.
 *
 * If the locale is not set already, then the WPLANG constant is used if it is
 * defined. Then it is filtered through the {@see 'locale'} filter hook and
 * the value for the locale global set and the locale is returned.
 *
 * The process to get the locale should only be done once, but the locale will
 * always be filtered using the {@see 'locale'} hook.
 *
 * @since 1.5.0
 *
 * @global string $locale           The current locale.
 * @global string $hkm_local_package Locale code of the package.
 *
 * @return string The locale of the blog or from the {@see 'locale'} hook.
 */
function get_locale() {
	global $locale, $hkm_local_package;

	if ( isset( $locale ) ) {
		/** This filter is documented in wp-includes/l10n.php */
		return hkm_applyFilters( 'locale', $locale );
	}

	if ( isset( $hkm_local_package ) ) {
		$locale = $hkm_local_package;
	}

	// WPLANG was defined in wp-config.
	if ( defined( 'HKMLANG' ) ) {
		$locale = HKMLANG;
	}

	
		$db_locale = get_option( 'HKMLANG' );
		if ( false !== $db_locale ) {
			$locale = $db_locale;
		}
	

	if ( empty( $locale ) ) {
		$locale = 'en_US';
	}

	/**
	 * Filters the locale ID of the WordPress installation.
	 *
	 * @since 1.5.0
	 *
	 * @param string $locale The locale ID.
	 */
	return hkm_applyFilters( 'locale', $locale );
}


/**
 * Load a .mo file into the text domain $domain.
 *
 * If the text domain already exists, the translations will be merged. If both
 * sets have the same string, the translation from the original value will be taken.
 *
 * On success, the .mo file will be placed in the $l10n global by $domain
 * and will be a MO object.
 *
 * @since 1.5.0
 *
 * @global MO[] $l10n          An array of all currently loaded text domains.
 * @global MO[] $l10n_unloaded An array of all text domains that have been unloaded again.
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @param string $mofile Path to the .mo file.
 * @return bool True on success, false on failure.
 */
function load_textdomain( $domain, $mofile ) {
	global $l10n, $l10n_unloaded;

	$l10n_unloaded = (array) $l10n_unloaded;

	/**
	 * Filters whether to override the .mo file loading.
	 *
	 * @since 2.9.0
	 *
	 * @param bool   $override Whether to override the .mo file loading. Default false.
	 * @param string $domain   Text domain. Unique identifier for retrieving translated strings.
	 * @param string $mofile   Path to the MO file.
	 */
	$plugin_override = hkm_applyFilters( 'override_load_textdomain', false, $domain, $mofile );

	if ( true === (bool) $plugin_override ) {
		unset( $l10n_unloaded[ $domain ] );

		return true;
	}

	/**
	 * Fires before the MO translation file is loaded.
	 *
	 * @since 2.9.0
	 *
	 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
	 * @param string $mofile Path to the .mo file.
	 */
	hkm_do_action( 'load_textdomain', $domain, $mofile );

	/**
	 * Filters MO file path for loading translations for a specific text domain.
	 *
	 * @since 2.9.0
	 *
	 * @param string $mofile Path to the MO file.
	 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
	 */
	$mofile = hkm_applyFilters( 'load_textdomain_mofile', $mofile, $domain );

	if ( ! is_readable( $mofile ) ) {
		return false;
	}

	$mo = new MO();
	if ( ! $mo->import_from_file( $mofile ) ) {
		return false;
	}

	if ( isset( $l10n[ $domain ] ) ) {
		$mo->merge_with( $l10n[ $domain ] );
	}

	unset( $l10n_unloaded[ $domain ] );

	$l10n[ $domain ] = &$mo;

	return true;
}
