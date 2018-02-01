<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Module
 *
 * @package HostCMS
 * @subpackage Company
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Module extends Core_Module
	 * Module version
	 * @var string
	 */
	public $version = '6.7';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2017-12-25';
	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'company';
	
	/**
	 * Constructor.
	 */
		parent::__construct();

				'ico' => 'fa fa-building-o',
}