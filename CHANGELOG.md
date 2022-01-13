# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Transition to PHP v8.0
- Transition to Symfony v6.0

### Removed
- Annotations
- Conversion of `array|object` types by `BuiltinTypeSafeConverter`.
- Deprecated configuration option `entity_response.default_content_type`.

## [1.4.2] - 2022-01-03

### Fixed
- Deprecations of missing return type declarations.

## [1.4.1] - 2021-09-22

### Fixed
- Deprecated PHP 8.1 "null" on the 2nd argument of the `InvalidArgumentException` in `DefaultObjectExporter`.

## [1.4.0] - 2021-08-29

### Changed
- Attributes are taken either directly from the `ArgumentMetadata` or from the attribute locator.
- Annotations (not attributes) will be only taken into account when the `doctrine\annotations` package is available (in no-dev mode).

### Deprecated
- `onAttribute()` and `onAnnotation()` methods on `RequestBodyValueResolver`, use the constructor instead
- `onAttribute()` and `onAnnotation()` methods on `RequestCookieValueResolver`, use the constructor instead
- `onAttribute()` and `onAnnotation()` methods on `RequestHeaderValueResolver`, use the constructor instead
- `onAttribute()` and `onAnnotation()` methods on `RequestParamValueResolver`, use the constructor instead
- `onAttribute()` and `onAnnotation()` methods on `QueryParamValueResolver`, use the constructor instead
- `onAttribute()` and `onAnnotation()` methods on `QueryParamsValueResolver`, use the constructor instead

### Removed
- Support for Symfony lower than v5.3

## [1.3.0] - 2021-07-31

### Added
- Added support of nullable `RequestBody` arguments. When a request body is empty, and the content type is unavailable, a default argument value is used, or `null` in case of a nullable argument.

### Changed
- No error for nullable `RequestBody` arguments.

### Removed
- Doctrine annotations from the composer dependencies.
- Support for Symfony v4.4

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

[unreleased]: https://github.com/jungi-php/framework-extra-bundle/compare/v1.4.2...HEAD
[1.4.2]: https://github.com/jungi-php/framework-extra-bundle/compare/v1.4.1...v1.4.2
[1.4.1]: https://github.com/jungi-php/framework-extra-bundle/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/jungi-php/framework-extra-bundle/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/jungi-php/framework-extra-bundle/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/jungi-php/framework-extra-bundle/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/jungi-php/framework-extra-bundle/compare/v1.0.0...v1.1.0
