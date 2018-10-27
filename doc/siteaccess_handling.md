# Siteaccess handling

The GraphQL endpoint is siteaccess aware, following the regular matching, be it URL or host:
- https://admin.host/graphiql: will match the admin siteaccess
- https://host/sa/graphiql: will match the `sa` siteaccess
- https://host/graphiql: will match the default siteaccess

Resolution of values is done using the siteaccess aware Repository services, and will therefore
obey the siteaccess' language priorities.