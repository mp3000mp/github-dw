<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Package;
use App\Entity\PackageTypeFile;
use App\Entity\Repository;
use App\Entity\RepositoryPackage;
use App\Entity\RepositoryPackageTypeFile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DwFixtures extends Fixture
{
    /** @var PackageTypeFile[] */
    private array $packageTypeFiles;
    /** @var Repository[] */
    private array $repositories;
    /** @var RepositoryPackageTypeFile[] */
    private array $repositoryPackageTypeFiles;
    /** @var Package[] */
    private array $packages;
    /** @var RepositoryPackage[] */
    /** @phpstan-ignore-next-line */
    private array $repositoryPackages;

    private ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;
        $this->packageTypeFiles = [];
        $this->repositories = [];
        $this->repositoryPackageTypeFiles = [];
        $this->packages = [];
        $this->repositoryPackages = [];

        // routine1
        $this->packageTypeFiles['PHP'] = new PackageTypeFile();
        $this->packageTypeFiles['PHP']->setName('composer');
        $this->packageTypeFiles['PHP']->setLanguage('PHP');
        $this->packageTypeFiles['PHP']->setFile('composer.json');
        $this->packageTypeFiles['PHP']->setGithubCurrentSize(100);
        $this->packageTypeFiles['PHP']->setGithubCurrentPage(4);
        $this->packageTypeFiles['PHP']->setPriority(true);
        $this->packageTypeFiles['PHP']->setUpdatedAt(new \DateTime('2022-07-22 00:00:00'));
        $manager->persist($this->packageTypeFiles['PHP']);
        $this->packageTypeFiles['js'] = new PackageTypeFile();
        $this->packageTypeFiles['js']->setName('npm');
        $this->packageTypeFiles['js']->setLanguage('js');
        $this->packageTypeFiles['js']->setFile('package.json');
        $this->packageTypeFiles['js']->setGithubCurrentSize(100);
        $this->packageTypeFiles['js']->setGithubCurrentPage(1);
        $this->packageTypeFiles['js']->setPriority(false);
        $this->packageTypeFiles['js']->setUpdatedAt(new \DateTime('2022-07-22 00:00:00'));
        $manager->persist($this->packageTypeFiles['js']);

        // repos
        $this->createRepository('A');
        $this->createRepository('B');
        $this->createRepository('C');

        // blob
        $this->createRepositoryPackageTypeFile('A', 'PHP');
        $this->createRepositoryPackageTypeFile('A', 'js');
        $this->createRepositoryPackageTypeFile('B', 'PHP');
        $this->createRepositoryPackageTypeFile('C', 'PHP');
        $this->createRepositoryPackageTypeFile('C', 'js');

        // packages
        $this->createPackage('A', 'PHP');
        $this->createPackage('B', 'PHP');
        $this->createPackage('C', 'js');

        // repository packages
        $this->createRepositoryPackage('A', 'A', '1.0.0', '2.0.0');
        $this->createRepositoryPackage('A', 'B', '1.5.5', '1.7.0');
        $this->createRepositoryPackage('A', 'C', '2.5.0', '3.0.0');
        $this->createRepositoryPackage('B', 'A', '1.0.0', '2.0.0');
        $this->createRepositoryPackage('C', 'A', '1.0.0', '2.0.0');
        $this->createRepositoryPackage('C', 'B', '1.0.0', '2.0.0');
        $this->createRepositoryPackage('C', 'C', '1.0.0', '2.0.0');

        // not done
        $this->createRepository('T');
        $this->repositories['T']->setRoutine2At(null);
        $this->createRepositoryPackageTypeFile('T', 'PHP');
        $this->repositoryPackageTypeFiles['T_PHP']->setRoutine3At(null);

        // errors
        $this->repositories['err'] = new Repository();
        $this->repositories['err']->setRoutine1At(new \DateTime('-3 days'));
        $this->repositories['err']->setRoutine2At(new \DateTime('-1 days'));
        $this->repositories['err']->setName('nameErr');
        $this->repositories['err']->setUsername('usernameErr');
        $this->repositories['err']->setRoutineError('error2');
        $this->repositories['err']->setUrl('https://err.github.com');
        $manager->persist($this->repositories['err']);

        $this->repositoryPackageTypeFiles['err_err'] = new RepositoryPackageTypeFile();
        $this->repositoryPackageTypeFiles['err_err']->setRoutine1At(new \DateTime('-6 days'));
        $this->repositoryPackageTypeFiles['err_err']->setRoutine3At(new \DateTime('-2 days'));
        $this->repositoryPackageTypeFiles['err_err']->setPath('path/err');
        $this->repositoryPackageTypeFiles['err_err']->setSha('sha_err');
        $this->repositoryPackageTypeFiles['err_err']->setRepository($this->repositories['err']);
        $this->repositoryPackageTypeFiles['err_err']->setPackageTypeFile($this->packageTypeFiles['PHP']);
        $this->repositoryPackageTypeFiles['err_err']->setRoutineError('error3');
        $manager->persist($this->repositoryPackageTypeFiles['err_err']);

        $manager->flush();
    }

    private function createRepository(string $repoRef): void
    {
        $this->repositories[$repoRef] = new Repository();
        $this->repositories[$repoRef]->setName("repo$repoRef");
        $this->repositories[$repoRef]->setDescription("description$repoRef");
        $this->repositories[$repoRef]->setUsername("username$repoRef");
        $this->repositories[$repoRef]->setUrl("https://$repoRef.github.com");
        $this->repositories[$repoRef]->setRoutine1At(new \DateTime('-5 days'));
        $this->repositories[$repoRef]->setRoutine2At(new \DateTime('-4 days'));
        $this->em->persist($this->repositories[$repoRef]);
    }

    private function createRepositoryPackageTypeFile(string $repoRef, string $packageTypeFileRef): void
    {
        $ref = $repoRef.'_'.$packageTypeFileRef;
        $this->repositoryPackageTypeFiles[$ref] = new RepositoryPackageTypeFile();
        $this->repositoryPackageTypeFiles[$ref]->setRoutine1At(new \DateTime('-5 days'));
        $this->repositoryPackageTypeFiles[$ref]->setRoutine3At(new \DateTime('-3 days'));
        $this->repositoryPackageTypeFiles[$ref]->setPath("path/$ref");
        $this->repositoryPackageTypeFiles[$ref]->setSha("sha_$ref");
        $this->repositoryPackageTypeFiles[$ref]->setRepository($this->repositories[$repoRef]);
        $this->repositoryPackageTypeFiles[$ref]->setPackageTypeFile($this->packageTypeFiles[$packageTypeFileRef]);
        $this->em->persist($this->repositoryPackageTypeFiles[$ref]);
    }

    private function createPackage(string $packageRef, string $packageTypeFileRef): void
    {
        $this->packages[$packageRef] = new Package();
        $this->packages[$packageRef]->setName("package$packageRef");
        $this->packages[$packageRef]->setPackageTypeFile($this->packageTypeFiles[$packageTypeFileRef]);
        $this->em->persist($this->packages[$packageRef]);
    }

    private function createRepositoryPackage(string $repoRef, string $packageRef, string $minVersion, string $maxVersion): void
    {
        $ref = $repoRef.'_'.$packageRef;
        $repositoryPackageTypeFileRef = $repoRef.'_'.$this->packages[$packageRef]->getPackageTypeFile()->getLanguage();
        $repoPackage = new RepositoryPackage();
        $repoPackage->setVersionStr("$ref $minVersion $maxVersion");
        $this->setVersions($repoPackage, $minVersion, $maxVersion);
        $repoPackage->setValid(true);
        $repoPackage->setPackage($this->packages[$packageRef]);
        $repoPackage->setRepository($this->repositories[$repoRef]);
        $repoPackage->setRepositoryPackageTypeFile($this->repositoryPackageTypeFiles[$repositoryPackageTypeFileRef]);
        $this->em->persist($repoPackage);
        $this->repositoryPackages[$ref] = $repoPackage;
    }

    private function setVersions(RepositoryPackage $repoPackage, string $minVersion, string $maxVersion): void
    {
        $minArr = explode('.', $minVersion);
        $maxArr = explode('.', $maxVersion);
        $repoPackage->setVersionMinMajor((int) $minArr[0]);
        $repoPackage->setVersionMinMinor((int) $minArr[1]);
        $repoPackage->setVersionMinPatch((int) $minArr[2]);
        $repoPackage->setVersionMaxMajor((int) $maxArr[0]);
        $repoPackage->setVersionMaxMinor((int) $maxArr[1]);
        $repoPackage->setVersionMaxPatch((int) $maxArr[2]);
    }
}
