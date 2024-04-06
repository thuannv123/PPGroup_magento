<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Operator;

use Firebear\PlatformFeeds\Feeds\Parser\Abstracts\AbstractParser;
use Firebear\PlatformFeeds\Feeds\Parser\Variable\Modifier;

/**
 * @method Modifier getModifier()
 * @method ForGalleryOperator setModifier(Modifier $modifier)
 */
class ForGalleryOperator extends AbstractParser
{
    /**
     * @var string
     */
    const FOR_PATTERN = '/{%[\s]*for image in set[\s]*%}(.*?){%[\s]*endforImage[\s]*%}/si';

    /**
     * @var string
     */
    const ATTRIBUTE_CODE_GALLERY = 'additional_images';

    /**
     * @var string
     */
    const IMAGE_PATTERN = '/\{\{[\s]*src([^}]+)\}\}/si';

    /**
     * @var string
     */
    const IMAGE_DELIMITER = ',';

    /**
     * ForGalleryOperator constructor.
     *
     * @param Modifier $modifier
     * @param array $data
     */
    public function __construct(
        Modifier $modifier,
        array $data = []
    ) {
        parent::__construct($data);

        $this->setModifier($modifier);
    }

    /**
     * @inheritdoc
     */
    public function translate(array $data)
    {
        $this->setRowData($data);

        return preg_replace_callback(
            self::FOR_PATTERN,
            [&$this, "replaceCallback"],
            $this->getTemplate()
        );
    }

    /**
     * Replace callback
     *
     * @param array $matches
     * @return string
     */
    protected function replaceCallback($matches)
    {
        $result = '';
        $data = $this->getRowData();
        if (empty($data[self::ATTRIBUTE_CODE_GALLERY])) {
            // Do replacement with an empty string
            return '';
        }

        $images = $data[self::ATTRIBUTE_CODE_GALLERY];
        $images = explode(self::IMAGE_DELIMITER, $images);

        foreach ($images as $image) {
            preg_match(self::IMAGE_PATTERN, $matches[1], $imageMatches);
            if (empty($imageMatches)) {
                continue;
            }

            $value = $this->getModifier()->modify($image, $imageMatches[0]);
            $result .= str_replace($imageMatches[0], $value, $matches[1]);
        }

        return $result;
    }
}
