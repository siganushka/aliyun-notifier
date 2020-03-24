<?php

namespace Siganushka\Notifier\Bridge\Aliyun;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AliyunTransport extends AbstractTransport
{
    const ENDPOINT = 'https://dysmsapi.aliyuncs.com';

    private $accessId;
    private $accessSecret;
    private $signName;

    public function __construct(string $accessId, string $accessSecret, string $signName, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->accessId = $accessId;
        $this->accessSecret = $accessSecret;
        $this->signName = $signName;

        parent::__construct($client, $dispatcher);
    }

    protected function doSend(MessageInterface $message): void
    {
        if (!$message instanceof AliyunSmsMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, AliyunSmsMessage::class, \get_class($message)));
        }

        $options = $message->getOptions();
        if ($options && !$options instanceof AliyunSmsMessageOptions) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" for options.', __CLASS__, AliyunSmsMessageOptions::class));
        }

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'PhoneNumbers' => $message->getPhoneNumbers(),
            'SignName' => $this->signName,
            'TemplateCode' => $message->getTemplateCode(),
            'RegionId' => 'cn-hangzhou',
            'Action' => 'SendSms',
            'Version' => '2017-05-25',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid(mt_rand(0, 0xffff), true),
            'SignatureVersion' => '1.0',
            'AccessKeyId' => $this->accessId,
            'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"),
        ]);

        $templateParams = $message->getTemplateParams();
        if (!empty($templateParams)) {
            $resolver->setDefault('TemplateParam', json_encode($templateParams, JSON_UNESCAPED_UNICODE));
        }

        $defaults = $options ? $options->toArray() : [];
        $query = $resolver->resolve($defaults);

        // Format cannot modifiy
        $query['Format'] = 'JSON';

        ksort($query);

        $sortedQueryStringTmp = http_build_query($query, null, '&', PHP_QUERY_RFC3986);

        $method = 'GET';
        $stringToSign = "${method}&%2F&".rawurlencode($sortedQueryStringTmp);

        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $this->accessSecret.'&', true));
        $signature = rawurlencode($signature);

        $response = $this->client->request($method, self::ENDPOINT."?Signature={$signature}&{$sortedQueryStringTmp}");

        $data = json_decode($response->getContent(false), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \UnexpectedValueException(json_last_error_msg());
        }

        if (isset($data['Code']) && 'OK' != $data['Code']) {
            throw new TransportException(sprintf('Unable to send sms (%s: %s)', $data['Code'], $data['Message']), $response);
        }
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof AliyunSmsMessage;
    }

    public function __toString(): string
    {
        return sprintf('aliyun://%s?access_id=%s&access_secret=%s&sign_name=%s',
            $this->getEndpoint(),
            $this->accessId,
            $this->accessSecret,
            $this->signName);
    }
}
