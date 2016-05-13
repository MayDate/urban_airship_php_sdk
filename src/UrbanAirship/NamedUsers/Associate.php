<?php
namespace UrbanAirship\NamedUsers;

use UrbanAirship\Push\PushResponse;
use UrbanAirship\UALog;

class Associate
{
    const TAG_URL = "/api/named_users/associate";
    const DEVICE_TYPE_IOS = 'ios';
    const DEVICE_TYPE_ANDROID = 'android';

    private $airship;
    private $payload;
    private $addressesToImport;

    public function __construct($airship)
    {
        $this->airship = $airship;
        $this->payload = array();
    }

    private function buildPostObject($channelId, $deviceType, $namedUser)
    {
        $payload = new \stdClass();
        $payload->channel_id = $channelId;
        $payload->device_type = $deviceType;
        $payload->named_user_id = (string)$namedUser;

        return $payload;
    }

    public function associate($channelId, $deviceType, $namedUser)
    {
        $uri = $this->airship->buildUrl(self::TAG_URL);
        $logger = UALog::getLogger();

        $associate = $this->buildPostObject($channelId, $deviceType, $namedUser);
        $response = $this->airship->request("POST", json_encode($associate), $uri, "application/json", 3);

        $logger->info("Named channel association created successfully");
        return new PushResponse($response);
    }
}
