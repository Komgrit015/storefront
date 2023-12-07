<?php
namespace SwagTemplate\Services;

use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelDefinitionInterface;


/**
 * Class SalesChannelProductRepositoryDecorator
 * @package SwagTemplate\Services
 */
class SalesChannelProductRepositoryDecorator extends SalesChannelRepository
{

    /**
     * @var string[]
     */
    protected $neededAssociations = [
        'properties',
        'manufacturer',
        'customFields',
        'properties.group',
        'swagCustomerGroups',
    ];

    /**
     * @var SalesChannelRepository
     */
    protected $decorated;

    /**
     * SalesChannelProductRepositoryDecorator constructor.
     * @param SalesChannelRepository $decorated
     */
    public function __construct(
        SalesChannelRepository $decorated
    ) {
        $this->decorated = $decorated;
    }

    /**
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     * @return EntitySearchResult
     */
    public function search(Criteria $criteria, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        foreach ($this->neededAssociations as $association) {
            $criteria->getAssociation($association);
        }

        $searchResult = $this->decorated->search($criteria, $salesChannelContext);

        return $searchResult;
    }

    /**
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     * @return AggregationResultCollection
     */
    public function aggregate(Criteria $criteria, SalesChannelContext $salesChannelContext): AggregationResultCollection
    {
        return $this->decorated->aggregate($criteria, $salesChannelContext);
    }

    /**
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     * @return IdSearchResult
     */
    public function searchIds(Criteria $criteria, SalesChannelContext $salesChannelContext): IdSearchResult
    {
        foreach($criteria->getFilters() as &$filter) {
            if ($filter instanceof ProductAvailableFilter) {
                $filter->addQuery(new MultiFilter(
                    MultiFilter::CONNECTION_OR,
                    [
                        new RangeFilter('product.availableStock', [RangeFilter::GTE => 1]),
                        new RangeFilter('product.restockTime', [RangeFilter::GTE => 1])
                    ]
                ));
            }
        }

        return $this->decorated->searchIds($criteria, $salesChannelContext);
    }
}
