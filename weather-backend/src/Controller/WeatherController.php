<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\WeatherRepository;


class WeatherController extends AbstractController
{

    #[Route('/api/weather', name: 'weather_create', methods: ['POST'], defaults: ['_format' => 'json'])]
    public function createWeather(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = $request->getContent();
    
        // Désérialisation des données JSON 
        $weather = $serializer->deserialize($data, Weather::class, 'json');
    
       
        $errors = $validator->validate($weather);
        if (count($errors) > 0) {
            
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
    
        
        $em->persist($weather);
        $em->flush();
    
      
        return $this->json(['message' => 'Successfully created !'], Response::HTTP_CREATED);
    }
    
#[Route('/api/weather/{id}', name: 'weather_update', methods: ['PATCH'])]
public function updateWeather(
    int $id,
    Request $request,
    WeatherRepository $repository,
    EntityManagerInterface $em,
    SerializerInterface $serializer
): JsonResponse {
    $weather = $repository->find($id);
    if (!$weather) {
        return $this->json(['message' => 'No data'], Response::HTTP_NOT_FOUND);
    }

    $serializer->deserialize(
        $request->getContent(),
        Weather::class,
        'json',
        ['object_to_populate' => $weather]
    );

    $em->flush();

    return $this->json(['message' => 'Successfully updated !']);
}

#[Route('/api/weather/{id}', name: 'weather_delete', methods: ['DELETE'])]
public function deleteWeather(
    int $id,
    WeatherRepository $repository,
    EntityManagerInterface $em
): JsonResponse {
    $weather = $repository->find($id);
    if (!$weather) {
        return $this->json(['message' => 'No data'], Response::HTTP_NOT_FOUND);
    }

    $em->remove($weather);
    $em->flush();

    return $this->json(['message' => 'Successfully deleted!']);
}
    
    #[Route('/api/fetch-weather', name: 'fetch_weather', methods: ['GET'])]
    public function fetchWeather(WeatherService $weatherService): JsonResponse
    {
        $weather = $weatherService->fetchAndSaveWeather("Paris");

        return $this->json([
            'message' => $weather ? 'data saved' : 'Error',
        ]);
    }

 

#[Route('/api/weather_data', name: 'weather_list', methods: ['GET'])]
public function listWeather(WeatherRepository $repository): JsonResponse
{
    $weathers = $repository->findAll();
    $data = [];

    foreach ($weathers as $weather) {
        $data[] = [
            'id' => $weather->getId(),
            'city' => $weather->getCity(),
            'temperature' => $weather->getTemperature(),
            'humidity' => $weather->getHumidity(),
            'windSpeed' => $weather->getWindSpeed(),
            'date' => $weather->getDate()->format('Y-m-d H:i:s'),
        ];
    }

    return $this->json($data);
}

}


