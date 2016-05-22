<?php

namespace Sle\TYPO3\ExceptionHandler;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

/**
 * ProductionExceptionHandler
 *
 * @author Steve Lenz <kontakt@steve-lenz.de>
 * @copyright (c) 2015, Steve Lenz
 */
class ProductionExceptionHandler extends \TYPO3\CMS\Core\Error\ProductionExceptionHandler
{
    /**
     * Displays the given exception
     *
     * @param \Exception $exception The exception object
     * @return void
     */
    public function handleException($exception)
    {
        $this->sendNotificationMail($exception);
        parent::handleException($exception);
    }

    /**
     * Sends an exception as notification e-mail
     *
     * @param $exception
     */
    protected function sendNotificationMail($exception)
    {
        // Build message
        $message = array();
        $message[] = 'Title: ' . $this->getTitle($exception);
        $message[] = 'Message: ' . PHP_EOL . $this->getMessage($exception);
        $message[] = 'Server name: ' . filter_input(INPUT_SERVER, 'SERVER_NAME');
        $message[] = 'Request URI: ' . PHP_EOL
            . filter_input(INPUT_SERVER, 'SERVER_NAME')
            . filter_input(INPUT_SERVER, 'REQUEST_URI');
        $message[] = 'Request-Info: ' . PHP_EOL . print_r(filter_input_array(INPUT_SERVER), true);
        $message[] = 'POST: ' . PHP_EOL . print_r(filter_input_array(INPUT_POST), true);
        $message[] = 'GET: ' . PHP_EOL . print_r(filter_input_array(INPUT_GET), true);

        try {
            /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
            $mail = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
            $mail->setFrom(MailUtility::getSystemFrom())
                ->setTo(array($GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr']))
                ->setSubject(MailUtility::getSystemFromName() . ' - ' . $this->getTitle($exception))
                ->setBody(implode(PHP_EOL . PHP_EOL, $message))
                ->send();
        } catch (\Exception $e) {
            /** @var $logger \TYPO3\CMS\Core\Log\Logger */
            $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
            $logger->error('Could not send exception message to system admin!', array($e->__toString()));
        }
    }

}
