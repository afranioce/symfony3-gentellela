<?php

namespace Admin\MainBundle\Menu;

use Admin\MainBundle\Event\ConfigureMenuEvent;
use Admin\MainBundle\MenuEvents;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MainMenuBuilder implements ContainerAwareInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param RequestStack $requestStack
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu(RequestStack $requestStack)
    {
        $menu = $this->factory->createItem('root', array('route' => 'admin_main_homepage'));
        $menu->setChildrenAttributes(array('class' => 'nav side-menu'));

        $this->container->get('event_dispatcher')->dispatch(
            MenuEvents::CONFIGURE_MAIN,
            new ConfigureMenuEvent($this->factory, $menu)
        );

        $menu->addChild($this->factory->createItem('menu.config'));

        $this->container->get('event_dispatcher')->dispatch(
            MenuEvents::CONFIGURE_SETTINGS,
            new ConfigureMenuEvent($this->factory, $menu)
        );
        $this->reorderMenuItems($menu);

        return $menu;
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     */
    public function reorderMenuItems($menu)
    {
        $menuOrderArray = array();
        $addLast = array();
        $alreadyTaken = array();

        foreach ($menu->getChildren() as $key => $menuItem) {

            if ($menuItem->hasChildren()) {
                $this->reorderMenuItems($menuItem);
            }

            $orderNumber = $menuItem->getExtra('orderNumber');

            if ($orderNumber != null) {
                if (!isset($menuOrderArray[$orderNumber])) {
                    $menuOrderArray[$orderNumber] = $menuItem->getName();
                } else {
                    $alreadyTaken[$orderNumber] = $menuItem->getName();
                    // $alreadyTaken[] = array('orderNumber' => $orderNumber, 'name' => $menuItem->getName());
                }
            } else {
                $addLast[] = $menuItem->getName();
            }
        }

        // sort them after first pass
        ksort($menuOrderArray);

        // handle position duplicates
        if (count($alreadyTaken)) {
            foreach ($alreadyTaken as $key => $value) {
                // the ever shifting target
                $keysArray = array_keys($menuOrderArray);

                $position = array_search($key, $keysArray);

                if ($position === false) {
                    continue;
                }

                $menuOrderArray = array_merge(array_slice($menuOrderArray, 0, $position), array($value), array_slice($menuOrderArray, $position));
            }
        }

        // sort them after second pass
        ksort($menuOrderArray);

        // add items without ordernumber to the end
        if (count($addLast)) {
            foreach ($addLast as $key => $value) {
                $menuOrderArray[] = $value;
            }
        }

        if (count($menuOrderArray)) {
            $menu->reorderChildren($menuOrderArray);
        }
    }

}
