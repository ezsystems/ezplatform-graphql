# The domain schema

The domain content is the main GraphQL schema of eZ Platform. It is created based on the content model: 
your types and theirs fields.

Its usage requires that the GraphQL model is generated for a given repository, as a set of configuration files in the project.

By default, the generated schema will list:
- the content types groups
- for each group the content types it contains
- for each content type, its fields definitions, mapped to their Field Value Type

Queries look like this:

```
{
  content {
    articles {
      title
      body { html }
      image {
        name
        variations(alias: large) { uri }
      }
    }
    folders {
      name
    }
  }
}
```

## Setting it up

Run `php bin/console ezplatform:graphql:generate-schema` from the root of your
eZ Platform installation. It will go over your repository, and generate the matching
types in `app/config/graphql/ezplatform`.

Open `<host>/graphiql`. The content type groups, content types and their fields
will be exposed as the schema.

## Schema generation

The schema generator works by:
1. looping over arguments yielded by Domain Iterators (`BD\EzPlatformGraphQLBundle\Schema\Domain\Iterator`)
2. for each worker, test if it can work on the current arguments set by calling `canWork()`
3. if it can, call `work()` so that the worker can modify the schema using the `SchemaBuilder`.

### Schema workers

Schema workers, instances of `BD\EzPlatformGraphQLBundle\Schema\Worker`, perform the schema changes. They usually only perform one specific schema operation. Each particular domain (Content, Page) will use its own workers.

Example:

The `DefineDomainContent` worker will, given a Content Type, define the matching Domain Content type.
Another, `AddDomainContentToDomainGroup`, will add the same Domain Content to its Domain Group.

## Customizing the generated schema

### Custom Field Types

Fields values are handled by the `AddFieldValueToDomainContent` content domain schema worker. It uses Field Value Builders (instances of `Schema\Domain\Content\FieldValueBuilder\FieldValueBuilder`) to generate the type and resolution for each field.

Basic field types are handled by the `BaseFieldValueBuilder`. It maps each fieldtype, using its identifier, to either a type, or a type and a resolver. For the time being, it can not be customized.

Extra Field Value Builders can be added to it by implementing the `FieldValueBuilder` interface, and tagging the service with `ezplatform_graphql.field_value_builder`, with a `type` attribute set to the field type identifier:

```yaml
    AppBundle\GraphQL\Schema\MyCustomFieldValueBuilder:
        tags:
            - {name: ezplatform_graphql.field_value_builder, type: 'my_custom_field'}
```

Builders have a `buildDefinition()` method. It receives the `FieldDefinition` object, and return an array with the `type` and `resolve` keys. Complex Field Types would come with their own resolver and graphql types, but simple ones can simply map to a native scalar type, like `String` or `Integer`.

`RelationFieldValueBuilder` or `SelectionFieldValueBuilder` can be used as examples.

### Custom domain

For custom domains that would require their own dynamic schema, custom domain iterators can be implemented. 

Examples:

- the `ImageVariationIdentifier` enum, used as an argument when providing an image variation, has a domain iterator that returns each image variation.
- the page system uses an iterator that yields the custom blocks types defined in the system

### Creating a custom worker

Custom workers can be added to customize the schema based on the content model. A worker implements the `Schema\Worker` interface, and are tagged with `ezplatform_graphql.domain_schema_worker`. They implement two methods:

- `work` will use the arguments to modify the schema.
- `canWork` will test the schema and arguments, and say if the worker can run on this data.
  It must be called before calling `work`.
  **the method must also verify that the schema hasn't been worked on already**, as it could overwrite changes made by other workers.

Both method receive as argument:

- A `SchemaBuilder` object
- the current arguments

