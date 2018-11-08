# The domain schema

The domain content is the main GraphQL schema of eZ Platform. It is created
based on the content model: your types and theirs fields.

Its usage requires that the GraphQL model is generated for a given repository, as a set of configuration files in the project's app.

The generated schema will list:
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

Run `php bin/console bd:platform-graphql:generate-domain-schema` from the root of your
eZ Platform installation. It will go over your repository, and generate the matching
types in `app/config/graphql/`.

Open `<host>/graphiql`. The content type groups, content types and their fields
will be exposed as the schema.

## Customizing the schema

### Schema workers

Schema workers are used by the Repository Domain Generator to generate the domain's schema.
The generator iterates on objects from the repository, and passes on the loaded data and the schema.

Example:

The `DefineDomainContent` worker will, given a Content Type, define the matching Domain Content type.
Another, `AddDomainContentToDomainGroup`, will add the same Domain Content to its Domain Group.

#### Creating a custom worker

A custom worker will be added to customize the schema based on the content model.
The generator will iterate over a given set of objects from the repository (content type groups,
content types...), and provide workers with those.

A worker implements the `DomainSchema\SchemaWorker\SchemaWorker` interface. They implement two methods:

- `work` will use the arguments to modify the schema.
- `canWork` will test the schema and arguments, and say if the worker can run on this data.
  It must be called before calling `work`.
  **the method must also verify that the schema hasn't been worked on already**
  (usually by testing the schema itself). Yes, it is a bit redundant.

Both method receive as arguments a reference to the schema array, and an array of arguments.

A custom worker must be passed to the `BD\EzPlatformGraphQLBundle\DomainContent\RepositoryDomainGenerator` service
by means of a compiler pass that adds a call to `addWorker()`.

### Data available to workers

A worker that does something for each Content Type should test in `canWork()` if the `ContentType`
argument is defined. What data is available depends on the iteration.

| **Iteration**      | ContentTypeGroup | ContentType | FieldDefinition |
| ------------------ | ---------------- | ----------- | --------------- |
| Content Type Group | Yes              | No          | No              |
| Content Type       | Yes              | Yes         | No              |
| Field Definition   | Yes              | Yes         | Yes             |

