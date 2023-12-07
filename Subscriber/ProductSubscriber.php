<?php declare(strict_types=1);

namespace SwagTemplate\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity; // เพิ่มเติมที่นี่

class ProductSubscriber implements EventSubscriberInterface
{
    const PROPERTY_GROUP_ID_MAPPING = [
        'ced9356fbb6e45b48151b4337df48a25' => 'wine_type',
        'a018c989a02643baa2d26c85f80cfd5c' => 'region',
        '6d3e04d9b87a4668a945bf4012591c39' => 'year',
        '74f19f7169344a5aa8df4aeeeb1bae7b' => 'lager',
        '4ed1b8baab83467780543065849409f7' => 'bio',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'onProductsLoaded',
            ProductListingResultEvent::class    => 'onProductListingResult',
        ];
    }

    public function onProductsLoaded(EntityLoadedEvent $event): void
    {
        /** @var ProductEntity $productEntity */
        foreach ($event->getEntities() as $productEntity) {
            $properties = $productEntity->getProperties() ?? [];
            $sortedProperties = [];

            /** @var PropertyGroupOptionEntity $property */
            foreach ($properties as $property) {
                if (! array_key_exists($property->getGroupId(), self::PROPERTY_GROUP_ID_MAPPING)) {
                    continue;
                }

                $group = self::PROPERTY_GROUP_ID_MAPPING[$property->getGroupId()];

                if (! array_key_exists($group, $sortedProperties)) {
                    $sortedProperties[$group] = [];
                }

                if (count($sortedProperties[$group]) >= $this->getShowMaxOptions($group)) {
                    continue;
                }

                // แก้ไขให้ใช้ PropertyGroupOptionEntity จากเนมสเปซของ Shopware
                $sortedProperties[$group][] = $this->processProperty($property, $group, $event);
            }
//            var_dump($sortedProperties);

//            die('Execution stopped after var_dump($sortedProperties)');
//            $productEntity->addExtension('Swag_product_attributes', new ArrayStruct($sortedProperties));
            $productEntity->addExtension('Swag_product_attributes', new ArrayEntity($sortedProperties));
//            var_dump($sortedProperties);
        }
    }

    protected function processProperty(PropertyGroupOptionEntity $property, string $group, $event)
    {
        switch ($group) {
            case 'year':
            case 'region':
            case 'wine_type':
            case 'lager':
            case 'bio':
                return [
                    'id'   => $property->getId(),
                    'name'  => $property->getTranslation('name'),
                ];
            default:
                return $property->getTranslation('name');
        }
    }

    protected function getShowMaxOptions(string $group): int
    {
        switch ($group) {
            case 'wine_type':
                return 5;
            default:
                return 1;
        }
    }

    protected function getCategoryIdByName(string $categoryName): ?string
    {
        // ตัวอย่างการ implement เมทอดนี้เพื่อหา ID ของ category จากชื่อ
        // คุณต้องแก้ไขตามโค้ดที่คุณใช้ในการหา ID ของ category
        return null;
    }

    public function onProductListingResult(ProductListingResultEvent $event): void
    {
        // สามารถทำการแก้ไขการปรับปรุงข้อมูลที่จะแสดงในหน้า product list ได้ตามต้องการ
    }
}
