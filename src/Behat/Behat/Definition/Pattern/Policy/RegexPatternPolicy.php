<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern\Policy;

use Behat\Behat\Definition\Pattern\Pattern;
use Behat\Transliterator\Transliterator;

/**
 * Behat regex pattern policy.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RegexPatternPolicy implements PatternPolicy
{
    /**
     * @var string[string]
     */
    private static $replacePatterns = array(
        "/(?<=\W|^)\\\'(?:((?!\\').)*)\\\'(?=\W|$)/" => "'([^']*)'", // Single quoted strings
        '/(?<=\W|^)\"(?:[^\"]*)\"(?=\W|$)/'          => "\"([^\"]*)\"", // Double quoted strings
        '/(?<=\W|^)(\d+)(?=\W|$)/'                   => "(\\d+)", // Numbers
    );

    /**
     * {@inheritdoc}
     */
    public function supportsPatternType($type)
    {
        return 'regex' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePattern($stepText)
    {
        $canonicalText = $this->generateCanonicalText($stepText);
        $stepRegex = $this->generateRegex($stepText);
        $placeholderCount = $this->countPlaceholders($stepText, $stepRegex);

        return new Pattern($canonicalText, '/^' . $stepRegex . '$/', $placeholderCount);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsPattern($pattern)
    {
        return false !== @preg_match($pattern, 'anything');
    }

    /**
     * {@inheritdoc}
     */
    public function transformPatternToRegex($pattern)
    {
        return $pattern;
    }

    /**
     * Generates regex from step text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function generateRegex($stepText)
    {
        return preg_replace(
            array_keys(self::$replacePatterns),
            array_values(self::$replacePatterns),
            $this->escapeStepText($stepText)
        );
    }

    /**
     * Generates canonical text for step text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function generateCanonicalText($stepText)
    {
        $canonicalText = preg_replace(array_keys(self::$replacePatterns), '', $stepText);
        $canonicalText = Transliterator::transliterate($canonicalText, ' ');
        $canonicalText = preg_replace('/[^a-zA-Z\_\ ]/', '', $canonicalText);
        $canonicalText = str_replace(' ', '', ucwords($canonicalText));

        return $canonicalText;
    }

    /**
     * Counts regex placeholders using provided text.
     *
     * @param string $stepText
     * @param string $stepRegex
     *
     * @return integer
     */
    private function countPlaceholders($stepText, $stepRegex)
    {
        preg_match('/^' . $stepRegex . '$/', $stepText, $matches);

        return count($matches) ? count($matches) - 1 : 0;
    }

    /**
     * Returns escaped step text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function escapeStepText($stepText)
    {
        return preg_replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $stepText);
    }
}
