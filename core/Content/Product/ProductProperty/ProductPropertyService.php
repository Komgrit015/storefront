<?php declare(strict_types=1);

namespace SwagTemplate\Core\Content\Product\ProductProperty;

use Shopware\Core\Content\Product\ProductEntity;

class ProductPropertyService
{
    private const CUSTOM_PROPERTY_GROUP = 'sortedProperties'; // แก้ไขตามชื่อที่เหมาะสม

    public function getSortedProperties(ProductEntity $product): array
    {
        $customProperties = [];

//        var_dump($product->getProperties());

        foreach ($product->getProperties() as $property) {
            if ($property->getGroup()->getTranslation('name') === self::CUSTOM_PROPERTY_GROUP) {
                $customProperties[] = $property;
            }
        }
//        var_dump($customProperties);

        return $customProperties;
    }
}
