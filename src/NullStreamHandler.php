<?php

namespace Frankie\Logger;

use Monolog\Handler\AbstractProcessingHandler;

final class NullStreamHandler extends AbstractProcessingHandler
{
    protected function write(array $record): void
    {
    }
}
