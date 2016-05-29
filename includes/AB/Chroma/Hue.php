<?php
/**
 * Interface to the Hue system itself. This encapsulates all of the network API
 * stuff.
 *
 * PHP Version 5
 *
 * @category  Glow
 * @package   Glow
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2016 All Rights Reserved
 * @license   GNU GPLv3
 * @version   GIT: $Id$
 * @link      http://github.com/aaronbieber/glow
 */
namespace AB\Chroma;

class Hue
{
    protected static $instance = null;
    protected $config;
    protected $bridge_ip;
    protected $api_key;

    public function __construct()
    {
        $this->config    = Config::getInstance();
        $this->bridge_ip = $this->config->get('bridge_ip');
        $this->api_key   = $this->config->get('api_key');
    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function getServiceUrl($path = '/')
    {
        if (substr($path, 0, 1) != '/') {
            $path = "/$path";
        }

        return 'http://' . $this->bridge_ip . '/api/' . $this->api_key . $path;
    }

    public function setLightState($light_id, array $state)
    {
        // Translate values.
        if (isset($state['power'])) {
            $state['on'] = (bool) $state['power'];
            unset($state['power']);
        }
        $state_json = \json_encode($state);

        $service_url = $this->getServiceUrl('/lights/' . $light_id . '/state');
        $ch = curl_init($service_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $state_json);

        $response = curl_exec($ch);

        if ($response === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        return true;
    }

    public function getLight($light_id)
    {
        $service_url = $this->getServiceUrl('/lights/' . $light_id . '/');
        $ch = curl_init($service_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $response = curl_exec($ch);
        if ($response === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        $light_data = \json_decode($response, true);

        if (!empty($light_state['state'])) {
            // Translate values.
            if (isset($light_state['state']['on'])) {
                $light_state['state']['power'] = $light_state['state']['on'];
                unset($light_state['state']['on']);
            }
        }

        if (!empty($light_data)) {
            return $light_data;
        }

        return false;
    }

    public function getLightState($light_id)
    {
        $light_data = $this->getLight($light_id);

        if (!empty($light_data['state'])) {
            return $light_data['state'];
        }

        return false;
    }

    public function getLights()
    {
        $service_url = $this->getServiceUrl('/lights/');
        $ch = curl_init($service_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $response = curl_exec($ch);
        if ($response === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        return \json_decode($response, true);
    }
}
