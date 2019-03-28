# How-to upload binary files

The eZ Platform GraphQL schema supports binary files in two ways:
- binary field types: image, binary file and media
- multi-file upload, where a set of files are uploaded to a container, and created as content based on the system's configuration

In both cases, it is not possible to upload binary files through GraphiQL. Some 3rd party clients, such as [Altair GraphQL](https://altair.sirmuel.design/), support it. However, at this time, eZ Platform is limited to session (cookie) based authentication, and cookies are not supported by the GraphQL specification. Therefore, this how-to will use curl for the examples.

## Approach

GraphQL uses HTTP multipart form-data, like HTTP forms for files uploads, as specified on [jaydenseric/graphql-multipart-request-spec](jaydenseric/graphql-multipart-request-spec). One file defines the GraphQL operation (mutation + variables), another maps the variables to multipart files, and the files are added to the request:

```sh
curl -v -X POST \
  https://example.com/graphql \
  -H "Cookie: $AUTH_COOKIE" \
  -F 'operations={"query":"mutation CreateImage($file: FileUpload!) { createImage( parentLocationId: 51, language: eng_GB, input: { name: \"An image created over GraphQL\", image: { alternativeText: \"The alternative text\", file: $file } } ) { _info { id mainLocationId } _url name image { fileName alternativeText uri } } }","variables":{"file": null}}' \
  -F 'map={"image":["variables.file"]}' \
  -F "image"=@/path/to/image.png
```

This approach is implemented by several javascript clients. A list is available in the specification's README, in the [clients section](https://github.com/jaydenseric/graphql-multipart-request-spec#client).

## Creating an image content item

This mutation will create an image content item:

```graphql
mutation CreateImage($file: FileUpload!) { 
  createImage( 
    parentLocationId: 51, 
    language: eng_GB,
    input: { 
      name: "An image created over GraphQL", 
      image: {
        alternativeText: "The alternative text", 
        file: $file
      }
    }
  ) { 
    _info { id mainLocationId } 
    name 
    image { fileName alternativeText uri }
  } 
}
```

The file is provided as the `$file` variable, defined as an `UploadFile`.

### `operations`: mutation and variables
A first file, `operation`, contains a json with the GraphQL mutation (`query`) and the GraphQL `variables`.
`$file`, defined in the mutation, is set to `null`. Other variables can be defined as needed.

```json
{
  "query": "<the mutation>",
  "variables": {
    "file": null
  }
}
```

### `map`: maps operation variables to request files
The `map` file is a json hash that maps `FileUpload` variables from the `operation` to a file from the request. This maps the `$file` (`variables.file`) to the `image` file from the multipart request.

```sh
-F 'map={"image":["variables.file"]}'
```

### Add the files defined in the map to the request

Each file defind in the `map` is added to the request using the defined name:

```sh
-F "image"=@/Users/bdunogier/Desktop/screenshot.png
```

## Uploading multiple files with `UploadFiles`

The `UploadFiles` mutation uses the [multi-file upload configuration](https://doc.ezplatform.com/en/latest/guide/file_management/#multi-file-upload) to create content items from a list of given binary files. We will decompose the following `curl` request, that uploads five files to the eZ Platform repository, below the container with location id 51:

```shell
curl -v -X POST \
  https://localhost:8000/graphql \
  -H 'Cookie: $AUTH_COOKIE' \
  -F 'operations={"query": "mutation UploadMultipleFiles($files: [FileUpload]!) { uploadFiles( locationId: 51, files: $files, language: eng_GB ) { files { _url _location { id } ... on ImageContent { name image { uri } } ... on FileContent { name file { uri } } ... on VideoContent { name file { uri } } } warnings } }", "variables": {"files": [null, null, null, null, null]}}' \
  -F 'map={"image1":["variables.files.0"], "image2":["variables.files.1"], "file1":["variables.files.2"], "file2":["variables.files.3"], "media":["variables.files.4"]}' \
  -F "image1"=@/tmp/files/image1.png \
  -F "image2"=@/tmp/files/image2.png \
  -F "file1"=@/tmp/files/file1.pdf \
  -F "file2"=@/tmp/files/file2.zip \
  -F "media"=@/tmp/files/media.mp4
```

It uses the `uploadFiles` mutation, that takes an array of `FileUpload` as the input:

```graphql
mutation UploadMultipleFiles($files: [FileUpload]!) {
  uploadFiles(
    locationId: 51, 
    files: $files, 
    language: eng_GB
  ) {
    files {
      _url
      _location {
        id
      }
      ... on ImageContent {
        name
        image {
          uri
        }
      }
      ... on FileContent {
        name
        file {
          uri
        }
      }
      ... on VideoContent {
        name
        file {
          uri
        }
      }
    }
    warnings
  }
}
```

The HTTP request is similar to the field type example above, except that `$files` is an array. The `variables` entry in the operation needs to contain the same number of `null` values than the files we are going to upload. We will use five files:

```json
{"files": [null, null, null, null, null]}}
```

The resulting `operations` file is as follows:

```
-F 'operations={"query": "mutation BunchOfImages($files: [FileUpload]!) { uploadFiles( locationId: 51, files: $files, language: eng_GB ) { files { _url _location { id } ... on ImageContent { name image { uri } } ... on FileContent { name file { uri } } ... on VideoContent { name file { uri } } } warnings } }", "variables": {"files": [null, null, null, null, null]}}'
```

The map is similar to the one from earlier, with the syntax trick to set each array key individually:

```
-F 'map={"image1":["variables.files.0"], "image2":["variables.files.1"], "file1":["variables.files.2"], "file2":["variables.files.3"], "media":["variables.files.4"]}'
```

The files are added one after the other, with the name given in `map`:

```
  -F "image1"=@/tmp/files/image1.png \
  -F "image2"=@/tmp/files/image2.png \
  -F "file1"=@/tmp/files/file1.pdf \
  -F "file2"=@/tmp/files/file2.zip \
  -F "media"=@/tmp/files/media.mp4
```