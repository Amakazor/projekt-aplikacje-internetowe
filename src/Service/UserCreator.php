<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreator
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserCreator constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface $tokenStorage
     * @param UserRepository $userRepository
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenStorageInterface $tokenStorage, UserRepository $userRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    /**
     * Finalizes user creation
     * @param User $partialUser
     * @return User finalized user
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function finishUserCreation(User $partialUser): User
    {
        $partialUser->setCompany($this->tokenStorage->getToken()->getUser()->getCompany());
        $this->generateLogin($partialUser);
        $this->generatePassword($partialUser);
        $partialUser->setIsVerified(false);
        return $partialUser;
    }

    /**
     * @param User $user
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function generateLogin(User $user): void
    {
        $login = str_replace(' ', '_',$this->mb_ucfirst(mb_strtolower($this->tokenStorage->getToken()->getUser()->getCompany()->getName())) . '_' . $this->mb_ucfirst(mb_strtolower($user->getfirstName())) . '_' . $this->mb_ucfirst(mb_strtolower($user->getlastName())));

        if ($this->userRepository->doesUsernameExist($login)) {
            $i = 1;

            do {
                $i++;
            } while ($this->userRepository->doesUsernameExist($login . $i));

            $login .= $i;
        }

        $user->setUsername($login);
    }

    private function generatePassword(User $user): void
    {
        $user->setPlainPassword(sha1(random_bytes(20)));
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
    }

    /**
     * @param string $string
     * @return string
     */
    private function mb_ucfirst($string): string
    {
        return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
    }
}