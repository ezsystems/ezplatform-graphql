mutation {
  createSection(
    input: {
      clientMutationId: "test"
      identifier: "someidentifier"
      name: "Some name"
    }
  ) {
    id
    name
  }
}
