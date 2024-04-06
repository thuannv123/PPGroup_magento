<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Mapper;

use Magento\Framework\ObjectManagerInterface;
use Firebear\PlatformFeeds\Feeds\Parser\Variable\Modifier;
use Firebear\PlatformFeeds\Feeds\Parser\Mapper\Category as CategoryMapper;

class Mapper
{
    /**
     * Mapped attributes
     *
     * @var array
     */
    const VARIABLE_MAP = [
        'categories' => CategoryMapper::class
    ];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Mapper constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get replacement for mapped variable
     *
     * @param string $attribute
     * @param string $value
     * @param $oldValue
     * @param Modifier $modifier
     * @param string $pattern
     * @return string
     */
    public function getMapped($attribute, $value, $oldValue, $modifier, $pattern)
    {
        if (!$this->isVariableMapped($attribute)) {
            return $value;
        }

        $mapper = $this->getSpecificMapper($attribute);
        $value = $mapper->map($oldValue);
        $value = $modifier->modify($value, $pattern);

        return $value;
    }

    /**
     * Check is variable mapped
     *
     * @param string $attribute
     * @return bool
     */
    protected function isVariableMapped($attribute)
    {
        return isset(self::VARIABLE_MAP[$attribute]);
    }

    /**
     * Get specific mapper
     *
     * @param string
     * @return AbstractVariableMapper
     */
    protected function getSpecificMapper($attribute)
    {
        $mapper = $this->objectManager->get(self::VARIABLE_MAP[$attribute]);
        return $mapper;
    }
}
