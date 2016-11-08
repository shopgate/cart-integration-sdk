<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault
    implements Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface
{
    public function generate($e, $maxDepth = 10)
    {
        $msg = array($this->generateFormattedHeader($e) . "\n" . $this->generateFormattedTrace($e->getTrace()));
        
        $depthCounter = 1;
        $previous     = $e->getPrevious();
        while ($previous !== null && $depthCounter < $maxDepth) {
            $msg[] =
                $this->generateFormattedHeader($previous, false) . "\n" .
                $this->generateFormattedTrace($previous->getTrace());
            
            $previous = $previous->getPrevious();
            $depthCounter++;
        }
        
        return implode("\n\n", $msg);
    }
    
    /**
     * @param Exception|Throwable $e
     * @param bool                $first
     *
     * @return string
     */
    private function generateFormattedHeader($e, $first = true)
    {
        $prefix = $first
            ? ""
            : "caused by ";
        
        $exceptionClass = get_class($e);
        
        return "{$prefix}{$exceptionClass}: {$e->getMessage()}\n\nthrown from {$e->getFile()} on line {$e->getLine()}";
    }
    
    /**
     * @param array $traces
     *
     * @return string
     */
    private function generateFormattedTrace(array $traces)
    {
        $formattedTraceLines = array();
        $traces              = array_reverse($traces);
        foreach ($traces as $trace) {
            $args = implode(', ', $trace['arguments']);
            
            $formattedTraceLines[] =
                "at {$trace['class']}{$trace['type']}{$trace['function']}({$args}) " .
                "in {$trace['file']}:{$trace['line']}";
        }
        
        return implode("\n", $formattedTraceLines);
    }
}