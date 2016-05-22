# Advanced TYPO3 exception handler

## What does it do?
This package offers a advanced TYPO3 exception handler. The purpose is that all errors will be send as mail to the warning_email_addr of your TYPO3 instance.


## Requirements
- PHP 5.5 or heigher
- TYPO3 6 or 7

## Installation
`composer require sle/typo3-exceptionhandler`

## Configuration
Login into *Install Tool* of your TYPO3 instance, switch to *All configuration* and configure the following properties:

- Enter a *warning_email_addr*
- Set the *SYS* the *systemLogLevel*
- Configure in *MAIL* the *transport* and *defaultMailFromAddress*
