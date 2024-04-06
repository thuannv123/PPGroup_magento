<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Test\Unit\Model\Export;

use Amasty\Feed\Model\Export\Product;
use Amasty\Feed\Model\Export\RowCustomizer\Composite;
use Amasty\Feed\Model\InventoryResolver;
use Amasty\Feed\Test\Unit\Traits;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductTest
 *
 * @see Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const EXPORT_RAW_DATA = [
        1 => [
            'test1' => [
                1 => 'test2'
            ]
        ]
    ];

    /**
     * @var Product|MockObject
     */
    private $product;

    public function setUp(): void
    {
        $this->product = $this->createPartialMock(
            Product::class,
            [
                'collectRawData',
                'collectMultiRowData',
                'prepareCatalogInventory',
                'getAttributeCollection',
                'filterAttributeCollection'
            ]
        );
        $rowCustomizer = $this->createPartialMock(
            Composite::class,
            [
                'init',
                'prepareData',
                'addHeaderColumns'
            ]
        );

        $this->setProperty($this->product, 'rowCustomizer', $rowCustomizer, Product::class);
        $this->setProperty($this->product, '_entityCollection', 'collection', Product::class);
        $inventoryResolver = $this->createPartialMock(
            InventoryResolver::class,
            [
                'getInventoryData'
            ]
        );
        $stockItemRows = [
            1 => [
                1 => 'test_row'
            ]
        ];
        $inventoryResolver->expects($this->any())->method('getInventoryData')
            ->with(array_keys(self::EXPORT_RAW_DATA))->willReturn($stockItemRows);
        $this->setProperty($this->product, 'inventoryResolver', $inventoryResolver, Product::class);
    }

    /**
     * @covers Product::getExportData
     */
    public function testGetExportData()
    {
        $this->product->expects($this->once())->method('collectRawData')
            ->willReturn(self::EXPORT_RAW_DATA);

        $multiRowData = [
            'customOptionsData' => ''
        ];
        $this->product->expects($this->once())->method('collectMultiRowData')
            ->willReturn($multiRowData);

        $exportData = $this->invokeMethod($this->product, 'getExportData', []);
        $this->assertEquals([0 => ['test2', 'test_row']], $exportData);
    }

    /**
     * @covers Product::hasParentAttributes
     *
     * @dataProvider hasParentAttributesDataProvider
     */
    public function testHasParentAttributes($parentAttrs, $expected)
    {
        $this->setProperty(
            $this->product,
            '_parentAttributes',
            $parentAttrs,
            Product::class
        );

        $result = $this->product->hasParentAttributes();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Product::getExportAttrCodesList
     *
     * @dataProvider getExportAttrCodesListDataProvider
     */
    public function testGetExportAttrCodesList($attrCode, $expected)
    {
        $exportAttrCodes = [
            1 => 'test_code'
        ];
        $this->setProperty($this->product, '_attrCodes', $exportAttrCodes, Product::class);

        $collection = $this->createPartialMock(Collection::class, []);
        $this->product->expects($this->once())->method('getAttributeCollection')
            ->willReturn($collection);
        $attribute = $this->createPartialMock(Attribute::class, []);
        $attribute->setAttributeCode($attrCode);
        $attribute->setFrontendLabel('test_label');
        $this->product->expects($this->once())->method('filterAttributeCollection')
            ->with($collection)->willReturn([$attribute]);

        $result = $this->product->getExportAttrCodesList();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Product::getAttributeOptions
     *
     * @dataProvider getAttributeOptionsDataProvider
     */
    public function testGetAttributeOptions($input, $values, $expected)
    {
        $options = [
            [
                'value' => $values
            ]
        ];
        $source = $this->createPartialMock(AbstractSource::class, ['getAllOptions']);
        $source->expects($this->any())->method('getAllOptions')
            ->willReturn($options);
        $attribute = $this->createPartialMock(AbstractAttribute::class, ['getSource']);
        $attribute->setFrontendInput($input);
        $attribute->expects($this->any())->method('getSource')
            ->willReturn($source);

        $result = $this->product->getAttributeOptions($attribute);
        $this->assertEquals($expected, $result);
    }

    /**
     * data provider for hasParentAttributes test
     *
     * @return array
     */
    public function hasParentAttributesDataProvider()
    {
        return [
            [
                '', false
            ],
            [
                [
                    [
                        'test1' => null
                    ]
                ],
                false
            ],
            [
                [
                    [
                        'test1' => 'test2'
                    ]
                ],
                true
            ]
        ];
    }

    /**
     * Data provider for getExportAttrCodesList test
     *
     * @return array
     */
    public function getExportAttrCodesListDataProvider()
    {
        return [
            ['test_code', ['test_code' => 'test_label']],
            ['test_code2', []]
        ];
    }

    public function getAttributeOptionsDataProvider()
    {
        return [
            [
                'text', '', []
            ],
            [
                'select', 'test_value', []
            ],
            [
                'select',
                [
                    [
                        'label' => 'test_label',
                        'value' => 'test_value'
                    ]
                ],
                [
                    'test_value' => 'test_label'
                ]
            ]
        ];
    }
}
