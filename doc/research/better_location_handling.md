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

## Tech stuff

- Do we introduce a custom value object used when loading domain items, a composition of content + location ?
  - Or is the location dynamically determined based on... some context ?
  - That custom value object could just be "a location", since it always refer to one content item