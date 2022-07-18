<?php
namespace LanguageApiPlugin\Pomo;

use LanguageApiPlugin\Pomo\POMO_CachedFileReader;

/**
	 * Reads the contents of the file in the beginning.
	 */
	class POMO_CachedIntFileReader extends POMO_CachedFileReader {
		/**
		 * PHP5 constructor.
		 */
		public function __construct( $filename ) {
			parent::__construct( $filename );
		}

		/**
		 * PHP4 constructor.
		 *
		 * @deprecated 5.4.0 Use __construct() instead.
		 *
		 * @see POMO_CachedIntFileReader::__construct()
		 */
		public function POMO_CachedIntFileReader( $filename ) {
			// _deprecated_constructor( self::class, '5.4.0', static::class );
			self::__construct( $filename );
		}
	}
    ?>