<?php
namespace UrbanAirship\Channel;

use UrbanAirship\UALog;

class Tagger
{
    const TAG_URL = "/api/channels/tags";

    private $airship;
    private $payload;
    private $addTags;
    private $removeTags;
    private $setTags;

    public function __construct($airship)
    {
        $this->airship = $airship;
        $this->payload = array();
    }

    public function iosChannel($channel)
    {
        $this->audience = array("ios_channel" => $channel);
        return $this;
    }

    public function add($tags)
    {
        if (! empty($tags)) {
            $this->addTags = $this->arrayifyTags($tags);
        }
        return $this;
    }

    public function remove($tags)
    {
        if (! empty($tags)) {
            $this->removeTags = $this->arrayifyTags($tags);
        }
        return $this;
    }

    public function set($tags)
    {
        if (! empty($tags)) {
            $this->setTags = $this->arrayifyTags($tags);
        }
        return $this;
    }

    private function arrayifyTags($tags)
    {
        if (! is_array($tags)) {
            $tags = [$tags];
        }

        if (! is_array($tags[key($tags)])) {
            $tags[key($tags)] = [$tags[key($tags)]];
        }

        return $tags;
    }

    protected function getPayload()
    {
        $payload['audience'] = $this->audience;
        if (! empty($this->addTags)) {
            $payload['add'] = $this->addTags;
        }
        if (! empty($this->removeTags)) {
            $payload['remove'] = $this->removeTags;
        }
        if (! empty($this->setTags)) {
            $payload['set'] = $this->setTags;
        }

        return $payload;
    }

    public function send()
    {
        $uri = $this->airship->buildUrl(self::TAG_URL);
        $logger = UALog::getLogger();

        $response = $this->airship->request("POST",
            json_encode($this->getPayload()), $uri, "application/json", 3);

        $payload = json_decode($response->raw_body, true);
        $logger->debug("Response  is: " . var_export($response));
        $logger->info("Tags created successfully");
        return new PushResponse($response);
    }
}
