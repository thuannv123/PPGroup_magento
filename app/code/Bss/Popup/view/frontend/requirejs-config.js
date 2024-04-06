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
var config = {
    map: {
        '*': {
            initpopup: 'Bss_Popup/js/initpopup',
            renderpopup: 'Bss_Popup/js/renderpopup'
        }
    },
    paths: {
        'mfpopup': 'Bss_Popup/js/mfpopup'
    },
    shim: {
        'mfpopup': {
            'deps': ['jquery']
        }
    }
};