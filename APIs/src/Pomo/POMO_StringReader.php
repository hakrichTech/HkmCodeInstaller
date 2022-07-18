<?php
namespace LanguageApiPlugin\Pomo;

use LanguageApiPlugin\POMO_Reader;

   /**
	 * Provides file-like methods for manipulating a string instead
	 * of a physical file.
	 */
	class POMO_StringReader extends POMO_Reader {

		public $_str = '';

		/**
		 * PHP5 constructor.
		 */
		public function __construct( $str = '' ) {
			parent::__construct();
			$this->_str = $str;
			$this->_pos = 0;
		}

		/**
		 * PHP4 constructor.
		 *
		 * @deprecated 5.4.0 Use __construct() instead.
		 *
		 * @see POMO_StringReader::__construct()
		 */
		public function POMO_StringReader( $str = '' ) {
			// _deprecated_constructor( self::class, '5.4.0', static::class );
			self::__construct( $str );
		}

		/**
		 * @param string $bytes
		 * @return string
		 */
		public function read( $bytes ) {
			$data        = $this->substr( $this->_str, $this->_pos, $bytes );
			$this->_pos += $bytes;
			if ( $this->strlen( $this->_str ) < $this->_pos ) {
				$this->_pos = $this->strlen( $this->_str );
			}
			return $data;
		}

		/**
		 * @param int $pos
		 * @return int
		 */
		public function seekto( $pos ) {
			$this->_pos = $pos;
			if ( $this->strlen( $this->_str ) < $this->_pos ) {
				$this->_pos = $this->strlen( $this->_str );
			}
			return $this->_pos;
		}

		/**
		 * @return int
		 */
		public function length() {
			return $this->strlen( $this->_str );
		}

		/**
		 * @return string
		 */
		public function read_all() {
			return $this->substr( $this->_str, $this->_pos, $this->strlen( $this->_str ) );
		}

	}

    ?>