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
        $packagePhp = new PackageTypeFile();
        $packagePhp->setName('composer');
        $packagePhp->setLanguage('PHP');
        $packagePhp->setFile('composer.json');
        $packagePhp->setGithubCurrentSize(100);
        $packagePhp->setGithubCurrentPage(4);
        $packagePhp->setPriority(true);
        $packagePhp->setUpdatedAt(new \DateTime('2022-07-22 00:00:00'));
        $manager->persist($packagePhp);

        $packageJs = new PackageTypeFile();
        $packageJs->setName('npm');
        $packageJs->setLanguage('Javascript');
        $packageJs->setFile('package.json');
        $packageJs->setGithubCurrentSize(100);
        $packageJs->setGithubCurrentPage(1);
        $packageJs->setPriority(false);
        $packageJs->setUpdatedAt(new \DateTime('2022-07-22 00:00:00'));
        $manager->persist($packageJs);

        $repoA = new Repository();
        $repoA->setName('nameA');
        $repoA->setUsername('usernameA');
        $repoA->setUrl('https://a.github.com');
        $repoA->setRoutine1At(new \DateTime('2022-07-22 02:00:00'));
        $repoA->setRoutine2At(new \DateTime('2022-07-22 02:30:00'));
        $manager->persist($repoA);

        $repoB = new Repository();
        $repoB->setName('nameB');
        $repoB->setUsername('usernameB');
        $repoB->setUrl('https://b.github.com');
        $repoB->setRoutine1At(new \DateTime('2022-07-22 04:00:00'));
        $repoA->setRoutine2At(new \DateTime('2022-07-22 04:30:00'));
        $manager->persist($repoB);

        $repoPackageTypeFile = new RepositoryPackageTypeFile();
        $repoPackageTypeFile->setRoutine1At(new \DateTime('2022-07-22 02:30:00'));
        $repoPackageTypeFile->setPath('path/a');
        $repoPackageTypeFile->setSha('sha_a');
        $repoPackageTypeFile->setRepository($repoA);
        $repoPackageTypeFile->setPackageTypeFile($packagePhp);
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

        $repoPackage = clone $repoPackage;
        $repoPackage->setName('repoPackageB');
        $manager->persist($repoPackage);

        // errors
        $repoC = new Repository();
        $repoC->setName('nameC');
        $repoC->setUsername('usernameC');
        $repoC->setRoutineError('error2');
        $repoC->setUrl('https://c.github.com');
        $repoC->setRoutine1At(new \DateTime('2022-07-22 06:00:00'));
        $manager->persist($repoC);

        $repoPackageTypeFile = new RepositoryPackageTypeFile();
        $repoPackageTypeFile->setRoutine1At(new \DateTime('2022-07-22 04:30:00'));
        $repoPackageTypeFile->setPath('path/b');
        $repoPackageTypeFile->setSha('sha_b');
        $repoPackageTypeFile->setRepository($repoB);
        $repoPackageTypeFile->setPackageTypeFile($packagePhp);
        $repoPackageTypeFile->setRoutineError('error3');
        $manager->persist($repoPackageTypeFile);

        $manager->flush();
    }
}
