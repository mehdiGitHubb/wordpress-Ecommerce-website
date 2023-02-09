# Dhii - Validation - Interface

[![Build Status](https://travis-ci.org/Dhii/validation-interface.svg?branch=develop)](https://travis-ci.org/Dhii/validation-interface)
[![Code Climate](https://codeclimate.com/github/Dhii/validation-interface/badges/gpa.svg)](https://codeclimate.com/github/Dhii/validation-interface)
[![Latest Stable Version](https://poser.pugx.org/dhii/validation-interface/version)](https://packagist.org/packages/dhii/validation-interface)

Simple interface for most basic validator implementations.

## Details
This package aims to standardize validators, so as to make consuming code
compatible with a wide variety of validator implementations. Validation is such
a common task that being able to validate in an interoperable way is extremely
useful. Interfaces in this package aim to fix that by providing a common
validation entry point on one side, and standards-compliant validation error
exceptions on the other. This allow developers to take advantage of exception
"bubbling" and handle validation errors where they think best, as well as
to retrieve a list of human-readable validation error messages without knowledge
of the validator internals, or prior reference to the validator object. Also,
reporting validation failure as an exception is very convenient for cases where
it is only possible to continue if a validation subject is valid.

### Interfaces
- [`ValidatorInterface`] - The central interface of the standard. Provides
validation entry point (trigger) as `validate()`. Validators MUST implement
this interface.
- [`ValidationFailedExceptionInterface`] - Occurs when subject fails validation,
and reports the failed subject, and the validator that validated the subject,
as well as a list of validation error messages.
- [`ValidationExceptionInterface`] - Represents an error related to a validator.


[`ValidatorInterface`]:                     src/ValidatorInterface.php
[`ValidationFailedExceptionInterface`]:     src/Exception/ValidationFailedExceptionInterface.php
[`ValidationExceptionInterface`]:           src/Exception/ValidationExceptionInterface.php
