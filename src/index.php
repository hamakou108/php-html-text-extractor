<?php

$html = file_get_contents('resources/index.html');

// Remove indentation
$html = preg_replace('/^[ \t]+/m', '', $html);

// Remove line break
$html = str_replace(["\r\n", "\r", "\n"], '', $html);

// Except autonomous custom elements and text
const PHRASING_CONTENT_NAMES = ['a', 'abbr', 'area', 'audio', 'b', 'bdi', 'bdo', 'br', 'button', 'canvas', 'cite', 'code', 'data', 'datalist', 'del', 'dfn', 'em', 'embed', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'label', 'link', 'map', 'mark', 'math', 'meta', 'meter', 'noscript', 'object', 'output', 'picture', 'progress', 'q', 'ruby', 's', 'samp', 'script', 'select', 'slot', 'small', 'span', 'strong', 'sub', 'sup', 'svg', 'template', 'textarea', 'time', 'u', 'var', 'video', 'wbr'];

function isText(DOMNode $node): bool {
    return $node->nodeType === XML_TEXT_NODE;
}

function isPhrasingContent(DOMNode $node): bool {
    return in_array($node->nodeName, PHRASING_CONTENT_NAMES, true);
}

function flushTextBuffer(array &$textBuffer, array &$extractedTexts) {
    if (!empty($textBuffer)) {
        $extractedTexts[] = implode(' ', $textBuffer);
        $textBuffer = [];
    }
}

function extractTextFromNodeList(DOMNode $node, array &$textBuffer, array &$extractedTexts) {
    foreach ($node->childNodes as $childNode) {
        if (isText($childNode)) {
            // For text nodes, concatenate and store in buffer
            $textBuffer[] = trim($childNode->nodeValue);
        } elseif (isPhrasingContent($childNode)) {
            // For phrasing content, the next appearing text should be concatenated with the text in buffer, so buffer is not flushed.
            extractTextFromNodeList($childNode, $textBuffer, $extractedTexts);
        } else {
            // For not phrasing content, texts should be separated by the opening tag of the element, so buffer is flushed.
            flushTextBuffer($textBuffer, $extractedTexts);
            extractTextFromNodeList($childNode, $textBuffer, $extractedTexts);
            // Texts should also be separated by the closing tag of the element, so buffer is flushed.
            flushTextBuffer($textBuffer, $extractedTexts);
        }
    }
}

$doc = new DOMDocument();
$doc->loadHTML($html);

$textBuffer = [];
$extractedTexts = [];
extractTextFromNodeList($doc, $textBuffer, $extractedTexts);
flushTextBuffer($textBuffer, $extractedTexts);

foreach ($extractedTexts as $text) {
    echo $text . "\n";
}
