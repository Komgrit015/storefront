<?php

namespace SwagTemplate\Storefront\Page\Product;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ProductPageLoadedSubscriber
 * @package SwagTemplate\Storefront\Page\Product
 */
class ProductPageLoadedSubscriber implements EventSubscriberInterface
{

    /**
     * @var ProductListingLoader
     */
    private $listingLoader;
//    /**
//     * @var Connection
//     */
//    private Connection $connection;

    /**
     * ProductPageLoadedSubscriber constructor.
     * @param ProductListingLoader $listingLoader
     */
    public function __construct(
        ProductListingLoader $listingLoader
    ) {
        $this->listingLoader = $listingLoader;
//        $this->connection = $connection;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    /**
     * @param ProductPageLoadedEvent $event
     */
    public function onProductPageLoaded(ProductPageLoadedEvent $event)
    {
        /** @var ProductEntity $product */
        $product = $event->getPage()->getProduct();

        $this->checkProductCustomerGroupAssignment($product, $event);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('manufacturerId', $product->getManufacturerId()));
        $criteria->addAssociation('media');
        $criteria->addAssociation('properties');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('customFields');
        $criteria->addFilter(new NotFilter(
            NotFilter::CONNECTION_AND,
            [new EqualsFilter('id', $product->getId())]
        ));

        $crosssells = $this->listingLoader->load($criteria, $event->getSalesChannelContext());

        $event->getPage()->assign(['manufacturerCrosssells' => $crosssells->getEntities()]);

//        $result = $this->connection->fetchAllAssociative("
//            SELECT  *
//            FROM vinaturel_author_product
//            INNER JOIN vinaturel_author ON vinaturel_author.id = vinaturel_author_product.vinaturel_author_id
//            WHERE vinaturel_author_product.product_id = UNHEX(:id)
//        ", ['id' => $product->getId()]);
//
//        $event->getPage()->assign(['authors' => $result]);
    }

    /**
     * @param ProductEntity          $product
     * @param ProductPageLoadedEvent $event
     *
     * @return void
     */
    protected function checkProductCustomerGroupAssignment(ProductEntity $product, ProductPageLoadedEvent $event)
    {
//        if (! $product->hasExtension('vinaturelCustomerGroups')) {
//            return;
//        }
//
//        // Get customer group IDs assigned to product.
//        $customerGroupIds = $product->getExtension('vinaturelCustomerGroups')->getElements();
//
//        // Product has no customer groups assigned: Product will be shown to everyone.
//        if (! $customerGroupIds) {
//            return;
//        }
//
//        // Customer is logged in.
//        if ($event->getSalesChannelContext()->getCustomer()) {
//            // Customer group of current proudct is assigned to product: Show product.
//            if (isset($customerGroupIds[$event->getSalesChannelContext()->getCustomer()->getGroupId()])) {
//                return;
//            }
//
//            // Customer group not assgined to product.
//            $exNotFound = new HttpException(Response::HTTP_NOT_FOUND);
//            Throw($exNotFound);
//        }
//
//        // Product has customer groups assigned. Customer not logged it, not show product.
//        $exNotFound = new HttpException(Response::HTTP_NOT_FOUND);
//        Throw($exNotFound);
    }
}
