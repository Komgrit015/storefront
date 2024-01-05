<?php

namespace SwagTemplate\Extension;

use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Framework\Struct\Struct;

class ManufacturerExtension extends Struct
{
    /**
     * @var ProductManufacturerEntity|null
     */
    private $manufacturer;

    /**
     * ManufacturerExtension constructor.
     * @param ProductManufacturerEntity|null $manufacturer
     */
    public function __construct(?ProductManufacturerEntity $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return ProductManufacturerEntity|null
     */
    public function getManufacturer(): ?ProductManufacturerEntity
    {
        return $this->manufacturer;
    }

    /**
     * @param ProductManufacturerEntity|null $manufacturer
     */
    public function setManufacturer(?ProductManufacturerEntity $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }
}
