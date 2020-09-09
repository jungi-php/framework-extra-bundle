# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added `text/plain` mapper.
- Information about no registered message body mappers when creating an entity response.

### Changed
- Extended ability of mapping data to any type in `MapperInterface`.
- Use mappers instead of converters on non-object types (scalars, collections) in the `RequestBodyValueResolver`.
- Instead of 406 HTTP response, return 500 HTTP response in case of no registered message body mapper.

### Fixed
- Typo in the message of not acceptable http exception.

[unreleased]: https://github.com/piku235/JungiFrameworkExtraBundle/compare/v1.0.0...HEAD
