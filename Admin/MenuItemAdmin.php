<?php

namespace Fousky\AppBundle\Admin\Menu;

use Fousky\AppBundle\Entity\Menu\MenuItem;
use Fousky\TreeBundle\Admin\AbstractTreeAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class MenuItemAdmin extends AbstractTreeAdmin
{
    protected $baseRouteName = 'admin_app_menu';
    protected $baseRoutePattern = 'menu';
    protected $datagridValues = [
        '_sort_by' => 'left',
        '_sort_order' => 'ASC',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     *
     * @throws \RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Název kategorie',
            ])
            ->add('active', null, [
                'label' => 'Aktivní?',
            ])
            ->add('parent', 'doctrine_orm_model_autocomplete', [
                'label' => 'Nadřazená kategorie',
            ], 'sonata_type_model_autocomplete', [
                'property' => ['title'],
                'to_string_callback' => function(MenuItem $item) {
                    return $item->generatePath();
                },
            ])
        ;
    }

    /**
     * @param ListMapper $listMapper
     *
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->addIdentifier('generatePath', null, [
                'label' => 'Strom',
            ])
            ->add('internalName', null, [
                'label' => 'Interní název',
            ])
            ->add('active', null, [
                'label' => 'Aktivní?',
                'editable' => true,
            ])
            ->add('level', null, [
                'label' => 'Úroveň zanoření',
            ])
            ->add('_action', null, [
                'label' => 'Možnosti',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param FormMapper $formMapper
     *
     * @throws \RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var MenuItem $item */
        $item = $this->getSubject();

        $formMapper
            ->with('Popis', ['class' => 'col-md-6'])
                ->add('title', null, [
                    'label' => 'Veřejný název položky menu',
                    'required' => true,
                ])
                ->add('active', null, [
                    'label' => 'Aktivní?',
                ])
            ->end()
        ;

        if ($item->getParent() === null) {
            $formMapper
                ->with('Ukotvení menu')
                    ->add('anchor', null, [
                        'label' => 'Ukotvení menu v šabloně (interní)',
                    ])
                ->end();
        }

        $formMapper
            ->with('Nastavení', ['class' => 'col-md-6'])
                ->add('type', 'choice', [
                    'label' => 'Typ obsahu',
                    'choices' => MenuItem::$typeChoices,
                ])
                ->add('route', null, [
                    'label' => 'Routa',
                    'attr' => [
                        'class' => 'menu-needed menu-route',
                    ],
                ])
                ->add('link', null, [
                    'label' => 'Odkaz',
                    'attr' => [
                        'class' => 'menu-needed menu-link',
                    ],
                ])
                ->add('linkTarget', 'choice', [
                    'label' => 'Odkaz do',
                    'choices' => MenuItem::$linkTargetChoices,
                    'attr' => [
                        'class' => 'menu-needed menu-route menu-link',
                    ],
                ])
            ->end()
        ;
    }

    /**
     * @param MenuItem $object
     */
    public function postUpdate($object)
    {
        $object->update();
    }

    /**
     * @param MenuItem $object
     */
    public function postPersist($object)
    {
        $object->update();
        $object->prePersist();
    }
}
