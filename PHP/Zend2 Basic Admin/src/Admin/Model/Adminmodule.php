<?php
namespace Admin\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Adminmodule implements InputFilterAwareInterface
{
	public $id;
	public $name;
	public $order;
	public $sections;
	protected $inputFilter;

	public function exchangeArray($data)
	{
		$this->id     = (isset($data['id'])) ? $data['id'] : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->order = (isset($data['name'])) ? $data['order'] : null;
		$this->sections = (isset($data['sections'])) ? $data['sections'] : null;
	}

	// Add the following method:
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}


	// Add content to this method:
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}



	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory     = new InputFactory();

			$inputFilter->add($factory->createInput(array(
					'name'     => 'name',
					'required' => true,
					'filters'  => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
					),
					'validators' => array(
							array(
									'name'    => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min'      => 1,
											'max'      => 100,
									),
							),
					),
			)));

			$inputFilter->add($factory->createInput(array(
					'name'     => 'order',
					'required' => true,
					'filters'  => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
					),
					'validators' => array(
							array(
									'name'    => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min'      => 3,
											'max'      => 100,
									),
							),
					),
			)));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}
}