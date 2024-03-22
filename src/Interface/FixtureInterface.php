<?php

namespace TestTraits\Interface;

/**
 * Fixture classes contain the properties $table and $records.
 *
 * @property string $table
 * @property array $records
 */
interface FixtureInterface
{
    // Attributes are public, but php doesn't support class properties in interfaces, so getters are needed
    public function getTable(): string;

    /**
     * @return array<array<string, mixed>>
     */
    public function getRecords(): array;
}
