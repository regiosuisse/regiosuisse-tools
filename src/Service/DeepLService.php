<?php

namespace App\Service;

use DeepL\DeepLClient;

class DeepLService {

    protected $client;
    protected $glossaryId;
    protected $styleRules;

    public function __construct(
        string $authKey,
        string $glossaryId,
        string $styleRuleDE,
        string $styleRuleFR,
        string $styleRuleIT
    )
    {
        $this->client = new DeepLClient($authKey);
        $this->glossaryId = $glossaryId;

        $this->styleRules = [
            'de' => $styleRuleDE,
            'fr' => $styleRuleFR,
            'it' => $styleRuleIT,
        ];
    }

    public function translate($fields, $sourceLanguage, $targetLanguage)
    {
        $sourceLanguage = strtolower($sourceLanguage);
        $targetLanguage = strtolower($targetLanguage);

        $result = $fields;

        $plainKeys = [];
        $plainTexts = [];

        $htmlKeys = [];
        $htmlTexts = [];

        foreach($fields as $key => $value) {
            if(!is_string($value) || !trim($value)) {
                continue;
            }

            if($this->containsHtml($value)) {
                $htmlKeys[] = $key;
                $htmlTexts[] = $value;
            } else {
                $plainKeys[] = $key;
                $plainTexts[] = $value;
            }
        }

        $options = [];

        if($this->glossarySupportsLanguagePair($sourceLanguage, $targetLanguage)) {
            $options['glossary'] = $this->glossaryId;
        }

        if(
            array_key_exists($targetLanguage, $this->styleRules) &&
            $this->styleRules[$targetLanguage]
        ) {
            $options['style_id'] = $this->styleRules[$targetLanguage];
        }

        if(count($plainTexts)) {
            $translations = $this->client->translateText(
                $plainTexts,
                $sourceLanguage,
                $targetLanguage,
                $options
            );

            foreach($translations as $index => $translation) {
                $result[$plainKeys[$index]] = $translation->text;
            }
        }

        if(count($htmlTexts)) {
            $htmlOptions = $options;
            $htmlOptions['tag_handling'] = 'html';
            $htmlOptions['tag_handling_version'] = 'v2';

            $translations = $this->client->translateText(
                $htmlTexts,
                $sourceLanguage,
                $targetLanguage,
                $htmlOptions
            );

            foreach($translations as $index => $translation) {
                $result[$htmlKeys[$index]] = $this->normalizeHtmlTextEntities(
                    $translation->text
                );
            }
        }

        return $result;
    }

    private function glossarySupportsLanguagePair($sourceLanguage, $targetLanguage): bool
    {
        if(!$this->glossaryId) {
            return false;
        }

        $glossary = $this->client->getMultilingualGlossary($this->glossaryId);

        foreach($glossary->dictionaries as $dictionary) {
            if(
                strtolower($dictionary->sourceLang) === strtolower($sourceLanguage) &&
                strtolower($dictionary->targetLang) === strtolower($targetLanguage)
            ) {
                return true;
            }
        }

        return false;
    }

    private function containsHtml($value): bool
    {
        return strip_tags($value) !== $value;
    }

    private function normalizeHtmlTextEntities($html): string
    {
        $parts = preg_split(
            '/(<[^>]+>)/s',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if($parts === false) {
            return $html;
        }

        foreach($parts as $index => $part) {

            if(
                !$part ||
                preg_match('/^<[^>]+>$/s', $part)
            ) {
                continue;
            }

            $parts[$index] = preg_replace_callback(
                '/(?:&#\d+;|&#x[0-9a-fA-F]+;|&[a-zA-Z][a-zA-Z0-9]+;)/',
                function ($match) {

                    $decoded = html_entity_decode(
                        $match[0],
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    );

                    if(in_array($decoded, [
                        "'",
                        '"',
                        '‘',
                        '’',
                        '“',
                        '”',
                    ], true)) {
                        return $decoded;
                    }

                    return $match[0];
                },
                $part
            );
        }

        return implode('', $parts);
    }

}