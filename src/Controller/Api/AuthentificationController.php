<?php

namespace App\Controller\Api;
use App\Service\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Entity\User;
use App\Service\JsonResponseHelper;

#[Route('/api/')]
class AuthentificationController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $jwtManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager  // inject interface JWTTokenManagerInterface
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }


    #[Route('token ', name: 'app_token')]
    public function login(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        

        if (!$email || !$password) {
             return JsonResponseHelper::forbidden('Email and possword are required');
        }
    
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return JsonResponseHelper::unauthorized('Invalid credentials.');
        }
    
        // generate token if identifiant is ok
        $jwt = $this->jwtManager->create($user);
        return JsonResponseHelper::success(['token' => $jwt], 'token is generated');
    }

    #[Route('account', name: 'app_account', methods: ['POST'])]
    public function createUser(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
                                {
                                    $em = $doctrine->getManager();
                                    $decoded = json_decode($request->getContent());
                                    $email = $decoded->email;
                                    $firstname = $decoded->firstname;
                                    $plaintextPassword = $decoded->password;
                               
                                    $user = new User();
                                    $hashedPassword = $passwordHasher->hashPassword(
                                        $user,
                                        $plaintextPassword
                                    );
                                    $user->setPassword($hashedPassword);
                                    $user->setEmail($email);
                                    $user->setUsername($email);
                                    $user->setFirstName($firstname);
                                    try {
                                        $em->persist($user);
                                        $em->flush();

                                        return JsonResponseHelper::success([], 'User added successfully.');
                                    } 
                                    catch (\Exception $e) {
                                        return JsonResponseHelper::exceptionMessage('Error: ' . $e->getMessage());
                                    }
                            
                                }



    #[Route('test', name: 'app_test')]
    public function apiTest(): Response{
        return new Response('you are connected');
    }

}
