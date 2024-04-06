<?php
/**
 * Author: Son Nguyen
 * Copyright © Wiki Solution All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PPGroup\Antispam\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    const XML_PATH_ANTISPAM_ENABLE = 'catalog/antispam_catalog_search/enable';
    const XML_PATH_ANTISPAM_LIST_WORDS_DISALLOW = 'catalog/antispam_catalog_search/list_words_disallow';
    const XML_PATH_ANTISPAM_DISALLOW_CHINESE_WORDS = 'catalog/antispam_catalog_search/disallow_chinese_words';
    const XML_PATH_ANTISPAM_DISALLOW_FOREIGN_WORDS = 'catalog/antispam_catalog_search/disallow_foreign_words';

    const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'\s\d]){1,255}+/u';

    /**
     * If it is return true this is no spam
     * @param $string
     * @return bool
     */
    public function checkSpam($string): bool
    {
        if ($this->isEnable()) {
            $array = explode(" ", $string);
            foreach ($array as $value) {
                if ($this->isChinese($value)) {
                    return false;
                }
                if ($this->isForeignWords($value)) {
                    return false;
                }
                if ($this->isSpamContent($value)) {
                    return false;
                }
                if ($this->hasSpecialCharacters($string)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $string
     * @return bool
     */
    public function isChinese($string): bool
    {
        if ($this->isDisallowChineseWords()) {
            if (preg_match("/\p{Han}+/u", $string)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $string
     * @return bool
     */
    public function isForeignWords($string): bool
    {
        if ($this->isDisallowForeignWords()) {
            if (!preg_match('/\p{Thai}/u', $string) && !$this->isEnglish($string)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $str
     * @return bool
     */
    public function isEnglish($str): bool
    {
        if (strlen($str) != strlen(utf8_decode($str))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $string
     * @return bool
     */
    public function hasSpecialCharacters($string): bool
    {
        if ($string != null) {
            if (preg_match(self::PATTERN_NAME, $string)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $string
     * @return bool
     */
    public function isSpamContent($string): bool
    {
        $spamContent = $this->getListWordsDisallow();
        foreach ($spamContent as $value) {
            if (str_contains($string, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enable module
     *
     * @return bool
     */
    public function isEnable(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ANTISPAM_ENABLE);
    }

    /**
     * Get Disallow Chinese Words
     *
     * @return bool
     */
    public function isDisallowChineseWords(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ANTISPAM_DISALLOW_CHINESE_WORDS);
    }

    /**
     * Get Disallow Foreign Words
     *
     * @return bool
     */
    public function isDisallowForeignWords(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ANTISPAM_DISALLOW_FOREIGN_WORDS);
    }

    /**
     * Get List Words Disallow
     *
     * @return array
     */
    public function getListWordsDisallow(): array
    {
        $listWordsDisallow = $this->scopeConfig->getValue(self::XML_PATH_ANTISPAM_LIST_WORDS_DISALLOW);
        if (!is_null($listWordsDisallow)) {
            return explode(",", $listWordsDisallow);
        }
        return [];
    }
}
