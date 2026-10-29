<?php

namespace App\Twig;

use App\Repository\ServicesRepository;
use App\Repository\SocialMediaRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

final class AppExtension extends AbstractExtension
{
    private ?array $socials = null;
    private ?array $services = null;

    public function __construct(
        private readonly UploaderHelper $uploaderHelper,
        private readonly CacheManager $imagineCache,
        private readonly Packages $packages,
        private readonly SocialMediaRepository $socialMediaRepository,
        private readonly ServicesRepository $servicesRepository,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', $this->excerpt(...)),
            new TwigFilter('meta_description', $this->metaDescription(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media', $this->media(...)),
            new TwigFunction('social_links', $this->socialLinks(...)),
            new TwigFunction('menu_services', $this->menuServices(...)),
        ];
    }

    /**
     * Raccourcit un texte sans couper un mot en deux.
     */
    public function excerpt(?string $text, int $length = 160, string $suffix = '…'): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $cut = mb_substr($text, 0, $length + 1);
        $lastSpace = mb_strrpos($cut, ' ');
        if (false !== $lastSpace && $lastSpace > $length * 0.6) {
            $cut = mb_substr($cut, 0, $lastSpace);
        } else {
            $cut = mb_substr($cut, 0, $length);
        }

        return rtrim($cut, " ,;:.-–—").$suffix;
    }

    /** Texte adapté à la balise <meta name="description"> (≈ 155 caractères). */
    public function metaDescription(?string $text): string
    {
        return $this->excerpt($text, 155, '…');
    }

    /**
     * URL d'une image uploadée (VichUploader) redimensionnée par LiipImagine.
     * Renvoie l'image de repli si l'entité n'a pas d'image.
     */
    public function media(?object $entity, string $field = 'image', string $filter = 'card', ?string $fallback = null): ?string
    {
        $path = $entity ? $this->uploaderHelper->asset($entity, $field) : null;

        if (null === $path || '' === $path) {
            if (null === $fallback) {
                return null;
            }
            $path = $fallback;
        }

        // Les SVG ne se redimensionnent pas
        if (str_ends_with(strtolower($path), '.svg')) {
            return $this->packages->getUrl($path);
        }

        return $this->imagineCache->getBrowserPath(ltrim($path, '/'), $filter);
    }

    /** Réseaux sociaux de l'entreprise (affichés dans le pied de page de toutes les pages). */
    public function socialLinks(): array
    {
        // try/catch : les pages d'erreur doivent s'afficher même si la base est indisponible
        try {
            return $this->socials ??= array_values(array_filter(
                $this->socialMediaRepository->findAll(),
                static fn ($s) => filter_var($s->getLien(), FILTER_VALIDATE_URL)
            ));
        } catch (\Throwable) {
            return $this->socials = [];
        }
    }

    /** Liste des services pour le menu et le pied de page. */
    public function menuServices(): array
    {
        try {
            return $this->services ??= $this->servicesRepository->findBy([], ['id' => 'ASC']);
        } catch (\Throwable) {
            return $this->services = [];
        }
    }
}
