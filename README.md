# Simple TYPO3 exception monitoring tool

## What does it do?
This package offers a simple and cheap monitoring solution for small and low budget TYPO3 projects. The purpose is that all in production mode occuring errors be send as mail to the warning_email_addr of your TYPO3 instance.

## Requirements
- PHP 5.5 or heigher
- TYPO3 6 or 7

## Installation
```bash
composer require sle/typo3-exceptionhandler
```

## Configuration
The configuration is very easy and done within 3 minutes:

1. Login into *Install Tool* of your TYPO3 instance
2. Switch to *All configuration*
3. Enter a *warning_email_addr*
4. Configure in *MAIL* the *transport* and *defaultMailFromAddress*
5. Set in *SYS* the *systemLogLevel* (Common value: 2 or heigher)
6. Set in *SYS* the *errorHandler* to "Sle\TYPO3\ExceptionHandler\ErrorHandler"
7. Set in *SYS* the *productionExceptionHandler* to "Sle\TYPO3\ExceptionHandler\ProductionExceptionHandler"
