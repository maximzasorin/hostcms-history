<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract skin
 *
 * @package HostCMS 6\Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Skin
{
	/**
	 * Show header
	 */
	abstract public function header();

	/**
	 * Show footer
	 */
	abstract public function footer();

	/**
	 * Show main part of page
	 */
	abstract public function index();

	/**
	 * Skin name
	 * @var string
	 */
	protected $_skinName = 'default';

	/**
	 * Set skin name
	 * @param string $skinName skin name
	 * @return self
	 */
	public function skinName($skinName)
	{
		$this->_skinName = $skinName;
		return $this;
	}

	/**
	 * Get skin name
	 * @return string
	 */
	public function getSkinName()
	{
		return $this->_skinName;
	}

	/**
	 * Mode
	 * @var string
	 */
	protected $_mode = NULL;

	/**
	 * Set mode
	 * @param string $mode mode
	 * @return self
	 */
	public function setMode($mode)
	{
		$this->_mode = $mode;
		return $this;
	}

	/**
	 * Get mode
	 * @return string
	 */
	public function getMode()
	{
		return $this->_mode;
	}

	/**
	 * Skin title
	 * @var string
	 */
	protected $_title;

	/**
	 * Set title
	 * @param string $title title
	 * @return self
	 */
	public function title($title)
	{
		$this->_title = $title;
		return $this;
	}

	/**
	 * List of JS files
	 * @var array
	 */
	protected $_js = array();

	/**
	 * Add JS file path
	 * @param string $path file path
	 * @return self
	 */
	public function addJs($path)
	{
		$this->_js[] = $path;
		return $this;
	}

	/**
	 * Get array of JS's paths
	 * @return array
	 */
	public function getJs()
	{
		return $this->_js;
	}

	/**
	 * List of CSS files
	 * @var array
	 */
	protected $_css = array();

	/**
	 * Add CSS file path
	 * @param string $path file path
	 * @return self
	 */
	public function addCss($path)
	{
		$this->_css[] = $path;
		return $this;
	}

	/**
	 * Get array of CSS's paths
	 * @return array
	 */
	public function getCss()
	{
		return $this->_css;
	}

	/**
	 * Answer
	 * @var object
	 */
	protected $_answer = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$sAnswerName = 'Skin_' . ucfirst($this->_skinName) . '_Answer';
		$this->_answer = new $sAnswerName();
	}

	/**
	 * The singleton instances.
	 * @var array
	 */
	static public $instance = array();

	/**
	 * Get instance of object
	 * @param string $name name of skin
	 * @return mixed
	 */
	static public function instance($name = NULL)
	{
		is_null($name) && $name = isset($_SESSION['skin'])
			? $_SESSION['skin']
			: Core::$mainConfig['skin'];

		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$skin = 'Skin_' . ucfirst($name);
			self::$instance[$name] = new $skin();

			// Set skinname
			self::$instance[$name]->skinName($name);
		}

		return self::$instance[$name];
	}

	/**
	 * Set answer
	 * @return string
	 */
	public function answer()
	{
		return $this->_answer;
	}

	/**
	 * Mark of current version
	 * @return int
	 */
	protected function _getTimestamp()
	{
		$currentVersion = defined('CURRENT_VERSION') ? CURRENT_VERSION : '6.0';
		return abs(Core::crc32($currentVersion . $currentVersion));
	}

	/**
	 * Get skin's module name
	 * @param string $modulePath module path
	 * @return string
	 */
	public function getSkinModuleName($modulePath)
	{
		return "Skin_{$this->_skinName}_Module_{$modulePath}_Module";
	}

	/**
	 * Get modules list which has been approved for current user
	 * @return array
	 */
	public function _getAllowedModules()
	{
		$oUser = Core_Entity::factory('User')->getCurrent();

		if (!$oUser)
		{
			return array();
		}

		$oModule = Core_Entity::factory('Module');
		$oModule->queryBuilder()->where('active', '=', 1);

		if ($oUser->superuser == 0)
		{
			$oModule->queryBuilder()
				->select('modules.*')
				->join('user_modules', 'modules.id', '=', 'user_modules.module_id',
				array(
					array('AND' => array('user_group_id', '=', $oUser->user_group_id)),
					array('AND' => array('site_id', '=', CURRENT_SITE))
				));
		}

		return $oModule->findAll();
	}

	/**
	 * Language
	 * @var string
	 */
	protected $_lng = NULL;

	/**
	 * Get language
	 * @return string
	 */
	public function getLng()
	{
		if (is_null($this->_lng))
		{
			if (Core::isInit())
			{
				$this->_lng = htmlspecialchars(
					Core_Entity::factory('Admin_Language')->getCurrent()->shortname
				);
			}
			else
			{
				$this->_lng = Core_I18n::instance()->getLng();
			}
		}
		return $this->_lng;
	}

	/**
	 * Set language
	 * @param string $lng language
	 * @return self
	 */
	public function setLng($lng)
	{
		$this->_lng = $lng;
		return $this;
	}

	/**
	 * Get image href
	 * @return string
	 */
	public function getImageHref()
	{
		return "/modules/skin/{$this->_skinName}/images/";
	}
}