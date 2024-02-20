<?php

namespace Kejedi\Lucid;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

/**
 * @method ColumnDefinition id($column = 'id')
 * @method ColumnDefinition increments($column)
 * @method ColumnDefinition integerIncrements($column)
 * @method ColumnDefinition tinyIncrements($column)
 * @method ColumnDefinition smallIncrements($column)
 * @method ColumnDefinition mediumIncrements($column)
 * @method ColumnDefinition bigIncrements($column)
 * @method ColumnDefinition char($column, $length = null)
 * @method ColumnDefinition string($column, $length = null)
 * @method ColumnDefinition tinyText($column)
 * @method ColumnDefinition text($column)
 * @method ColumnDefinition mediumText($column)
 * @method ColumnDefinition longText($column)
 * @method ColumnDefinition integer($column, $autoIncrement = false, $unsigned = false)
 * @method ColumnDefinition tinyInteger($column, $autoIncrement = false, $unsigned = false)
 * @method ColumnDefinition smallInteger($column, $autoIncrement = false, $unsigned = false)
 * @method ColumnDefinition mediumInteger($column, $autoIncrement = false, $unsigned = false)
 * @method ColumnDefinition bigInteger($column, $autoIncrement = false, $unsigned = false)
 * @method ColumnDefinition unsignedInteger($column, $autoIncrement = false)
 * @method ColumnDefinition unsignedTinyInteger($column, $autoIncrement = false)
 * @method ColumnDefinition unsignedSmallInteger($column, $autoIncrement = false)
 * @method ColumnDefinition unsignedMediumInteger($column, $autoIncrement = false)
 * @method ColumnDefinition unsignedBigInteger($column, $autoIncrement = false)
 * @method ColumnDefinition float($column, $total = 8, $places = 2, $unsigned = false)
 * @method ColumnDefinition double($column, $total = null, $places = null, $unsigned = false)
 * @method ColumnDefinition decimal($column, $total = 8, $places = 2, $unsigned = false)
 * @method ColumnDefinition unsignedFloat($column, $total = 8, $places = 2)
 * @method ColumnDefinition unsignedDouble($column, $total = null, $places = null)
 * @method ColumnDefinition unsignedDecimal($column, $total = 8, $places = 2)
 * @method ColumnDefinition boolean($column)
 * @method ColumnDefinition enum($column, array $allowed)
 * @method ColumnDefinition set($column, array $allowed)
 * @method ColumnDefinition json($column)
 * @method ColumnDefinition jsonb($column)
 * @method ColumnDefinition date($column)
 * @method ColumnDefinition dateTime($column, $precision = 0)
 * @method ColumnDefinition dateTimeTz($column, $precision = 0)
 * @method ColumnDefinition time($column, $precision = 0)
 * @method ColumnDefinition timeTz($column, $precision = 0)
 * @method ColumnDefinition timestamp($column, $precision = 0)
 * @method ColumnDefinition timestampTz($column, $precision = 0)
 * @method ColumnDefinition timestamps($precision = 0)
 * @method ColumnDefinition softDeletes($column = 'deleted_at', $precision = 0)
 * @method ColumnDefinition softDeletesTz($column = 'deleted_at', $precision = 0)
 * @method ColumnDefinition softDeletesDatetime($column = 'deleted_at', $precision = 0)
 * @method ColumnDefinition year($column)
 * @method ColumnDefinition binary($column)
 * @method ColumnDefinition uuid($column = 'uuid')
 * @method ColumnDefinition ulid($column = 'ulid', $length = 26)
 * @method ColumnDefinition ipAddress($column = 'ip_address')
 * @method ColumnDefinition macAddress($column = 'mac_address')
 * @method ColumnDefinition geometry($column)
 * @method ColumnDefinition point($column, $srid = null)
 * @method ColumnDefinition lineString($column)
 * @method ColumnDefinition polygon($column)
 * @method ColumnDefinition geometryCollection($column)
 * @method ColumnDefinition multiPoint($column)
 * @method ColumnDefinition multiLineString($column)
 * @method ColumnDefinition multiPolygon($column)
 * @method ColumnDefinition multiPolygonZ($column)
 * @method ColumnDefinition computed($column, $expression)
 * @method ColumnDefinition rememberToken()
 */
class Table
{
    public Blueprint $table;

    public function __construct(Blueprint $table)
    {
        $this->table = $table;
    }

    public function __call($name, $arguments)
    {
        $schema = $this->table->$name(...$arguments);

        $attributes = $schema->getAttributes();

        if ($attributes['type'] == 'boolean') {
            $schema->default(false);
        } else if (
            empty($attributes['autoIncrement']) &&
            empty($attributes['primary'])
        ) {
            $schema->nullable();
        }

        return $schema;
    }
}
