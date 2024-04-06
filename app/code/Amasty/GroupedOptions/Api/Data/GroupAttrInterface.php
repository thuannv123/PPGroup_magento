<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Grouped Options for Magento 2
 */

namespace Amasty\GroupedOptions\Api\Data;

interface GroupAttrInterface
{
    public const ID = 'group_id';
    public const ATTRIBUTE_ID = 'attribute_id';
    public const NAME = 'name';
    public const GROUP_CODE = 'group_code';
    public const URL = 'url';
    public const POSITION = 'position';
    public const VISUAL = 'visual';
    public const TYPE = 'type';
    public const ENABLED = 'enabled';
    public const TITLE = 'title';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getAttributeId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getGroupCode();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @return string
     */
    public function getVisual();

    /**
     * @return int
     */
    public function getType();

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param $id
     * @return GroupAttrInterface
     */
    public function setId($id);

    /**
     * @param $id
     * @return GroupAttrInterface
     */
    public function setAttributeId($id);

    /**
     * @param $name
     * @return GroupAttrInterface
     */
    public function setName($name);

    /**
     * @param $code
     * @return GroupAttrInterface
     */
    public function setGroupCode($code);

    /**
     * @param $url
     * @return GroupAttrInterface
     */
    public function setUrl($url);

    /**
     * @param $pos
     * @return GroupAttrInterface
     */
    public function setPosition($pos);

    /**
     * @param $visual
     * @return GroupAttrInterface
     */
    public function setVisual($visual);

    /**
     * @param $type
     * @return GroupAttrInterface
     */
    public function setType($type);

    /**
     * @param $enabled
     * @return GroupAttrInterface
     */
    public function setEnabled($enabled);

    /**
     * @param GroupAttrOptionInterface $option
     * @return $this
     */
    public function addOption(GroupAttrOptionInterface $option);

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = []);

    /**
     * @return GroupAttrOptionInterface[]
     */
    public function getOptions();

    /**
     * @return bool
     */
    public function hasOptions();

    /**
     * @param GroupAttrValueInterface $value
     * @return $this
     */
    public function addValue(GroupAttrValueInterface $value);

    /**
     * @param array $values
     * @return $this
     */
    public function setValues(array $values = []);

    /**
     * @return GroupAttrValueInterface[]
     */
    public function getValues();

    /**
     * @return bool
     */
    public function hasValues();
}
