<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Restaurant;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/restaurants', name: 'restaurants')]
class RestaurantController extends AbstractController
{
    #[Route('/get_restaurants/{discord_id}', name: 'get_restaurants')]
    public function get_restaurants(ManagerRegistry $doctrine, string $discord_id): JsonResponse
    {
        $user = $doctrine->getRepository(User::class)->findOneBy(['discord_id' => $discord_id]);
        if ($user == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No user has this discord_id'
            ]);
        }

        $restaurants = $user->getRestaurants();
        $jsonRestaurants = [];
        foreach($restaurants as $restaurant) {
            $jsonRestaurants[] = $restaurant->jsonSerialize();
        }

        return $this->json([
            'error' => 'False',
            'restaurants' => $jsonRestaurants
        ]);
    }

    #[Route('/add_restaurant/{discord_id}', name: 'add_restaurant')]
    public function add_restaurants(ManagerRegistry $doctrine, string $discord_id): JsonResponse
    {
        $user = $doctrine->getRepository(User::class)->findOneBy(['discord_id' => $discord_id]);
        if ($user == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No user has this discord_id'
            ]);
        }

        // First, let's check whether the user is allowed to get another restaurant
        // (1) : Retrieve the number of restaurant the user owns and figure whether they have enough rebirths
        $count = $user->getRestaurants()->count();
        if ($user->getRebirth() + 1 <= $count) {
            return $this->json([
                'error' => 'True',
                'message' => 'Can\'t purchase another restaurant, no enough rebirths.'
            ]);
        }

        // (2) : Check the user balance IF AND ONLY IF the user has already at least 1 restaurant : the first one is free
        if ($count >= 1 && $user->getMoney() < Restaurant::PRICE) {
            return $this->json([
                'error' => 'True',
                'message' => 'Can\'t purchase another restaurant, not enough funds.'
            ]);
        }

        // Arrived there, it should be okay for verifications (!), so let's construct a restaurant, add it to the user object and return a nice JSON
        $restaurant = new Restaurant();
        $restaurant->setCapacity(1);
        $restaurant->setQuality(1);
        $restaurant->setRamenStored(0);
        $restaurant->setWorkers(0);

        $user->addRestaurant($restaurant);
        // Let's not forget to actually withdraw the money (only if it's not the first restaurant)
        if ($count >= 1) {
            $user->withdrawMoney(Restaurant::PRICE);
        }

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'restaurant_shard' => $restaurant->getPublicId()
        ]);
    }
}
