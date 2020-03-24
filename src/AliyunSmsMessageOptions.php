<?php

namespace Siganushka\Notifier\Bridge\Aliyun;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

class AliyunSmsMessageOptions implements MessageOptionsInterface
{
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function getRecipientId(): ?string
    {
        return $this->options['recipient_id'] ?? null;
    }
}
