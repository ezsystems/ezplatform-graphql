Feature: Schema generation
  In order to use my content over GraphQL
  As an API consumer
  I need to expose my content structure as a graph

Scenario: Content type groups are exposed at the root of the schema
  Given there is a Content Type Group with identifier content
    And there is a Content Type Group with identifier media
   When I query the schema
   Then the query type is set to "Domain"
    And "Domain" has the following fields:
    | field   | type               |
    | content | DomainGroupContent |
    | media   | DomainGroupMedia   |



#Scenario: Content types are exposed as leafs of their content type group
#  Given there is a Content Type Group with identifier content
#    And there is a Content Type "folder" in the Content Type Group "content"
#   When I query the schema
#   Then it has a
