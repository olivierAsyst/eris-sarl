<?php
/*
 * Routeur pour le serveur PHP intégré (développement local uniquement) :
 *   composer serve      →  http://127.0.0.1:8000
 * Sert les fichiers existants de public/, sinon passe la main à Symfony
 * (nécessaire pour la génération des miniatures LiipImagine en local).
 */
$public = dirname(__DIR__).'/public';
$file = $public.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (is_file($file)) {
    // Les miniatures sont en WebP malgré leur extension .jpg/.png
    if (str_contains($file, '/media/cache/')) {
        header('Content-Type: image/webp');
        readfile($file);

        return true;
    }

    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $public.'/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
chdir($public);
require $public.'/index.php';
