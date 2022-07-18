<?php

use Hkm_code\Exceptions\Hkm_Error;


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
 *     Requires at least: Optional. Specify the minimum required HkmCode version.
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
 *     @type string $PluginURI   Plugin URI.
 *     @type string $Version     Plugin version.
 *     @type string $Description Plugin description.
 *     @type string $Author      Plugin author's name.
 *     @type string $AuthorURI   Plugin author's website address (if set).
 *     @type string $TextDomain  Plugin textdomain.
 *     @type string $DomainPath  Plugin's relative directory path to .mo files.
 *     @type bool   $Network     Whether the plugin can only be activated network-wide.
 *     @type string $RequiresHKM  Minimum required version of HkmCode.
 *     @type string $RequiresPHP Minimum required version of PHP.
 *     @type string $UpdateURI   ID of the plugin for update purposes, should be a URI.
 *     @type string $Title       Title of the plugin and link to the plugin's site (if set).
 *     @type string $AuthorName  Plugin author's name.
 * }
 */
function hkm_get_plugin_data( $plugin_file, $markup = true, $translate = true ) {

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
		'RequiresHKM'  => 'Requires at least',
		'RequiresPHP' => 'Requires PHP',
		'UpdateURI'   => 'Update URI',
		// Site Wide Only is deprecated in favor of Network.
		'_sitewide'   => 'Site Wide Only',
	); 

	hkm_helper('filesystem');
	$plugin_data = hkm_get_file_data( $plugin_file, $default_headers, 'plugin' );

	// Site Wide Only is the old header for Network.
	if ( ! $plugin_data['Network'] && $plugin_data['_sitewide'] ) {
		/* translators: 1: Site Wide Only: true, 2: Network: true */
		// _deprecated_argument( __FUNCTION__, '3.0.0', sprintf( __( 'The %1$s plugin header is deprecated. Use %2$s instead.' ), '<code>Site Wide Only: true</code>', '<code>Network: true</code>' ) );
		$plugin_data['Network'] = $plugin_data['_sitewide'];
	}
	$plugin_data['Network'] = ( 'true' === strtolower( $plugin_data['Network'] ) );
	unset( $plugin_data['_sitewide'] );

	// If no text domain is defined fall back to the plugin slug.
	if ( ! $plugin_data['TextDomain'] ) {
		$plugin_slug = dirname(hkm_plugin_basename( $plugin_file ) );
		if ( '.' !== $plugin_slug && false === strpos( $plugin_slug, '/' ) ) {
			$plugin_data['TextDomain'] = $plugin_slug;
		}
	}

	if ( $markup || $translate ) {
		$plugin_data = _hkm_get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
	} else {
		$plugin_data['Title']      = $plugin_data['Name'];
		$plugin_data['AuthorName'] = $plugin_data['Author'];
	}

	return $plugin_data;
}


function hkm_config_file($file, $app = 'system')
{
	hkm_helper('filesystem');

	if ($app == 'system') {
		$configPath = SYSTEMROOTPATH."Bin";
	}else {
		$configPath = SYSTEMROOTPATH."ConfigFiles/".$app;
	}
	$fileData = [];


    if (is_file($configPath . '/' . $file. ".config")) {
		$path = $configPath . '/' . $file. ".config";

		// Ensure the file is readable
		if (! is_readable($path))
		{
			throw new InvalidArgumentException("The file is not readable: {$path}");
		}

        $fileSize = (int) hkm_get_file_info($path)['size'];
        

		if ($fileSize) {
			$lines = hkm_get_file_data_in_array($path,"\n",$fileSize);
			foreach ($lines as $line)
			{

				// Is it a setting?
				if (strpos(trim($line), ';') === 0)
				{
					if (strpos($line, '=') !== false)
					{
					
	
						[$name, $value] = hkm_normalize_variable($line);
						$fileData[$name]        = $value;
					}
				}
				
	
				
			}
		}


		

		
    }
   return $fileData;
}

function hkm_create_config_file($file, $app = 'system')
{
	if ($app == 'system') {
		$configPath = SYSTEMROOTPATH."Bin";
	}else {
		$configPath = SYSTEMROOTPATH."ConfigFiles/".$app;
	}

	if (!is_dir($configPath)) {
        mkdir($configPath, 0777, true);
    }

	$fileCreated = true;
    if (!is_file($configPath . "/" . $file .".config")) {
        hkm_helper('filesystem');
        $fileCreated = hkm_write_file(
            $configPath . "/" . $file .".config",
            "
# Example Environment Configuration file
#
# This file can be used as a starting point for your own
# custom {project name}.auth files, and contains most of the possible settings
# available in a default install.

# By default, all of the settings are commented out. If you want
# to override the setting, you must un-comment it by removing the '#' and add ';' \r# at the beginning of the line.\r"
        );
		return $fileCreated;
    }
	return true;
}

function hkm_add_data_config_file($file, array $data, $app = 'system'){
    hkm_helper('filesystem');

	if ($app == 'system') {
		$configPath = SYSTEMROOTPATH."Bin";
	}else {
		$configPath = SYSTEMROOTPATH."ConfigFiles/".$app;
	}
	if (is_file($configPath . '/' . $file.".config")) {
		$path = $configPath . '/' . $file.".config";

		// Ensure the file is readable
		if (! is_readable($path))
		{
			throw new InvalidArgumentException("The file is not readable: {$path}");
		}

		if (hkm_isAssoc($data)) {
			$lines = "";
			$curData = hkm_config_file($file,$app);
			foreach ($data as $key => $value) {
				if (!isset($curData[$key])) {
					$lines .= ";".$key."=".$value."\r";
				}
			}
			hkm_helper('filesystem');
			
            return empty($lines)? true : hkm_write_file($configPath . "/" . $file .".config", $lines,'a+');
		}
		return __('Configuration data is not Assoc array!');

	}else{
		return __('Configuration file does not exist!');
	}

}

function hkm_modif_data_config_file($file, array $data, $app = 'system'){
    hkm_helper('filesystem');

	if ($app == 'system') {
		$configPath = SYSTEMROOTPATH."Bin";
	}else {
		$configPath = SYSTEMROOTPATH."ConfigFiles/".$app;
	}
	if (is_file($configPath .'/' . $file.".config")) {
		$path = $configPath . '/' . $file.".config";

		// Ensure the file is readable
		if (! is_readable($path))
		{
			throw new InvalidArgumentException("The file is not readable: {$path}");
		}

		if (hkm_isAssoc($data)) {

			$fileSize = (int) hkm_get_file_info($path)['size'];
			if ($fileSize) {
				$lines = hkm_get_file_data_in_array($path,"\n",$fileSize);
				$linesUpdate = array();
				foreach ($lines as $line)
				{
					// Is it a setting?
					if (strpos(trim($line), ';') === 0)
					{
						if (strpos($line, '=') !== false)
						{
						
		
							[$name, $value] = hkm_normalize_variable($line);
							if (isset($data[$name])) {
								$line = str_replace($value,$data[$name],$line);
							}
						}
					}

					$linesUpdate[] = $line;
					
		
					
				}
			}
			$lines = "";
			foreach ($linesUpdate as $ln) {
				$lines .= $ln."\r";
			}
			hkm_helper('filesystem');
			unlink($configPath . "/" . $file .".config");
			
            return empty($lines)? true : hkm_write_file($configPath . "/" . $file .".config", $lines);
		}
		return __('Configuration data is not Assoc array!');

	}else{
		return __('Configuration file does not exist!');
	}

}

function hkm_normalize_variable(string $name, string $value = '')
{
	// Split our compound string into its parts.
	if (strpos($name, '=') !== false)
	{
		[$name, $value] = explode('=', $name, 2);
	}


	// Sanitize the name
	$name = str_replace(['export', '\'', '"',';'], '', $name);
	$name  = trim($name);
	$value = trim($value);


	// Sanitize the value
	$value = hkm_sanitize_value($value);

	$value = hkm_resolve_nested_variable($value);

	return [
		$name,
		$value,
	];
}

function hkm_sanitize_value($value)
{
	if (! $value)
		{
			return $value;
		}

		// Does it begin with a quote?
		if (strpbrk($value[0], '"\'') !== false)
		{
			// value starts with a quote
			$quote        = $value[0];
			$regexPattern = sprintf(
					'/^
					%1$s          # match a quote at the start of the value
					(             # capturing sub-pattern used
								  (?:          # we do not need to capture this
								   [^%1$s\\\\] # any character other than a quote or backslash
								   |\\\\\\\\   # or two backslashes together
								   |\\\\%1$s   # or an escaped quote e.g \"
								  )*           # as many characters that match the previous rules
					)             # end of the capturing sub-pattern
					%1$s          # and the closing quote
					.*$           # and discard any string after the closing quote
					/mx', $quote
			);

			$value = preg_replace($regexPattern, '$1', $value);
			$value = str_replace("\\$quote", $quote, $value);
			$value = str_replace('\\\\', '\\', $value);
		}
		else
		{
			$parts = explode(' #', $value, 2);

			$value = trim($parts[0]);

			// Unquoted values cannot contain whitespace
			if (preg_match('/\s+/', $value) > 0)
			{
				throw new InvalidArgumentException('.env values containing spaces must be surrounded by quotes.');
			}
		}

		return $value;
}

function hkm_resolve_nested_variable($value){
	if (strpos($value, '$') !== false)
		{
			$value = preg_replace_callback(
				'/\${([a-zA-Z0-9_\.]+)}/',
				function ($matchedPatterns) {
					$nestedVariable = hkm_get_variable($matchedPatterns[1]);

					if (is_null($nestedVariable))
					{
						return $matchedPatterns[0];
					}

					return $nestedVariable;
				},
				$value
			);
		}

		return $value;
}

function hkm_get_variable(string $name)
{
	switch (true)
	{
		case array_key_exists($name, $_ENV):
			return $_ENV[$name];
		case array_key_exists($name, $_SERVER):
			return $_SERVER[$name];
		default:
			$value = getenv($name);

			// switch getenv default to null
			return $value === false ? null : $value;
	}
} 
function hkm_active_plugins($app='system')
{
	$act = array();
	$plugins = hkm_config_file( '_plugins_',$app);
	foreach ($plugins as $key => $value) {
		if ($value == "active") {
			$act[]=$key;
		}
	}
	return $act;
}

function hkm_active_plugins_update($data,$app='system')
{
	$res = hkm_modif_data_config_file('_plugins_', $data, $app);
	$res = hkm_add_data_config_file('_plugins_',$data,$app);
	return $res;
}

/**
 * Validate the plugin path.
 *
 * Checks that the main plugin file exists and is a valid plugin. See hkm_validate_file().
 *
 *
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * @return int|Hkm_Error 0 on success, Hkm_Error on failure.
 */
function hkm_validate_plugin( $plugin ) {
	if ( hkm_validate_file( $plugin ) ) {
		return new Hkm_Error( 'plugin_invalid', __( 'Invalid plugin path.' ) );
	}
	if ( ! is_dir( HKMMU_PLUGIN_DIR . '/' . $plugin ) ) {
		if (! is_dir( HKM_PLUGIN_DIR . '/' . $plugin )) {
		return new Hkm_Error( 'plugin_not_found', __( 'Plugin does not exist.' ) );
		}
		
	}

	$installed_sys_plugins = hkm_get_sys_plugins();

	if ( ! isset( $installed_sys_plugins[ $plugin."/__hkm__.txt" ] ) ) {
	    $installed_plugins = hkm_get_plugins();
		if(! isset( $installed_plugins[ $plugin ."/__hkm__.txt" ] )){
			return new Hkm_Error( 'no_plugin_header', __( 'The plugin does not have a valid header.' ) );
		}
	}
	return 0;
}

/**
 * Validates a file name and path against an allowed set of rules.
 *
 * A return value of `1` means the file path contains directory traversal.
 *
 * A return value of `2` means the file path contains a Windows drive path.
 *
 * A return value of `3` means the file is not in the allowed files list.
 *
 * @since 1.2.0
 *
 * @param string   $file          File path.
 * @param string[] $allowed_files Optional. Array of allowed files.
 * @return int 0 means nothing is wrong, greater than 0 means something was wrong.
 */
function hkm_validate_file( $file, $allowed_files = array() ) {
	if ( ! is_scalar( $file ) || '' === $file ) {
		return 0;
	}

	// `../` on its own is not allowed:
	if ( '../' === $file ) {
		return 1;
	}

	// More than one occurrence of `../` is not allowed:
	if ( preg_match_all( '#\.\./#', $file, $matches, PREG_SET_ORDER ) && ( count( $matches ) > 1 ) ) {
		return 1;
	}

	// `../` which does not occur at the end of the path is not allowed:
	if ( false !== strpos( $file, '../' ) && '../' !== mb_substr( $file, -3, 3 ) ) {
		return 1;
	}

	// Files not in the allowed file list are not allowed:
	if ( ! empty( $allowed_files ) && ! in_array( $file, $allowed_files, true ) ) {
		return 3;
	}

	// Absolute Windows drive paths are not allowed:
	if ( ':' === substr( $file, 1, 1 ) ) {
		return 2;
	}

	return 0;
}

/**
 * Checks compatibility with the current HkmCode version.
 *
 * @since 5.2.0
 *
 * @global string $hkm_version HkmCode version.
 *
 * @param string $required Minimum required HkmCode version.
 * @return bool True if required version is compatible or empty, false if not.
 */
function is_hkm_version_compatible( $required ) {
	global $hkm_version;

	// Strip off any -alpha, -RC, -beta, -src suffixes.
	list( $version ) = explode( '-', $hkm_version );

	return empty( $required ) || version_compare( $version, $required, '>=' );
}

/**
 * Checks compatibility with the current PHP version.
 *
 * @since 5.2.0
 *
 * @param string $required Minimum required PHP version.
 * @return bool True if required version is compatible or empty, false if not.
 */
function is_php_version_compatible( $required ) {
	return empty( $required ) || version_compare( phpversion(), $required, '>=' );
}

/**
 * Gets the URL to learn more about updating the PHP version the site is running on.
 *
 * This URL can be overridden by specifying an environment variable `HKM_UPDATE_PHP_URL` or by using the
 * {@see 'hkm_update_php_url'} filter. Providing an empty string is not allowed and will result in the
 * default URL being used. Furthermore the page the URL links to should preferably be localized in the
 * site language.
 *
 * @since 5.1.0
 *
 * @return string URL to learn more about updating PHP.
 */
function hkm_get_update_php_url() {
	$default_url = hkm_get_default_update_php_url();

	$update_url = $default_url;
	if ( false !== getenv( 'HKM_UPDATE_PHP_URL' ) ) {
		$update_url = getenv( 'HKM_UPDATE_PHP_URL' );
	}

	/**
	 * Filters the URL to learn more about updating the PHP version the site is running on.
	 *
	 * Providing an empty string is not allowed and will result in the default URL being used. Furthermore
	 * the page the URL links to should preferably be localized in the site language.
	 *
	 * @since 5.1.0
	 *
	 * @param string $update_url URL to learn more about updating PHP.
	 */
	$update_url = hkm_apply_filters( 'hkm_update_php_url', $update_url );

	if ( empty( $update_url ) ) {
		$update_url = $default_url;
	}

	return $update_url;
}

/**
 * Gets the default URL to learn more about updating the PHP version the site is running on.
 *
 * Do not use this function to retrieve this URL. Instead, use {@see hkm_get_update_php_url()} when relying on the URL.
 * This function does not allow modifying the returned URL, and is only used to compare the actually used URL with the
 * default one.
 *
 * @since 5.1.0
 * @access private
 *
 * @return string Default URL to learn more about updating PHP.
 */
function hkm_get_default_update_php_url() {
	return _x( 'https://wordpress.org/support/update-php/', 'localized PHP upgrade information page' );
}

/**
 * Returns the default annotation for the web hosting altering the "Update PHP" page URL.
 *
 * This function is to be used after {@see hkm_get_update_php_url()} to return a consistent
 * annotation if the web host has altered the default "Update PHP" page URL.
 *
 * @since 5.2.0
 *
 * @return string Update PHP page annotation. An empty string if no custom URLs are provided.
 */
function hkm_get_update_php_annotation() {
	$update_url  = hkm_get_update_php_url();
	$default_url = hkm_get_default_update_php_url();

	if ( $update_url === $default_url ) {
		return '';
	}

	$annotation = sprintf(
		/* translators: %s: Default Update PHP page URL. */
		__( 'This resource is provided by your web host, and is specific to your site. For more information, <a href="%s" target="_blank">see the official HkmCode documentation</a>.' ),
		esc_url( $default_url )
	);

	return $annotation;
}

/**
 * Validates the plugin requirements for HkmCode version and PHP version.
 *
 * Uses the information from `Requires at least` and `Requires PHP` headers
 * defined in the plugin's main PHP file.
 *
 * @since 5.2.0
 * @since 5.3.0 Added support for reading the headers from the plugin's
 *              main PHP file, with `readme.txt` as a fallback.
 * @since 5.8.0 Removed support for using `readme.txt` as a fallback.
 *
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * @return true|Hkm_Error True if requirements are met, Hkm_Error on failure.
 */
function hkm_validate_plugin_requirements( $plugin ,$app = '') {
	$plugin_headers = false;
	if ($app == 'system' && is_file(HKMMU_PLUGIN_DIR . '/' . $plugin . '/__hkm__.txt')) {
		$plugin_headers = hkm_get_plugin_data( HKMMU_PLUGIN_DIR . '/' . $plugin . '/__hkm__.txt' );
	}else{
		if (is_file(HKM_PLUGIN_DIR . '/' . $plugin . '/__hkm__.txt')) {
			$plugin_headers = hkm_get_plugin_data( HKM_PLUGIN_DIR . '/' . $plugin . '/__hkm__.txt' );
		}	
	}

	if ($plugin_headers) {
		$requirements = array(
			'requires'     => ! empty( $plugin_headers['RequiresHKM'] ) ? $plugin_headers['RequiresHKM'] : '',
			'requires_php' => ! empty( $plugin_headers['RequiresPHP'] ) ? $plugin_headers['RequiresPHP'] : '',
		);
		$compatible_hkm  = is_hkm_version_compatible( $requirements['requires'] );
	    $compatible_php = is_php_version_compatible( $requirements['requires_php'] );

		$php_update_message = '</p><p>' . sprintf(
			/* translators: %s: URL to Update PHP page. */
			__( '<a href="%s">Learn more about updating PHP</a>.' ),
			esc_url( hkm_get_update_php_url() )
		);

	
		$annotation = hkm_get_update_php_annotation();
	
		if ( $annotation ) {
			$php_update_message .= '</p><p><em>' . $annotation . '</em>';
		}

		if ( ! $compatible_hkm && ! $compatible_php ) {
			return new Hkm_Error(
				'plugin_hkm_php_incompatible',
				'<p>' . sprintf(
					/* translators: 1: Current HkmCode version, 2: Current PHP version, 3: Plugin name, 4: Required HkmCode version, 5: Required PHP version. */
					_x( '<strong>Error:</strong> Current versions of HkmCode (%1$s) and PHP (%2$s) do not meet minimum requirements for %3$s. The plugin requires HkmCode %4$s and PHP %5$s.', 'plugin' ),
					// get_bloginfo( 'version' ),
					2,
					phpversion(),
					$plugin_headers['Name'],
					$requirements['requires'],
					$requirements['requires_php']
				) . $php_update_message . '</p>'
			);
		} elseif ( ! $compatible_php ) {
			return new Hkm_Error(
				'plugin_php_incompatible',
				'<p>' . sprintf(
					/* translators: 1: Current PHP version, 2: Plugin name, 3: Required PHP version. */
					_x( '<strong>Error:</strong> Current PHP version (%1$s) does not meet minimum requirements for %2$s. The plugin requires PHP %3$s.', 'plugin' ),
					phpversion(),
					$plugin_headers['Name'],
					$requirements['requires_php']
				) . $php_update_message . '</p>'
			);
		} elseif ( ! $compatible_hkm ) {
			return new Hkm_Error(
				'plugin_hkm_incompatible',
				'<p>' . sprintf(
					/* translators: 1: Current HkmCode version, 2: Plugin name, 3: Required HkmCode version. */
					_x( '<strong>Error:</strong> Current HkmCode version (%1$s) does not meet minimum requirements for %2$s. The plugin requires HkmCode %3$s.', 'plugin' ),
					// get_bloginfo( 'version' ),
					2,
					$plugin_headers['Name'],
					$requirements['requires']
				) . '</p>'
			);
		}
	}else{
		return  new Hkm_Error('plugin_not_found',__( 'Plugin does not exist.' ));
	}

	

	

	



	return true;
}

/**
 * Load a given plugin attempt to generate errors.
 *
 * @since 4.4.0 Function was moved into the `hkm-admin/includes/plugin.php` file.
 *
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 */
function hkm_plugin_sandbox_scrape( $plugin ) {
	if ( ! defined( 'HKM_SANDBOX_SCRAPING' ) ) {
		define( 'HKM_SANDBOX_SCRAPING', true );
	}
   
	if (is_file(HKMMU_PLUGIN_DIR . '/' . $plugin. '/'. $plugin . '.php')) {
		hkm_register_plugin_realpath( HKMMU_PLUGIN_DIR . '/' . $plugin. '/'. $plugin . '.php'  );
	    include_once HKMMU_PLUGIN_DIR . '/' . $plugin . '/'. $plugin . '.php' ;
	}else{
		if (is_file(HKM_PLUGIN_DIR . '/' . $plugin. '/'. $plugin . '.php')) {
		hkm_register_plugin_realpath( HKM_PLUGIN_DIR . '/' . $plugin. '/'. $plugin . '.php'  );
			include_once HKM_PLUGIN_DIR . '/' . $plugin . '/'. $plugin . '.php' ;
		}
	}
}


/**
 * Attempts activation of plugin in a "sandbox" and redirects on success.
 *
 * A plugin that is already activated will not attempt to be activated again.
 *
 * The way it works is by setting the redirection to the error before trying to
 * include the plugin file. If the plugin fails, then the redirection will not
 * be overwritten with the success message. Also, the options will not be
 * updated and the activation hook will not be called on plugin error.
 *
 * It should be noted that in no way the below code will actually prevent errors
 * within the file. The code should not be used elsewhere to replicate the
 * "sandbox", which uses redirection to work.
 * {@source 13 1}
 *
 * If any errors are found or text is outputted, then it will be captured to
 * ensure that the success redirection will update the error redirection.
 *
 * @since 2.5.0
 * @since 5.2.0 Test for HkmCode version and PHP version compatibility.
 *
 * @param string $plugin       Path to the plugin file relative to the plugins directory.
 * @param bool   $silent       Optional. Whether to prevent calling activation hooks. Default false.
 * @return null|Hkm_Error Null on success, Hkm_Error on invalid file.
 */
function hkm_activate_plugin( $plugin, $silent = false, $app='system' ) {
	$plugin = hkm_plugin_basename( trim( $plugin ) );

	$current = hkm_active_plugins($app);


	$valid = hkm_validate_plugin( $plugin );
	if ( is_hkm_error( $valid ) ) {
		return $valid;
	}

	$requirements = hkm_validate_plugin_requirements( $plugin ,$app);
	if ( is_hkm_error( $requirements ) ) {
		return $requirements;
	}

	if (!in_array($plugin,$current) ) {
		
		ob_start();

		// Load the plugin to test whether it throws any errors.
		hkm_plugin_sandbox_scrape( $plugin );

		if ( ! $silent ) {
			/**
			 * Fires before a plugin is activated.
			 *
			 * If a plugin is silently activated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $plugin       Path to the plugin file relative to the plugins directory.
			 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
			 *                             or just the current site. Multisite only. Default false.
			 */
			// do_action( 'activate_plugin', $plugin );

			/**
			 * Fires as a specific plugin is being activated.
			 *
			 * This hook is the "activation" hook used internally by register_activation_hook().
			 * The dynamic portion of the hook name, `$plugin`, refers to the plugin basename.
			 *
			 * If a plugin is silently activated (such as during an update), this hook does not fire.
			 *
			 * @since 2.0.0
			 *
			 * @param bool $network_wide Whether to enable the plugin for all sites in the network
			 *                           or just the current site. Multisite only. Default false.
			 */
			hkm_do_action( "activate_{$plugin}");
		}

		$fc = hkm_active_plugins_update([$plugin => 'active']);
		if(!is_bool($fc)){
          echo $fc;
		}
		

		if ( ! $silent ) {
			/**
			 * Fires after a plugin has been activated.
			 *
			 * If a plugin is silently activated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $plugin       Path to the plugin file relative to the plugins directory.
			 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
			 *                             or just the current site. Multisite only. Default false.
			 */
			// do_action( 'activated_plugin', $plugin );
		}

		if ( ob_get_length() > 0 ) {
			$output = ob_get_clean();
			return new Hkm_Error( 'unexpected_output', __( 'The plugin generated unexpected output.' ), $output );
		}

		ob_end_clean();
	}

	return null;
}

function hkm_is_plugin_active($plugin)
{
	return in_array( $plugin, hkm_active_plugins(), true );

}

/**
 * Deactivate a single plugin or multiple plugins.
 *
 * The deactivation hook is disabled by the plugin upgrader by using the $silent
 * parameter.
 *
 *
 * @param string|string[] $plugins      Single plugin or list of plugins to deactivate.
 * @param bool            $silent       Prevent calling deactivation hooks. Default false.
 * @param bool|null       $network_wide Whether to deactivate the plugin for all sites in the network.
 *                                      A value of null will deactivate plugins for both the network
 *                                      and the current site. Multisite only. Default null.
 */
function hkm_deactivate_plugins( $plugins, $silent = false, $app = 'system' ) {
	
	$current    = hkm_active_plugins($app);
	$errors    = [];

	foreach ( (array) $plugins as $plugin ) {
		$plugin = hkm_plugin_basename( trim( $plugin ) );
		if (!in_array($plugin,$current)) {
		    $errors[] = new Hkm_Error('plugin_not_found',
			sprintf(
				__( '%s does not exist in activated plugins.' ),
				$plugin
			));
			$fjg = true;
		}else{
            $fjg=false;
		}

		if (!$fjg) {
			if ( ! $silent ) {
				/**
				 * Fires before a plugin is deactivated.
				 *
				 * If a plugin is silently deactivated (such as during an update),
				 * this hook does not fire.
				 *
				 *
				 * @param string $plugin               Path to the plugin file relative to the plugins directory.
				 * @param bool   $network_deactivating Whether the plugin is deactivated for all sites in the network
				 *                                     or just the current site. Multisite only. Default false.
				 */
				// do_action( 'deactivate_plugin', $plugin, $network_deactivating );
			}
            hkm_active_plugins_update([$plugin=>'inactive'],$app);

			if ( ! $silent ) {
				/**
				 * Fires as a specific plugin is being deactivated.
				 *
				 * This hook is the "deactivation" hook used internally by register_deactivation_hook().
				 * The dynamic portion of the hook name, `$plugin`, refers to the plugin basename.
				 *
				 * If a plugin is silently deactivated (such as during an update), this hook does not fire.
				 *
				 *
				 * @param bool $network_deactivating Whether the plugin is deactivated for all sites in the network
				 *                                   or just the current site. Multisite only. Default false.
				 */
				hkm_do_action( "deactivate_{$plugin}");
	
				/**
				 * Fires after a plugin is deactivated.
				 *
				 * If a plugin is silently deactivated (such as during an update),
				 * this hook does not fire.
				 *
				 *
				 * @param string $plugin               Path to the plugin file relative to the plugins directory.
				 * @param bool   $network_deactivating Whether the plugin is deactivated for all sites in the network
				 *                                     or just the current site. Multisite only. Default false.
				 */
				// do_action( 'deactivated_plugin', $plugin, $network_deactivating );
			}


		}

	}

	if ( ! empty( $errors ) ) {
		return new Hkm_Error( 'plugins_invalid', __( 'One of the plugins is invalid.' ), $errors );
	}

	return true;
}

/**
 * Activate multiple plugins.
 *
 * When Hkm_Error is returned, it does not mean that one of the plugins had
 * errors. It means that one or more of the plugin file paths were invalid.
 *
 * The execution will be halted as soon as one of the plugins has an error.
 *
 * @since 2.6.0
 *
 * @param string|string[] $plugins      Single plugin or list of plugins to activate.
 * @param string          $redirect     Redirect to page after successful activation.
 * @param bool            $network_wide Whether to enable the plugin for all sites in the network.
 *                                      Default false.
 * @param bool            $silent       Prevent calling activation hooks. Default false.
 * @return bool|Hkm_Error True when finished or Hkm_Error if there were errors during a plugin activation.
 */
function hkm_activate_plugins( $plugins, $silent = false, $app="system" ) {
	if ( ! is_array( $plugins ) ) {
		$plugins = array( $plugins );
	}

	$errors = array();
	foreach ( $plugins as $plugin ) {
		
		$result = hkm_activate_plugin( $plugin, $silent, $app );
		if ( is_hkm_error( $result ) ) {
			$errors[ $plugin ] = $result;
		}
	}

	if ( ! empty( $errors ) ) {
		return new Hkm_Error( 'plugins_invalid', __( 'One of the plugins is invalid.' ), $errors );
	}

	return true;
}




/**
 * Sanitizes plugin data, optionally adds markup, optionally translates.
 *
 *
 * @see hkm_get_plugin_data()
 *
 * @access private
 *
 * @param string $plugin_file Path to the main plugin file.
 * @param array  $plugin_data An array of plugin data. See `get_plugin_data()`.
 * @param bool   $markup      Optional. If the returned data should have HTML markup applied.
 *                            Default true.
 * @param bool   $translate   Optional. If the returned data should be translated. Default true.
 * @return array Plugin data. Values will be empty if not supplied by the plugin.
 *               See hkm_get_plugin_data() for the list of possible values.
 */
function _hkm_get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup = true, $translate = true ) {
	// Sanitize the plugin filename to a HKM_PLUGIN_DIR relative path.
	$plugin_file = hkm_plugin_basename( $plugin_file );
  
	// Translate fields.
	if ( $translate ) {
		$textdomain = $plugin_data['TextDomain'];
		if ( $textdomain ) {
			hkm_helper('Api_Language');
			if ( ! is_textdomain_loaded( $textdomain ) ) {
				if ( $plugin_data['DomainPath'] ) {
					load_plugin_textdomain( $textdomain, false, dirname( $plugin_file ) . $plugin_data['DomainPath'] );
				} else {
					load_plugin_textdomain( $textdomain, false, dirname( $plugin_file ) );
				}
			}
		} elseif ( 'hello.php' === basename( $plugin_file ) ) {
			$textdomain = 'default';
		}
		if ( $textdomain ) {
			foreach ( array( 'Name', 'PluginURI', 'Description', 'Author', 'AuthorURI', 'Version' ) as $field ) {
				// phpcs:ignore HkmCode.HKM.I18n.LowLevelTranslationFunction,HkmCode.HKM.I18n.NonSingularStringLiteralText,HkmCode.HKM.I18n.NonSingularStringLiteralDomain
				$plugin_data[ $field ] = translate( $plugin_data[ $field ], $textdomain );
			}
		}
	}

	// Sanitize fields.
	$allowed_tags_in_links = array(
		'abbr'    => array( 'title' => true ),
		'acronym' => array( 'title' => true ),
		'code'    => true,
		'em'      => true,
		'strong'  => true,
	);

	$allowed_tags      = $allowed_tags_in_links;
	$allowed_tags['a'] = array(
		'href'  => true,
		'title' => true,
	);

	// Name is marked up inside <a> tags. Don't allow these.
	// Author is too, but some plugins have used <a> here (omitting Author URI).
	// $plugin_data['Name']   = hkm_kses( $plugin_data['Name'], $allowed_tags_in_links );
	// $plugin_data['Author'] = hkm_kses( $plugin_data['Author'], $allowed_tags );

	// $plugin_data['Description'] = hkm_kses( $plugin_data['Description'], $allowed_tags );
	// $plugin_data['Version']     = hkm_kses( $plugin_data['Version'], $allowed_tags );

	// $plugin_data['PluginURI'] = esc_url( $plugin_data['PluginURI'] );
	// $plugin_data['AuthorURI'] = esc_url( $plugin_data['AuthorURI'] );

	// $plugin_data['Title']      = $plugin_data['Name'];
	// $plugin_data['AuthorName'] = $plugin_data['Author'];

	// Apply markup.
	// if ( $markup ) {
	// 	if ( $plugin_data['PluginURI'] && $plugin_data['Name'] ) {
	// 		$plugin_data['Title'] = '<a href="' . $plugin_data['PluginURI'] . '">' . $plugin_data['Name'] . '</a>';
	// 	}

	// 	if ( $plugin_data['AuthorURI'] && $plugin_data['Author'] ) {
	// 		$plugin_data['Author'] = '<a href="' . $plugin_data['AuthorURI'] . '">' . $plugin_data['Author'] . '</a>';
	// 	}

	// 	$plugin_data['Description'] = hkmtexturize( $plugin_data['Description'] );

	// 	if ( $plugin_data['Author'] ) {
	// 		$plugin_data['Description'] .= sprintf(
	// 			/* translators: %s: Plugin author. */
	// 			' <cite>' . __( 'By %s.' ) . '</cite>',
	// 			$plugin_data['Author']
	// 		);
	// 	}
	// }

	return $plugin_data;
}








/**
 * Check the plugins directory and retrieve all plugin files with plugin data.
 *
 * HkmCode only supports plugin files in the base plugins directory
 * (hkm-content/plugins) and in one directory above the plugins directory
 * (hkm-content/plugins/my-plugin). The file it looks for has the plugin data
 * and must be found in those two locations. It is recommended to keep your
 * plugin files in their own directories.
 *
 * The file with the plugin data is the file that will be included and therefore
 * needs to have the main execution for the plugin. This does not mean
 * everything must be contained in the file and it is recommended that the file
 * be split for maintainability. Keep everything in one file for extreme
 * optimization purposes.
 *
 * @since 1.5.0
 *
 * @param string $plugin_folder Optional. Relative path to single plugin folder.
 * @return array[] Array of arrays of plugin data, keyed by plugin file name. See `get_plugin_data()`.
 */
function hkm_get_plugins( $plugin_folder = '' ) {

	// $cache_plugins = hkm_cache_get( 'plugins', 'plugins' );
	// if ( ! $cache_plugins ) {
	// 	$cache_plugins = array();
	// }

	// if ( isset( $cache_plugins[ $plugin_folder ] ) ) {
	// 	return $cache_plugins[ $plugin_folder ];
	// }

	$hkm_plugins  = array();
	$plugin_root = HKM_PLUGIN_DIR;
	if ( ! empty( $plugin_folder ) ) {
		$plugin_root .= $plugin_folder;
	}


	// Files in hkm-content/plugins directory.
	$plugins_dir  = @opendir( $plugin_root );
	$plugin_files = array();
	if ( $plugins_dir ) {
		while ( ( $file = readdir( $plugins_dir ) ) !== false ) {
			if ( '.' === substr( $file, 0, 1 ) ) {
				continue;
			}

			if ( is_dir( $plugin_root . '/' . $file ) ) {
				$plugins_subdir = @opendir( $plugin_root . '/' . $file );


				if ( $plugins_subdir ) {
					while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
						if ( '.' === substr( $subfile, 0, 1 ) ) {
							continue;
						}


						if ( '__hkm__.txt' === substr( $subfile, -11 ) ) {
							$plugin_files[] = "$file/$subfile";
						}
					}

					closedir( $plugins_subdir );
				}
			} 
		}

		closedir( $plugins_dir );
	}

	if ( empty( $plugin_files ) ) {
		return $hkm_plugins;
	}

	foreach ( $plugin_files as $plugin_file ) {
		if ( ! is_readable( "$plugin_root/$plugin_file" ) ) {
			continue;
		}

		// Do not apply markup/translate as it will be cached.
		$plugin_data = hkm_get_plugin_data( "$plugin_root/$plugin_file", false, false );

		if ( empty( $plugin_data['Name'] ) ) {
			continue;
		}

		$hkm_plugins[hkm_plugin_basename( $plugin_file ) ] = $plugin_data;
	}

	uasort( $hkm_plugins, '_sort_uname_callback' );

	// $cache_plugins[ $plugin_folder ] = $hkm_plugins;
	// hkm_cache_set( 'plugins', $cache_plugins, 'plugins' );

	return $hkm_plugins;
}


/**
 * Check the sys-plugins directory and retrieve all sys-plugin files with any plugin data.
 *
 * HkmCode only includes sys-plugin files in the base sys-plugins directory (SystemPlugins).
 *
 * @return array[] Array of arrays of sys-plugin data, keyed by plugin file name. See `get_plugin_data()`.
 */
function hkm_get_sys_plugins() {
	// $cache_plugins = hkm_cache_get( 'plugins', 'plugins' );
	// if ( ! $cache_plugins ) {
	// 	$cache_plugins = array();
	// }

	// if ( isset( $cache_plugins[ $plugin_folder ] ) ) {
	// 	return $cache_plugins[ $plugin_folder ];
	// }

	$hkm_plugins  = array();
	$plugin_root = HKMMU_PLUGIN_DIR;
	if ( ! empty( $plugin_folder ) ) {
		$plugin_root .= $plugin_folder;
	}


	// Files in hkm-content/plugins directory.
	$plugins_dir  = @opendir( $plugin_root );
	$plugin_files = array();
	if ( $plugins_dir ) {
		while ( ( $file = readdir( $plugins_dir ) ) !== false ) {
			if ( '.' === substr( $file, 0, 1 ) ) {
				continue;
			}

			if ( is_dir( $plugin_root . '/' . $file ) ) {
				$plugins_subdir = @opendir( $plugin_root . '/' . $file );


				if ( $plugins_subdir ) {
					while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
						if ( '.' === substr( $subfile, 0, 1 ) ) {
							continue;
						}


						if ( '__hkm__.txt' === substr( $subfile, -11 ) ) {
							$plugin_files[] = "$file/$subfile";
						}
					}

					closedir( $plugins_subdir );
				}
			} 
		}

		closedir( $plugins_dir );
	}

	if ( empty( $plugin_files ) ) {
		return $hkm_plugins;
	}

	foreach ( $plugin_files as $plugin_file ) {
		if ( ! is_readable( "$plugin_root/$plugin_file" ) ) {
			continue;
		}

		// Do not apply markup/translate as it will be cached.
		$plugin_data = hkm_get_plugin_data( "$plugin_root/$plugin_file", false, false );

		if ( empty( $plugin_data['Name'] ) ) {
			continue;
		}

		$hkm_plugins[hkm_plugin_basename( $plugin_file ) ] = $plugin_data;
	}

	uasort( $hkm_plugins, '_sort_uname_callback' );

	// $cache_plugins[ $plugin_folder ] = $hkm_plugins;
	// hkm_cache_set( 'plugins', $cache_plugins, 'plugins' );

	return $hkm_plugins;

}

//
// Functions for handling plugins.
//

/**
 * Gets the basename of a plugin.
 *
 * This method extracts the name of a plugin from its filename.
 *
 * @since 1.5.0
 *
 * @global array $hkm_plugin_paths
 *
 * @param string $file The filename of plugin.
 * @return string The name of a plugin.
 */
function hkm_plugin_basename( $file ) {
	global $hkm_plugin_paths;

	// $hkm_plugin_paths contains normalized paths.
	$file = hkm_normalize_path( $file );

	arsort( $hkm_plugin_paths );

	foreach ( $hkm_plugin_paths as $dir => $realdir ) {
		if ( strpos( $file, $realdir ) === 0 ) {
			$file = $dir . substr( $file, strlen( $realdir ) );
		}
	}

	$plugin_dir    = hkm_normalize_path( HKM_PLUGIN_DIR );
	$mu_plugin_dir = hkm_normalize_path( HKMMU_PLUGIN_DIR );

	// Get relative path from plugins directory.
	$file = preg_replace( '#^' . preg_quote( $plugin_dir, '#' ) . '/|^' . preg_quote( $mu_plugin_dir, '#' ) . '/#', '', $file );
	$file = trim( $file, '/' );
	return $file;
}

/**
 * Register a plugin's real path.
 *
 * This is used in hkm_plugin_basename() to resolve symlinked paths.
 *
 *
 * @see hkm_normalize_path()
 *
 * @global array $hkm_plugin_paths
 *
 * @param string $file Known path to the file.
 * @return bool Whether the path was able to be registered.
 */
function hkm_register_plugin_realpath( $file ) {
	global $hkm_plugin_paths;
	// Normalize, but store as static to avoid recalculation of a constant value.
	static $hkm_plugin_path = null, $hkmmu_plugin_path = null;

	if ( ! isset( $hkm_plugin_path ) ) {
		$hkm_plugin_path   = hkm_normalize_path( HKM_PLUGIN_DIR );
		$hkmmu_plugin_path = hkm_normalize_path( HKMMU_PLUGIN_DIR );
	}

	$plugin_path     = hkm_normalize_path( dirname( $file ) );
	$plugin_realpath = hkm_normalize_path( dirname( realpath( $file ) ) );

	if ( $plugin_path === $hkm_plugin_path || $plugin_path === $hkmmu_plugin_path ) {
		return false;
	}

	if ( $plugin_path !== $plugin_realpath ) {
		$hkm_plugin_paths[ $plugin_path ] = $plugin_realpath;
	}

	return true;
}

/**
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the filesystem path of the directory that contains the plugin.
 */
function hkm_plugin_dir_path( $file ) {

	return trailingslashit( dirname( $file ) );
}


/**
 * Get the URL directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the URL path of the directory that contains the plugin.
 */
function hkm_plugin_dir_url( $file ) {
	return trailingslashit( plugins_url( '', $file ) );
}

/**
 * Set the activation hook for a plugin.
 *
 * When a plugin is activated, the action 'activate_PLUGINNAME' hook is
 * called. In the name of this hook, PLUGINNAME is replaced with the name
 * of the plugin, including the optional subdirectory. For example, when the
 * plugin is located in hkm-content/plugins/sampleplugin/sample.php, then
 * the name of this hook will become 'activate_sampleplugin/sample.php'.
 *
 * When the plugin consists of only one file and is (as by default) located at
 * hkm-content/plugins/sample.php the name of this hook will be
 * 'activate_sample.php'.
 *
 *
 * @param string   $file     The filename of the plugin including the path.
 * @param callable $callback The function hooked to the 'activate_PLUGIN' action.
 */
function hkm_register_activation_hook( $file, $callback ) {
	$file = hkm_plugin_basename( $file );
	hkm_add_action( 'activate_' . $file, $callback );
}


/**
 * Sets the deactivation hook for a plugin.
 *
 * When a plugin is deactivated, the action 'deactivate_PLUGINNAME' hook is
 * called. In the name of this hook, PLUGINNAME is replaced with the name
 * of the plugin, including the optional subdirectory. For example, when the
 * plugin is located in hkm-content/plugins/sampleplugin/sample.php, then
 * the name of this hook will become 'deactivate_sampleplugin/sample.php'.
 *
 * When the plugin consists of only one file and is (as by default) located at
 * hkm-content/plugins/sample.php the name of this hook will be
 * 'deactivate_sample.php'.
 *
 *
 * @param string   $file     The filename of the plugin including the path.
 * @param callable $callback The function hooked to the 'deactivate_PLUGIN' action.
 */
function hkm_register_deactivation_hook( $file, $callback ) {
	$file = hkm_plugin_basename( $file );
	hkm_add_action( 'deactivate_' . $file, $callback );
}

/**
 * Sets the uninstallation hook for a plugin.
 *
 * Registers the uninstall hook that will be called when the user clicks on the
 * uninstall link that calls for the plugin to uninstall itself. The link won't
 * be active unless the plugin hooks into the action.
 *
 * The plugin should not run arbitrary code outside of functions, when
 * registering the uninstall hook. In order to run using the hook, the plugin
 * will have to be included, which means that any code laying outside of a
 * function will be run during the uninstallation process. The plugin should not
 * hinder the uninstallation process.
 *
 * If the plugin can not be written without running code within the plugin, then
 * the plugin should create a file named 'uninstall.php' in the base plugin
 * folder. This file will be called, if it exists, during the uninstallation process
 * bypassing the uninstall hook. The plugin, when using the 'uninstall.php'
 * should always check for the 'HKM_UNINSTALL_PLUGIN' constant, before
 * executing.
 *
 *
 * @param string   $file     Plugin file.
 * @param callable $callback The callback to run when the hook is called. Must be
 *                           a static method or function.
 */
function hkm_register_uninstall_hook( $file, $callback ) {
	if ( is_array( $callback ) && is_object( $callback[0] ) ) {
		print 'Only a static class method or function can be used in an uninstall hook';
		// _hkm_doing_it_wrong( __FUNCTION__, __hkm__( 'Only a static class method or function can be used in an uninstall hook.' ), '3.1.0' );
		return;
	}

	/*
	 * The option should not be autoloaded, because it is not needed in most
	 * cases. Emphasis should be put on using the 'uninstall.php' way of
	 * uninstalling the plugin.
	 */
	$uninstallable_plugins = (array) get_option( 'uninstall_plugins' );
	$plugin_basename       = hkm_plugin_basename( $file );

	if ( ! isset( $uninstallable_plugins[ $plugin_basename ] ) || $uninstallable_plugins[ $plugin_basename ] !== $callback ) {
		$uninstallable_plugins[ $plugin_basename ] = $callback;
		hkm_update_option( 'uninstall_plugins', $uninstallable_plugins );
	}
}


/**
 * Callback to sort array by a 'Name' key.
 *
 *
 * @access private
 *
 * @param array $a array with 'Name' key.
 * @param array $b array with 'Name' key.
 * @return int Return 0 or 1 based on two string comparison.
 */
function _sort_uname_callback( $a, $b ) {
	return strnatcasecmp( $a['Name'], $b['Name'] );
}

/**
 * Retrieve an array of system plugin files.
 *
 * The default directory is Hkm_System/SystemPlugins. To change the default
 * directory manually, define `HKMMU_PLUGIN_DIR` and `HKMMU_PLUGIN_URL`
 * in hkm-config.php.
 *
 * @access private
 *
 * @return string[] Array of absolute paths of files to include.
 */
function hkm_get_active_and_valid_sys_plugins() {
	$plugins = hkm_active_plugins();
	$errors = []; 
	$mu_plugins = array();

	foreach ($plugins as $plugin) {
		$errV = false;
		$errR = false;
		$pass = true;
		$valid = hkm_validate_plugin( $plugin );
		if ( is_hkm_error( $valid ) ) {
            $errV = true;
			$errors[] = $valid;
		}

		$requirements = hkm_validate_plugin_requirements( $plugin ,'system');
		if ( is_hkm_error( $requirements ) ) {
            $errR = true;
			$errors[] = $requirements;
		}

		if ($errR || $errV) {
			$pass = false;
		}
		if ($pass) {

			if (is_file(HKMMU_PLUGIN_DIR . '/' . $plugin . '/'. $plugin. '.php')) {

				$mu_plugins[] = HKMMU_PLUGIN_DIR . '/' . $plugin . '/'. $plugin. '.php' ;
			}else{
				$errors[] =  new Hkm_Error( 'plugin_invalid_file',
				 sprintf(
					__( '%s file does not exist in plugin directory.' ),
					$plugin.'.php'
				));
			}
		}
		

	}
	
	if (!empty($errors)) return new Hkm_Error( 'plugins_invalid', __( 'One of the plugins is invalid.' ), $errors );
	// sort( $mu_plugins );

	return $mu_plugins;
}

/**
 * Retrieve an array of plugin files.
 *
 * The default directory is ROOTPATH/SystemPlugins. To change the default
 * directory manually, define `HKM_PLUGIN_DIR` and `HKM_PLUGIN_URL`
 * in hkm-config.php.
 *
 * @access private
 *
 * @return string[] Array of absolute paths of files to include.
 */
function hkm_get_active_and_valid_plugins() {
	global $engine;
	$mu_plugins = array();

	if ($engine!='.') {
		$plugins = hkm_active_plugins($engine);
		$errors = []; 

		foreach ($plugins as $plugin) {
			$errV = false;
			$errR = false;
			$pass = true;
			$valid = hkm_validate_plugin( $plugin );
			if ( is_hkm_error( $valid ) ) {
				$errV = true;
				$errors[] = $valid;
			}

			$requirements = hkm_validate_plugin_requirements( $plugin ,$engine);
			if ( is_hkm_error( $requirements ) ) {
				$errR = true;
				$errors[] = $requirements;
			}

			if ($errR || $errV) {
				$pass = false;
			}
			if ($pass) {
				if (is_file(HKM_PLUGIN_DIR . '/' . $plugin . '/'. $plugin. '.php')) {
					$mu_plugins[] = HKM_PLUGIN_DIR . '/' . $plugin . '/'. $plugin. '.php' ;
				}else{
					$errors[] =  new Hkm_Error( 'plugin_invalid_file',
					sprintf(
						__( '%s file does not exist in plugin directory.' ),
						$plugin.'.php'
					));
				}
			}
			

		}
		
		if (!empty($errors)) return new Hkm_Error( 'plugins_invalid', __( 'One of the plugins is invalid.' ), $errors );
		
	}
	sort( $mu_plugins );
	return $mu_plugins;
}