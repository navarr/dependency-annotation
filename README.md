# The #[Dependency] Attribute
![Static Analysis Test](https://github.com/navarr/dependency-annotation/workflows/Static%20Analysis%20Test/badge.svg)

This project supplies a Composer plugin that adds a command (`why-block`) that interprets a PHP `#[Dependency]`
 attribute.

## How to use the `#[Dependency]` annotation

Simply include a `#[Dependency]` attribute on any attributable target in the following format:

    #[Navarr\Depends\Annotation\Dependency('package', 'versionConstraint', 'reason')]
    
This FQN may be imported, in which case you can simply use `#[Dependency(...)]`

All fields except the explanation are mandatory.  Adding an explanation is _highly recommended_, however.

## How to process reasons not to upgrade a composer dependency

If you are using the `#[Dependency]` annotation thoroughly, and you are having issues updating a composer dependency, you
can use the command `composer why-block composer-package version`

This will output a list of files containing a `#[Dependency]` annotation on composer-package with a version-constraint
 that cannot be fulfilled by the specified version.

## How to install

`composer global require navarr/dependency-annotation`

## Compatibility with v1

For speed, version 2 automatically excludes the legacy `@dependency` annotation in favor of the PHP8 `#[Dependency]` 
attribute.  While transitioning, you may specify the `-l` or `--include-legacy-annotations` flag to the `why-block` 
command to force it to process v1 annotations as well.
