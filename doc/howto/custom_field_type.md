## Support field types

Custom field types have their own values, that need to be defined for GraphQL.The Domain Worker `AddFieldValueToDomainContent` that defines fields uses `FieldValueBuilder` objects to get the type, resolution. 

Custom ones can be added by implementing an instance of `FieldValueBuilder` interface tagged with `ezplatform_graphql.field_value_builder`. The tag expects the field type identifier as the `type` attribute.

```yaml
    AppBundle\GraphQL\Schema\MyCustomFieldValueBuilder:
        tags:
            - {name: ezplatform_graphql.field_value_builder, type: 'my_custom_field'}
```

Builders have a `buildDefinition()` method. It receives the `FieldDefinition` object of the tagged type, and return an array with the `type` and `resolve` keys. The field definition settings and constraints can be used to customize the type returned by the field value, and how it is resolved. 

Many field types values can use the default resolver that returns the Field object. They can either use a native type (`String`, `Boolean`), or a custom one. Several kernel field types are defined that way.

```php
public function buildDefinition(FieldDefinition $field)
{
    return ['type' => '[String]'];
}
```

Complex Field Types can define their own graphql types and resolver.

```php
public function buildDefinition(FieldDefinition $field)
{
    return [
        'type' => 'Address',
        'resolve' => '@=resolver("resolveAddress", [field])'
    ]
}
```

Two variables are available in the resolver's expression:

- `field` is the current field, as an extension of the API's Field object that proxies properties requests to the Field Value
- `content` is the resolved content item's `ContentInfo`.

`RelationFieldValueBuilder` or `SelectionFieldValueBuilder` can be used as examples.