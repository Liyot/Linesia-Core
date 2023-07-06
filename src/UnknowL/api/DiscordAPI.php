<?php

namespace UnknowL\api;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;

class DiscordAPI
{
    public static function sendMessage(string $msg)
    {
        $message = new Message();
        $message->setContent($msg);

        $webhook = new Webhook("https://discord.com/api/webhooks/1070052286996422686/fLerH-oEBap45Z8sHauNkpnsbhqzmdepWV5Q5l5tMP8TkutDyOQEkX0lzgRssLkD0IjB");
        $webhook->send($message);
    }
}