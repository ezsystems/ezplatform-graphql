# eZ Platform GraphQL Bundle

This Symfony bundle adds a GraphQL server to eZ Platform, the Open Source CMS.

It relies on overblog/graphql-bundle.

## Installation

Install the package and its dependencies using composer:

```
composer require bdunogier/ezplatform-graphql-bundle
```

Add the bundles to `app/AppKernel.php`:

```php
$bundles = array(
    // ...
    new Overblog\GraphQLBundle\OverblogGraphQLBundle(),
    new BD\EzPlatformGraphQLBundle\BDEzPlatformGraphQLBundle(),
    new AppBundle\AppBundle(),
);
```

Configure the overblog graphQL bundle in `app/config/config.yml`:
```yaml
overblog_graphql:
    definitions:
        internal_error_message: "An error occurred, please retry later or contact us!"
        config_validation: %kernel.debug%
        schema:
            query: Query
            mutation: ~
```

Add the GraphQL server route to `app/config/routing.yml`:

```yaml
overblog_graphql_endpoint:
    resource: "@OverblogGraphQLBundle/Resources/config/routing/graphql.yml"
    prefix: /graphql
```

Add the GraphiQL route to `app/config/routing_dev.yml`:
```yaml
overblog_graphql_graphiql:
    resource: "@OverblogGraphQLBundle/Resources/config/routing/graphiql.yml"
```

Go to http://<yourhost>/graphiql to check that everything is configured correctly.
