# Custom application schema

If your application requires custom GraphQL resources, for instance for Doctrine entities, the schema generated from the eZ Platform repository can be customized. To do so, create the `app/config/graphql/Query.types.yml` file. It will be used as the GraphQL query root.

In that file, you can add new fields that use any custom type or custom logic you require, based
on [overblog/GraphQLBundle](https://github.com/overblog/GraphQLBundle).

## Configuration

To include the eZ Platform schema, you can either inherit from it (inheritance), or set it as a field in your custom schema (composition).

### Inheritance example

```yaml
# app/config/graphql/Query.types.yml
Query:
    type: object
    inherits:
        - Domain
    config:
        fields:
            customField:
                type: object
```

### Composition example

```yaml
# app/config/graphql/Query.types.yml
Query:
    type: object
    config:
        fields:
            myCustomField: {}
            myOtherCustomField: {}
            ezplatform:
                type: Domain
```

## Custom mutations
The same way, you can create the `app/config/graphql/Mutation.types.yml` file. It will be used as the source for mutations definitions in your schema.

Once mutations are implemented for the eZ Platform schema (https://github.com/ezsystems/ezplatform-graphql/pull/4), your custom Mutation type will have to be modified to inherit from it:

```yaml
Mutation:
    type: object
    inherits: [PlatformMutation]
    config:
        fields:
            createSomething:
                builder: Mutation
                builderConfig:
                        inputType: CreateSomethingInput
                        payloadType: SomethingPayload
                        mutateAndGetPayload: "@=mutation('CreateSomething', [value])"

CreateSomethingInput:
    type: relay-mutation-input
    config:
        fields:
            name:
                type: String

SomethingPayload:
    type: object
    config:
        fields:
            name:
                type: String

```