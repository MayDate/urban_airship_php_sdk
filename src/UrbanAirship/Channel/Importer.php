<?php
namespace UrbanAirship\Channel;

use UrbanAirship\Push\PushResponse;
use UrbanAirship\UALog;

class Importer
{
    const TAG_URL = "/api/channels/import";
    const DEVICE_TYPE_IOS = 'ios';
    const DEVICE_TYPE_ANDROID = 'android';
    const MAX_SINGLE_IMPORT = 200;

    private $airship;
    private $payload;
    private $addressesToImport;

    public function __construct($airship)
    {
        $this->airship = $airship;
        $this->payload = array();
        $this->addressesToImport = array();
    }

    private function buildPostObject($deviceType, $pushAddress, $namedUserId = '', $optIn = true, $tags = array())
    {
        $payload = new \stdClass();
        $payload->device_type = $deviceType;
        $payload->opt_in = $optIn;
        $payload->push_address = $pushAddress;

        if (! empty($namedUserId)) {
            $payload->named_user_id = $namedUserId;
        }

        if (!empty($tags)) {
            $payload->set_tags = true;
            $payload->tags = $tags;
        }

        return $payload;
    }

    public function add($deviceType, $pushAddress, $namedUserId = '', $optIn = true, $tags = array())
    {
        if (count($this->addressesToImport) >= self::MAX_SINGLE_IMPORT) {
            throw new \Exception("Maximum number (" . self::MAX_SINGLE_IMPORT . ") of addresses has been reached.");
        }

        $this->addressesToImport[] = $this->buildPostObject($deviceType, $pushAddress, $namedUserId, $optIn, $tags);
        return $this;
    }

    public function import()
    {
        if (empty($this->addressesToImport)) {
            throw new \Exception("Unable to import an empty list");
        }

        $uri = $this->airship->buildUrl(self::TAG_URL);
        $logger = UALog::getLogger();

        $response = $this->airship->request("POST", json_encode($this->addressesToImport), $uri, "application/json", 3);

        $logger->info("Import completed successfully");
        return new PushResponse($response);
    }
}
