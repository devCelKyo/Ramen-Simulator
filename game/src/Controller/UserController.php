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
}
