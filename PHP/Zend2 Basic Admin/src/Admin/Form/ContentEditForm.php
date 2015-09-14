<?php
namespace Admin\Form;

use Zend\Form\Form;

class ContentEditForm extends Form
{
    public function __construct($name = null,$fields)
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
			                'id'	=>"id_".$field_name,
			                 
			            ),
			        ));
	        	break;
	        	case 'title':
	        		$this->add(array(
	        				'name' => $field_name,
	        				'value'  =>$field,
	        				'attributes' => array(
	        						'type'  => "text",
	        						'id'	=>"id_".$field_name,
	        				),
	        				
	        		));
	        	break;	
	        	case 'content':
	        		$this->add(array(
	        				'name' => $field_name,
	        				'value'  =>$field,
	        				'attributes' => array(
	        						'type'  => "textarea",
	        						'id'	=>"id_".$field_name,
	        						'class' =>'txt_area'
	        				)
	        				
	        		));
	        	break;
	        	case 'author':
	        		if($field!=''){
	        			$this->add(array(
	        					'name' => $field_name,
	        					'value'  =>$field,
	        					'attributes' => array(
	        							'type'  => "hidden",
	        							'id'	=>"id_".$field_name,
	        					),
	        			));
	        		}
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
}