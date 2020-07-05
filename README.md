# The @dependency Annotation
![Static Analysis Test](https://github.com/navarr/dependency-annotation/workflows/Static%20Analysis%20Test/badge.svg)

This project supplies a Composer plugin that adds a command (`why-block`) that interprets the PHP `@dependency`
 annotation.

## How to use the `@dependency` annotation

Simply include a `@dependency` annotation in any slash-based comment block, in the following format:

    @dependency composer-package:version-constraint [Explanation]
    
All fields except the explanation are mandatory.  Adding an explanation is _highly recommended_, however.

The version-constraint field cannot contain spaces (even if surrounded by quotes).

## How to process reasons not to upgrade a composer dependency

If you are using the `@dependency` annotation thoroughly, and you are having issues updating a composer dependency, you
can use the command `composer why-block composer-package version`

This will output a list of files containing a `@dependency` annotation on composer-package with a version-constraint
 that cannot be fulfilled by your specified version.

## How to install

`composer global require navarr/dependency-annotation`
