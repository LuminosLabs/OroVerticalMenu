<?php

namespace Luminoslabs\OroVerticalMenu\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\FormBundle\Form\Type\CheckboxType;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MenuContentNodeHeading implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_web_catalog_content_node');

        $table->addColumn(
            'has_heading',
            'boolean',
            [
                'default' => false,
                'oro_options' => [
                    'extend' => [
                        'is_extend' => true,
                        'owner' => ExtendScope::OWNER_CUSTOM
                    ],
                    'entity' => [
                        'label' => 'app.webcatalog.contentnode.has_heading.label',
                        'description' => 'app.webcatalog.contentnode.has_heading.description'
                    ],
                    'form' => [
                        'form_type' => CheckboxType::class,
                    ],
                ]
            ]
        );
    }
}
