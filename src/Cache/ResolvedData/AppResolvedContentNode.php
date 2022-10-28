<?php

namespace Luminoslabs\OroVerticalMenu\Cache\ResolvedData;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;

class AppResolvedContentNode extends ResolvedContentNode
{
    public function __construct(
        $id,
        $identifier,
        Collection $titles,
        ResolvedContentVariant $resolvedContentVariant,
        $rewriteVariantTitle = true,
        protected $hasHeading = false
    ){
        parent::__construct($id, $identifier, $titles, $resolvedContentVariant, $rewriteVariantTitle);
    }

    /**
     * @return bool
     */
    public function getHasHeading(): bool
    {
        return $this->hasHeading;
    }

    /**
     * @param bool $hasHeading
     */
    public function setHasHeading(mixed $hasHeading): void
    {
        $this->hasHeading = $hasHeading;
    }
}
