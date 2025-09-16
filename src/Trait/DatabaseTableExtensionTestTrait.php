<?php

namespace TestTraits\Trait;

use PDO;

/**
 * Extension to the DatabaseTableTestTrait.
 */
trait DatabaseTableExtensionTestTrait
{
    /**
     * Fetch rows by the given column.
     *
     * @param string $table Table name
     * @param string $whereColumn The column name of the select query
     * @param mixed $whereValue The value that will be searched for
     * @param string|array $selectClause Fields array or string after "SELECT" and before "FROM" like 'id, `name`'
     * WARNING: the column names passed as array or string must be escaped with ` if they match a reserved word
     * Example: ['`column_name`', '`column_name_2`'] or '`column_name`, `column_name_2`'
     *
     * @return array[] array or rows
     */
    protected function findTableRowsByColumn(
        string $table,
        string $whereColumn,
        mixed $whereValue,
        string|array $selectClause = '*',
    ): array {
        // Convert array to string if needed
        $selectClause = is_array($selectClause) ? implode(', ', $selectClause) : $selectClause;

        $sql = "SELECT $selectClause FROM `$table` WHERE `$whereColumn` = :whereValue";
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['whereValue' => $whereValue]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch rows by the given "where" array.
     *
     * @param string $table Table name
     * @param string $whereString
     * @param string|array $selectClause Fields array or string after "SELECT" and before "FROM" like 'id, `name`'
     * WARNING: the column names passed as array or string must be escaped with ` if they match a reserved word
     * Example: ['`column_name`', '`column_name_2`'] or '`column_name`, `column_name_2`'
     * @param string $joinString
     *
     * @return array[] array or rows
     */
    protected function findTableRowsWhere(
        string $table,
        string $whereString = 'true',
        string|array $selectClause = '*',
        string $joinString = '',
    ): array {
        // Convert array to string if needed
        $selectClause = is_array($selectClause) ? implode(', ', $selectClause) : $selectClause;

        $sql = "SELECT $selectClause FROM `$table` $joinString WHERE $whereString;";
        $statement = $this->createPreparedStatement($sql);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns the record with the highest id of the given table.
     *
     * @param string $table Table name
     *
     * @return array last inserted row
     */
    protected function findLastInsertedTableRow(string $table): array
    {
        $sql = "SELECT * FROM `$table` ORDER BY id DESC LIMIT 1";
        $statement = $this->createPreparedStatement($sql);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Asserts that result row of given table is the same as the given row.
     *
     * @param array $expectedRow Row expected to find
     * @param string $table Table to look into
     * @param string $whereColumn The column of the search query
     * @param mixed $whereValue The value that will be searched for
     * @param string|array|null $selectClause Fields array or string after "SELECT" and before "FROM" like 'id, `name`'
     * WARNING: the column names passed as array or string must be escaped with ` if they match a reserved word
     * Example: ['`column_name`', '`column_name_2`'] or '`column_name`, `column_name_2`'
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowsByColumn(
        array $expectedRow,
        string $table,
        string $whereColumn,
        mixed $whereValue,
        string|array|null $selectClause = null,
        string $message = '',
    ): void {
        $rows = $this->findTableRowsByColumn(
            $table,
            $whereColumn,
            $whereValue,
            $selectClause ?: array_map(fn ($key) => "`$key`", array_keys($expectedRow))
        );
        foreach ($rows as $row) {
            $this->assertSame(
                $expectedRow,
                $row,
                $message
            );
        }
    }
}
