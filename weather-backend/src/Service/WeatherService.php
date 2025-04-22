<?php

namespace App\Service;

use App\Entity\Weather;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $em)
    {
        $this->httpClient = $httpClient;
        $this->em = $em;
    }

    public function fetchAndSaveWeather(string $city = "Cergy"): ?Weather
    {
        
        $latitude = 49.0365;
        $longitude = 2.0761;

       
        $url = "https://api.open-meteo.com/v1/forecast?latitude=$latitude&longitude=$longitude&current_weather=true";
        $response = $this->httpClient->request('GET', $url);

        $data = $response->toArray();

        if (!isset($data['current_weather'])) {
            return null;
        }

        $weather = new Weather();
        $weather->setCity($city);
        $weather->setTemperature($data['current_weather']['temperature']);
        $weather->setHumidity(50); // Valeur fictive (API gratuite ne la donne pas)
        $weather->setWindSpeed($data['current_weather']['windspeed']);
        $weather->setDate(new \DateTime());

        $this->em->persist($weather);
        $this->em->flush();

        return $weather;
    }
}
