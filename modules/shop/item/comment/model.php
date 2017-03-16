<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS 6\Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2013 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Comment_Model extends Comment_Model{
	/**
	 * Name of the table
	 * @var string
	 */	protected $_tableName = 'comments';

	/**
	 * Name of the model
	 * @var string
	 */	protected $_modelName = 'comment';
	/**
	 * Backend callback method
	 * @return string
	 */	public function view()	{		ob_start();		$oShop_Item = $this->Comment_Shop_Item->Shop_Item;		$href = $oShop_Item->Shop->Structure->getPath() . $oShop_Item->getPath();
		$oSite = $oShop_Item->Shop->Site;
		$oSite_Alias = $oSite->getCurrentAlias();
		!is_null($oSite_Alias) && $href = 'http://' . $oSite_Alias->name . $href;
		Core::factory('Core_Html_Entity_A')			->href($href)			->target('_blank')			->add(				Core::factory('Core_Html_Entity_Img')					->src('/admin/images/comment_view.gif')			)			->execute();		return ob_get_clean();	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		// save original _nameColumn
		$nameColumn = $this->_nameColumn;
		$this->_nameColumn = 'subject';
		$newObject = parent::copy();		$aNewComment_Shop_Item = clone $this->Comment_Shop_Item;		$newObject->add($aNewComment_Shop_Item);

		// restore original _nameColumn
		$this->_nameColumn = $nameColumn;
		return $newObject;	}}