<?php declare(strict_types=1);

namespace SwagTemplate\Extension\Content\Product;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ObjectField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductAttributesExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ObjectField('Swag_product_attributes', 'SwagProductAttributes'))->addFlags(new Runtime())
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
