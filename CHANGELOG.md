# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Use the default content type `application/json` (can be overwritten in the configuration) when the request `Content-Type` is unavailable in the `RequestBodyValueResolver`.
- Information about no registered message body mappers when creating an entity response.

### Changed
- Moved validation of `@RequestBody` argument type from `RequestBodyValueResolver` to `RegisterControllerAnnotationLocatorsPass`.
- Extended ability of mapping data to any type in `MapperInterface`.
- Use mappers instead of converters on non-object types (scalars, collections) in the `RequestBodyValueResolver`.
- Instead of 406 HTTP response, return 500 HTTP response in case of no registered message body mapper.

### Fixed
- Typo in the message of not acceptable http exception.

[unreleased]: https://github.com/piku235/JungiFrameworkExtraBundle/compare/v1.0.0...HEAD
