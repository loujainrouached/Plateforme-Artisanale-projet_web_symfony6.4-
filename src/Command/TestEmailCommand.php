<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestEmailCommand extends Command
{
    protected static $defaultName = 'app:test-email';
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('skanderselmi19@gmail.com')
            ->to('recipient@example.com') // Replace with a valid email address
            ->subject('Test Email')
            ->text('This is a test email.');

        $this->mailer->send($email);

        $output->writeln('Email sent successfully!');
        return Command::SUCCESS;
    }
}