<?php
namespace Admin\Controller;



use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\LoginForm; 
use Admin\Model\User; 

use Zend\Session\Container;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;
use Zend\Debug;

class AdminController extends AbstractActionController
{
    protected $userTable;
    
    protected $session;
    
    protected $user_details;
    
    protected $user_modules;
    
    
    public function __construct() {
        $this->session = new Container('admin');
    }
    
    public function indexAction()
    {
         //check if user is logged 
         $this->checkUserLogged();
         $this->user_details = $this->session->offsetGet('user');
         $this->layout()->logged = 1;
         //check and redirect in the first controller
         $this->user_modules = json_decode($this->user_details->modules);
         if( count($this->user_modules)>0 ){//if the user has at least a module assigned
             return $this->redirect()->toRoute($this->user_modules[0]->name.'admin',array('controller'=>$this->user_modules[0]->name.'admin','action' => 'index'));
         }
         return new ViewModel();
    }
    
    public function loginAction()
    {
        
        $form  = new LoginForm();
        $request = $this->getRequest();
        $error_msg = '';
        if($request->isPost()){
           $user = new User();
           $form->setInputFilter($user->getInputFilter());
           $form->setData($request->getPost());
           if ($form->isValid()) {
               $user = $this->getUSerTable()->getUser($request->getPost('username'),$request->getPost('password'));
               if($user){
                   $this->session->offsetSet('user', $user);
                   return $this->redirect()->toRoute('admin',array('controller'=>'admin'));
               }
               else{
               		$error_msg = 'Wrong username or password';
               }
           }
        }
        return array('form' => $form,'error_msg'=>$error_msg);
        
    }
    
    public function logoutAction()
    {
        $this->session->offsetUnset('user');
        return $this->redirect()->toRoute('admin',array('controller'=>'admin','action' => 'login'));
    }
    
    protected function checkUserLogged(){
        if(!$this->session->offsetGet('user')){
           return $this->redirect()->toRoute('admin',array('controller'=>'admin','action' => 'login'));
        }
        return true;
    }
    
    protected function setLayoutVariables($options = array())
    {
    	$this->checkUserLogged();
    	$this->user_details = $this->session->offsetGet('user');
    	if(array_key_exists("breadcrumbs", $options)){
    		$this->getEvent()->getViewModel()->breadcrumbs = $options["breadcrumbs"];
    	}
    	$this->getEvent()->getViewModel()->user_modules = $this->user_modules;
    	$this->getEvent()->getViewModel()->logged = 1;
    	$this->getEvent()->getViewModel()->username = $this->user_details->username;
    }
    
    protected function setAdminModVars($breadcrumbs=null)
    {
    	$this->user_details = $this->session->offsetGet('user');
    	$this->user_modules = json_decode($this->user_details->modules);
    	if($breadcrumbs){
    		$this->setLayoutVariables(array("breadcrumbs"=>$breadcrumbs));
    	}
    }
    
    public function getUSerTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }
    
    
    
}