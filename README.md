# Ibexa GraphQL Bundle

This Symfony bundle adds a GraphQL server to [Ibexa DXP](https://www.ibexa.co/products) and 
Ibexa Open Source.

## The schema: `/graphql`
`https://<host>/graphql`

It first and foremost exposes the domain modelled using the repository,
based on  content types groups, content types and fields definitions. Use it to implement
apps or sites dedicated to a given repository structure.

Example: a Ibexa site.

**Warning: this feature requires specific setup steps. See the [Domain Schema documentation](doc/domain_schema.md).**

In addition to the schema based on the content model, the repository's Public API is also available under `_repository`.
It exposes content, location, field, url alias...
It is recommended for admin like applications, not limited to a particular repository.

Example: an Ibexa Admin UI extension.

[Repository schema documentation](doc/repository_schema.md)

## Installation

Install the package and its dependencies using composer:

```
composer require ezsystems/ezplatform-graphql
```

Add the bundles to `config/bundles.php` (*pay attention to the order*, it is important):

```php
return [
    // ...
    EzSystems\EzPlatformGraphQL\EzSystemsEzPlatformGraphQLBundle::class => ['all' => true],
    Overblog\GraphQLBundle\OverblogGraphQLBundle::class => ['all' => true],
    // ...
];
```

Add the GraphQL routing configuration to `app/config/routing.yml`:

```yaml
overblog_graphql:
    resource: "@OverblogGraphQLBundle/Resources/config/routing/graphql.yml"

overblog_graphql_endpoint:
    path: /graphql
    defaults:
        _controller: Overblog\GraphQLBundle\Controller\GraphController::endpointAction
        _format: "json"
```

### Generate your schema
Run the command that generates the GraphQL schema:
```
php bin/console ibexa:graphql:generate-schema
php bin/console cache:clear
```

It will generate a lot of yaml files in `app/config/graphql/ezplatform`, based on your content types.

### GraphiQL
The graphical graphQL client, GraphiQL, must be installed separately if you want to use it.
As a developer, you probably want to.

```
composer require --dev overblog/graphiql-bundle
```

Add `OverblogGraphiQLBundle` to the `dev` bundles:

```php
// config/bundles.php
return [
    // ...
    Overblog\GraphiQLBundle\OverblogGraphiQLBundle::class => ['dev' => true],
    // ...
];
```

Add the GraphiQL route to `app/config/routing_dev.yml`:
```yaml
overblog_graphql_graphiql:
    resource: "@OverblogGraphiQLBundle/Resources/config/routing.xml"
```

Open `http://<yourhost>/graphiql`.

## COPYRIGHT
Copyright (C) 1999-2021 Ibexa AS (formerly eZ Systems AS). All rights reserved.

## LICENSE
This source code is available separately under the following licenses:

A - Ibexa Business Use License Agreement (Ibexa BUL),
version 2.4 or later versions (as license terms may be updated from time to time)
Ibexa BUL is granted by having a valid Ibexa DXP (formerly eZ Platform Enterprise) subscription,
as described at: https://www.ibexa.co/product
For the full Ibexa BUL license text, please see:
https://www.ibexa.co/software-information/licenses-and-agreements (latest version applies)

AND

B - GNU General Public License, version 2
Grants an copyleft open source license with ABSOLUTELY NO WARRANTY. For the full GPL license text, please see:
https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
