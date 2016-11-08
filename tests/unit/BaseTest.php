<?php
class BaseTest extends PHPUnit_Framework_TestCase
{
	
	public function getPrivateProperty($object, $attributeName)
	{
		return $this->getObjectAttribute($object, $attributeName);
	}
	
}