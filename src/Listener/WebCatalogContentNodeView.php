<?php

namespace Luminoslabs\OroVerticalMenu\Listener;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;

class WebCatalogContentNodeView
{
    public function onContentNodeView(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            '@AppMenu/WebCatalogContentNode/heading.html.twig',
            ['form' => $event->getFormView()]
        );

        $subBlockId = $event->getScrollData()->addSubBlock(0, '');

        $event->getScrollData()->addSubBlockData(0, $subBlockId, $template);
    }
}
