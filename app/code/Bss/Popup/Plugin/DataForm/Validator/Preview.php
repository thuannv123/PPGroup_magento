<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Plugin\DataForm\Validator;

class Preview
{
    /**
     * Force check invalid form key for action run cron now
     * The reason: Mass action post data to /cron/index to front end area
     * In this process, csrf do validate and mark /cron/index is invalid action
     * then email stock notice could not send
     */

    /**
     * @var \Bss\Popup\Model\Form\FormKey
     */
    private $formKey;

    /**
     * RunCronNow constructor.
     * @param \Bss\Popup\Model\Form\FormKey $formKey
     */
    public function __construct(
        \Bss\Popup\Model\Form\FormKey $formKey
    ) {
        $this->formKey = $formKey;
    }

    /**
     * @param \Magento\Framework\Data\Form\FormKey\Validator $validator
     * @param $result
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function afterValidate(
        \Magento\Framework\Data\Form\FormKey\Validator $validator,
        $result,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (strpos($request->getFullActionName(), 'cms_index_index') !== false) {
            $key = $request->getParam('cid');
            $checkKey = $this->formKey->getFormKey();
            if ($key == $checkKey) {
                return true;
            }
        }
        return $result;
    }
}
