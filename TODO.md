- Implement missing FieldValue objects
- Think about FieldValue objects auto-mapping
- Fix FieldValue objects: it should not be necessary to redefine every resolver for every field.
  The Value should be directly usable. Maybe the `ContentFieldValue` object should be changed 
  to a Proxy of FieldValue ?
- Suggest the repo team to implement cursors for search
- ObjectStates support
- Think about search support
- Think about accessing root content / media locations
- Think about Global ID / Node support
- Think about Content interface / dedicated Content types.
  Dedicated ContentType objects would be instances of their ContentType, and provide
  direct access to their fields.
  `ArticleContent` would have `title`, `intro`, `body` and `image` fields.
- Fix lists by adding the `edges {cursor node}` structure specified by Relay
  https://facebook.github.io/relay/graphql/connections.htm
