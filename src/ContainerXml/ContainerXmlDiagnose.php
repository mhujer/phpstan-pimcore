<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ContainerXml;

use PHPStan\Command\Output;
use PHPStan\Diagnose\DiagnoseExtension;

final class ContainerXmlDiagnose implements DiagnoseExtension
{
    public function __construct(private readonly ContainerXmlReader $reader)
    {
    }

    public function print(Output $output): void
    {
        $output->writeRaw($this->reader->diagnosis() . "\n");
    }
}
