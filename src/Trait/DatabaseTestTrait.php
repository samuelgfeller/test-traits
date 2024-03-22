<?php

namespace TestTraits\Trait;

trait DatabaseTestTrait
{
    use DatabaseConnectionTestTrait;
    use DatabaseSchemaTestTrait;
    use DatabaseTableTestTrait;
}
