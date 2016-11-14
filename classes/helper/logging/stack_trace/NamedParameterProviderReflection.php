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

/**
 * Tries to get the argument names from a function and map the actual values it was called with to them using Reflection.
 */
class Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterfaceReflection
    implements Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterface
{
    /** @var array [string, string[]] */
    protected $functionArgumentsCache;
    
    public function __construct()
    {
        $this->functionArgumentsCache = array();
    }
    
    public function get($className, $functionName, array $arguments)
    {
        if (!empty($className) && !class_exists($className)) {
            return $arguments;
        }
        
        $fullFunctionName = (empty($className)) ? $functionName : $className . '::' . $functionName;
        
        if (!function_exists($fullFunctionName) && !method_exists($className, $functionName)) {
            return $arguments;
        }
        
        $reflectionFunction = (empty($className))
            ? new ReflectionFunction($fullFunctionName)
            : new ReflectionMethod($className, $functionName);
        
        $this->functionArgumentsCache[$fullFunctionName] = array();
        
        $i              = 0;
        $namedArguments = array();
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $this->functionArgumentsCache[$fullFunctionName][] = $parameter->getName();
            $namedArguments[$parameter->getName()]             = $arguments[$i];
            $i++;
        }
        
        for (/* using the current value of $i */; $i < count($arguments); $i++) {
            $namedArguments['unnamed argument ' . $i] = $arguments[$i];
        }
        
        return $namedArguments;
    }
}