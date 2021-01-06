<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordAction
{
    private ValidatorInterface $validator;
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $JWTTokenManager;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $JWTTokenManager
    )
    {
        $this->validator = $validator;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->entityManager = $entityManager;
        $this->JWTTokenManager = $JWTTokenManager;
    }

    public function __invoke(User $data): JsonResponse
    {
        $this->validator->validate($data);
        $data->setPassword($this->userPasswordEncoder->encodePassword($data, $data->getNewPassword()));

        $data->setPasswordChangeDate(time() );

        $this->entityManager->flush();
        $token = $this->JWTTokenManager->create($data);

        return new JsonResponse(['token' => $token]);
    }
}