<?php

namespace Bundle\Controller\Mobile\Plusshop;


use Bundle\Component\PlusShop\WeatherWidget\WeatherWidgetConfig;
use Bundle\Component\PlusShop\WeatherWidget\WeatherWidgetDao;
use Bundle\Controller\Mobile\Controller;
use Request;

class WeatherWidgetController extends Controller
{
    private $config;

    public function __construct()
    {
        parent::__construct();

        $this->config = new WeatherWidgetConfig();
    }

    public function index()
    {
        $method = Request::post()->get('method');
        $baseLocation = Request::post()->get('base_location');

        if ($method == 'cookie') {
            $this->setCookie($baseLocation);
        }

        if (\Cookie::get('base_location')) {
            $baseLocation = \Cookie::get('base_location');
        } else {
            $dao = new WeatherWidgetDao();
            $data = $dao->getData();
            $baseLocation = $data['base_location'];
        }

        $this->json([
            'weather' => $this->config->getWeather($baseLocation),
            'location' => $this->config->getLocationKeys(),
            'base_location' => $baseLocation
        ]);
    }

    private function setCookie($baseLocation)
    {
        \Cookie::set('base_location', $baseLocation);
    }
}
