<?php
namespace LanguageApiPlugin\Pomo;

use LanguageApiPlugin\Pomo\POMO_StringReader;

 /**
	 * Reads the contents of the file in the beginning.
	 */
	class POMO_CachedFileReader extends POMO_StringReader {
		/**
		 * PHP5 constructor.
		 */
		public function __construct( $filename ) {
			parent::__construct();
			$this->_str = file_get_contents( $filename );
			if ( false === $this->_str ) {
				return false;
			}
			$this->_pos = 0;
		}

		/**
		 * PHP4 constructor.
		 *
		 * @deprecated 5.4.0 Use __construct() instead.
		 *
		 * @see POMO_CachedFileReader::__construct()
		 */
		public function POMO_CachedFileReader( $filename ) {
			// _deprecated_constructor( self::class, '5.4.0', static::class );
			self::__construct( $filename );
		}
	}
    ?>