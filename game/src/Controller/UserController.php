<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/create_user', name: 'create_user')]
    public function create_user(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        // Retrieve the POST request discord_id argument
        $discord_id = $request->request->get('discord_id');
        
        // Using the EntityManager to create a user with this discord_id
        $em = $doctrine->getManager();

        $user = new User();
        $user->setDiscordId($discord_id);
        $user->setMoney(50);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'User created successfully!',
            'error' => 'False'
        ]);
    }

    #[Route('/get_user/{discord_id}', name: 'get_user')]
    public function get_user(ManagerRegistry $doctrine, string $discord_id): JsonResponse
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneBy(['discord_id' => $discord_id]);

        if ($user == null) {
            $json = $this->json([
                'message' => 'No user has this discord_id',
                'error' => 'True'
            ]);
        }
        else {
            $json = $this->json([
                'user' => $user->jsonSerialize(),
                'message' => 'User found',
                'error' => 'False'
            ]);
        }

        return $json;
    }

    #[Route('/claim_daily', name : 'claim_daily')]
    public function claim_daily(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $em = $doctrine->getManager();
        $repository = $doctrine->getRepository(User::class);

        // Retrieve user based on request parameters
        $discord_id = $request->request->get('discord_id');
        $user = $repository->findOneBy(['discord_id' => $discord_id]);

        // Check time difference to see if claim is available
        $CLAIM_DELAY = 12; // hours
        $CLAIM_MONEY = 50; // in-game currency

        $last_claim = $user->getLastDailyClaim();
        $now = new \DateTime("now");

        if ($last_claim == null) {
            $last_claim = new \DateTime("01-01-1970");    
        }

        $diff = $last_claim->diff($now);
        // Let's convert diff to plain seconds, then hours by multiplying by 3600
        $diff = date_create('@0')->add($diff)->getTimestamp() / 3600;
    
        if ($diff >= $CLAIM_DELAY) {
            $user->addMoney($CLAIM_MONEY);
            $user->setLastDailyClaim($now);
            $em->persist($user);

            $json = $this->json([
                'money_given' => $CLAIM_MONEY,
                'error' => 'False'
            ]);
        }
        else {
            $next_claim = $last_claim->add(\DateInterval::createFromDateString($CLAIM_DELAY . " hours"));
            $time_remaining = $now->diff($next_claim);
            $json = $this->json([
                'time_remaining' => $time_remaining->format("%h hours %i minutes %s seconds"),
                'error' => 'True'
            ]);
        }
        $em->flush();

        return $json;
    }
}
