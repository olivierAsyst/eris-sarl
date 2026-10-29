<?php

namespace App\Command;

use App\Image\ImageOptimizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

/**
 * Réduit les images déjà en ligne (à lancer une fois après la mise à jour) :
 *   php bin/console app:images:optimize
 */
#[AsCommand(name: 'app:images:optimize', description: 'Réduit les images trop lourdes de public/images (max 2400 px, qualité 82).')]
final class OptimizeImagesCommand extends Command
{
    public function __construct(
        private readonly ImageOptimizer $optimizer,
        #[Autowire('%kernel.project_dir%/public/images')] private readonly string $imagesDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche les fichiers concernés sans les modifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        ImageOptimizer::raiseMemoryLimit('1024M');
        $dry = (bool) $input->getOption('dry-run');

        $before = 0;
        $after = 0;
        $count = 0;
        $finder = (new Finder())->files()->in($this->imagesDir)->name('/\.(jpe?g|png|webp)$/i');

        foreach ($finder as $file) {
            $path = $file->getPathname();
            $size = $file->getSize();
            [$w, $h] = @getimagesize($path) ?: [0, 0];
            if ($w <= ImageOptimizer::MAX_SIZE && $h <= ImageOptimizer::MAX_SIZE && $size <= 1_500_000) {
                continue;
            }
            $before += $size;
            if ($dry) {
                $io->writeln(sprintf('%s (%dx%d, %s)', $file->getRelativePathname(), $w, $h, self::human($size)));
                ++$count;
                continue;
            }
            try {
                if ($this->optimizer->shrink($path)) {
                    ++$count;
                    $new = filesize($path);
                    $after += $new;
                    $io->writeln(sprintf('✔ %s : %s → %s', $file->getRelativePathname(), self::human($size), self::human($new)));
                }
            } catch (\Throwable $e) {
                $io->warning($file->getRelativePathname().' : '.$e->getMessage());
            }
        }

        if ($dry) {
            $io->success(sprintf('%d image(s) seraient réduites (%s au total).', $count, self::human($before)));
        } else {
            $io->success(sprintf('%d image(s) réduites : %s → %s. Pensez à vider le cache des miniatures : php bin/console liip:imagine:cache:remove', $count, self::human($before), self::human($after)));
        }

        return Command::SUCCESS;
    }

    private static function human(int $bytes): string
    {
        return $bytes > 1048576 ? round($bytes / 1048576, 1).' Mo' : round($bytes / 1024).' Ko';
    }
}
