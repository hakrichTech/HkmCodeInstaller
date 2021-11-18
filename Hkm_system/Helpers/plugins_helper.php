<?php

use Hkm_code\Hook\Hkm_Hook;

/** @var Hkm_Hook[] $hkm_filter */
global $hkm_filter;

/** @var int[] $hkm_actions */
global $hkm_actions;

/** @var string[] $hkm_current_filter */
global $hkm_current_filter;
if ( $hkm_filter ) {
	$hkm_filter = Hkm_Hook::BUILD_PREINITIALIZED_HOOKS( $hkm_filter );
} else {
	$hkm_filter = array();
}

if ( ! isset( $hkm_actions ) ) {
	$hkm_actions = array();
}

if ( ! isset( $hkm_current_filter ) ) {
	$hkm_current_filter = array();
}

hkm_helper('plugins');

/**
 * Parses the plugin contents to retrieve plugin's metadata.
 *
 * All plugin headers must be on their own line. Plugin description must not have
 * any newlines, otherwise only parts of the description will be displayed.
 * The below is formatted for printing.
 *
 *     /*
 *     Plugin Name: Name of the plugin.
 *     Plugin URI: The home page of the plugin.
 *     Description: Plugin description.
 *     Author: Plugin author's name.
 *     Author URI: Link to the author's website.
 *     Version: Plugin version.
 *     Text Domain: Optional. Unique identifier, should be same as the one used in
 *          load_plugin_textdomain().
 *     Domain Path: Optional. Only useful if the translations are located in a
 *          folder above the plugin's base path. For example, if .mo files are
 *          located in the locale folder then Domain Path will be "/locale/" and
 *          must have the first slash. Defaults to the base folder the plugin is
 *          located in.
 *     Network: Optional. Specify "Network: true" to require that a plugin is activated
 *          across all sites in an installation. This will prevent a plugin from being
 *          activated on a single site when Multisite is enabled.
 *     Requires at least: Optional. Specify the minimum required WordPress version.
 *     Requires PHP: Optional. Specify the minimum required PHP version.
 *     * / # Remove the space to close comment.
 *
 * The first 8 KB of the file will be pulled in and if the plugin data is not
 * within that first 8 KB, then the plugin author should correct their plugin
 * and move the plugin data headers to the top.
 *
 * The plugin file is assumed to have permissions to allow for scripts to read
 * the file. This is not checked however and the file is only opened for
 * reading.
 *
 * @since 1.5.0
 * @since 5.3.0 Added support for `Requires at least` and `Requires PHP` headers.
 * @since 5.8.0 Added support for `Update URI` header.
 *
 * @param string $plugin_file Absolute path to the main plugin file.
 * @param bool   $markup      Optional. If the returned data should have HTML markup applied.
 *                            Default true.
 * @param bool   $translate   Optional. If the returned data should be translated. Default true.
 * @return array {
 *     Plugin data. Values will be empty if not supplied by the plugin.
 *
 *     @type string $Name        Name of the plugin. Should be unique.
 *     @type string $Title       Title of the plugin and link to the plugin's site (if set).
 *     @type string $Description Plugin description.
 *     @type string $Author      Author's name.
 *     @type string $AuthorURI   Author's website address (if set).
 *     @type string $Version     Plugin version.
 *     @type string $TextDomain  Plugin textdomain.
 *     @type string $DomainPath  Plugins relative directory path to .mo files.
 *     @type bool   $Network     Whether the plugin can only be activated network-wide.
 *     @type string $RequiresWP  Minimum required version of WordPress.
 *     @type string $RequiresPHP Minimum required version of PHP.
 *     @type string $UpdateURI   ID of the plugin for update purposes, should be a URI.
 * }
 */
function hkm_getPluginData( $plugin_file, $markup = true, $translate = true ) {

	$default_headers = array(
		'Name'        => 'Plugin Name',
		'PluginURI'   => 'Plugin URI',
		'Version'     => 'Version',
		'Description' => 'Description',
		'Author'      => 'Author',
		'AuthorURI'   => 'Author URI',
		'TextDomain'  => 'Text Domain',
		'DomainPath'  => 'Domain Path',
		'Network'     => 'Network',
		'RequiresWP'  => 'Requires at least',
		'RequiresPHP' => 'Requires PHP',
		'UpdateURI'   => 'Update URI',
		// Site Wide Only is deprecated in favor of Network.
		'_sitewide'   => 'Site Wide Only',
	);

	$plugin_data = hkm_getFileData( $plugin_file, $default_headers, 'plugin' );

	// Site Wide Only is the old header for Network.
	if ( ! $plugin_data['Network'] && $plugin_data['_sitewide'] ) {
		/* translators: 1: Site Wide Only: true, 2: Network: true */
		_hkm_deprecated_argument( __FUNCTION__, '3.0.0', sprintf( __( 'The %1$s plugin header is deprecated. Use %2$s instead.' ), '<code>Site Wide Only: true</code>', '<code>Network: true</code>' ) );
		$plugin_data['Network'] = $plugin_data['_sitewide'];
	}
	$plugin_data['Network'] = ( 'true' === strtolower( $plugin_data['Network'] ) );
	unset( $plugin_data['_sitewide'] );

	// If no text domain is defined fall back to the plugin slug.
	if ( ! $plugin_data['TextDomain'] ) {
		$plugin_slug = dirname( plugin_basename( $plugin_file ) );
		if ( '.' !== $plugin_slug && false === strpos( $plugin_slug, '/' ) ) {
			$plugin_data['TextDomain'] = $plugin_slug;
		}
	}

	if ( $markup || $translate ) {
		$plugin_data = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
	} else {
		$plugin_data['Title']      = $plugin_data['Name'];
		$plugin_data['AuthorName'] = $plugin_data['Author'];
	}

	return $plugin_data;
}





/**
 * Calls the callback functions that have been added to a filter hook.
 *
 * This function invokes all functions attached to filter hook `$hook_name`.
 * It is possible to create new filter hooks by simply calling this function,
 * specifying the name of the new hook using the `$hook_name` parameter.
 *
 * The function also allows for multiple additional arguments to be passed to hooks.
 *
 * Example usage:
 *
 *     // The filter callback function.
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string.
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback()' function
 *      * that's hooked onto `example_filter` above.
 *      *
 *      * - 'example_filter' is the filter hook.
 *      * - 'filter me' is the value being filtered.
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @since 0.71
 *
 * @global Hkm_Hook[] $hkm_filter         Stores all of the filters and actions.
 * @global string[]  $hkm_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the filter hook.
 * @param mixed  $value     The value to filter.
 * @param mixed  ...$args   Additional parameters to pass to the callback functions.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function hkm_applyFilters( $hook_name, $value ) {
	global $hkm_filter, $hkm_current_filter;

	$args = func_get_args();

	// Do 'all' actions first.
	if ( isset( $hkm_filter['all'] ) ) {
		$hkm_current_filter[] = $hook_name;
		_hkm_callAllHook( $args );
	}

	if ( ! isset( $hkm_filter[ $hook_name ] ) ) {
		if ( isset( $hkm_filter['all'] ) ) {
			array_pop( $hkm_current_filter );
		}

		return $value;
	}

	if ( ! isset( $hkm_filter['all'] ) ) {
		$hkm_current_filter[] = $hook_name;
	}

	// Don't pass the tag name to Hkm_Hook.
	array_shift( $args );

	$filtered = $hkm_filter[ $hook_name ]::APPLY_FILTERS( $value, $args );

	array_pop( $hkm_current_filter );

	return $filtered;
}



/**
 * Calls the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @since 2.5.0
 * @access private
 *
 * @global WP_Hook[] $hkm_filter Stores all of the filters and actions.
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _hkm_callAllHook( $args ) {
	global $hkm_filter;

	$hkm_filter['all']::DO_ALL_HOOK( $args );
}


/**
 * Builds Unique ID for storage and retrieval.
 *
 * The old way to serialize the callback caused issues and this function is the
 * solution. It works by checking for objects and creating a new property in
 * the class to keep track of the object and new objects of the same class that
 * need to be added.
 *
 * It also allows for the removal of actions and filters for objects after they
 * change class properties. It is possible to include the property $hkm_filter_id
 * in your class and set it to "null" or a number to bypass the workaround.
 * However this will prevent you from adding new classes and any new classes
 * will overwrite the previous hook by the same class.
 *
 * Functions and static method callbacks are just returned as strings and
 * shouldn't have any speed penalty.
 *
 * @link https://core.trac.wordpress.org/ticket/3875
 *
 * @since 2.2.3
 * @since 5.3.0 Removed workarounds for spl_object_hash().
 *              `$hook_name` and `$priority` are no longer used,
 *              and the function always returns a string.
 * @access private
 *
 * @param string   $hook_name Unused. The name of the filter to build ID for.
 * @param callable $callback  The function to generate ID for.
 * @param int      $priority  Unused. The order in which the functions
 *                            associated with a particular action are executed.
 * @return string Unique function ID for usage as array key.
 */
function _hkm_filter_build_unique_id( $hook_name, $callback, $priority ) {
	if ( is_string( $callback ) ) {
		return $callback;
	}
	 

	if ( is_object( $callback ) ) {
		// Closures are currently implemented as objects.
		$callback = array( $callback, '' );
	} else {
		$callback = (array) $callback;
	}

	if ( is_object( $callback[0] ) ) {
		// Object class calling.
		return spl_object_hash( $callback[0] ) . $callback[1];
	} elseif ( is_string( $callback[0] ) ) {
		// Static calling.
		return $callback[0] . '::' . $callback[1];
	}
}




/**
 * Calls the callback functions that have been added to an action hook.
 *
 * This function invokes all functions attached to action hook `$hook_name`.
 * It is possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$hook_name` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with `apply_filters()`.
 *
 * Example usage:
 *
 *     // The action callback function.
 *     function example_callback( $arg1, $arg2 ) {
 *         // (maybe) do something with the args.
 *     }
 *     add_action( 'example_action', 'example_callback', 10, 2 );
 *
 *     /*
 *      * Trigger the actions by calling the 'example_callback()' function
 *      * that's hooked onto `example_action` above.
 *      *
 *      * - 'example_action' is the action hook.
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = do_action( 'example_action', $arg1, $arg2 );
 *
 * @since 1.2.0
 * @since 5.3.0 Formalized the existing and already documented `...$arg` parameter
 *              by adding it to the function signature.
 *
 * @global WP_Hook[] $hkm_filter         Stores all of the filters and actions.
 * @global int[]     $hkm_actions        Stores the number of times each action was triggered.
 * @global string[]  $hkm_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the action to be executed.
 * @param mixed  ...$arg    Optional. Additional arguments which are passed on to the
 *                          functions hooked to the action. Default empty.
 */
function hkm_do_action( $hook_name, ...$arg ) {
	global $hkm_filter, $hkm_actions, $hkm_current_filter;

	if ( ! isset( $hkm_actions[ $hook_name ] ) ) {
		$hkm_actions[ $hook_name ] = 1;
	} else {
		++$hkm_actions[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $hkm_filter['all'] ) ) {
		$hkm_current_filter[] = $hook_name;
		$all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_hkm_callAllHook( $all_args );
	}

	if ( ! isset( $hkm_filter[ $hook_name ] ) ) {
		if ( isset( $hkm_filter['all'] ) ) {
			array_pop( $hkm_current_filter );
		}

		return;
	}

	if ( ! isset( $hkm_filter['all'] ) ) {
		$hkm_current_filter[] = $hook_name;
	}

	if ( empty( $arg ) ) {
		$arg[] = '';
	} elseif ( is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) ) {
		// Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
		$arg[0] = $arg[0][0];
	}

	$hkm_filter[ $hook_name ]::D0_ACTION( $arg );

	array_pop( $hkm_current_filter );
}
