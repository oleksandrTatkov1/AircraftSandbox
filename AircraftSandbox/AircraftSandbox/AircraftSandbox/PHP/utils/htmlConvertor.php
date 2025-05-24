<?php
namespace PHP\utils;

class HtmlConverter
{
    public static function textToHtml(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
        return nl2br($escaped);
    }

    public static function htmlToText(string $html): string
    {
        $stripped = strip_tags($html);
        return html_entity_decode($stripped, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>