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
class Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Shopgate_Helper_Logging_Obfuscator */
    protected $obfuscator;
    
    /** @var Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTestFixtureBuilder */
    protected $fixtureProvider;
    
    public function setUp()
    {
        // workaround for PHP versions below 7: load Throwable interface; TODO move to some bootstrap.php or the like
        include_once(dirname(__FILE__) . '/../../../../stubs/Throwable.php');
        
        $this->obfuscator = $this->getMockBuilder('Shopgate_Helper_Logging_Obfuscator')->getMock();
        
        include_once(dirname(__FILE__)
            . '/../../../../fixtures/helper/logging/stack_trace/GeneratorDefaultTestFixtureBuilder.php');
        $this->fixtureProvider = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTestFixtureBuilder($this);
    }
    
    public function testSimpleExceptionProducesProperStackTrace()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $SUT = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault($this->obfuscator);
        
        $this->assertEquals(
            $this->fixtureProvider->getSimpleExceptionExpected(),
            $SUT->generate($this->fixtureProvider->getSimpleException()));
    }
    
    public function testExceptionWithPreviousExceptionsProducesProperStackTrace()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $SUT = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault($this->obfuscator);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithPreviousExceptionsExpected(),
            $SUT->generate($this->fixtureProvider->getExceptionWithPreviousExceptions())
        );
    }
    
    public function testDepthLimitIsHonoured()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $SUT = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault($this->obfuscator);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithPreviousExceptionsDepth2Expected(),
            $SUT->generate($this->fixtureProvider->getExceptionWithPreviousExceptions(), 2)
        );
    }
    
    public function testObfuscation()
    {
        $args = array(
            'user_and_pass' => array(
                'user' => 'herp@derp.com',
                'pass' => 'XXXXXXXX'
            ),
            'data'          => array(
                'data' => array(
                    'action'      => 'get_customer',
                    'shop_number' => '23456',
                    'user'        => 'herp@derp.com',
                    'pass'        => 'XXXXXXXX',
                )
            )
        );
        
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnOnConsecutiveCalls(
            ### on ShopgateLibraryException
                $args['user_and_pass'],
                $args['data'],
                $args['data'],
                $args['user_and_pass'],
                ### on LoginException
                $args['user_and_pass'],
                $args['user_and_pass'],
                $args['data'],
                $args['data'],
                $args['user_and_pass']
            )
        ;
        
        $SUT = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault($this->obfuscator);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionExampleForFailedGetCustomerObfuscationExpected(),
            $SUT->generate($this->fixtureProvider->getExceptionExampleForFailedGetCustomer())
        );
    }
}