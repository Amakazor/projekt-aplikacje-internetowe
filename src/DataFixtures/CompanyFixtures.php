<?php

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $company = new Company();
        $company->setName('Firma Testowa');
        $company->setAddress('Warszawa, ul. warszawska 102');
        $company->setIdentifier('423523532532532235');

        $manager->persist($company);
        $manager->flush();

        $this->addReference('company', $company);
    }
}
