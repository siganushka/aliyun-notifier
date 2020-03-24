<?php

namespace Siganushka\Notifier\Bridge\Aliyun;

use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

class AliyunSmsMessage implements MessageInterface
{
    private $phoneNumbers;
    private $templateCode;
    private $templateParams = [];

    private $options;
    private $transport;

    public function __construct(string $phoneNumbers, string $templateCode, array $templateParams = [])
    {
        $this->phoneNumbers = $phoneNumbers;
        $this->templateCode = $templateCode;
        $this->templateParams = $templateParams;
    }

    public function getPhoneNumbers(): string
    {
        return $this->phoneNumbers;
    }

    public function getTemplateCode(): string
    {
        return $this->templateCode;
    }

    public function getTemplateParams(): array
    {
        return $this->templateParams;
    }

    public function getRecipientId(): ?string
    {
        return $this->options ? $this->options->getRecipientId() : null;
    }

    public function getSubject(): string
    {
        return $this->templateCode;
    }

    public function options(MessageOptionsInterface $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): ?MessageOptionsInterface
    {
        return $this->options;
    }

    public function transport(string $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getTransport(): ?string
    {
        return $this->transport;
    }
}
