<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Notifications.
 *
 * @package HostCMS
 * @subpackage Notification
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Notification_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'title';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'notification_user' =>  array(),
		'user' => array('through' => 'notification_users')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'module' => array()
	);

	/**
	 * Backend property
	 * @var mixed
	 */
	public $userId = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $titleDescription = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $read = NULL;

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function image($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->module_id && ($oCore_Module = $this->Module->Core_Module) && !is_null($oCore_Module))
		{
			$aNotificationDecorations = $oCore_Module->getNotifications($this->type, $this->entity_id);

			$aNotification['icon'] = Core_Array::get($aNotificationDecorations, 'icon');

			$sReturn = "<i class=\"notification-ico {$aNotification['icon']['ico']} {$aNotification['icon']['background-color']} {$aNotification['icon']['color']} fa-fw\"></i>";
		}
		else
		{
			$sReturn = '<i class="fa fa-info bg-themeprimary white fa-fw"></i>';
		}

		return $sReturn;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function titleDescription($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sReturn = htmlspecialchars($this->title);

		if ($this->module_id && ($oCore_Module = $this->Module->Core_Module) && !is_null($oCore_Module))
		{
			$aNotificationDecorations = $oCore_Module->getNotifications($this->type, $this->entity_id);

			$href = Core_Array::get($aNotificationDecorations, 'href');
			$onclick = Core_Array::get($aNotificationDecorations, 'onclick');

			if (strlen($href) || strlen($onclick))
			{
				ob_start();

				Admin_Form_Entity::factory('A')
					->href($href)
					->onclick($onclick)
					->value($sReturn)
					->execute();

				$sReturn = ob_get_clean();
			}
		}

		!empty($this->description)
			&& $sReturn .= '<span class="notification-description">' . htmlspecialchars($this->description) . '</span>';

		return $sReturn;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event notification.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Notification_Users->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}