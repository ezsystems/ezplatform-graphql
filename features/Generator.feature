Feature: Schema generation
  In order to use GraphQL
  As an application maintainer
  I need to generate the schema

Scenario: An application maintainer generates the schema
 Given the schema has not been generated
  When I run the command "ezplatform:graphql:generate-schema"
  When I clear the cache
  Then the schema files are generated in "app/config/graphql/ezplatform"
   And the GraphQL extension is configured to use that schema
