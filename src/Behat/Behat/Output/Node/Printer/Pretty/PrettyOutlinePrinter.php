<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\OutlinePrinter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Behat\Output\Node\Printer\SkippedStepPrinter;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat pretty outline printer.
 *
 * Prints outline header with outline steps and table header.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyOutlinePrinter implements OutlinePrinter
{
    /**
     * @var ScenarioPrinter
     */
    private $scenarioPrinter;
    /**
     * @var SkippedStepPrinter
     */
    private $stepPrinter;
    /**
     * @var string
     */
    private $indentText;
    /**
     * @var string
     */
    private $subIndentText;

    /**
     * @param ScenarioPrinter    $scenarioPrinter
     * @param SkippedStepPrinter $stepPrinter
     * @param integer            $indentation
     * @param integer            $subIndentation
     */
    public function __construct(
        ScenarioPrinter $scenarioPrinter,
        SkippedStepPrinter $stepPrinter,
        $indentation = 4,
        $subIndentation = 2
    ) {
        $this->scenarioPrinter = $scenarioPrinter;
        $this->stepPrinter = $stepPrinter;
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, OutlineNode $outline)
    {
        $this->scenarioPrinter->printHeader($formatter, $feature, $outline);

        $this->printExamplesSteps($formatter, $outline, $outline->getSteps());
        $this->printExamplesTableHeader($formatter->getOutputPrinter(), $outline->getExampleTable());
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, FeatureNode $feature, OutlineNode $outline, TestResult $result)
    {
        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints outline steps.
     *
     * @param Formatter   $formatter
     * @param OutlineNode $outline
     * @param StepNode[]  $steps
     */
    private function printExamplesSteps(Formatter $formatter, OutlineNode $outline, array $steps)
    {
        foreach ($steps as $step) {
            $this->stepPrinter->printStep($formatter, $outline, $step);
        }

        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints examples table header.
     *
     * @param OutputPrinter    $printer
     * @param ExampleTableNode $table
     */
    private function printExamplesTableHeader(OutputPrinter $printer, ExampleTableNode $table)
    {
        $printer->writeln(sprintf('%s{+keyword}%s:{-keyword}', $this->indentText, $table->getKeyword()));

        $rowNum = 0;
        $wrapper = $this->getWrapperClosure();
        $row = $table->getRowAsStringWithWrappedValues($rowNum, $wrapper);

        $printer->writeln(sprintf('%s%s', $this->subIndentText, $row));
    }

    /**
     * Creates wrapper-closure for the example header.
     *
     * @return callable
     */
    private function getWrapperClosure()
    {
        $result = new TestResult(TestResult::SKIPPED);

        return function ($col) use ($result) {
            return sprintf('{+%s_param}%s{-%s_param}', $result, $col, $result);
        };
    }
}