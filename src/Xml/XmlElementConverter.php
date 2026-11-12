<?php

declare(strict_types=1);

namespace Xchert\FileReader\Xml;

class XmlElementConverter
{
    public function convert(\DOMElement $element): array|string
    {
        $result = [];
        $lists = [];
        $text = '';
        $hasChildElements = false;

        foreach ($element->attributes as $attribute) {
            $result['@'.$attribute->nodeName] = $attribute->value;
        }

        foreach ($element->childNodes as $child) {
            // DOMCdataSection extends DOMText
            if ($child instanceof \DOMText) {
                $text .= $child->data;
                continue;
            }

            if (!$child instanceof \DOMElement) {
                continue;
            }

            $hasChildElements = true;
            $name = $child->nodeName;
            $value = $this->convert($child);

            if (!\array_key_exists($name, $result)) {
                $result[$name] = $value;
            } elseif (isset($lists[$name])) {
                $result[$name][] = $value;
            } else {
                $result[$name] = [$result[$name], $value];
                $lists[$name] = true;
            }
        }

        // Text between child elements is mostly indentation, so it is trimmed
        if ($hasChildElements) {
            $text = \trim($text);
        }

        if ($result === []) {
            return $text;
        }

        if ($text !== '') {
            $result['#'] = $text;
        }

        return $result;
    }
}
