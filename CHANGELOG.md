# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2020-09-27

### Added
- Attributes (PHP 8.0).

### Changed
- Refreshed the named value argument value resolvers. Simplified the `getArgumentValue` method signature by using the new `NamedValueArgument`.
- Attributes are now key part of the bundle, annotations are used as adapters for attributes and are intended for projects basing on <= PHP 7.4.
- Updated all argument value resolvers to support both annotations and attributes.
- Changed internal `Jungi\FrameworkExtraBundle\Annotation\AbstractAnnotation` to `StatefulTrait`.

### Removed
- internal `Jungi\FrameworkExtraBundle\Annotation\NamedValueArgument`.

### Fixed
- Detecting duplicated annotations on argument by `RegisterControllerAnnotationLocatorsPass`.
- Deprecation of ReflectionParameter::isArray() in `RegisterControllerAnnotationLocatorsPass`.

## [1.1.0] - 2020-09-11

### Added
- Use the default content type `application/json` (can be overwritten in the configuration) when the request `Content-Type` is unavailable in the `RequestBodyValueResolver`.
- Information about no registered message body mappers when creating an entity response.

### Changed
- Moved validation of `@RequestBody` argument type from `RequestBodyValueResolver` to `RegisterControllerAnnotationLocatorsPass`.
- Extended ability of mapping data to any type in `MapperInterface`.
- Use mappers instead of converters on non-object types (scalars, collections) in the `RequestBodyValueResolver`.
- Instead of 406 HTTP response, return 500 HTTP response in case of no registered message body mapper.

### Fixed
- Handle exception on mapping to an array when scalar data has been provided in `SerializerMapperAdapter`.
- Typo in the message of not acceptable http exception.

### Deprecated
- Config option "default_content_type" at "entity_response". Moved to the root node "jungi_framework_extra".

[unreleased]: https://github.com/piku235/JungiFrameworkExtraBundle/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/piku235/JungiFrameworkExtraBundle/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/piku235/JungiFrameworkExtraBundle/compare/v1.0.0...v1.1.0
