<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\ProductBundle\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\Component\Form\Type\DeliveryChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class DeliveryAdmin extends AbstractAdmin
{
    protected $parentAssociationMapping = 'product';

    public function configure(): void
    {
        $this->setTranslationDomain('SonataProductBundle');
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper): void
    {
        if (!$this->isChild()) {
            $formMapper->add('product', ModelListType::class, [], [
                'admin_code' => 'sonata.product.admin.product',
            ]);
        }

        $formMapper
            ->add('enabled')
            ->add('code', DeliveryChoiceType::class)
            ->add('perItem')
            ->add('countryCode', CountryType::class)
            ->add('zone')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     */
    public function configureListFields(ListMapper $list): void
    {
        if (!$this->isChild()) {
            $list
                ->addIdentifier('id')
                ->addIdentifier('product', null, [
                    'admin_code' => 'sonata.product.admin.product',
                ]);
        }

        $list
            ->addIdentifier('code')
            ->add('enabled')
            ->add('perItem')
            ->add('countryCode')
            ->add('zone')
        ;
    }

    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !\in_array($action, ['edit'], true)) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('product.sidemenu.link_product_edit', [], 'SonataProductBundle'),
            ['uri' => $admin->generateUrl('edit', ['id' => $id])]
        );
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code')
            ->add('countryCode')
        ;
    }
}
