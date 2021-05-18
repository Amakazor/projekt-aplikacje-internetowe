<?php
namespace App\DataFixtures;
use App\Entity\Company;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        /** @var Company $company */
        $company = $this->getReference('company');

        $user = new User();
        $user->setUsername('testowy');
        $user->setPassword($this->passwordEncoder->encodePassword($user,'the_new_password'));
        $user->setCompany($company);
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setFirstname('tester');
        $user->setLastname('testowski');

        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class
        ];
    }
}
