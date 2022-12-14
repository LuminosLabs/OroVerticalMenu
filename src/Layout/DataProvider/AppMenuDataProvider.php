<?php

namespace Luminoslabs\OroVerticalMenu\Layout\DataProvider;

use Luminoslabs\OroVerticalMenu\Cache\ResolvedData\AppResolvedContentNode;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebCatalogBundle\ContentNodeUtils\ContentNodeTreeResolverInterface;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\Repository\ContentNodeRepository;
use Oro\Bundle\WebCatalogBundle\Layout\DataProvider\MenuDataProvider;
use Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class AppMenuDataProvider extends MenuDataProvider
{
    /** @var CacheProvider */
    private $cache;

    /** @var int */
    private $cacheLifeTime;

    /** @var ContentNode */
    private $rootNode = false;

    public function __construct(
        private ManagerRegistry $doctrine,
        private WebCatalogProvider $webCatalogProvider,
        private ContentNodeTreeResolverInterface $contentNodeTreeResolver,
        private LocalizationHelper $localizationHelper,
        private RequestWebContentScopeProvider $requestWebContentScopeProvider,
        private WebsiteManager $websiteManager
    ){
        parent::__construct($doctrine, $webCatalogProvider, $contentNodeTreeResolver, $localizationHelper, $requestWebContentScopeProvider, $websiteManager);
    }

    /**
     * @param CacheProvider $cache
     * @param int           $lifeTime
     */
    public function setCache(CacheProvider $cache, $lifeTime = 0)
    {
        $this->cache = $cache;
        $this->cacheLifeTime = $lifeTime;
    }

    public function getItems(int $maxNodesNestedLevel = null)
    {
        $scope = $this->requestWebContentScopeProvider->getScope();
        if (null !== $scope) {
            $cacheKey = $this->getCacheKey($scope, $maxNodesNestedLevel);
            $rootItem = $this->cache->fetch($cacheKey);

            if (false === $rootItem) {
                $rootItem = $this->getResolvedRootItem($scope, $maxNodesNestedLevel);
                $this->cache->save($cacheKey, $rootItem, $this->cacheLifeTime);
            }

            if (array_key_exists(self::CHILDREN, $rootItem)) {
                return $rootItem[self::CHILDREN];
            }
        }

        return [];
    }

    /**
     * @param Scope $scope
     * @param int|null $maxNodesNestedLevel
     * @return array
     */
    private function getResolvedRootItem(Scope $scope, int $maxNodesNestedLevel = null)
    {
        $rootItem = [];
        $rootNode = $this->getRootNode();

        if (!$rootNode) {
            $webCatalog = $this->webCatalogProvider->getWebCatalog();
            if ($webCatalog) {
                $rootNode = $this->getContentNodeRepository()->getRootNodeByWebCatalog($webCatalog);
            }
        }

        if ($rootNode) {
            $resolvedNode = $this->contentNodeTreeResolver->getResolvedContentNode($rootNode, $scope);
            if ($resolvedNode) {
                $rootItem = $this->prepareItemsData($resolvedNode, $maxNodesNestedLevel);
            }
        }

        return $rootItem;
    }

    /**
     * @param AppResolvedContentNode $node
     * @param int|null $remainingNestedLevel
     *
     * @return array
     */
    private function prepareItemsData(AppResolvedContentNode $node, int $remainingNestedLevel = null)
    {
        $result = [];

        $result[self::IDENTIFIER] = $node->getIdentifier();
        $result[self::LABEL] = (string)$this->localizationHelper->getLocalizedValue($node->getTitles());
        $result[self::URL] = (string)$this->localizationHelper
            ->getLocalizedValue($node->getResolvedContentVariant()->getLocalizedUrls());
        $result['hasHeading'] = $node->getHasHeading();

        $result[self::CHILDREN] = [];
        if (null === $remainingNestedLevel || $remainingNestedLevel > 0) {
            $childrenRemainingNestedLevel = null !== $remainingNestedLevel ? $remainingNestedLevel - 1 : null;
            foreach ($node->getChildNodes() as $child) {
                $result[self::CHILDREN][] = $this->prepareItemsData(
                    $child,
                    $childrenRemainingNestedLevel
                );
            }
        }

        return $result;
    }

    private function getRootNode()
    {
        if ($this->rootNode === false) {
            $website = $this->websiteManager->getCurrentWebsite();
            $this->rootNode = $this->webCatalogProvider->getNavigationRoot($website);
        }

        return $this->rootNode;
    }

    /**
     * @return ContentNodeRepository
     */
    private function getContentNodeRepository()
    {
        return $this->doctrine->getRepository(ContentNode::class);
    }

    private function getCacheKey(Scope $scope, ?int $maxNodesNestedLevel): string
    {
        $rootNode = $this->getRootNode();
        $localization = $this->localizationHelper->getCurrentLocalization();

        return sprintf(
            'menu_items_%s_%s_%s_%s',
            (string)$maxNodesNestedLevel,
            $scope ? $scope->getId() : 0,
            $rootNode ? $rootNode->getId() : 0,
            $localization ? $localization->getId() : 0
        );
    }
}
