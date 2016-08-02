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
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain
 * unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class Shopgate_Helper_Redirect_Forwarder extends ShopgateObject
{
    /** @var Shopgate_Helper_Redirect_Redirector */
    private $redirector;
    /** @var Shopgate_Helper_Redirect_JsScriptBuilder */
    private $jsScriptBuilder;
    /** @var Shopgate_Helper_Redirect_Type_TypeInterface */
    private $currentType;
    /** @var Shopgate_Helper_Redirect_Type_Http */
    private $http;
    /** @var Shopgate_Helper_Redirect_Type_Js */
    private $js;

    /**
     * @param Shopgate_Helper_Redirect_Redirector      $redirector
     * @param Shopgate_Helper_Redirect_JsScriptBuilder $jsScriptBuilder
     */
    public function __construct(
        Shopgate_Helper_Redirect_Redirector $redirector,
        Shopgate_Helper_Redirect_JsScriptBuilder $jsScriptBuilder
    ) {
        $this->redirector      = $redirector;
        $this->jsScriptBuilder = $jsScriptBuilder;
    }

    /**
     * @return Shopgate_Helper_Redirect_Forwarder
     */
    public function setTypeHttp()
    {
        if (!$this->http) {
            $this->http = new Shopgate_Helper_Redirect_Type_Http($this->redirector);
        }
        $this->currentType = $this->http;

        return $this;
    }

    /**
     * @return Shopgate_Helper_Redirect_Forwarder
     */
    public function setTypeJs()
    {
        if (!$this->js) {
            $this->js = new Shopgate_Helper_Redirect_Type_Js($this->jsScriptBuilder);
        }
        $this->currentType = $this->js;

        return $this;
    }

    /**
     * @param string | int $id
     *
     * @return mixed
     */
    public function loadCms($id)
    {
        return $this->getType()->runCmsScript($id);
    }

    /**
     * @return Shopgate_Helper_Redirect_Type_TypeInterface
     * @throws Exception
     */
    public function getType()
    {
        if (!$this->currentType) {
            throw new Exception('Type was not set before calling the script');
        }

        return $this->currentType;
    }

    /**
     * @param string $manufacturer
     *
     * @return mixed
     */
    public function loadBrands($manufacturer)
    {
        return $this->getType()->runBrandScript($manufacturer);
    }

    /**
     * @param string $query
     *
     * @return mixed
     */
    public function loadSearch($query)
    {
        return $this->getType()->runSearchScript($query);
    }

    /**
     * @param string | int $productId
     *
     * @return mixed
     */
    public function loadProduct($productId)
    {
        return $this->getType()->runProductScript($productId);
    }

    /**
     * @param string | int $categoryId
     *
     * @return mixed
     */
    public function loadCategory($categoryId)
    {
        return $this->getType()->runCategoryScript($categoryId);
    }

    /**
     * @return mixed
     */
    public function loadHome()
    {
        return $this->getType()->runHomeScript();
    }

    /**
     * @return mixed
     */
    public function loadDefault()
    {
        return $this->getType()->runDefaultScript();
    }

    /**
     * @return Shopgate_Helper_Redirect_JsScriptBuilder
     */
    public function getJsBuilder()
    {
        return $this->jsScriptBuilder;
    }

    /**
     * @return Shopgate_Helper_Redirect_Redirector
     */
    public function getHttpBuilder()
    {
        return $this->redirector;
    }
}
