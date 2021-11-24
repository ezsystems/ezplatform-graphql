# GraphQL schema sync

An experimental mechanism for publishing a compiled schema so that secondary servers can pull it and install it without recompiling their own containe.

## Configuration

### Redis
The feature requires Redis for publishing the latest schema timestamp (it is suggested in `composer.json`).

The feature comes with a default redis client service, `ibexa_graphql.sync.redis_client`.
It is configured using the following environment variables:
```.dotenv
REDIS_GRAPHQL_HOST=1.2.3.4
REDIS_GRAPHQL_PORT=6379
REDIS_GRAPHQL_DBINDEX=0
```

If you want to use your own client, you can redefined the service with the same name in your 
project's services definitions.

If you already have your own and want to re-use it, create an alias with that name:
```yaml
# config/services.yaml
services:
    ibexa_graphql.sync.redis_client: '@app.redis_client'
```

### AWS S3
Amazon S3 can be used to publish the schema files. To enable it, make sure that `aws/aws-sdk-php`
is installed on your project.

It uses a default client service named `ibexa_graphql.sync.s3_client`, based on the default
environment variables expected by the SDK:

```dotenv
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
```

If you already have an s3 client service, alias it to `ibexa_graphql.sync.s3_client`:
```yaml
# config/services.yaml
services:
    ibexa_graphql.sync.s3_client: '@app.s3_client'
```

The feature also requires two extra settings for the bucket and the region:
```dotenv
GRAPHQL_SYNC_S3_BUCKET=ibexa-graphql
GRAPHQL_SYNC_S3_REGION=eu-west-1
```

## Usage
One server will do the schema generation + compiling, and run a command
(`ibexa:graphql:publish-schema`) to publish the schema. The command can be executed during a deployment process,
or manually.

Publishing will:

1. push the compiled schema (`%kernel.cache_dir%/overblog/graphql-bundle/__definitions__/*`) to a SharedSchema
2. set the published schema timestamp using a TimestampHandler.

Secondary servers, when a GraphQL query is executed (`UpdateSchemaIfNeeded` subscriber), will compare the timestamp to
theirs (modification time of `__classes.map`). If the remote schema is newer, it will be pulled and installed on shutdown.

Since the graphql schema types are compiled into the container as services, types that were added to the published schema
(new content types, etc) need to be registered on runtime. This is done by the `Schema\Sync\AddTypesSolutions` subscriber.
It checks which of the type classes do not have a solution in the current schema, and adds them to it.
