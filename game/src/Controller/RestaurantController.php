<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Restaurant;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/get_restaurant/{restaurant_public_id}', name: 'get_restaurant')]
    public function get_restaurant(ManagerRegistry $doctrine, Request $request, string $restaurant_public_id): JsonResponse
    {
        $restaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $restaurant_public_id]);
        if ($restaurant == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No restaurant has this ID.'
            ]);
        }
        
        $user_id = $request->request->get('discord_id');
        $owner_id = $restaurant->getOwner()->getDiscordId();

        if ($owner_id != $user_id) {
            return $this->json([
                'error' => 'True',
                'message' => 'You are not allowed to see this restaurant'
            ]);
        }

        $restaurant->update();
        return $this->json([
            'error' => 'False',
            'restaurant' => $restaurant->jsonSerialize()
        ]);
    }

    #[Route('/add_restaurant/{discord_id}', name: 'add_restaurant')]
    public function add_restaurant(ManagerRegistry $doctrine, string $discord_id): JsonResponse
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
        $restaurant->setWorkers(10); // SHOULD BE REMOVED AS SOON AS WORKERS CAN BE BOUGHT!!!!
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

    #[Route('/update_restaurant/{restaurant_public_id}', name: 'update_restaurant')]
    public function update_restaurant(ManagerRegistry $doctrine, string $restaurant_public_id): JsonResponse
    {
        $em = $doctrine->getManager();
        $restaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $restaurant_public_id]);
        if ($restaurant == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No restaurant has this ID.'
            ]);
        }

        $restaurant->update();
        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False'
        ]);
    }

    #[Route('/update_restaurants/{discord_id}', name: 'update_restaurants')]
    public function update_restaurants(ManagerRegistry $doctrine, string $discord_id): JsonResponse
    {
        $em = $doctrine->getManager();
        $owner = $doctrine->getRepository(User::class)->findOneBy(['discord_id' => $discord_id]);
        if ($owner == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'This user is not registered or does not exist.'
            ]);
        }

        $restaurants = $owner->getRestaurants();
        foreach ($restaurants as $restaurant) {
            $restaurant->update();
            $em->persist($restaurant);
        }

        $em->flush();

        return $this->json([
            'error' => 'False'
        ]);
    }

    #[Route('/claim_restaurant/{restaurant_public_id}', name: 'claim_restaurant')]
    public function claim_restaurant(ManagerRegistry $doctrine, string $restaurant_public_id): JsonResponse
    {
        $em = $doctrine->getManager();
        $restaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $restaurant_public_id]);
        if ($restaurant == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No restaurant has this ID.'
            ]);
        }

        $owner = $restaurant->getOwner();
        $given_money = $restaurant->claim();
        $em->persist($owner);
        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'given_money' => $given_money
        ]);
    }

    #[Route('/claim_restaurants/{discord_id}', name: 'claim_restaurants')]
    public function claim_restaurants(ManagerRegistry $doctrine, string $discord_id): JsonResponse
    {
        $em = $doctrine->getManager();
        $owner = $doctrine->getRepository(User::class)->findOneBy(['discord_id' => $discord_id]);
        if ($owner == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'This user is not registered or does not exist.'
            ]);
        }

        $restaurants = $owner->getRestaurants();
        
        $given_money = 0;
        foreach ($restaurants as $restaurant) {
            $given_money += $restaurant->claim();
            $em->persist($restaurant);
        }

        $em->persist($owner);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'given_money' => $given_money
        ]);
    }

    #[Route('/refill_restaurant/{restaurant_public_id}', name: 'refill_restaurant')]
    public function refill_restaurant(ManagerRegistry $doctrine, string $restaurant_public_id): JsonResponse
    {
        $em = $doctrine->getManager();
        $restaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $restaurant_public_id]);
        if ($restaurant == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No restaurant has this ID'
            ]);
        }

        $owner = $restaurant->getOwner();
        
        $ramen_cost = Restaurant::RAMEN_COST;
        $max_ramen_to_add = $restaurant->getStorage() - $restaurant->getRamenStored();
        if ($ramen_cost * $max_ramen_to_add <= $owner->getMoney()) {
            $added_ramen = $max_ramen_to_add;
        }
        else {
            $added_ramen = intdiv($owner->getMoney(), $ramen_cost);
        }
        $restaurant->addRamenStored($added_ramen);

        $em->persist($owner);
        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'added_ramen' => $added_ramen,
            'cost' => $added_ramen * $ramen_cost
        ]);
    }

    #[Route('/refill_restaurants/{discord_id}', name: 'refill_restaurants')]
    public function refill_restaurants(ManagerRegistry $doctrine, string $discord_id): JsonResponse
    {
        $em = $doctrine->getManager();
        $owner = $doctrine->getRepository(User::class)->findOneBy(['discord_id' => $discord_id]);
        if ($owner == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'This user is not registered or does not exist.'
            ]);
        }
        $restaurants = $owner->getRestaurants();
        $total_cost = 0;
        $total_ramen_added = 0;
        $ramen_cost = Restaurant::RAMEN_COST;

        foreach ($restaurants as $restaurant) {
            $max_ramen_to_add = $restaurant->getStorage() - $restaurant->getRamenStored();
            if ($ramen_cost * $max_ramen_to_add <= $owner->getMoney()) {
                $added_ramen = $max_ramen_to_add;
            }
            else {
                $added_ramen = intval(fdiv($owner->getMoney(), $ramen_cost));
            }
            $cost = $added_ramen * $ramen_cost;
            $restaurant->addRamenStored($added_ramen);
            $owner->withdrawMoney($cost);

            $total_ramen_added += $added_ramen;
            $total_cost += $cost;

            $em->persist($restaurant);
        }

        $em->persist($owner);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'total_added_ramen' => $total_ramen_added,
            'total_cost' => $total_cost
        ]);
    }

    #[Route('/add_workers/{restaurant_public_id}', name: 'add_workers')]
    public function add_workers(ManagerRegistry $doctrine, Request $request, string $restaurant_public_id): JsonResponse
    {
        /**
         * This route should be used by sending a POST request containing a JSON with the keys 'discord_id'
         * and 'workers_to_add'
         */

        $em = $doctrine->getManager();
        $restaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $restaurant_public_id]);
        if ($restaurant == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No restaurant has this ID.'
            ]);
        }
        $request_discord_id = $request->request->get('discord_id');
        $owner = $restaurant->getOwner(); // should be verified with discord_id key
        if ($request_discord_id != $owner->getDiscordId()) {
            return $this->json([
                'error' => 'True',
                'message' => 'You are not allowed to manage this restaurant.'
            ]);
        }

        $workers_to_add = $request->request->get('workers_to_add');
        $workers_cost = $restaurant->getWorkersCost();
        $total_cost = $workers_cost * $workers_to_add;

        if ($owner->getMoney() < $total_cost) {
            return $this->json([
                'error' => 'True',
                'message' => 'You don\'t have enough money.'
            ]);
        }

        // All verifications are done, we can add the workers and withdraw the money
        $restaurant->addWorkers($workers_to_add);
        $owner->withdrawMoney($total_cost);

        $em->persist($owner);
        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'added_workers' => $workers_to_add,
            'total_cost' => $total_cost
        ]);
    }

    #[Route('/upgrade/{restaurant_public_id}/{upgrade_type}', name: 'upgrade_capacity')]
    public function upgrade(ManagerRegistry $doctrine, string $restaurant_public_id, string $upgrade_type): JsonResponse
    {
        $em = $doctrine->getManager();
        $restaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $restaurant_public_id]);
        if ($restaurant == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'No restaurant has this ID.'
            ]);
        }
        $restaurant->update();
        $owner = $restaurant->getOwner();
        if ($upgrade_type == 'capacity') {
           $upgrade_cost = $restaurant->getUpgradeCapacityPrice();
        }
        else {
            $upgrade_cost = $restaurant->getUpgradeQualityPrice();
        }

        if ($owner->getMoney() < $upgrade_cost) {
            return $this->json([
                'error' => 'True',
                'message' => 'You don\'t have enough money.'
            ]);
        }
        
        // All verifications done, so lets withdraw money, yada yada yada
        if ($upgrade_type == 'capacity') {
            $restaurant->upgradeCapacity();
            $owner->withdrawMoney($upgrade_cost);
        }
        else {  
            $restaurant->upgradeQuality();
            $owner->withdrawMoney($upgrade_cost);
        }

        $em->persist($owner);
        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'upgrade_cost' => $upgrade_cost,
            'upgrade_type' => $upgrade_type
        ]);
    }

}
