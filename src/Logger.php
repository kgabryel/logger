<?php

namespace Frankie\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;

final class Logger
{
    private static Logger $instance;
    private Monolog $logger;
    private int $level;
    private static string $basePath = '/';

    public static function getBasePath(): string
    {
        return self::$basePath;
    }

    public static function setBasePath(string $basePath): void
    {
        $basePath = rtrim(
                str_replace(
                    [
                        '\\',
                        '/'
                    ],
                    DIRECTORY_SEPARATOR,
                    $basePath
                )
            ) . DIRECTORY_SEPARATOR;
        self::$basePath = $basePath;
    }

    /**
     * @param array $env
     *
     * @return Logger
     * @throws LoggerException
     */
    public static function getInstance(array $env = []): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger(
                new Monolog('Frankie'),
                new StreamHandlerFactory(
                    self::$basePath . 'log' . DIRECTORY_SEPARATOR . date('d-m-Y') . '.log'
                ),
                $env['LOG_DEFAULT_OUTPUT'] ?? null,
                $env['LOG_DEFAULT_DATE'] ?? null,
                $env['LOGGING'] ?? 0
            );
        }
        return self::$instance;
    }

    /**
     * Logger constructor.
     *
     * @param Monolog $logger
     * @param StreamHandlerFactory $factory
     * @param string|null $outputFormat
     * @param string|null $dateFormat
     * @param int $level
     *
     * @throws LoggerException
     */
    private function __construct(
        Monolog $logger, StreamHandlerFactory $factory, ?string $outputFormat, ?string $dateFormat,
        int $level = 0
    )
    {
        $handler = $factory->setLevel($level)
            ->setFormat($outputFormat, $dateFormat)
            ->build()
            ->get();
        $this->logger = $logger;
        $this->level = $level;
        $this->setHandler($handler);
    }

    private function __clone()
    {
    }

    /**
     * @param string $level
     * @param array $env
     *
     * @return Logger
     * @throws LoggerException
     */
    public function setHandlerForLevel(string $level, array $env): self
    {
        $outputFormat = $env['LOG_' . $level . '_OUTPUT'] ?? null;
        $dateFormat = $env['LOG_' . $level . '_DATE'] ?? null;
        if (!$outputFormat || !$dateFormat) {
            $outputFormat = $env['LOG_DEFAULT_OUTPUT'] ?? null;
            $dateFormat = $env['LOG_DEFAULT_DATE'] ?? null;
        }
        $path = ($this->getHandler() instanceof StreamHandler) ? $this->getHandler()
            ->getUrl() : '';
        $factory = new StreamHandlerFactory($path);
        $this->setHandler(
            $factory->setLevel($this->level)
                ->setFormat($outputFormat, $dateFormat)
                ->build()
                ->get()
        );
        return $this;
    }

    private function setHandler(AbstractProcessingHandler $handler): self
    {
        while (\count($this->logger->getHandlers()) !== 0) {
            $this->logger->popHandler();
        }
        $this->logger->pushHandler($handler);
        return $this;
    }

    private function getHandler(): AbstractProcessingHandler
    {
        return $this->logger->getHandlers()[0];
    }

    public function createDebugMessage($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function createInfoMessage($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function createNoticeMessage($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function createWarningMessage($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function createErrorMessage($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function createCriticalMessage($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function createAlertMessage($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function createEmergencyMessage($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function debug($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('DEBUG', $_ENV);
        $logger->createDebugMessage($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function info($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('INFO', $_ENV);
        $logger->createInfoMessage($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function notice($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('NOTICE', $_ENV);
        $logger->createNoticeMessage($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function warning($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('WARNING', $_ENV);
        $logger->createWarningMessage($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function error($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('ERROR', $_ENV);
        $logger->createErrorMessage($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function critical($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('CRITICAL', $_ENV);
        $logger->createCriticalMessage($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function alert($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('ALERT', $_ENV);
        $logger->createAlertMessage($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     *
     * @throws LoggerException
     */
    public static function emergency($message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->setHandlerForLevel('EMERGENCY', $_ENV);
        $logger->createEmergencyMessage($message, $context);
    }
}
