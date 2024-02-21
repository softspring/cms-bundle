<?php

namespace Softspring\CmsBundle\Utils;

class HtmlValidator
{
    public static function validateModule(string $html): array
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        $errors = array_filter($errors, function (\LibXMLError $error) {
            // error codes here: https://gnome.pages.gitlab.gnome.org/libxml2/devhelp/libxml2-xmlerror.html

            if (801 === $error->code) { // XML_HTML_UNKNOWN_TAG
                return false; // ignore invalid tag name
            }
            if (68 === $error->code) { // XML_ERR_NAME_REQUIRED
                return false; // an special character is not escaped: & < > " ' should be &amp; &lt; &gt; &quot; &apos;
            }
            if (23 === $error->code) { // XML_ERR_ENTITYREF_SEMICOL_MISSING
                return false; // missing semicolon in entity reference (for example A&B expected A&B; that is wrong)
            }
            if (513 === $error->code) { // XML_DTD_ID_REDEFINED
                return false; // duplicated id, it's posible when editing the html in the browser
            }

            return true;
        });

        $messages = array_map(function (\LibXMLError $error) use ($html) {
            $lines = explode(PHP_EOL, $html);
            $line = $lines[$error->line - 1] ?? '';
            $line = trim($line);

            return [
                'type' => LIBXML_ERR_FATAL === $error->level || LIBXML_ERR_ERROR === $error->level ? 'error' : 'warning',
                'message' => $error->message.' at line ('.$line.')',
            ];
        }, $errors);

        $status = empty($errors) ? 'success' : (count(array_filter($messages, function ($error) {
            return 'error' === $error['type'];
        })) ? 'error' : 'warning');

        return [
            'status' => $status,
            'messages' => $messages,
        ];
    }
}
