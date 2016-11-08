<?php

class Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTestFixtureBuilder
{
    /** @var PHPUnit_Framework_TestCase */
    private $testCase;
    
    public function __construct(PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getSimpleException()
    {
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $this->getTraceFixture('DumboLandingException'),
                'previous'        => null,
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionWithPreviousExceptions()
    {
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $this->getTraceFixture('DumboLandingException'),
                'previous'        => array(
                    'exception_class' => 'DumboHurtException',
                    'message'         => 'Dumbo is hurt.',
                    'code'            => 168,
                    'file'            => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'            => 48,
                    'trace'           => $this->getTraceFixture('DumboHurtException'),
                    'previous'        => array(
                        'exception_class' => 'DumboBrokenLegException',
                        'message'         => 'Dumbo has a broken leg.',
                        'code'            => 256,
                        'file'            => '/Animals/Mammals/Elephants/Dumbo/Legs/Leg.php',
                        'line'            => 130,
                        'trace'           => $this->getTraceFixture('DumboBrokenLegException'),
                        'previous'        => null,
                    ),
                ),
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionExampleForFailedGetCustomer()
    {
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'ShopgateLibraryExceptionStub',
                'message'         => 'wrong username or password: Username or password is incorrect',
                'code'            => 71,
                'file'            => '/var/www/cart/plugins/shopgate/plugin.php',
                'line'            => 158,
                'trace'           => $this->getTraceFixture('ShopgateLibraryExceptionStub'),
                'previous'        => array(
                    'exception_class' => 'LoginException',
                    'message'         => 'Wrong username or password',
                    'code'            => 196,
                    'file'            => '/var/www/cart/classes/User.php',
                    'line'            => 223,
                    'trace'           => $this->getTraceFixture('LoginException'),
                    'previous'        => null,
                ),
            )
        );
    }
    
    /**
     * @return string
     */
    public function getSimpleExceptionExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithPreviousExceptionsExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) in /Animals/Mammals/Elephants/Dumbo.php:12

caused by DumboHurtException: Dumbo is hurt.

thrown from /Animals/Mammals/Elephants/Dumbo/Legs.php on line 48
at \Animals\Mammals\Elephants\Dumbo\Leg->checkHealth() in /Animals/Mammals/Elephants/Dumbo/Legs.php:45
at \Animals\Mammals\Elephants\Dumbo->checkHealth() in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) in /Animals/Mammals/Elephants/Dumbo.php:12

caused by DumboBrokenLegException: Dumbo has a broken leg.

thrown from /Animals/Mammals/Elephants/Dumbo/Legs/Leg.php on line 130
at \Animals\Mammals\Elephants\Dumbo\Legs\Leg->checkHealth() in /Animals/Mammals/Elephants/Dumbo/Legs/Leg.php:114
at \Animals\Mammals\Elephants\Dumbo\Legs->checkHealthFor(left_front) in /Animals/Mammals/Elephants/Dumbo/Legs.php:163
at \Animals\Mammals\Elephants\Dumbo\Legs->checkHealthFor(left_front) in /Animals/Mammals/Elephants/Dumbo/Legs.php:163
at \Animals\Mammals\Elephants\Dumbo\Legs->checkHealth() in /Animals/Mammals/Elephants/Dumbo/Legs.php:45
at \Animals\Mammals\Elephants\Dumbo->checkHealth() in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithPreviousExceptionsDepth2Expected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) in /Animals/Mammals/Elephants/Dumbo.php:12

caused by DumboHurtException: Dumbo is hurt.

thrown from /Animals/Mammals/Elephants/Dumbo/Legs.php on line 48
at \Animals\Mammals\Elephants\Dumbo\Leg->checkHealth() in /Animals/Mammals/Elephants/Dumbo/Legs.php:45
at \Animals\Mammals\Elephants\Dumbo->checkHealth() in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionExampleForFailedGetCustomerObfuscationExpected()
    {
        return <<<STACK_TRACE
ShopgateLibraryExceptionStub: wrong username or password: Username or password is incorrect

thrown from /var/www/cart/plugins/shopgate/plugin.php on line 158
at ShopgatePluginMyCart->getCustomer(herp@derp.com, XXXXXXXX) in /var/www/cart/plugins/shopgate/plugin.php:80
at ShopgatePlugin->handleRequest(Array) in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php:1590
at ShopgatePluginApi->handleRequest(Array) in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:238
at ShopgatePluginApi->getCustomer(herp@derp.com, XXXXXXXX) in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:857

caused by LoginException: Wrong username or password

thrown from /var/www/cart/classes/User.php on line 223
at User->login(herp@derp.com, XXXXXXXX) in /var/www/cart/classes/User.php:215
at ShopgatePluginMyCart->getCustomer(herp@derp.com, XXXXXXXX) in /var/www/cart/plugins/shopgate/plugin.php:80
at ShopgatePlugin->handleRequest(Array) in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php:1590
at ShopgatePluginApi->handleRequest(Array) in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:238
at ShopgatePluginApi->getCustomer(herp@derp.com, XXXXXXXX) in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:857
STACK_TRACE;
    }
    
    /**
     * @param string $index
     *
     * @return array
     */
    public function getTraceFixture($index)
    {
        $fixtures = array(
            'DumboLandingException'        => array(
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'      => 12,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo',
                    'type'      => '->',
                    'function'  => 'land',
                    'arguments' => array('angle' => 90, 'speed' => 30),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'      => 23,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo',
                    'type'      => '->',
                    'function'  => 'checkHealth',
                    'arguments' => array(),
                ),
            ),
            'DumboHurtException'           => array(
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'      => 12,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo',
                    'type'      => '->',
                    'function'  => 'land',
                    'arguments' => array('angle' => 90, 'speed' => 30),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'      => 23,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo',
                    'type'      => '->',
                    'function'  => 'checkHealth',
                    'arguments' => array(),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'      => 45,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo\Leg',
                    'type'      => '->',
                    'function'  => 'checkHealth',
                    'arguments' => array(),
                )
            ),
            'DumboBrokenLegException'      => array(
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'      => 12,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo',
                    'type'      => '->',
                    'function'  => 'land',
                    'arguments' => array('angle' => 90, 'speed' => 30),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'      => 23,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo',
                    'type'      => '->',
                    'function'  => 'checkHealth',
                    'arguments' => array(),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'      => 45,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo\Legs',
                    'type'      => '->',
                    'function'  => 'checkHealth',
                    'arguments' => array(),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'      => 163,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo\Legs',
                    'type'      => '->',
                    'function'  => 'checkHealthFor',
                    'arguments' => array('leg' => 'left_front'),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'      => 163,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo\Legs',
                    'type'      => '->',
                    'function'  => 'checkHealthFor',
                    'arguments' => array('leg' => 'left_front'),
                ),
                array(
                    'file'      => '/Animals/Mammals/Elephants/Dumbo/Legs/Leg.php',
                    'line'      => 114,
                    'class'     => '\Animals\Mammals\Elephants\Dumbo\Legs\Leg',
                    'type'      => '->',
                    'function'  => 'checkHealth',
                    'arguments' => array(),
                ),
            ),
            'ShopgateLibraryExceptionStub' => array(
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'      => 857,
                    'class'     => 'ShopgatePluginApi',
                    'type'      => '->',
                    'function'  => 'getCustomer',
                    'arguments' => array(
                        'user' => 'herp@derp.com',
                        'pass' => 'herpiderp',
                    ),
                ),
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'      => 238,
                    'class'     => 'ShopgatePluginApi',
                    'type'      => '->',
                    'function'  => 'handleRequest',
                    'arguments' => array(
                        'data' => array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php',
                    'line'      => 1590,
                    'class'     => 'ShopgatePlugin',
                    'type'      => '->',
                    'function'  => 'handleRequest',
                    'arguments' => array(
                        'data' => array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/plugin.php',
                    'line'      => 80,
                    'class'     => 'ShopgatePluginMyCart',
                    'type'      => '->',
                    'function'  => 'getCustomer',
                    'arguments' => array(
                        'user' => 'herp@derp.com',
                        'pass' => 'herpiderp',
                    ),
                ),
            ),
            'LoginException'               => array(
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'      => 857,
                    'class'     => 'ShopgatePluginApi',
                    'type'      => '->',
                    'function'  => 'getCustomer',
                    'arguments' => array(
                        'user' => 'herp@derp.com',
                        'pass' => 'herpiderp',
                    ),
                ),
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'      => 238,
                    'class'     => 'ShopgatePluginApi',
                    'type'      => '->',
                    'function'  => 'handleRequest',
                    'arguments' => array(
                        'data' => array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php',
                    'line'      => 1590,
                    'class'     => 'ShopgatePlugin',
                    'type'      => '->',
                    'function'  => 'handleRequest',
                    'arguments' => array(
                        'data' => array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'      => '/var/www/cart/plugins/shopgate/plugin.php',
                    'line'      => 80,
                    'class'     => 'ShopgatePluginMyCart',
                    'type'      => '->',
                    'function'  => 'getCustomer',
                    'arguments' => array(
                        'user' => 'herp@derp.com',
                        'pass' => 'herpiderp',
                    ),
                ),
                array(
                    'file'      => '/var/www/cart/classes/User.php',
                    'line'      => 215,
                    'class'     => 'User',
                    'type'      => '->',
                    'function'  => 'login',
                    'arguments' => array(
                        'user' => 'herp@derp.com',
                        'pass' => 'herpiderp',
                    ),
                ),
            ),
        );
        
        return $fixtures[$index];
    }
    
    /**
     * @param array $values
     *
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub
     */
    private function buildMockFromFixture(array $values)
    {
        $exceptions = array();
        
        do {
            $exception = $this->testCase->getMockBuilder('ThrowableStub')
                                        ->setMockClassName($values['exception_class'])
                                        ->getMock()
            ;
            
            $exception->expects($this->testCase->any())->method('getMessage')->willReturn($values['message']);
            $exception->expects($this->testCase->any())->method('getCode')->willReturn($values['code']);
            $exception->expects($this->testCase->any())->method('getFile')->willReturn($values['file']);
            $exception->expects($this->testCase->any())->method('getLine')->willReturn($values['line']);
            $exception->expects($this->testCase->any())->method('getTrace')->willReturn($values['trace']);
            
            $exceptions[] = $exception;
            
            if (!empty($values['previous'])) {
                $values = $values['previous'];
            } else {
                break;
            }
        } while (true);
        
        $previous = null;
        foreach (array_reverse($exceptions) as $exception) {
            /** @var ThrowableStub|PHPUnit_Framework_MockObject_MockObject $exception */
            $exception->expects($this->testCase->any())->method('getPrevious')->willReturn($previous);
            $previous = $exception;
        }
        
        return $exceptions[0];
    }
}