<?php

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\Company;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CarFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Company $company */
        $company = $this->getReference('company');

        $car = new Car();
        $car->setCompany($company);
        $car->setBrand('Volkswagen');
        $car->setModel('Passat B5');
        $car->setHorsepower('101');
        $car->setEngine('1.9 TDI');
        $car->setColor('Czarny');
        $car->setDescription('Bardzo ładny samochód');
        $car->setimage(' ');
        $car->setYear(new DateTimeImmutable('2002-01-01'));
        $manager->persist($car);

        $car = new Car();
        $car->setCompany($company);
        $car->setBrand('Audi');
        $car->setModel('A4 B9');
        $car->setHorsepower('149');
        $car->setEngine('1.9');
        $car->setColor('Biały');
        $car->setDescription('Jeszcze ładniejszy samochód');
        $car->setimage(' ');
        $car->setYear(new DateTimeImmutable('2020-01-01'));
        $manager->persist($car);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class
        ];
    }
}
