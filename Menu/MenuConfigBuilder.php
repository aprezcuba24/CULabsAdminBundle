<?php

namespace CULabs\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use CULabs\AdminBundle\Event\MenuEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

class MenuConfigBuilder
{
    protected $factory;
    protected $router;
    protected $event_dispatcher;
    protected $security_context;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory, RouterInterface $router, EventDispatcherInterface $event_dispatcher, SecurityContextInterface $security_context)
    {
        $this->factory          = $factory;
        $this->router           = $router;
        $this->event_dispatcher = $event_dispatcher;
        $this->security_context = $security_context;
    }
    public function getMenu(Request $request, $menu_config, array $options = array())
    {   
        if (isset($menu_config['roles']) && !$this->security_context->isGranted($menu_config['roles']))
            return;
        
        $menu = $this->factory->createItem('root');
        
        $label = isset($menu_config['label'])? $menu_config['label']: $this->buildLabel('root');
        $menu->setLabel($label);
        
        if (isset($menu_config['route'])) {
            
            $path = $this->router->generate($menu_config['route']);
            $menu->setUri($path);
        }
        
        $request_uri = $request->getRequestUri();
        $options['remove_get_parameters'] = isset($options['remove_get_parameters'])? $options['remove_get_parameters']: true;
        if ($options['remove_get_parameters']) {
            
            $request_uri = explode('?', $request_uri);
            $request_uri = $request_uri[0];
        }
        
//        $menu->setCurrentUri($request_uri);
        
        $this->event_dispatcher->dispatch(MenuEvent::CONFIGURE, new MenuEvent($this->factory, $menu));
        
        $this->doMenu($menu, $menu_config['items']);
        return $menu;
    }
    protected function doMenu($menu, $menu_config)
    {
        foreach ($menu_config as $key => $item) {
            
            if (isset($item['roles']) && !$this->security_context->isGranted($item['roles']))
                continue;
            
            $child = new MenuItem($key, $this->factory);
            
            $label = isset($menu_config['label'])? $menu_config['label']: $this->buildLabel($key);            
            $child->setLabel($label);
            
            if (isset($item['route'])) {
                
                $path  = $this->router->generate($item['route']);
                $child->setUri($path);
            }
            
            $this->event_dispatcher->dispatch(MenuEvent::CONFIGURE, new MenuEvent($this->factory, $child));
            
            $menu->addChild($child);
            
            if (isset($item['items'])) {
                $this->doMenu($child, $item['items']);
            }
        }
    }
    protected function buildLabel($key)
    {
        return 'label_menu_'.$key;
    }
}