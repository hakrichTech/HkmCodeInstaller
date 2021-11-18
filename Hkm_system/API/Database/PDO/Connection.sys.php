<?php


namespace Hkm_code\Database\PDO;
use PDO;
use stdClass;
use Throwable;
use LogicException;
use Hkm_code\Database\BaseConnection;
use Hkm_code\Database\Exceptions\DatabaseException;

class Connection extends BaseConnection
{
	public static $DBDriver = 'PDO';
    /**
	 * Identifier escape character
	 *
	 * @var string
	 */
	public static $escapeChar = '`';
	public static $pdo;

    public function __construct(array $params){

		parent::__construct($params);
        
	}

    public static function CONNECT(bool $persistent = false)
    {
        if (self::$hostname[0] === '/')
		{
			$hostname = null;
			$port     = null;
			$socket   = self::$hostname;
		}
		else
		{
			$hostname = ($persistent === true) ? 'p:' . self::$hostname : self::$hostname;
			$port     = empty(self::$port) ? null : self::$port;
			$socket   = '';
		}
        print "connect";exit;
    }
    public static function EXECUTE(string $sql ){

    }

    public static function ERROR(): array
    {
        
    }

    public static function LIST_TABLES(bool $constrainByPrefix = false)
    {
        
    }

    public static function PERSISTENT_CONNECT()
    {
        
    }

    public static function SET_DATABASE(string $databaseName)
    {
        
    }
    public static function GET_CONNECT_DURATION(int $decimals = 6): string
    {
        
    }
    public static function GET_CONNECT_START(): ?float
    {
        
    }

    public static function GET_CONNECTION(?string $alias = null)
    {
        
    }
    public static function GET_DATABASE(): string
    {
        
    }

    public static function GET_FIELD_DATA(string $table)
    {
        
    }

    public static function GET_FIELD_NAMES(string $table)
    {
        
    }

    public static function GET_FOREIGN_KEY_DATA(string $table)
    {
        
    }

    public static function GET_INDEX_DATA(string $table)
    {
        
    }
    public static function GET_LAST_QUERY()
    {
        
    }
    public static function GET_PLATFORM(): string
    {
        
    }
    public static function GET_PREFIX(): string
    {
        
    }

    public static function GET_VERSION(): string
    {
        
    }

    public static function SET_ALIASED_TABLES(array $aliases)
    {
        
    }

    public static function SET_PREFIX(string $prefix = ''): string
    {
        
    }

    public static function IS_WRITE_TYPE($sql): bool
    {
        
    }

    public static function ADD_TABLE_ALIAS(string $table)
    {
        
    }
    public static function DISABLED_FOREIGN_KEY_CHECKS()
    {
        
    }
    public static function AFFECTED_ROWS(): int
    {
        
    }
    public static function PROTECT_INDENTIFIERS($item, bool $prefixSingle = false, ?bool $protectIdentifiers = null, bool $fieldExists = true)
    {
        
    }

    public static function SIMPLE_QUERY(string $sql)
    {
        
    }
    public static function PREFIX_TABLE(string $table = ''): string
    {
        
    }
    public static function INSERT_ID()
    {
        
    }
    public static function ESCAPE_STRING($str, bool $like = false)
    {
        
    }

    public static function ESCAPE_LIKE_SRING($str)
    {
        
    }
    public static function ESCAPE_IDENTIFIERS($item)
    {
        
    }

    public static function ENABLE_FOREIGN_KEY_CHECKS()
    {
        
    }

    public static function TRANS_STRICT(bool $mode = true)
    {
        
    }

    public static function TRANS_STATUS(): bool
    {
        
    }
    public static function TRANS_START(bool $testMode = false): bool
    {
        
    }
    public static function TRANS_ROLLBACK(): bool
    {
        
    }
    public static function TRANS_OFF()
    {
        
    }
    public static function TRANS_COMPLETE(): bool
    {
        
    }

    public static function TRANS_COMMIT(): bool
    {
        
    }
    public static function TABLE_EXISTS(string $tableName): bool
    {
        
    }

    public static function TRANS_BEGIN(bool $testMode = false): bool
    {
        
    }
    public static function RESET_DATA_CACHE()
    {
        
    }

    public static function FIELD_EXISTS(string $fieldName, string $tableName): bool
    {
        
    }
    public static function SHOW_LAST_QUERY(): string
    {
        
    }
    public static function CALL_FUNCTION(string $functionName, ...$params): bool
    {
        
    }

    public static function _TRANS_BEGIN():bool
    {

    }
    public static function _TRANS_ROLLBACK():bool
    {

    }
    public static function _CLOSE(){

    }
    public static function RECONNECT()
    {
        
    }
    public static function _TRANS_COMMIT():bool
    {

    }
    
    public static function _LIST_TABLES(bool $constrainByPrefix = false)
    {

    }
    public static function _LIST_COLUMNS(string $table = '')
    {

    }

    public static function _INDEX_DATA(string $table):array
    {

    }
    public static function _FIELD_DATA(string $table):array
    {

    }

    public static function _FOREIGN_KEY_DATA(string $table):array
    {

    }
}