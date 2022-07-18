<?php
namespace LanguageApiPlugin\Pomo;

use LanguageApiPlugin\POMO_Reader;


class POMO_FileReader extends POMO_Reader {

    /**
     * @param string $filename
     */
    public function __construct( $filename ) {
        parent::__construct();
        $this->_f = fopen( $filename, 'rb' );
    }

    /**
     * PHP4 constructor.
     *
     * @deprecated 5.4.0 Use __construct() instead.
     *
     * @see POMO_FileReader::__construct()
     */
    public function POMO_FileReader( $filename ) {
        // _deprecated_constructor( self::class, '5.4.0', static::class );
        self::__construct( $filename );
    }

    /**
     * @param int $bytes
     * @return string|false Returns read string, otherwise false.
     */
    public function read( $bytes ) {
        return fread( $this->_f, $bytes );
    }

    /**
     * @param int $pos
     * @return bool
     */
    public function seekto( $pos ) {
        if ( -1 == fseek( $this->_f, $pos, SEEK_SET ) ) {
            return false;
        }
        $this->_pos = $pos;
        return true;
    }

    /**
     * @return bool
     */
    public function is_resource() {
        return is_resource( $this->_f );
    }

    /**
     * @return bool
     */
    public function feof() {
        return feof( $this->_f );
    }

    /**
     * @return bool
     */
    public function close() {
        return fclose( $this->_f );
    }

    /**
     * @return string
     */
    public function read_all() {
        $all = '';
        while ( ! $this->feof() ) {
            $all .= $this->read( 4096 );
        }
        return $all;
    }
}

?>