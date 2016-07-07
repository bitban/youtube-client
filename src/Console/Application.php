<?php

/**
 * Copyright 2016 Bitban Technologies, S.L.
 * Todos los derechos reservados.
 */

namespace Bitban\YoutubeClient\Console;


use Bitban\YoutubeClient\Command\UploadCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Youtube CLI upload', '0.0.1');

        // project:create
        $this->addCommands([
            new UploadCommand()
        ]);
    }
}
