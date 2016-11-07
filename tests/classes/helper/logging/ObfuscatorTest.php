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
class Shopgate_Helper_Logging_ObfuscatorTest extends PHPUnit_Framework_TestCase
{
    /** @var Shopgate_Helper_Logging_Obfuscator */
    private $obfuscator;
    
    public function setUp()
    {
        $this->obfuscator = new Shopgate_Helper_Logging_Obfuscator();
    }
    
    public function testAddObfuscationFields()
    {
        $this->obfuscator->addObfuscationFields(array('test'));
        $data = array(
            'mytestData' => 'this must be readable',
            'user'       => 'this must be readable',
            'pass'       => 'this is secure',
            'test'       => 'this is secure'
        );
        $this->assertEquals(
            "Array\n(\n    [mytestData] => this must be readable\n    [user] => this must be readable\n    [pass] => XXXXXXXX\n    [test] => XXXXXXXX\n)\n",
            $this->obfuscator->cleanParamsForLog($data)
        );
    }
    
    /**
     * @param array  $data
     * @param string $expectedResult
     *
     * @dataProvider addRemoveFieldProvider
     */
    public function testAddRemoveFields($data, $expectedResult)
    {
        $this->obfuscator->addRemoveFields(array('pass'));
        $this->assertEquals(
            $expectedResult,
            $this->obfuscator->cleanParamsForLog($data)
        );
    }
    
    public function addRemoveFieldProvider()
    {
        return array(
            'remove pass' => array(
                array(
                    'user' => 'this must be readable',
                    'pass' => 'this shall be removed',
                ),
                "Array\n(\n    [user] => this must be readable\n    [pass] => <removed>\n)\n"
            ),
            'remove cart' => array(
                array(
                    'user' => 'this must be readable',
                    'cart' => array(
                        'amount'    => 12.34,
                        'all infos' => 'in this array must be removed'
                    ),
                ),
                "Array\n(\n    [user] => this must be readable\n    [cart] => <removed>\n)\n"
            ),
        );
    }
    
    /**
     * @param array  $data
     * @param string $resultString
     *
     * @dataProvider cleanParamsForLogDefaultProvider
     */
    public function testCleanParamsForLogDefault($data, $resultString)
    {
        $loggingResult = $this->obfuscator->cleanParamsForLog($data);
        
        $this->assertEquals($resultString, $loggingResult);
    }
    
    public function cleanParamsForLogDefaultProvider()
    {
        return array(
            'secure'      => array(
                array(
                    'username' => 'this must be readable',
                    'pass'     => 'this is secure',
                ),
                "Array\n(\n    [username] => this must be readable\n    [pass] => XXXXXXXX\n)\n"
            ),
            'secure only' => array(
                array(
                    'pass' => 'this is secure',
                ),
                "Array\n(\n    [pass] => XXXXXXXX\n)\n"
            ),
            'no secure'   => array(
                array(
                    'test' => 'this must be readable',
                ),
                "Array\n(\n    [test] => this must be readable\n)\n"
            ),
        );
    }
    
}