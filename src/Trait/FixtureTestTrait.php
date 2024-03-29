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
     * @param array $attributes Attributes to override in the fixture.
     * ['field_name' => 'expected_value', 'other_field_name' => 'other expected value', ] -> one insert
     * [['field_name' => 'value', 'other_field_name',], ['field_name' => 'value',], ] -> two insets
     *
     * @return array inserted row values
     */
    protected function insertFixture(FixtureInterface $fixture, array $attributes = []): array
    {
        $attributesIsMultidimensional = true;

        // Check if attributes is an array with multiple sets of attributes or only one array of attributes
        if (count($attributes) === count($attributes, COUNT_RECURSIVE)) {
            // Put $attributes in an additional array
            $attributes = [$attributes];
            $attributesIsMultidimensional = false;
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
        if ($attributesIsMultidimensional === false) {
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