<?php
class Que_Mytunes_Test_Config_Config extends EcomDev_PHPUnit_Test_Case_Config
{
	
	/**
	 * Check version of module
	 * 
	 * @test
	 */
	public function checkVersion()
	{
		$this->assertModuleVersion('0.1.1');
	}
	
	/**
	 * Test Module dependencies
	 * 
	 * @test
	 */
	public function moduleDependencies()
	{
		$this->assertModuleDepends('Mxperts_Jquery');
	}
	
	/**
	 * Test class alias overrides for models, blocks and helpers
	 * 
	 * @test
	 */
	public function classAliasOverrides()
	{
		$this->assertBlockAlias('checkout/onepage_payment', 'Que_Mytunes_Block_Checkout_Onepage_Payment');
	}
	
	/**
	 * Test class prefix definitions for models, blocks and helpers
	 * 
	 * @test
	 */
	public function classDefinitions()
	{
		$this->assertConfigNodeHasChild('global/models', 'mytunes');
		$this->assertConfigNodeHasChild('global/models/mytunes', 'class');
		$this->assertConfigNodeValue('global/models/mytunes/class', 'Que_Mytunes_Model');
		
		$this->assertConfigNodeHasChild('global/blocks', 'mytunes');
		$this->assertConfigNodeHasChild('global/blocks/mytunes', 'class');
		$this->assertConfigNodeValue('global/blocks/mytunes/class', 'Que_Mytunes_Block');
		
		$this->assertConfigNodeHasChild('global/helpers', 'mytunes');
		$this->assertConfigNodeHasChild('global/helpers/mytunes', 'class');
		$this->assertConfigNodeValue('global/helpers/mytunes/class', 'Que_Mytunes_Helper');
	}
	
	/**
	 * Test for definitions of Mytunes default config settings in XML
	 * 
	 * @test
	 */
	public function defaultSettingsDefinedInXML()
	{
		// the module namespace
		$this->assertConfigNodeHasChild('default', 'mytunes_settings');
		
		// global presets
		$this->assertConfigNodeHasChild('default/mytunes_settings', 'globals');
		$this->assertConfigNodeHasChild('default/mytunes_settings/globals', 'enabled');
		$this->assertConfigNodeHasChild('default/mytunes_settings/globals', 'albumprice');
		$this->assertConfigNodeHasChild('default/mytunes_settings/globals', 'trackprice');
		$this->assertConfigNodeHasChild('default/mytunes_settings/globals', 'unlimited_downloads');
		$this->assertConfigNodeHasChild('default/mytunes_settings/globals', 'num_downloads_if_limited');
		$this->assertConfigNodeHasChild('default/mytunes_settings/globals', 'open_download_in_new_window');
		
		// payment settings
		$this->assertConfigNodeHasChild('default/mytunes_settings', 'payment');
		$this->assertConfigNodeHasChild('default/mytunes_settings/payment', 'allowall');
		$this->assertConfigNodeHasChild('default/mytunes_settings/payment', 'restrict');
		
		// sox settings
		$this->assertConfigNodeHasChild('default/mytunes_settings', 'sox');
		$this->assertConfigNodeHasChild('default/mytunes_settings/sox', 'binary');
		$this->assertConfigNodeHasChild('default/mytunes_settings/sox', 'min_version');
		$this->assertConfigNodeHasChild('default/mytunes_settings/sox', 'transcode_ogg');
		$this->assertConfigNodeHasChild('default/mytunes_settings/sox', 'trim_start_default');
		$this->assertConfigNodeHasChild('default/mytunes_settings/sox', 'trim_end_default');
		$this->assertConfigNodeHasChild('default/mytunes_settings/sox', 'fade_duration_default');
		$this->assertConfigNodeHasChild('default/mytunes_settings/sox', 'autorename_samples');
	}
	
	/**
	* Test if Mytunes default config settings are available through Mage Store Config 
	*
	* @test
	*/
	public function defaultSettingsAvailableThroughStoreConfig()
	{
		// Mytunes Settings - Global
		$this->assertEquals(
			"1",
			Mage::getStoreConfig('mytunes_settings/globals/enabled')
		);
		$this->assertEquals(
			"7.99",
			Mage::getStoreConfig('mytunes_settings/globals/albumprice')
		);
		$this->assertEquals(
			"0.59",
			Mage::getStoreConfig('mytunes_settings/globals/trackprice')
		);
		$this->assertEquals(
			"1",
			Mage::getStoreConfig('mytunes_settings/globals/unlimited_downloads')
		);
		$this->assertEquals(
			"5",
			Mage::getStoreConfig('mytunes_settings/globals/num_downloads_if_limited')
		);
		$this->assertEquals(
			"1",
			Mage::getStoreConfig('mytunes_settings/globals/open_download_in_new_window')
		);
		
		// Mytunes Settings - Payment
		$this->assertEquals(
			"1",
			Mage::getStoreConfig('mytunes_settings/payment/allowall')
		);
		$this->assertEquals(
			"",
			Mage::getStoreConfig('mytunes_settings/payment/restrict')
		);
		
		// Mytunes Settings - Sox
		$this->assertEquals(
			"/usr/bin/sox",
			Mage::getStoreConfig('mytunes_settings/sox/binary')
		);
		$this->assertEquals(
			"14.3.0",
			Mage::getStoreConfig('mytunes_settings/sox/min_version')
		);
		$this->assertEquals(
			"00:30",
			Mage::getStoreConfig('mytunes_settings/sox/trim_start_default')
		);
		$this->assertEquals(
			"01:15",
			Mage::getStoreConfig('mytunes_settings/sox/trim_end_default')
		);
		$this->assertEquals(
			"1",
			Mage::getStoreConfig('mytunes_settings/sox/fade_duration_default')
		);
		$this->assertEquals(
			"1",
			Mage::getStoreConfig('mytunes_settings/sox/autorename_samples')
		);
	}
}