<?php
namespace Amastyfixed\GDPR\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CheckboxList implements ArrayInterface
{
    protected $_consentCollection;
    public function __construct(
        \Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory $consentCollection
        )
    {
        $this->_consentCollection = $consentCollection;
    }

    /*  
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->_consentCollection->create();
        $ret[0] = [
            'value' => '',
            'label' => ' '
        ];
        $i = 1;
        foreach ($collection as $consent)
        {
            $ret[$i] = [
                'value' => $consent->getData()['consent_code'],
                'label' => $consent->getData()['name']
            ];
            $i++;
        }
        return $ret;
    }
}
?>