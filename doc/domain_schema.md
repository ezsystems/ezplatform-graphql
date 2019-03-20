# The domain schema

The domain content is the main GraphQL schema of eZ Platform. It is created based on the content model: 
your types and theirs fields.

Its usage requires that the GraphQL model is generated for a given repository,
as a set of configuration files in the project's AppBundle.

The generated schema exposes:
- content types groups, with their camel cased identifier: `content`, `media`...
    - a `_types` field
        - each content type is exposed using its camel cased identifier: `blogPost`, `landingPage`
            - below each content type, one field per field definition of the type, using its
              camel cased identifier: `title`, `relatedContent`.
                - for each field definition, its properties:
                    - common ones: `name`, `descriptions`, `isRequired`...
                    - type specific ones: `constraints.minLength`, `settings.selectionLimit`...
    - the content types from the group, as two fields:
        a) plural (`articles`, `blogPosts`)
        b) singular (`article`, `blogPost`)
        - for each content type, one field per field definition, returning the field's value

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
    folder(id: 1234) {
      name
    }
  }
}
```

## Mutations
For each content type, two mutations will be exposed: `create{ContentType}` and `update{ContentType}`:
`createArticle`, `updateBlogPost`, ... they can be used to respectively create and update content items
of each type. In addition, an input type is created for each of those mutations: `{ContentType}ContentCreateInput`,
`{Article}ContentUpdateInput`, used to provide input data to the mutations.

### Authentication
The current user needs to be authorized to perform the operation. You can log in using `/login` to get a session cookie,
and add that session cookie to the request. With GraphiQL, logging in on another tab will work.

### Example

```
mutation CreateBlogPost {
  createBlogPost(
    parentLocationId: 2,
    language: eng_GB
    input: {
      title: "The blog post's title",
      author: [
        {name: "John Doe", email: "johndoe@unknown.net"}
      ],
      body: {
        format: html,
        input: "<h1>Title</h1><p>paragraph</p>"
    }
  ) {
    _info { id mainLocationId }
    title
    body { html5 }
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

### Custom entities/domain
For custom domains that would require their own generated schema, custom domain iterators can be implemented. 

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

