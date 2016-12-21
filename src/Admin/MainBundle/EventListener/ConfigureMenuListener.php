<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 6/9/2015
 * Time: 1:00 PM.
 */

namespace Admin\MainBundle\EventListener;

use Admin\MainBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Admin\MainBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigureMain(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $userMenu = $event->getFactory()->createItem('entity.config')
            ->setAttributes(array('icon' => 'fa fa-list'));

        $userMenu->addChild($event->getFactory()->createItem('Documento', array('route' => 'teste_index')));

        $menu->addChild($userMenu);
    }
}
