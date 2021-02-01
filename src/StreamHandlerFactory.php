<?php

namespace Frankie\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;

final class StreamHandlerFactory
{
    private int $level;
    private string $path;
    private AbstractProcessingHandler $handler;
    private string $outputFormat;
    private string $dateFormat;
    /** @var int[] */
    private static array $levels = [
        Monolog::DEBUG,
        Monolog::INFO,
        Monolog::NOTICE,
        Monolog::WARNING,
        Monolog::ERROR,
        Monolog::CRITICAL,
        Monolog::ALERT,
        Monolog::EMERGENCY
    ];

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->level = 0;
        $this->outputFormat = false;
        $this->dateFormat = false;
        $this->handler = new NullStreamHandler();
    }

    public function __clone()
    {
        $this->handler = clone $this->handler;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function setFormat(?string $outputFormat, ?string $dateFormat): self
    {
        $this->outputFormat = str_replace("\\n", "\n", $outputFormat);
        $this->dateFormat = $dateFormat;
        return $this;
    }

    /**
     * @return StreamHandlerFactory
     * @throws LoggerException
     * @throws \Exception
     */
    public function build(): self
    {
        if ($this->level === 0) {
            $this->handler = new NullStreamHandler();
        } elseif ($this->level > 0 && $this->level < 9) {
            $this->handler = new StreamHandler($this->path, self::$levels[$this->level - 1]);
        } else {
            throw new LoggerException(
                "Invalid logging mode: {$this->level}. Check 'LOGGING' variable in .env file."
            );
        }
        if ($this->outputFormat && $this->dateFormat) {
            $this->handler->setFormatter(new LineFormatter($this->outputFormat, $this->dateFormat));
        }
        return $this;
    }

    public function get(): AbstractProcessingHandler
    {
        return $this->handler;
    }
}
