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
 * @author             Shopgate GmbH <interfaces@shopgate.com>
 * @author             Konstantin Kiritsenko <konstantin.kiritsenko@shopgate.com>
 * @group              Shopgate_Library
 * @group              Shopgate_Library_Helpers
 *
 * @coversDefaultClass Shopgate_Helper_Redirect_TemplateParser
 */
class Shopgate_configurationTest extends PHPUnit_Framework_TestCase
{
    /** @var Shopgate_Helper_Redirect_TemplateParser $class */
    protected $class;

    public function setUp()
    {
        $this->class = new ShopgateConfig(array());
    }

    /**
     * Tests the most basic regex check, export {variable} name
     *
     * @uses   Shopgate_Model_Redirect_HtmlTagVariable::getData
     *
     * @covers ::getVariables
     */
    public function testGetVariablesSimple()
    {
		$input     = array(1,2);
		$expected  = array(1,2);
		$returned  = $this->class->setExcludeItemIds($input);
		$this->assertEquals($returned, $expected);

		$input     = '';
		$expected  = array();
		$returned  = $this->class->setExcludeItemIds($input);
		$this->assertEquals($returned, $expected);

		$input     = '[4,9,5,7,345,9864,1]';
		$expected  = array(4,9,5,7,345,9864,1);
		$returned  = $this->class->setExcludeItemIds($input);
		$this->assertEquals($returned, $expected);
    }
}
