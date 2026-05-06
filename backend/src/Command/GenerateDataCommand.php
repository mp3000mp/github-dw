<?php

namespace App\Command;

use App\Entity\Package;
use App\Entity\PackageTypeFile;
use App\Entity\Repository;
use App\Entity\RepositoryPackage;
use App\Entity\RepositoryPackageTypeFile;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:generate-data', description: 'Generate dev data.')]
class GenerateDataCommand extends Command
{
    private ?Generator $faker = null;

    private float $routine2Ratio = 0.75;
    private float $routine2ErrorRatio = 0.01;
    private float $routine3Ratio = 0.8;
    private float $routine3ErrorRatio = 0.03;
    private float $validPackageVersionRatio = 0.99;
    private int $packagePerRepo = 10;
    private float $createPackageRatio = 0.1;

    /** @var array<string, Package> */
    private array $packageUniq = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command generates DEV data.');
        $this->addArgument('number', InputArgument::REQUIRED, 'Number of package file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ('dev' !== $this->parameterBag->get('app.env')) {
            $output->writeln('This command should be used in DEV env only.');

            return Command::FAILURE;
        }
        $repositoryNumber = $input->getArgument('number') ?? 0;
        if ($repositoryNumber <= 0) {
            $output->writeln('Argument "number" must be higher than 0.');

            return Command::FAILURE;
        }

        $this->faker = Factory::create();
        $output->writeln(sprintf('Generating %d package files...', $repositoryNumber));
        $pb = new ProgressBar($output, $repositoryNumber, 1 / 8);
        $pb->start();

        $allPackageTypeFiles = $this->em->getRepository(PackageTypeFile::class)->findAll();
        $allPackages = $this->em->getRepository(Package::class)->findAll();
        foreach ($allPackages as $package) {
            $uniq = sprintf('%d__%s', $package->getPackageTypeFile()->getId(), $package->getName());
            $this->packageUniq[$uniq] = $package;
        }
        $allUrls = array_unique(array_map(function (Repository $repository) {
            return $repository->getUrl();
        }, $this->em->getRepository(Repository::class)->findAll()));
        for ($i = 0; $i < $repositoryNumber; ++$i) {
            $pb->advance();

            // routine 1
            $packageTypeFile = $this->faker->randomElement($allPackageTypeFiles);
            $repo = new Repository();
            $repo->setRoutine1At($this->fakerDate('-1 month', '-1 day'));
            $repo->setName($this->faker->domainWord());
            $repo->setUsername($this->faker->userName());
            $repo->setUrl($this->genUrl($allUrls));
            $this->em->persist($repo);
            // todo sometimes generate more than one file
            $repoPackageTypeFile = new RepositoryPackageTypeFile();
            $repoPackageTypeFile->setRepository($repo);
            $repoPackageTypeFile->setPackageTypeFile($packageTypeFile);
            $repoPackageTypeFile->setRoutine1At($repo->getRoutine1At());
            $repoPackageTypeFile->setPath('/'.implode('/', $this->faker->words($this->faker->numberBetween(0, 2))));
            $repoPackageTypeFile->setSha($this->faker->sha256());
            $this->em->persist($repoPackageTypeFile);

            // routine 2
            if (!$this->alea($this->routine2Ratio)) {
                continue;
            }
            $repo->setRoutine2At($this->fakerDate($repo->getRoutine1At()));
            if ($this->alea($this->routine2ErrorRatio)) {
                $repo->setRoutineError($this->faker->realText(100));
                continue;
            }
            $repo->setFullName($repo->getUsername().'/'.$repo->getName());
            $repo->setMainLanguage($packageTypeFile->getLanguage());
            $repo->setDescription($this->faker->realText(2000));
            $repo->setLicenseName($this->faker->randomElement([null, 'Apache 2.0', 'MIT', 'private']));
            $repo->setForksCount($this->faker->randomNumber(4));
            $repo->setStargazersCount($this->faker->randomNumber(4));
            $repo->setOpenIssuesCount($this->faker->randomNumber(4));
            $repo->setGithubId($this->faker->randomNumber(9));
            $repo->setSize($this->faker->randomNumber(7));
            $repo->setCreatedAt($this->fakerDate('-3 years', '-3 months'));
            $repo->setPushedAt($this->fakerDate('-3 months'));

            // routine 3
            if (!$this->alea($this->routine3Ratio)) {
                continue;
            }
            $repoPackageTypeFile->setRoutine3At($this->fakerDate($repo->getRoutine1At()));
            if ($this->alea($this->routine3ErrorRatio)) {
                $repoPackageTypeFile->setRoutineError($this->faker->realText(100));
                continue;
            }

            $packages = [];
            if (count($allPackages) >= 100) {
                $packages = $this->faker->randomElements($allPackages, $this->packagePerRepo, false);
            }
            for ($j = 0; $j < $this->packagePerRepo; ++$j) {
                if (0 === count($packages) || $this->alea($this->createPackageRatio)) {
                    $package = $this->genPackage($packageTypeFile);
                } else {
                    $package = array_pop($packages);
                }
                $this->genRepoPackage($repoPackageTypeFile, $package);
            }
        }
        $this->em->flush();

        $pb->finish();
        $output->writeln("\nSUCCESS");

        return Command::SUCCESS;
    }

    private function genPackage(PackageTypeFile $packageTypeFile): Package
    {
        $name = implode('_', $this->faker->words(2));
        $uniq = sprintf('%d__%s', $packageTypeFile->getId(), $name);
        if (array_key_exists($uniq, $this->packageUniq)) {
            return $this->packageUniq[$uniq];
        }

        $package = new Package();
        $package->setName($name);
        $package->setPackageTypeFile($packageTypeFile);
        $this->em->persist($package);
        $this->packageUniq[$uniq] = $package;

        return $package;
    }

    private function genRepoPackage(RepositoryPackageTypeFile $repoPackageTypeFile, Package $package): void
    {
        $repoPackage = new RepositoryPackage();
        $repoPackage->setPackage($package);
        $repoPackage->setRepository($repoPackageTypeFile->getRepository());
        $repoPackage->setRepositoryPackageTypeFile($repoPackageTypeFile);

        if (!$this->alea($this->validPackageVersionRatio)) {
            $repoPackage->setValid(false);
            $repoPackage->setVersionStr('invalid');
            $repoPackage->setVersionMinMajor(0);
            $repoPackage->setVersionMinMinor(0);
            $repoPackage->setVersionMinPatch(0);
            $repoPackage->setVersionMaxMajor(9999);
            $repoPackage->setVersionMaxMinor(9999);
            $repoPackage->setVersionMaxPatch(9999);
        } else {
            // todo more version combination
            $repoPackage->setValid(true);
            $v = $this->faker->randomDigit();
            $repoPackage->setVersionStr(sprintf('%d.*', $v));
            $repoPackage->setVersionMinMajor($v);
            $repoPackage->setVersionMinMinor(0);
            $repoPackage->setVersionMinPatch(0);
            $repoPackage->setVersionMaxMajor($v + 1);
            $repoPackage->setVersionMaxMinor(0);
            $repoPackage->setVersionMaxPatch(0);
        }

        $this->em->persist($repoPackage);
    }

    private function alea(float $ratio): bool
    {
        return $this->faker->randomFloat(3, 0, 1) < $ratio;
    }

    private function fakerDate(\DateTimeInterface|string $from = '-30 years', \DateTimeInterface|string $to = 'now'): \DateTimeImmutable
    {
        if ($from instanceof \DateTimeImmutable) {
            $from = \DateTime::createFromImmutable($from);
        }
        if ($to instanceof \DateTimeImmutable) {
            $to = \DateTime::createFromImmutable($to);
        }

        return \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween($from, $to));
    }

    /**
     * @param string[] $allUrls
     */
    private function genUrl(array &$allUrls): string
    {
        $url = $this->faker->url();
        while (in_array($url, $allUrls, true)) {
            $url = $this->faker->url();
        }
        $allUrls[] = $url;

        return $url;
    }
}
