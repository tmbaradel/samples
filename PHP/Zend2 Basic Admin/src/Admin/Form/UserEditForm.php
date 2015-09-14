<?php
namespace Admin\Form;

use Zend\Form\Element;

use Zend\Form\Form;

class UserEditForm extends Form
{
    public function __construct($name = null, $fields, $modules = null)
    {
    	
    	parent::__construct($name);
    	$this->setAttribute('method', 'post');
        
    	
    	
    	foreach($fields as $field_name =>$field){
	        switch($field_name){
	        	case 'id':
		        	$this->add(array(
			            'name' => $field_name,
			            'value'  =>$field,	
			            'attributes' => array(
			                'type'  => "hidden",
			            ),
			            'options' => array(
			                'id'	=>"id_".$field_name,
			            ),
			        ));
	        	break;
	        	case 'password':
	        		$this->add(array(
	        				'name' => $field_name,
	        				'attributes' => array(
	        						'type'  => "password",
	        				),
	        				'options' => array(
	        						'id'	=>"id_".$field_name,
	        				),
	        		));
	        	break;
	        	case 'modules':
	        		if($modules){
	        			$all_mods = array ();
	        			foreach ($modules as $mod){
	        				$all_mods[$mod->id] = $mod->name;
	        			}
	        			$this->add(array(
			            	'type' => 'Zend\Form\Element\MultiCheckbox',
			            	'name' => 'Modules',
			            	'options' => array(
			                'value_options' => $all_mods,
			            	),
			            	'attributes' =>$this->getModuleToCheck(json_decode($field))
			            	
	        			));
		        	}
	        	break;
	        	default:
	        		$this->add(array(
	        				'name' => $field_name,
	        				'value'  =>$field,
	        				'attributes' => array(
	        						'type'  => "text",
	        				),
	        				'options' => array(
	        						'id'	=>"id_".$field_name,
	        						'value'  =>$field
	        				),
	        		));
	        	break;
	        }
    	}
    	$btn_val = ($name=='user_new')? 'Submit': 'Change';
    	$this->add(array(
    			'name' => 'submit',
    			'attributes' => array(
    					'type'  => 'submit',
    					'value' => $btn_val,
    					'id' => 'submitbutton',
    			),
    	));
    }
    
    protected function getModuleToCheck($userModules)
    {
    	$ret["value"] = array();
    	if(count($userModules)>0)
    	{
	    	foreach($userModules as $mod){
	    		$ret_array[] = $mod->id;
	    	}
	    	$ret["value"] = $ret_array;
    	}
    	return $ret;	
    }
}