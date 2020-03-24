# Symfony Aliyun Notifier Bridge.

适用于 [symfony/notifier](https://symfony.com/doc/current/notifier.html) 消息组件的 [阿里云](https://help.aliyun.com/document_detail/101414.html) 短信传输。

> 实验性项目，请勿在生产环境中使用。

### 安装

```bash
$ composer require siganushka/aliyun-notifier:dev-master
```

### 配置

```bash
# .env

ALIYUN_DSN=aliyun://default?access_id={ACCESS_ID}&access_secret={ACCESS_SECRET}&sign_name={SIGN_NAME}
```

```yaml
# ./config/packages/notifier.yaml

framework:
    notifier:
        texter_transports:
            aliyun: '%env(ALIYUN_DSN)%'
```

```yaml
# ./config/services.yaml

Siganushka\Notifier\Bridge\Aliyun\AliyunTransportFactory:
    tags: [ texter.transport_factory ]
```

### 发送短信

```php
namespace App\Controller;

class FooController
{
    /**
     * @Route("/foo")
     */
    public function foo(TexterInterface $texter)
    {
        $templateCode = 'SMS_164611111';
        $templateParam = [
            'code' => mt_rand(1000, 9999),
        ];

        $message = new AliyunSmsMessage('18611111111', $templateCode, $templateParam);
        $texter->send($message);

        // ...
    }
}
```

