<?php declare(strict_types=1);

namespace SwagTemplate\Storefront\Checkout;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SwagTemplate\Extension\ManufacturerExtension;

/**
 * Class CheckoutSubscriber
 * @package SwagTemplate\Storefront\Checkout
 */
class CheckoutSubscriber implements EventSubscriberInterface
{
    /**
     * @var SalesChannelRepository
     */
    private $productRepository;

    /**
     * CheckoutSubscriber constructor.
     * @param SalesChannelRepository $productRepository
     */
    public function __construct(SalesChannelRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => ['CheckoutConfirmPageLoaded', PHP_INT_MAX],
            CheckoutCartPageLoadedEvent::class => 'onCheckoutCartPageLoaded',
            OffcanvasCartPageLoadedEvent::class => 'onOffcanvasLoaded',
            AccountEditOrderPageLoadedEvent::class => 'onEditOrderPageLoaded',
            CheckoutFinishPageLoadedEvent::class => 'onCheckoutFinishPageLoaded'
        ];
    }

    /**
     * @param OffcanvasCartPageLoadedEvent $event
     */
    public function onOffcanvasLoaded(OffcanvasCartPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $this->addManufacturerAndProperties($page->getCart(), $event->getSalesChannelContext());
    }

    /**
     * @param CheckoutCartPageLoadedEvent $event
     */
    public function onCheckoutCartPageLoaded(CheckoutCartPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $this->addManufacturerAndProperties($page->getCart(), $event->getSalesChannelContext());
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     */
    public function CheckoutConfirmPageLoaded(CheckoutConfirmPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $this->addManufacturerAndProperties($page->getCart(), $event->getSalesChannelContext());
    }

    public function onEditOrderPageLoaded(AccountEditOrderPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $this->addOrderProperties($page->getOrder(), $event->getSalesChannelContext());
    }

    public function onCheckoutFinishPageLoaded(CheckoutFinishPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $this->addOrderProperties($page->getOrder(), $event->getSalesChannelContext());
    }

    /**
     * @param Cart $cart
     * @param SalesChannelContext $context
     */
    private function addManufacturerAndProperties(Cart $cart, SalesChannelContext $context)
    {
        $ids = [];
        foreach ($cart->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }
            $ids[] = $lineItem->getReferencedId();
        }

        if (empty($ids)) {
            return;
        }

        $criteria = new Criteria($ids);
        $criteria->addAssociation('manufacturer');
        $criteria->addAssociation('properties.group');
        $products = $this->productRepository->search($criteria, $context);

        foreach ($products as $product) {
            $cart->getLineItems()->get($product->getId())
                ->addExtensions([
                    'swagManufacturer' => new ManufacturerExtension($product->getManufacturer()),
                    'sortedProperties' => $product->getSortedProperties(),
                    'swagVolume' => new ArrayEntity(['value' => $product->getPurchaseUnit()]),
                ]);
        }
    }

    private function addOrderProperties(OrderEntity $order, $context)
    {
        $ids = [];
        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }
            $ids[] = $lineItem->getReferencedId();
        }

        if (empty($ids)) {
            return;
        }

        $criteria = new Criteria($ids);
        $criteria->addAssociation('manufacturer');
        $criteria->addAssociation('properties.group');
        $products = $this->productRepository->search($criteria, $context);

        $propertyName = 'Jahrgang';

        /** @var ProductEntity $product */
        foreach ($products as $product) {
            $yearData = $product->getProperties()->fmap(
                static function (PropertyGroupOptionEntity $property) use ($propertyName) {
                    if ($property->getGroup()->getName() !== $propertyName) {
                        return null;
                    }

                    return $property->getTranslation('name');
                }
            );

            $yearValue = empty($yearData) ? false : implode(',', $yearData);

            foreach ($order->getLineItems() as $lineItem) {
                if ($product->getId() === $lineItem->getProductId()) {
                    $order->getLineItems()->get($lineItem->getId())->addExtensions([
                        'isOrderLineItem' => new ArrayEntity(['value' => true]),
                        'swagManufacturer' => new ManufacturerExtension($product->getManufacturer()),
                        'swagYear' => new ArrayEntity(['value' => $yearValue]),
                        'swagVolume' => new ArrayEntity(['value' => $product->getPurchaseUnit()]),
                    ]);
                }
            }
        }
    }
}
