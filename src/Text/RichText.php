<?php

namespace App\Text;

/**
 * Texte mis en forme saisi dans l'admin (éditeur de texte des services).
 *
 * - sanitize() : ne garde que quelques balises sûres (paragraphes, titres, gras,
 *   italique, listes, liens…). Tout le reste est supprimé : pas de script, pas de style.
 * - toHtml() : accepte aussi les anciennes descriptions en texte brut
 *   (retours à la ligne et listes « - ») et les convertit en HTML.
 */
final class RichText
{
    /** Balises autorisées => balise produite */
    private const ALLOWED = [
        'p' => 'p', 'br' => 'br', 'strong' => 'strong', 'b' => 'strong', 'em' => 'em', 'i' => 'em',
        'u' => 'u', 's' => 's', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4',
        'ul' => 'ul', 'ol' => 'ol', 'li' => 'li', 'a' => 'a', 'blockquote' => 'blockquote',
    ];

    /** Balises supprimées avec tout leur contenu */
    private const DROPPED = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'noscript', 'svg', 'math', 'head', 'title', 'form', 'select', 'textarea', 'button'];

    public static function isHtml(?string $text): bool
    {
        return null !== $text && 1 === preg_match('/<\s*\/?\s*[a-z][a-z0-9]*[\s>\/]/i', $text);
    }

    /** HTML sûr, prêt à afficher (texte brut ou HTML en entrée). */
    public static function toHtml(?string $text): string
    {
        $text = trim((string) $text);
        if ('' === $text) {
            return '';
        }

        return self::isHtml($text) ? self::sanitize($text) : self::fromPlainText($text);
    }

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ('' === $html) {
            return '';
        }

        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="rich-root">'.$html.'</div>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('rich-root');
        $out = $root ? self::walk($root) : '';

        // Paragraphes vides laissés par l'éditeur
        $out = (string) preg_replace('#<(p|h2|h3|h4|li)>(\s|<br>)*</\1>#', '', $out);
        $out = trim($out);

        return '' === trim(strip_tags($out)) ? '' : $out;
    }

    /** Ancien format : paragraphes séparés par une ligne vide, listes avec « - ». */
    public static function fromPlainText(string $text): string
    {
        $lines = preg_split('/\R/u', trim($text));
        $html = '';
        $paragraph = [];
        $list = [];

        $flushParagraph = static function () use (&$paragraph, &$html): void {
            if ($paragraph) {
                $html .= '<p>'.implode('<br>', $paragraph).'</p>';
                $paragraph = [];
            }
        };
        $flushList = static function () use (&$list, &$html): void {
            if ($list) {
                $html .= '<ul><li>'.implode('</li><li>', $list).'</li></ul>';
                $list = [];
            }
        };

        foreach ($lines as $line) {
            $line = trim($line);
            if ('' === $line) {
                $flushParagraph();
                $flushList();
            } elseif (preg_match('/^[-•*–]\s+(.+)$/u', $line, $m)) {
                $flushParagraph();
                $list[] = self::escape($m[1]);
            } else {
                $flushList();
                $paragraph[] = self::escape($line);
            }
        }
        $flushParagraph();
        $flushList();

        return $html;
    }

    private static function walk(\DOMNode $node): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $out .= self::escape(str_replace("\u{00A0}", ' ', $child->textContent));
                continue;
            }
            if (!$child instanceof \DOMElement) {
                continue; // commentaires, instructions…
            }

            $name = strtolower($child->nodeName);
            if (in_array($name, self::DROPPED, true)) {
                continue;
            }
            if (!isset(self::ALLOWED[$name])) {
                $out .= self::walk($child); // balise inconnue : on garde seulement son contenu
                continue;
            }

            $tag = self::ALLOWED[$name];
            if ('br' === $tag) {
                $out .= '<br>';
                continue;
            }

            // Quill note les listes à puces <ol><li data-list="bullet">
            if ('ol' === $tag && self::isBulletList($child)) {
                $tag = 'ul';
            }

            $attributes = '';
            if ('a' === $tag) {
                $href = self::safeHref($child->getAttribute('href'));
                if (null === $href) {
                    $out .= self::walk($child);
                    continue;
                }
                $attributes = ' href="'.self::escape($href).'"';
                if (preg_match('#^https?://#i', $href)) {
                    $attributes .= ' target="_blank" rel="noopener noreferrer"';
                }
            }

            $out .= '<'.$tag.$attributes.'>'.self::walk($child).'</'.$tag.'>';
        }

        return $out;
    }

    private static function isBulletList(\DOMElement $list): bool
    {
        foreach ($list->childNodes as $item) {
            if ($item instanceof \DOMElement && 'li' === strtolower($item->nodeName)) {
                return 'bullet' === $item->getAttribute('data-list');
            }
        }

        return false;
    }

    private static function safeHref(string $href): ?string
    {
        $href = trim($href);
        if ('' === $href) {
            return null;
        }
        if (preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href)) {
            return $href;
        }
        // « www.exemple.com » saisi sans protocole
        if (preg_match('#^[a-z0-9-]+(\.[a-z0-9-]+)+(/.*)?$#i', $href)) {
            return 'https://'.$href;
        }

        return null; // javascript:, data:, etc.
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
