<?php

namespace TestTraits\Trait;

use DomainException;
use PDO;

/**
 * Database test.
 */
trait DatabaseTableTestTrait
{
    /**
     * Asserts that a given table is the same as the given row.
     *
     * @param array $expectedRow Row expected to find
     * @param string $table Table to look into
     * @param int $id The primary key
     * @param string|array|null $selectClause Fields array or string after SELECT and before FROM like 'id, name'
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRow(
        array $expectedRow,
        string $table,
        int $id,
        string|array|null $selectClause = null,
        string $message = '',
    ): void {
        $this->assertSame(
            $expectedRow,
            $this->getTableRowById($table, $id, $selectClause ?: array_keys($expectedRow)),
            $message
        );
    }

    /**
     * Fetch row by ID.
     *
     * @param string $table Table name
     * @param int $id The primary key value
     * @param string|array $selectClause Fields string after SELECT and before FROM like 'id, name'
     *
     * @throws DomainException
     *
     * @return array Row
     */
    protected function getTableRowById(string $table, int $id, string|array $selectClause = '*'): array
    {
        // Convert the select clause array to string if needed
        $selectClause = is_array($selectClause) ? implode(', ', $selectClause) : $selectClause;

        $sql = "SELECT $selectClause FROM `$table` WHERE `id` = :id";
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($row)) {
            throw new DomainException("Row not found: $table.$id");
        }

        return $row;
    }

    /**
     * Asserts that a given table equals the given row.
     * Only the keys of the $expectedRow are used to compare.
     *
     * @param array $expectedRow Row expected to find
     * @param string $table Table to look into
     * @param int $id The primary key
     * @param string|array|null $selectClause Fields array or string after SELECT and before FROM like 'id, name'
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowEquals(
        array $expectedRow,
        string $table,
        int $id,
        string|array|null $selectClause = null,
        string $message = '',
    ): void {
        $this->assertEquals(
            $expectedRow,
            $this->getTableRowById($table, $id, $selectClause ?: array_keys($expectedRow)),
            $message
        );
    }

    /**
     * Asserts that a given table contains a given row value.
     *
     * @param mixed $expected The expected value
     * @param string $table Table to look into
     * @param int $id The primary key
     * @param string $field The column name
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowValue(
        mixed $expected,
        string $table,
        int $id,
        string $field,
        string $message = '',
    ): void {
        $actual = $this->getTableRowById($table, $id, $field)[$field];
        $this->assertSame($expected, $actual, $message);
    }

    /**
     * Asserts that a given table contains a given number of rows.
     *
     * @param int $expected The number of expected rows
     * @param string $table Table to look into
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowCount(int $expected, string $table, string $message = ''): void
    {
        $this->assertSame($expected, $this->getTableRowCount($table), $message);
    }

    /**
     * Get table row count.
     *
     * @param string $table The table name
     *
     * @return int The number of rows
     */
    protected function getTableRowCount(string $table): int
    {
        $sql = "SELECT COUNT(*) AS amount FROM `$table`;";
        $statement = $this->createQueryStatement($sql);
        $row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

        return (int)($row['amount'] ?? 0);
    }

    /**
     * Asserts that a given table contains a given number of rows.
     *
     * @param string $table Table to look into
     * @param int $id The id
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowExists(string $table, int $id, string $message = ''): void
    {
        $this->assertTrue((bool)$this->findTableRowById($table, $id), $message);
    }

    /**
     * Fetch row by ID.
     *
     * @param string $table Table name
     * @param int $id The primary key value
     *
     * @return array Row
     */
    protected function findTableRowById(string $table, int $id): array
    {
        $sql = "SELECT * FROM `$table` WHERE `id` = :id";
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['id' => $id]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Asserts that a given table contains a given number of rows.
     *
     * @param string $table Table to look into
     * @param int $id The id
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowNotExists(string $table, int $id, string $message = ''): void
    {
        $this->assertFalse((bool)$this->findTableRowById($table, $id), $message);
    }
}
