<?php

namespace TestTraits\Trait;

use TestTraits\Interface\FixtureInterface;

/**
 * Fixture Test Trait.
 */
trait FixtureTestTrait
{
    use DatabaseTestTrait;

    /**
     * Inserts fixtures, with given attributes or sets of attributes and returns rows with id.
     *
     * @param FixtureInterface $fixture The fixture instance
     * @param array ...$attributes Values to override in the fixture. With the argument unpacking operator
     * multiple arrays of attributes can be provided in which case multiple inserts are made.
     * One insert: ['name' => 'Bob']
     * Three inserts: ['name' => 'Frank'], ['name' => 'Alice'], ['name' => 'Eve', 'role' => 'admin']
     * Also allowed is one attribute that contains the sets for multiple inserts:
     * Two inserts: [['name' => 'Frank'], ['name' => 'Alice']]
     *
     * @return array inserted row values
     */
    protected function insertFixture(FixtureInterface $fixture, array ...$attributes): array
    {
        // If the first element of the first $attributes element is an array, then it's a 2-dimensional array
        // that contains multiple sets of attributes.
        if (isset($attributes[0][0]) && is_array($attributes[0][0])) {
            // Make the sets of attributes provided direct descendants of the $attributes array
            $attributes = $attributes[0];
        }

        // If $attributes is empty, assign it a default value of one empty array
        if (empty($attributes)) {
            $attributes = [[]];
        }

        $recordsCollection = [];
        foreach ($attributes as $attributesForOneRow) {
            // Get row with given attributes
            $row = $this->getFixtureRowWithCustomAttributes($attributesForOneRow, $fixture);
            // Insert fixture and get id
            $row['id'] = (int)$this->insertFixtureRow($fixture->getTable(), $row);
            $recordsCollection[] = $row;
        }

        // If only one row was inserted, return the row values
        if (count($attributes) === 1) {
            return $recordsCollection[0];
        }

        // Return array of inserted row values
        return $recordsCollection;
    }

    /**
     * Returns fixtures with given attributes and returns row values with id.
     *
     * @param array $attributes
     * @param FixtureInterface $fixture
     *
     * @return array
     */
    private function getFixtureRowWithCustomAttributes(
        array $attributes,
        FixtureInterface $fixture,
    ): array {
        $row = $fixture->getRecords()[0];

        // Unset id to prevent duplicate entries when id is not provided in the attributes and multiple inserts are made
        unset($row['id']);

        // Add given attributes to row
        foreach ($attributes as $colum => $value) {
            // Set value to given attribute value
            $row[$colum] = $value;
        }

        return $row;
    }
}
