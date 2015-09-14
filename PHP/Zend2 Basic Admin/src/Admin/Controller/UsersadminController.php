<?php
namespace Admin\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\LoginForm; 
use Admin\Model\User; 
use Admin\Model\Module;
use Admin\Controller\AdminController;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Debug\Debug;
use Admin\Form\UserEditForm;

class UsersadminController extends AdminController
{
    
	protected $route_name;
	protected $cls_name;
	protected $adminmoduleTable;
	
	public function __construct() {
        parent::__construct();
        $this->route_name = 'usersadmin';
    }
    
    
    public function indexAction() 
    {
    	$this->setAdminModVars(array("users"=>array("href"=>"","class"=>"current")));
    	//get all the users
        $all_users = $this->getUSerTable()->fetchAll();
        $fields = array("id","username");
        $edit_address = $this->url()->fromRoute($this->route_name,array("controller"=>$this->route_name,"action"=>"edituser"));
        $delete_address = $this->url()->fromRoute($this->route_name,array("controller"=>$this->route_name,"action"=>"deleteuser"));
        $table = new ViewModel(array('to_show'=>$all_users,
        							   'fields'=>$fields,
        							   'edit_address'=>$edit_address,
        							   'delete_address'=>$delete_address
        		 			   ));
        $table->setTemplate('components/table');
        $msg = '';
        $request = $this->getRequest();
        if($request->isGet()){
        	if($this->params()->fromQuery('message')=="del" && $this->params()->fromQuery('user')){
        		$msg = array("alert_info","User with Id ".(int)$this->params()->fromQuery('user')." deleted");
        	}
        }
        $view = new ViewModel(array("msg"=>$msg));
        $view->addChild($table,'table');
        return $view;
    }
    
	public function edituserAction() 
	{
		//set vars and pass the breacrumbs
		$this->setAdminModVars(array("users"=>array("href"=>$this->url()->fromRoute($this->route_name),"class"=>"current"),
		    						  "edit_user"=>array("href"=>"","class"=>"current")));
		 
		$edit_id = (int) $this->params()->fromRoute('id', 0);
		$user = $this->getUSerTable()->getUserById($edit_id);
		$all_modules = $this->getAdminmoduleTable()->fetchAll();
		
		if(!$user){//redirect if user doesn't exists
			$this->redirect()->toRoute($this->route_name,array('controller'=>$this->route_name,'action' => 'index'));
		}
		$edit_form = new UserEditForm('user_edit',$user,$all_modules);
		//check if there is a request
		$request = $this->getRequest();
		if ($request->isPost()) {
			$this->sendToSaveModule($request, $edit_form,$user);
		}
		$user->password = '';
		$edit_form->bind($user);
		$form_tpl = new ViewModel(array("form"=>$edit_form,"module_name"=>"User"));
		$form_tpl->setTemplate('components/edit_form');
		
		$view = new ViewModel();
		$view->addChild($form_tpl,'edit_form');
		
		return $view;
	}
	
	
	public function newuserAction()
	{
		$this->setAdminModVars(array("users"=>array("href"=>$this->url()->fromRoute('usersadmin'),"class"=>"current"),
									  "new_user"=>array("href"=>"","class"=>"current")));
		$all_modules = $this->getAdminmoduleTable()->fetchAll();
		
		$tmp_user = new User();
		$edit_form = new UserEditForm('user_new', $tmp_user, $all_modules);
		//check the post
		$request = $this->getRequest();
		$msg = "";
		if ($request->isPost()) {
			if($this->sendToSaveModule($request, $edit_form) === -1){
				$msg = array("alert_error","Username already used");		
			}
			else{
				$msg = array("alert_success","User added");
			}
		}
		
		$form_tpl = new ViewModel(array("form"=>$edit_form,"module_name"=>"User"));
		$form_tpl->setTemplate('components/edit_form');
		$view = new ViewModel(array("msg"=>$msg));
		$view->addChild($form_tpl,'new_form');
		return $view;
	}
	
	public function yourProfileAction()
	{
		$this->setAdminModVars();
		$this->redirect()->toUrl('/usersadmin/edituser/'.$this->user_details->id);
	}
	
	public function viewallusersAction()
	{
		$this->redirect()->toRoute($this->route_name);
	}
	
	public function deleteuserAction()
	{
		$delete_id = (int) $this->params()->fromRoute('id', 0);
		$user = $this->getUSerTable()->getUserById($delete_id);
		if(!$user){//redirect if user doesn't exists
			return $this->redirect()->toRoute('usersadmin');
		}
		$this->getUSerTable()->deleteUser($delete_id);
		return $this->redirect()->toUrl('/'.$this->route_name.'?message=del&user='.$delete_id);
	}
    
	protected function setAdminModVars($breadcrumbs=null)
	{
		$this->user_details = $this->session->offsetGet('user');
		$this->user_modules = json_decode($this->user_details->modules);
		if($breadcrumbs){
			$this->setLayoutVariables(array("breadcrumbs"=>$breadcrumbs));
		}
	}
    
	protected function sendToSaveModule($request,$form,$user = null)
	{
		if(!$user){
			$user = new User();
		}
		$form->setInputFilter($user->getInputFilter());
		$form->setData($request->getPost());
		if ($form->isValid()) {
			$user->exchangeArray($form->getData());
			return $this->getuserTable()->saveUser($user);
		}
		return false;
	}
	
    public function getUSerTable()
    {
    	if (!$this->userTable) {
    		$sm = $this->getServiceLocator();
    		$this->userTable = $sm->get('Admin\Model\UserTable');
    	}
    	return $this->userTable;
    }
    
    public function getAdminmoduleTable()
    {
    	if (!$this->adminmoduleTable) {
    		$sm = $this->getServiceLocator();
    		$this->adminmoduleTable = $sm->get('Admin\Model\AdminmoduleTable');
    	}
    	return $this->adminmoduleTable;
    }
}
