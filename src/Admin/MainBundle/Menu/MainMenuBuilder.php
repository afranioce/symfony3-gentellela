<?php

namespace Admin\MainBundle\Menu;

use Admin\MainBundle\Event\ConfigureMenuEvent;
use Admin\MainBundle\MenuEvents;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
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
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu(RequestStack $requestStack)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array('class' => 'nav side-menu'));

        $menu->addChild($this->factory->createItem('menu.dashboard', array('route' => 'admin_main_homepage')))
            ->setAttributes(array('icon' => 'fa fa-home'));

        $this->container->get('event_dispatcher')->dispatch(
            MenuEvents::CONFIGURE_MAIN,
            new ConfigureMenuEvent($this->factory, $menu)
        );

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

                $menuOrderArray = array_merge(
                    array_slice($menuOrderArray, 0, $position),
                    array($value),
                    array_slice($menuOrderArray, $position)
                );
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

    /**
     * @param Bundle Controller $class
     *
     * @return \JMS\SecurityExtraBundle\Metadata\ClassMetadata
     */
    public function getMetadata($controllerClass)
    {
        return $this->metadataReader->loadMetadataForClass(new \ReflectionClass($controllerClass));
    }

    /**
     * @param $routeName
     *
     * @return bool from AuthorizationCheckerInterface
     */
    public function hasRouteAccess($routeName)
    {
        $token = $this->tokenStorage->getToken();
        if ($token->isAuthenticated()) {
            //Logout is assigned in services so it does not have a default(_controller)
            //$fullyQualifiedClassNameAndMethod
            if ($routeName != 'user_auth_logout') {
                $route = $this->router->getRouteCollection()->get($routeName);
                $fullyQualifiedClassNameAndMethod = $route->getDefault('_controller');
                list($routeClass, $routeMethod) = explode('::', $fullyQualifiedClassNameAndMethod, 2);

                $metadata = $this->getMetadata($routeClass);

                if (!isset($metadata->methodMetadata[$routeMethod])) {
                    return false;
                }

                foreach ($metadata->methodMetadata[$routeMethod]->roles as $role) {
                    if ($this->authorizationChecker->isGranted($role)) {
                        return true;
                    }
                }

                return false;
            } elseif (($routeName == 'user_auth_logout')
                && ((true === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY'))
                    || (true === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
                )
            ) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     *
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function filterMenuByRouteAuthorization(ItemInterface $menu)
    {
        /** @var \Knp\Menu\MenuItem $child */
        foreach ($menu->getChildren() as $child) {
            $routes = $child->getExtra('routes');

            if ($routes !== null) {
                $route = current(current($routes));

                if ($route && !$this->hasRouteAccess($route)) {
                    $menu->removeChild($child);
                }
            } elseif ($child->hasChildren()) {
                $this->filterMenuByRouteAuthorization($child);
            }
        }

        return $menu;
    }
}
