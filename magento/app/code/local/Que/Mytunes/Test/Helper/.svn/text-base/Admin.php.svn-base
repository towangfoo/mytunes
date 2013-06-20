<?php
class Que_Mytunes_Test_Helper_Admin extends EcomDev_PHPUnit_Test_Case
{
	
	/**
	 * Test the calcuation of duration between two timestamps
	 * 
	 * @test
	 * @dataProvider dataProvider
	 */
	public function getDuration($tcStart, $tcEnd, $expected)
	{
		$helper = Mage::helper('mytunes/admin');
		$this->assertEquals(
			$expected,
			$helper->getDuration($tcStart, $tcEnd)
		);
	}
	
	/**
	 * Check that a negative duration marker setting throws an Exception
	 * 
	 * @test
	 */
	public function getDurationCheckException()
	{
		$this->setExpectedException('Mage_Core_Exception');
		
		$helper = Mage::helper('mytunes/admin');
		$helper->getDuration('00:45', '00:10');
	}
	
}