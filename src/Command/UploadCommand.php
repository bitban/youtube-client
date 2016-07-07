<?php

/**
 * Copyright 2016 Bitban Technologies, S.L.
 * Todos los derechos reservados.
 */

namespace Bitban\YoutubeClient\Command;

use Bitban\YoutubeClient\YoutubeUpload;
use Google_Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class UploadCommand extends Command
{
    const COMMAND_NAME = 'upload';
    const ARG_VIDEO_FILE = 'video';
    const OPT_CLIENT_SECRETS = 'client-secrets';
    const OPT_TITLE = 'title';
    
    protected function configure()
    {
        parent::configure();
        $this
            ->setName(self::COMMAND_NAME)
            ->addArgument(self::ARG_VIDEO_FILE, InputArgument::REQUIRED)
            ->addOption(self::OPT_CLIENT_SECRETS, null, InputOption::VALUE_REQUIRED)
            ->addOption(self::OPT_TITLE, null, InputOption::VALUE_OPTIONAL, '', 'Default title');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption(self::OPT_CLIENT_SECRETS)) {
            throw new RuntimeException('Not enough options (missing: "client-secrets")');
        }

        $client = new Google_Client();
        $client->setScopes('https://www.googleapis.com/auth/youtube');
        $client->setAuthConfigFile($input->getOption(self::OPT_CLIENT_SECRETS));

        if (!$client->getAccessToken()) {
            $authUrl = $client->createAuthUrl();

            $output->writeln("Open this URL in your browser, authenticate yourself and put back the validation code\n<info>$authUrl</info>");

            system("open \"$authUrl\"");

            $helper = $this->getHelper('question');
            $question = new Question('Please enter validation code: ');

            $validationCode = $helper->ask($input, $output, $question);
            $client->fetchAccessTokenWithAuthCode($validationCode);
        }

        if (!$client->getAccessToken()) {
            $output->writeln('<error>Sorry, no access ¯\_(ツ)_/¯</error>');
            die();
        }

        (new YoutubeUpload())->upload($client, $input->getArgument(self::ARG_VIDEO_FILE), $input->getOption(self::OPT_TITLE));
    }
}
