<?php
class Auth_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * Initialize Routes for the Auth Module
     *
     * @return void
     */
    public function _initRoutes()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        // sign in link
        $route = new Zend_Controller_Router_Route(
            'login',
            array(
                'module'     => 'auth',
                'controller' => 'index',
                'action'     => 'signin'
            )
        );
        $router->addRoute('login', $route);
        
        // sign out route
        $routeSignOut = new Zend_Controller_Router_Route(
            'logout',
            array(
                'module'     => 'auth',
                'controller' => 'index',
                'action'     => 'signout'
            )
        );
        $router->addRoute('logout', $routeSignOut);
        
        // sign up route
        $routeSignUp = new Zend_Controller_Router_Route(
            'register',
            array(
                'module'     => 'auth',
                'controller' => 'index',
                'action'     => 'signup'
            )
        );
        $router->addRoute('register', $routeSignUp);
        
        $route = new Zend_Controller_Router_Route(
            'update-password',
            array(
                'module'     => 'auth',
                'controller' => 'index',
                'action'     => 'update-password'
            )
        );
        $router->addRoute('update-password', $route);
        
        $route = new Zend_Controller_Router_Route(
            'forgot-password',
            array(
                'module'     => 'auth',
                'controller' => 'index',
                'action'     => 'forgot-password'
            )
        );
        $router->addRoute('forgot-password', $route);
    }
    
    /**
     * Initialize the action helpers
     * 
     * @return void
     */
    protected function _initActionHelpers()
    {
        Zend_Controller_Action_HelperBroker::addPath(
            dirname(__FILE__) . "/controllers/actionhelpers/",
            "Auth_Controller_Action_Helper_"
        );
    }

}

