<?php

namespace App\EventSubscriber;

use App\Image\ImageOptimizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * - Réduit automatiquement les images envoyées depuis l'administration
 * - Supprime les miniatures en cache quand une image est remplacée ou supprimée
 * - Donne plus de mémoire à PHP pendant la génération des miniatures
 */
final class ImageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ImageOptimizer $optimizer,
        private readonly CacheManager $cacheManager,
        private readonly LoggerInterface $logger,
        private readonly StorageInterface $storage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD => 'onPostUpload',
            Events::PRE_REMOVE => 'onPreRemove',
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onPostUpload(Event $event): void
    {
        $mapping = $event->getMapping();
        // Chemin du fichier une fois déplacé dans public/images/...
        $path = $this->storage->resolvePath($event->getObject(), $mapping->getFilePropertyName());

        try {
            if ($path && $this->optimizer->shrink($path)) {
                $this->logger->info('Image réduite après upload', ['path' => $path]);
            }
        } catch (\Throwable $e) {
            // Ne bloque jamais l'enregistrement : l'original reste utilisable
            $this->logger->warning('Réduction d\'image impossible', ['path' => $path, 'exception' => $e]);
        }
    }

    public function onPreRemove(Event $event): void
    {
        $mapping = $event->getMapping();
        // Même chemin que celui utilisé par la fonction Twig media()
        $uri = $this->storage->resolveUri($event->getObject(), $mapping->getFilePropertyName());
        if (!$uri) {
            return;
        }
        $path = ltrim($uri, '/');

        try {
            $this->cacheManager->remove($path);
        } catch (\Throwable $e) {
            $this->logger->warning('Suppression des miniatures impossible', ['path' => $path, 'exception' => $e]);
        }
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $route = (string) $event->getRequest()->attributes->get('_route');
        if (str_starts_with($route, 'liip_imagine') || str_starts_with($event->getRequest()->getPathInfo(), '/media/cache/resolve/')) {
            ImageOptimizer::raiseMemoryLimit();
        }
    }
}
