# The content schema

This GraphQL schema exposes the eZ Platform Repository with a structure
similar to that of the [Public API](https://doc.ezplatform.com/en/latest/api/public_php_api).
It gives access to Content, Fields, Locations, Content types... It can be accessed through
`https://<host>/graphql`, as well as `http://<host>/graphiql` if
you have installed GraphiQL.

## Examples

### List content types and their fields
```
{
  contentTypes
  {
    identifier
    groups {
      identifier
    }
    fieldDefinitions {
      identifier
      fieldTypeIdentifier
    }
  }
}
```

### List