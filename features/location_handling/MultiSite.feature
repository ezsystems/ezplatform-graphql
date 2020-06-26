Feature: Handling of multiple locations in a multi-site environment

Background: Given a frontend siteaccess "site_a"
              And a folder "/site_a"
              And the tree_root for the siteaccess "site_a" is set to "/site_a"
              And a frontend siteaccess "site_b"
              And the tree_root for the siteaccess "site_b" is set to "/site_b"
              And a folder "/global"

Scenario: Queries return the location from the active siteaccess tree root
  Given a content item "foo" with with its main location in "/global" and alternate locations in "/site_a" and "/site_b"
    And a GraphQL consumer using the siteaccess "site_a"
   When the content item "foo" is queried
   Then it is returned with the location "/site_a/foo"
