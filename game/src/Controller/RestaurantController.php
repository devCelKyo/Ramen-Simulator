<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Restaurant;
use App\Utils\Utils;
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
        // (1) : Retrieve the number of restaurant and check whether the user has enough slots
        $count = $user->getRestaurants()->count();
        if ($count >= $user->getRestaurantSlots()) {
            return $this->json([
                'error' => 'True',
                'message' => 'Can\t purchase another restaurant, not enough slots! Buy another.'
            ]);
        }
        // (2) : Check the user balance IF AND ONLY IF the user has already at least 1 restaurant : the first one is free
        if ($count >= 1 && $user->getMoney() < Restaurant::getPrice()) {
            return $this->json([
                'error' => 'True',
                'message' => 'Can\'t purchase another restaurant, not enough funds. ('. Utils::gmpToString(Restaurant::getPrice()) .')'
            ]);
        }

        // Arrived there, it should be okay for verifications (!), so let's construct a restaurant, add it to the user object and return a nice JSON
        $restaurant = new Restaurant();
        $restaurant->addWorkers(10); // SHOULD BE REMOVED AS SOON AS WORKERS CAN BE BOUGHT!!!!
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

    #[Route('/add_star/{restaurant_public_id}', name: 'add_star')]
    public function add_star(ManagerRegistry $doctrine, Request $request, string $restaurant_public_id): JsonResponse
    {
        /**
         * This API route should be used with a POST request containing the following keys :
         * - discord_id : string
         * - n : int
         * - restaurant_public_id1 : string
         * - restaurant_public_id2 : string
         * ...
         * - restaurant_public_idn : string 
         */
        
        // Parsing request data
        $discord_id = $request->request->get('discord_id');
        $n = $request->request->get('n');
        
        $restaurant_public_ids = array();
        for ($i = 1; $i <= $n; $i++) {
            $restaurant_public_ids[] = $request->request->get('restaurant_public_id'. $i);
        }

        // Retrieving user and restaurants and checking if they exist, if user can access them, etc.
        $user = $doctrine->getRepository(User::class)->findOneBy(['discord_id' => $discord_id]);
        if ($user == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'This user does not exist'
            ]);
        }

        $upgradeRestaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $restaurant_public_id]);
        if ($upgradeRestaurant == null) {
            return $this->json([
                'error' => 'True',
                'message' => 'You can\'t upgrade this restaurant, it doesn\'t even exist.'
            ]);
        }
        else {
            if ($upgradeRestaurant->getOwner() != $user) {
                return $this->json([
                    'error' => 'True',
                    'message' => 'You don\'t own this shop : '. $upgradeRestaurant->getPublicId()
                ]);
            }
        }

        $restaurants = array();
        foreach ($restaurant_public_ids as $current_restaurant_public_id) {
            $restaurant = $doctrine->getRepository(Restaurant::class)->findOneBy(['public_id' => $current_restaurant_public_id]);
            if ($restaurant == null) {
                return $this->json([
                    'error' => 'True',
                    'message' => 'The restaurant '. $current_restaurant_public_id . ' does not exist'
                ]);
            }
            $restaurants[] = $restaurant;
        }

        foreach ($restaurants as $restaurant) {
            if ($restaurant->getOwner() != $user) {
                return $this->json([
                    'error' => 'True',
                    'message' => 'You don\'t own this shop : '. $restaurant->getPublicId()
                ]);
            }
        }

        ////////
        // Actual gameplay checks... Finally!
        ////////

        $stars = $upgradeRestaurant->getStars();

        if ($n != $stars + 1) {
            return $this->json([
                'error' => 'True',
                'message' => 'Invalid amount of shops : '. $stars + 1 .' shops needed'
            ]);
        }

        foreach ($restaurants as $restaurant) {
            if ($restaurant->getStars() < $stars) {
                return $this->json([
                    'error' => 'True',
                    'message' => 'Operation failed! All the restaurants need to have at least '. $stars .' stars.'
                ]);
            }
        }

        if ($upgradeRestaurant->getCapacity() != 10 || $upgradeRestaurant->getQuality() != 10) {
            return $this->json([
                'error' => 'True',
                'message' => 'The restaurant to upgrade needs to have its capacity and quality levels maxxed.'
            ]);
        }

        // Now, ALL verifications are done. We may now destroy the restaurants and upgrade
        $em = $doctrine->getManager();
        foreach ($restaurants as $restaurant) {
            $em->remove($restaurant);
        }
        $upgradeRestaurant->addStar();
        $em->persist($upgradeRestaurant);
        $em->persist($user);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'stars' => $upgradeRestaurant->getStars(),
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
            'given_money' => Utils::gmpToString($given_money)
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
        
        $given_money = gmp_init(0);
        foreach ($restaurants as $restaurant) {
            $given_money = $given_money + $restaurant->claim();
            $em->persist($restaurant);
        }

        $em->persist($owner);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'given_money' => Utils::gmpToString($given_money)
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
        $added_ramen = $restaurant->refill();

        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'added_ramen' => Utils::gmpToString($added_ramen)
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
        $total_ramen_added = gmp_init(0);

        foreach ($restaurants as $restaurant) {
            $added_ramen = $restaurant->refill();
            $total_ramen_added = $total_ramen_added + $added_ramen;
            $em->persist($restaurant);
        }

        $em->flush();

        return $this->json([
            'error' => 'False',
            'total_added_ramen' => Utils::gmpToString($total_ramen_added)
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
        $owner = $restaurant->getOwner();
        if ($request_discord_id != $owner->getDiscordId()) {
            return $this->json([
                'error' => 'True',
                'message' => 'You are not allowed to manage this restaurant.'
            ]);
        }
        
        try {
            $workers_to_add = Utils::stringToGMP($request->request->get('workers_to_add'));
        }
        catch (\Exception $e) {
            return $this->json([
                'error' => 'True',
                'message' => $e->getMessage(),
            ]);
        }

        $workers_cost = $restaurant->getWorkersCost();
        $total_cost = $workers_cost * $workers_to_add;

        if ($owner->getMoney() < $total_cost) {
            return $this->json([
                'error' => 'True',
                'message' => 'You don\'t have enough money.'
            ]);
        }

        
        $workers_to_add = Utils::min($restaurant->getMaxWorkers() - $restaurant->getWorkers(), $workers_to_add);
        $total_cost = $workers_cost * $workers_to_add;
        
        // All verifications are done, we can add the workers and withdraw the money
        $restaurant->addWorkers($workers_to_add);
        $owner->withdrawMoney($total_cost);

        $em->persist($owner);
        $em->persist($restaurant);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'added_workers' => Utils::gmpToString($workers_to_add),
            'total_cost' => Utils::gmpToString($total_cost)
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
            'upgrade_cost' => Utils::gmpToString($upgrade_cost),
            'upgrade_type' => Utils::gmpToString($upgrade_type)
        ]);
    }

    #[Route('/test', name:'test')]
    public function test(): JsonResponse
    {
        return $this->json([
            '122.1B' => Utils::gmpToString(Utils::stringToGMP('122.1B'))
        ]);
    }

    #[Route('/buy_slot/{discord_id}', name: 'buy_slot')]
    public function buy_slot(ManagerRegistry $doctrine, Request $request, string $discord_id): JsonResponse
    {
        $owner = $doctrine->getRepository(User::class)->findOneBy(['discord_id' => $discord_id]);

        if ($owner->getMoney() < $owner->getRestaurantSlotsPrice()) {
            return $this->json([
                'error' => 'True',
                'message' => 'Insufficient funds'
            ]);
        }

        $cost = $owner->getRestaurantSlotsPrice();
        $owner->addRestaurantSlot();
        $owner->withdrawMoney($cost);

        $em = $doctrine->getManager();
        $em->persist($owner);
        $em->flush();

        return $this->json([
            'error' => 'False',
            'slots' => $owner->getRestaurantSlots(),
            'cost' => Utils::gmpToString($cost)
        ]);
    }

    // ADMIN ROUTE FOR PATCH 1.5 TO BE DELETED
    #[Route('/workers_fix', name: 'workers_fix')]
    public function workers_fix(ManagerRegistry $doctrine): JsonResponse
    {
        $em = $doctrine->getManager();
        $restaurants = $doctrine->getRepository(Restaurant::class)->findAll();

        foreach($restaurants as $restaurant) {
            $restaurant->setWorkers($restaurant->getMaxWorkers());    
            $em->persist($restaurant);
        }

        $em->flush();

        return $this->json([
            'error' => 'False',
            'message' => 'OK'
        ]);
    }
}
