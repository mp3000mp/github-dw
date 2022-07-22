<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PackageTypeFile;
use App\Entity\Repository;
use App\Entity\RepositoryPackage;
use App\Entity\RepositoryPackageTypeFile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DwFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $packageType = new PackageTypeFile();
        $packageType->setName('composer');
        $packageType->setLanguage('PHP');
        $packageType->setFile('composer.json');
        $packageType->setGithubCurrentSize(100);
        $packageType->setGithubCurrentPage(4);
        $packageType->setPriority(true);
        $packageType->setUpdatedAt(new \DateTime('2022-07-22 00:00:00'));
        $manager->persist($packageType);

        $repoA = new Repository();
        $repoA->setName('nameA');
        $repoA->setUsername('usernameA');
        $repoA->setUrl('https://a.github.com');
        $repoA->setRoutine1At(new \DateTime('2022-07-22 02:00:00'));
        $manager->persist($repoA);

        $repoB = new Repository();
        $repoB->setName('nameA');
        $repoB->setUsername('usernameA');
        $repoB->setUrl('https://a.github.com');
        $repoB->setRoutine1At(new \DateTime('2022-07-22 02:00:00'));
        $manager->persist($repoB);

        $repoPackageTypeFile = new RepositoryPackageTypeFile();
        $repoPackageTypeFile->setRoutine1At(new \DateTime(''));
        $repoPackageTypeFile->setPath('path/a');
        $repoPackageTypeFile->setSha('sha_a');
        $repoPackageTypeFile->setRepository($repoA);
        $repoPackageTypeFile->setPackageTypeFile($packageType);
        $manager->persist($repoPackageTypeFile);

        $repoPackage = new RepositoryPackage();
        $repoPackage->setName('repoPackageA');
        $repoPackage->setVersionStr('^1.1.0');
        $repoPackage->setVersionMinMajor(1);
        $repoPackage->setVersionMinMinor(1);
        $repoPackage->setVersionMinPatch(0);
        $repoPackage->setVersionMaxMajor(2);
        $repoPackage->setVersionMaxMinor(0);
        $repoPackage->setVersionMaxPatch(0);
        $repoPackage->setValid(true);
        $repoPackage->setRepositoryPackageTypeFile($repoPackageTypeFile);
        $manager->persist($repoPackage);

        $manager->flush();
    }
}
