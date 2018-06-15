# eZ Platform GraphQL Bundle

This Symfony bundle adds a GraphQL server to eZ Platform, the Open Source CMS.

It relies on overblog/graphql-bundle.

## Installation

Install the package and its dependencies using composer:

```
composer require bdunogier/ezplatform-graphql-bundle:dev-master
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
        config_validation: %kernel.debug%
        schema:
            query: Query
```

Add the GraphQL server route to `app/config/routing.yml`:

```yaml
overblog_graphql_endpoint:
    resource: "@OverblogGraphQLBundle/Resources/config/routing/graphql.yml"
    prefix: /graphql
```

### GraphiQL
The graphical graphQL client, GraphiQL, must be installed separately if you want to use it.
As a developer, you probably want to.

```
composer require --dev overblog/graphiql-bundle
```

Add `OverblogGraphiQLBundle` to the `dev` bundles:

```php
case 'dev':
    // ...
    $bundles[] = new Overblog\GraphiQLBundle\OverblogGraphiQLBundle();
```

Add the GraphiQL route to `app/config/routing_dev.yml`:
```yaml
overblog_graphql_graphiql:
    resource: "@OverblogGraphiQLBundle/Resources/config/routing/graphiql.yml"
```

Go to http://<yourhost>/graphiql.
