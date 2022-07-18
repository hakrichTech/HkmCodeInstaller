<?php

function hkm_die( $message = '', $title = '', $args = array() ) {
	global $hkm_query;

	if ( is_int( $args ) ) {
		$args = array( 'response' => $args );
	} elseif ( is_int( $title ) ) {
		$args  = array( 'response' => $title );
		$title = '';
	}

	if ( hkm_doing_ajax() ) {
		
		$function = hkm_apply_runtime_filters( 'hkm_die_ajax_handler', '_ajax_hkm_die_handler' );

	} elseif ( hkm_is_json_request() ) {
		
		$function = hkm_apply_runtime_filters( 'hkm_die_json_handler', '_json_hkm_die_handler' );

	} elseif ( hkm_is_jsonp_request() ) {

		$function = hkm_apply_runtime_filters( 'hkm_die_jsonp_handler', '_jsonp_hkm_die_handler' );

	} elseif ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {

		$function = hkm_apply_runtime_filters( 'hkm_die_xmlrpc_handler', '_xmlrpc_hkm_die_handler' );

	} elseif ( hkm_is_xml_request()
		|| isset( $hkm_query ) &&
			( function_exists( 'is_feed' ) && is_feed()
			|| function_exists( 'is_comment_feed' ) && is_comment_feed()
			|| function_exists( 'is_trackback' ) && is_trackback() ) ) {
		
		$function = hkm_apply_runtime_filters( 'hkm_die_xml_handler', '_xml_hkm_die_handler' );
	} else {
		
		$function = hkm_apply_runtime_filters( 'hkm_die_handler', '_default_hkm_die_handler' );
	}

	call_user_func( $function, $message, $title, $args );
}



function _ajax_hkm_die_handler( $message, $title = '', $args = array() ) {
	// Set default 'response' to 200 for Ajax requests.
	$args = hkm_parse_args(
		$args,
		array( 'response' => 200 )
	);

	list( $message, $title, $parsed_args ) = _hkm_die_process_input( $message, $title, $args );

	if ( ! headers_sent() ) {
		// This is intentional. For backward-compatibility, support passing null here.
		if ( null !== $args['response'] ) {
			status_header( $parsed_args['response'] );
		}
		nocache_headers();
	}

	if ( is_scalar( $message ) ) {
		$message = (string) $message;
	} else {
		$message = '0';
	}

	if ( $parsed_args['exit'] ) {
		die( $message );
	}

	echo $message;
}


function _json_hkm_die_handler( $message, $title = '', $args = array() ) {
	list( $message, $title, $parsed_args ) = _hkm_die_process_input( $message, $title, $args );

	$data = array(
		'code'              => $parsed_args['code'],
		'message'           => $message,
		'data'              => array(
			'status' => $parsed_args['response'],
		),
		'additional_errors' => $parsed_args['additional_errors'],
	);

	if ( ! headers_sent() ) {
		header( "Content-Type: application/json; charset={$parsed_args['charset']}" );
		if ( null !== $parsed_args['response'] ) {
			status_header( $parsed_args['response'] );
		}
		nocache_headers();
	}

	echo json_encode( $data );
	if ( $parsed_args['exit'] ) {
		die();
	}
}


function _jsonp_hkm_die_handler( $message, $title = '', $args = array() ) {
	list( $message, $title, $parsed_args ) = _hkm_die_process_input( $message, $title, $args );

	$data = array(
		'code'              => $parsed_args['code'],
		'message'           => $message,
		'data'              => array(
			'status' => $parsed_args['response'],
		),
		'additional_errors' => $parsed_args['additional_errors'],
	);

	if ( ! headers_sent() ) {
		header( "Content-Type: application/javascript; charset={$parsed_args['charset']}" );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Robots-Tag: noindex' );
		if ( null !== $parsed_args['response'] ) {
			status_header( $parsed_args['response'] );
		}
		nocache_headers();
	}

	$result         = hkm_json_encode( $data );
	$jsonp_callback = $_GET['_jsonp'];
	echo '/**/' . $jsonp_callback . '(' . $result . ')';
	if ( $parsed_args['exit'] ) {
		die();
	}
}

function _xmlrpc_hkm_die_handler( $message, $title = '', $args = array() ) {
	global $wp_xmlrpc_server;
  
	list( $message, $title, $parsed_args ) = _hkm_die_process_input( $message, $title, $args );

	if ( ! headers_sent() ) {
		nocache_headers();
	}

	if ( $wp_xmlrpc_server ) {
		$error = new IXR_Error( $parsed_args['response'], $message );
		$wp_xmlrpc_server->output( $error->getXml() );
	}
	if ( $parsed_args['exit'] ) {
		die();
	}
}

function _default_hkm_die_handler( $message, $title = '', $args = array() ) {
	list( $message, $title, $parsed_args ) = _hkm_die_process_input( $message, $title, $args );

	if ( is_string( $message ) ) {
		if ( ! empty( $parsed_args['additional_errors'] ) ) {
			$message = array_merge(
				array( $message ),
				hkm_list_pluck( $parsed_args['additional_errors'], 'message' )
			);
			$message = "<ul>\n\t\t<li>" . implode( "</li>\n\t\t<li>", $message ) . "</li>\n\t</ul>";
		}

		$message = sprintf(
			'<div class="wp-die-message">%s</div>',
			$message
		);
	}

	if ( ! empty( $parsed_args['link_url'] ) && ! empty( $parsed_args['link_text'] ) ) {
		$link_url = $parsed_args['link_url'];
		if ( function_exists( 'esc_url' ) ) {
			$link_url = esc_url( $link_url );
		}
		$link_text = $parsed_args['link_text'];
		$message  .= "\n<p><a href='{$link_url}'>{$link_text}</a></p>";
	}

	if ( isset( $parsed_args['back_link'] ) && $parsed_args['back_link'] ) {
		$back_text = '&laquo; Back';
		$message  .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
	}

	if ( ! hkm_did_action( 'admin_head' ) ) :
		if ( ! headers_sent() ) {
			header( "Content-Type: text/html; charset={$parsed_args['charset']}" );
			status_header( $parsed_args['response'] );
			nocache_headers();
		}

		$text_direction = $parsed_args['text_direction'];
		$dir_attr       = "dir='$text_direction'";

		// If `text_direction` was not explicitly passed,
		// use get_language_attributes() if available.
		if ( empty( $args['text_direction'] )
			&& function_exists( 'language_attributes' ) && function_exists( 'is_rtl' )
		) {
			// $dir_attr = get_language_attributes();
			$dir_attr = "";
		}
		?>
<!DOCTYPE html>
<html <?php echo $dir_attr; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $parsed_args['charset']; ?>" />
	<meta name="viewport" content="width=device-width">
		<?php
		if ( function_exists( 'hkm_robots' ) && function_exists( 'hkm_robots_no_robots' ) && function_exists( 'hkm_add_runtime_filter' ) ) {
			hkm_add_runtime_filter( 'hkm_robots', 'hkm_robots_no_robots' );
			// hkm_robots();
		}
		?>
	<title><?php echo $title; ?></title>
	<style type="text/css">
		html {
			background: #f1f1f1;
		}
		body {
			background: #fff;
			border: 1px solid #ccd0d4;
			color: #444;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			margin: 2em auto;
			padding: 1em 2em;
			max-width: 700px;
			-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
			box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
		}
		h1 {
			border-bottom: 1px solid #dadada;
			clear: both;
			color: #666;
			font-size: 24px;
			margin: 30px 0 0 0;
			padding: 0;
			padding-bottom: 7px;
		}
		#error-page {
			margin-top: 50px;
		}
		#error-page p,
		#error-page .wp-die-message {
			font-size: 14px;
			line-height: 1.5;
			margin: 25px 0 20px;
		}
		#error-page code {
			font-family: Consolas, Monaco, monospace;
		}
		ul li {
			margin-bottom: 10px;
			font-size: 14px ;
		}
		a {
			color: #0073aa;
		}
		a:hover,
		a:active {
			color: #006799;
		}
		a:focus {
			color: #124964;
			-webkit-box-shadow:
				0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, 0.8);
			box-shadow:
				0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, 0.8);
			outline: none;
		} 
		.button {
			background: #f3f5f6;
			border: 1px solid #016087;
			color: #016087;
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 2;
			height: 28px;
			margin: 0;
			padding: 0 10px 1px;
			cursor: pointer;
			-webkit-border-radius: 3px;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			-webkit-box-sizing: border-box;
			-moz-box-sizing:    border-box;
			box-sizing:         border-box;

			vertical-align: top;
		}

		.button.button-large {
			line-height: 2.30769231;
			min-height: 32px;
			padding: 0 12px;
		}

		.button:hover,
		.button:focus {
			background: #f1f1f1;
		}

		.button:focus {
			background: #f3f5f6;
			border-color: #007cba;
			-webkit-box-shadow: 0 0 0 1px #007cba;
			box-shadow: 0 0 0 1px #007cba;
			color: #016087;
			outline: 2px solid transparent;
			outline-offset: 0;
		}

		.button:active {
			background: #f3f5f6;
			border-color: #7e8993;
			-webkit-box-shadow: none;
			box-shadow: none;
		}

		<?php
		if ( 'rtl' === $text_direction ) {
			echo 'body { font-family: Tahoma, Arial; }';
		}
		?>
	</style>
</head>
<body id="error-page">
<?php endif; // ! did_action( 'admin_head' ) ?>
	<?php echo $message; ?>
</body>
</html>
	<?php
	if ( $parsed_args['exit'] ) {
		die();
	}
}

function _xml_hkm_die_handler( $message, $title = '', $args = array() ) {
	list( $message, $title, $parsed_args ) = _hkm_die_process_input( $message, $title, $args );

	$message = htmlspecialchars( $message );
	$title   = htmlspecialchars( $title );

	$xml = <<<EOD
<error>
    <code>{$parsed_args['code']}</code>
    <title><![CDATA[{$title}]]></title>
    <message><![CDATA[{$message}]]></message>
    <data>
        <status>{$parsed_args['response']}</status>
    </data>
</error>

EOD;

	if ( ! headers_sent() ) {
		header( "Content-Type: text/xml; charset={$parsed_args['charset']}" );
		if ( null !== $parsed_args['response'] ) {
			status_header( $parsed_args['response'] );
		}
		nocache_headers();
	}

	echo $xml;
	if ( $parsed_args['exit'] ) {
		die();
	}
}


function _scalar_hkm_die_handler( $message = '', $title = '', $args = array() ) {
	list( $message, $title, $parsed_args ) = _hkm_die_process_input( $message, $title, $args );

	if ( $parsed_args['exit'] ) {
		if ( is_scalar( $message ) ) {
			die( (string) $message );
		}
		die();
	}

	if ( is_scalar( $message ) ) {
		echo (string) $message;
	}
}

function _canonical_charset( $charset ) {
	if ( 'utf-8' === strtolower( $charset ) || 'utf8' === strtolower( $charset ) ) {

		return 'UTF-8';
	}

	if ( 'iso-8859-1' === strtolower( $charset ) || 'iso8859-1' === strtolower( $charset ) ) {

		return 'ISO-8859-1';
	}

	return $charset;
}

function _hkm_die_process_input( $message, $title = '', $args = array() ) {
	$defaults = array(
		'response'          => 0,
		'code'              => '',
		'exit'              => true,
		'back_link'         => false,
		'link_url'          => '',
		'link_text'         => '',
		'text_direction'    => '',
		'charset'           => 'utf-8',
		'additional_errors' => array(),
	);

	$args = hkm_parse_args( $args, $defaults );

	if ( function_exists( 'is_hkm_error' ) && is_hkm_error( $message ) ) {
		if ( ! empty( $message->errors ) ) {
			$errors = array();
			foreach ( (array) $message->errors as $error_code => $error_messages ) {
				foreach ( (array) $error_messages as $error_message ) {
					$errors[] = array(
						'code'    => $error_code,
						'message' => $error_message,
						'data'    => $message->get_error_data( $error_code ),
					);
				}
			}

			$message = $errors[0]['message'];
			if ( empty( $args['code'] ) ) {
				$args['code'] = $errors[0]['code'];
			}
			if ( empty( $args['response'] ) && is_array( $errors[0]['data'] ) && ! empty( $errors[0]['data']['status'] ) ) {
				$args['response'] = $errors[0]['data']['status'];
			}
			if ( empty( $title ) && is_array( $errors[0]['data'] ) && ! empty( $errors[0]['data']['title'] ) ) {
				$title = $errors[0]['data']['title'];
			}

			unset( $errors[0] );
			$args['additional_errors'] = array_values( $errors );
		} else {
			$message = '';
		}
	}


	// The $title and these specific $args must always have a non-empty value.
	if ( empty( $args['code'] ) ) {
		$args['code'] = 'hkm_die';
	}
	if ( empty( $args['response'] ) ) {
		$args['response'] = 500;
	}
	if ( empty( $title ) ) {
		$title = 'WordPress &rsaquo; Error';
	}
	if ( empty( $args['text_direction'] ) || ! in_array( $args['text_direction'], array( 'ltr', 'rtl' ), true ) ) {
		$args['text_direction'] = 'ltr';
		// if ( function_exists( 'is_rtl' ) && is_rtl() ) {
		// 	$args['text_direction'] = 'rtl';
		// }
	}

	if ( ! empty( $args['charset'] ) ) {
		$args['charset'] = _canonical_charset( $args['charset'] );
	}

	return array( $message, $title, $args );
}


