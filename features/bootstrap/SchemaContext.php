<?php

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

class SchemaContext implements \Behat\Symfony2Extension\Context\KernelAwareContext
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var array
     */
    private $schema;

    /**
     * Sets Kernel instance.
     *
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When /^I query the schema$/
     */
    public function iQueryTheSchema()
    {
        $query = <<< GQL
query IntrospectionQuery {
  __schema {
    queryType { name }
    types {
      name
      fields {
        name
        type { name }
      }
    }
  }
}
GQL;
        $request = Request::create('/graphql', 'POST', ['query' => $query]);
        $response = $this->kernel->handle($request);
        $this->schema = json_decode($response->getContent(), true)['data']['__schema'];
    }

    /**
     * @Then /^the query type is set to "([^"]*)"$/
     */
    public function theQueryTypeIsSetTo($queryTypeName)
    {
        Assert::assertEquals($queryTypeName, $this->schema['queryType']['name']);
    }

    /**
     * @Given /^"([^"]*)" has the following fields:$/
     */
    public function hasTheFollowingFields($queryType, TableNode $fieldsWithType)
    {
        $type = $this->getTypeFromSchema($queryType);

        foreach($fieldsWithType as $row) {
            $this->assertTypeHasFieldWithType($type, $row['field'], $row['type']);
        }
    }

    private function assertTypeHasFieldWithType($type, $fieldName, $fieldType)
    {
        $hasField = false;
        $hasType = false;
        $foundType = null;

        foreach ($type['fields'] as $field) {
            if ($field['name'] === $fieldName) {
                $hasField = true;
                if ($field['type']['name'] === $fieldType) {
                    $hasType = true;
                    break;
                } else {
                    $foundType = $field['type']['name'];
                }
            }
        }

        Assert::assertTrue($hasField, $type['name'] . " does not have a field named $fieldName");
        Assert::assertTrue($hasType, $type['name'] . ".$fieldType is not of type $fieldType (found " . $foundType . ")");
    }

    private function getTypeFromSchema($typeName)
    {
        $domainType = null;
        foreach ($this->schema['types'] as $type) {
            if ($type['name'] === $typeName) {
                $domainType = $type;
            }
        }

        if (null === $domainType) {
            throw new Exception("Type $type wasn't found in the schema");
        }

        return $domainType;
    }
}