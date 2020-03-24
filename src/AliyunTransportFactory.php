<?php

namespace Siganushka\Notifier\Bridge\Aliyun;

use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

class AliyunTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        if ('aliyun' !== $dsn->getScheme()) {
            throw new UnsupportedSchemeException($dsn, 'aliyun', $this->getSupportedSchemes());
        }

        if (null === $accessId = $dsn->getOption('access_id')) {
            throw new IncompleteDsnException('Missing "access_id" for notifier aliyun.');
        }

        if (null === $accessSecret = $dsn->getOption('access_secret')) {
            throw new IncompleteDsnException('Missing "access_secret" for notifier aliyun.');
        }

        if (null === $signName = $dsn->getOption('sign_name')) {
            throw new IncompleteDsnException('Missing "sign_name" for notifier aliyun.');
        }

        $transport = new AliyunTransport($accessId, $accessSecret, $signName, $this->client, $this->dispatcher);
        $transport->setHost($dsn->getHost());
        $transport->setPort($dsn->getPort());

        return $transport;
    }

    protected function getSupportedSchemes(): array
    {
        return ['aliyun'];
    }
}
