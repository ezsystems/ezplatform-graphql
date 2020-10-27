# Research: better location handling

Change how GraphQL and the query field load items so that they're always loaded _with_ a location. The goal is to better serve use-cases where the location is important, like getting a URL alias, or getting the results of a query field.

## GraphQL

What location is loaded depends on how the items are queried:

- Single item
  - If a contentId or remoteId is used, loads the main location
  - If a locationid or locationRemoteId is used, the requested location is used
- Collection
  - By default, items are returned with their main location

## Open questions

### Tree root

If the siteaccess uses a custom tree root, the locations should always be within that subtree.

### Multiple locations

Probably the biggest challenge. What do we do if a Query Field, resolved with `findLocations()`, returns several locations for the same item ? Do we want the same item several times ? Does it make sense ?

Is it likely ? Multiple locations in the same tree would most likely be used to model something. Can we just switch to searching for locations without further changes ?

## Tech stuff

- Do we introduce a custom value object used when loading domain items, a composition of content + location ?
  - Or is the location dynamically determined based on... some context ?
  - That custom value object could just be `Location`, since it always refer to a content item.
  
## Backward compatibility
The main BC change is with collections: the items returned by the same GraphQL query may differ after this change.
The easiest way to cover it would be to make the new resolver's tags optional, as an opt-in mechanism for the 2.x
release of ezplatform-graphql.

What could the new resolver be like ?
- `LocationCollectionResolver`
  But does it only handle collections ? It also handles fields values.
