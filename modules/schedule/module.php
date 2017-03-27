<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule.
 *
 * @package HostCMS 6\Schedule
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2015 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Schedule_Module extends Core_Module
	 * Module version
	 * @var string
	 */
	public $version = '6.5';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2016-02-03';
	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'schedule';

	/**
	 * Constructor.
	 */
		parent::__construct();

				'ico' => 'fa fa-calendar-check-o',