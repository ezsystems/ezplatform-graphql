# eZ Platform GraphQL Bundle

This Symfony bundle adds a GraphQL server to eZ Platform, the Open Source CMS. It exposes two endpoints.

## The domain graph: `/graphql/domain`
`https://<host>/graphql/domain`

A graph of the repository's domain. It exposes the domain modelled using the repository,
based on  content types groups, content types and fields definitions. Use it to implement
apps or sites dedicated to a given repository structure.

Example: an eZ Platform site.

**Warning: this feature requires extra configuration steps. See the [Domain Schema documentation](doc/domain_schema.md).**

## The repository graph: `/graphql/repository`
`https://<host>/graphql/repository`

A graph of the repository's Public API. It exposes value objects from the repository:
content, location, field, url alias...
It is recommended for admin like applications, not limited to a particular repository.

Example: an eZ Platform Admin UI extension.

[Repository schema documentation](doc/repository_schema.md)

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
            repository:
                query: Query
            domain:
                query: Domain
        builders:
            field:
                -
                    alias: "Domain"
                    class: "BD\\EzPlatformGraphQLBundle\\GraphQL\\Builder\\DomainFieldBuilder"
```

Add the GraphQL server route to `app/config/routing.yml`:

```yaml
overblog_graphql_endpoint:
    resource: "@OverblogGraphQLBundle/Resources/config/routing/graphql.yml"
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
    resource: "@OverblogGraphiQLBundle/Resources/config/routing.xml"
```

Open `http://<yourhost>/graphiql/repository` or `http://<yourhost>/graphiql/domain`
(`domain` has specific installation requirements, see the [domain Schema documentation](doc/domain_schema.md)).