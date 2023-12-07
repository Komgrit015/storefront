<?php

namespace SwagTemplate\Storefront\Product\Listing\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ListingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCriteriaEvent::class => 'handleListingRequest',
        ];
    }

    public function handleListingRequest(ProductListingCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('properties.group');
    }
}
