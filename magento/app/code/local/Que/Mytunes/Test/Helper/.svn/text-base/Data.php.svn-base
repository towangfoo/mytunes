<?php
class Que_Mytunes_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
	
	/**
	 * Test retrieval of original Mage SKUs from Download SKUs
	 * 
	 * @test
	 * @dataProvider dataProvider
	 */
	public function getMageSku($downloadSku, $expectedMageSku)
	{
		$helper = Mage::helper('mytunes');
		$this->assertEquals(
			$expectedMageSku,
			$helper->getMageSku($downloadSku)
		);
	}
	
	/**
	* Test detection of download types from Mytunes SKUs 
	*
	* @test
	* @dataProvider dataProvider
	*/
	public function getDownloadTypeBySku($sku, $expected)
	{
		$helper = Mage::helper('mytunes');
		$this->assertEquals(
			$expected,
			$helper->getDownloadTypeBySku($sku)
		);
	}
	
	/**
	 * Test retrieval of track numbers from Mytunes SKUs 
	 *
	 * @test
	 * @dataProvider dataProvider
	 */
	public function getTrackNumberFromDownloadSku($sku, $expectedNumber)
	{
		$helper = Mage::helper('mytunes');
		$this->assertEquals(
			$expectedNumber === "false" ? false : (int) $expectedNumber,
			$helper->getTrackNumberFromDownloadSku($sku)
		);
	}
	
	/**
	* remove the track indicator from a Mytunes download SKU
	*
	* @test
	* @dataProvider dataProvider
	*/
	public function stripTrackFromDownloadSku($sku, $expected)
	{
		$helper = Mage::helper('mytunes');
		$this->assertEquals(
			$expected,
			$helper->stripTrackFromDownloadSku($sku)
		);
	}
}