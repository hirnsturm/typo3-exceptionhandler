<?php

namespace Sle\TYPO3\ExceptionHandler;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

/**
 * ErrorHandler
 *
 * @author Steve Lenz <kontakt@steve-lenz.de>
 * @copyright (c) 2015, Steve Lenz
 * @version 1.0.0
 */
class ErrorHandler extends \TYPO3\CMS\Core\Error\ErrorHandler
{

    /**
     * Registers this class as default error handler
     *
     * @param int $errorHandlerErrors The integer representing the E_* error level which should be
     */
    public function __construct($errorHandlerErrors)
    {
        $excludedErrors = E_COMPILE_WARNING | E_COMPILE_ERROR | E_CORE_WARNING | E_CORE_ERROR | E_PARSE | E_ERROR;
        // reduces error types to those a custom error handler can process
        $errorHandlerErrors = $errorHandlerErrors & ~$excludedErrors;
        set_error_handler(array($this, 'handleError'), $errorHandlerErrors);
        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * @inheritdoc
     */
    public function handleError($errorLevel, $errorMessage, $errorFile, $errorLine)
    {
        if (E_ERROR == $errorLevel) {
            $this->sendNotificationMail($errorLevel, $errorMessage, $errorFile, $errorLine);
        }
        parent::handleError($errorLevel, $errorMessage, $errorFile, $errorLine);
    }

    /**
     *
     */
    public function shutdown()
    {
        $error = error_get_last();
        if (E_ERROR == $error['type']) {
            $this->sendNotificationMail($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * @param int $errorLevel
     * @param string $errorMessage
     * @param string $errorFile
     * @param int $errorLine
     */
    protected function sendNotificationMail($errorLevel, $errorMessage, $errorFile, $errorLine)
    {
        if (false === filter_var($GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr'], FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Build message
        $message = array();
        $message[] = 'Level: ' . $this->getErrorType($errorLevel);
        $message[] = 'Message: ' . $errorMessage;
        $message[] = 'Server name:  ' . filter_input(INPUT_SERVER, 'SERVER_NAME');
        $message[] = 'Request URI:  ' . filter_input(INPUT_SERVER, 'REQUEST_URI');
        $message[] = 'File: ' . $errorFile;
        $message[] = 'Line: ' . $errorLine;

        try {
            // Send mail
            $mail = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
            $mail->setFrom(MailUtility::getSystemFrom())
                ->setTo(array($GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr']))
                ->setSubject(MailUtility::getSystemFromName() . ' - ' . getErrorType($errorLevel))
                ->setBody(implode(PHP_EOL . PHP_EOL, $message))
                ->send();
        } catch (\Exception $e) {
            $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
            $logger->error('Could not send exception message to system admin!', array($e->__toString()));
        }
    }

    /**
     * @param $errorLevel
     * @return string|false
     */
    protected function getErrorType($errorLevel)
    {
        $errorTypes = array(
            E_ERROR             => 'ERROR',
            E_WARNING           => 'WARNING',
            E_PARSE             => 'PARSING ERROR',
            E_NOTICE            => 'NOTICE',
            E_CORE_ERROR        => 'CORE ERROR',
            E_CORE_WARNING      => 'CORE WARNING',
            E_COMPILE_ERROR     => 'COMPILE ERROR',
            E_COMPILE_WARNING   => 'COMPILE WARNING',
            E_USER_ERROR        => 'USER ERROR',
            E_USER_WARNING      => 'USER WARNING',
            E_USER_NOTICE       => 'USER NOTICE',
            E_STRICT            => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        );
        return isset($errorTypes[$errorLevel]) ? $errorTypes[$errorLevel] : 'UNKNOWN ERROR';
    }
}
