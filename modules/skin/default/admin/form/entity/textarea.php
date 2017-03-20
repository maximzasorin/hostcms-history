<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS 6\Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2013 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Textarea extends Admin_Form_Entity
{
	/**
	 * Config
	 * @var array
	 */
	protected $_init = NULL;

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'divAttr', // array
		'caption',
		'format', // array, массив условий форматирования
		'value', // идет в значение <textarea>
		'template_id' // ID макета для визуального редактора
	);

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'wysiwyg', // TRUE/FALSE
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Combine
		$this->_skipProperies = array_combine($this->_skipProperies, $this->_skipProperies);

		$oCore_Html_Entity_Textarea = new Core_Html_Entity_Textarea();
		$this->_allowedProperties += $oCore_Html_Entity_Textarea->getAllowedProperties();

		// Свойства, исключаемые для <textarea>, добавляем в список разрешенных объекта
		$this->_allowedProperties += $this->_skipProperies;

		parent::__construct();

		$oCore_Registry = Core_Registry::instance();
		$iAdmin_Form_Count = $oCore_Registry->get('Admin_Form_Count', 0);
		$oCore_Registry->set('Admin_Form_Count', $iAdmin_Form_Count + 1);

		$this->id = $this->name = 'field_id_' . $iAdmin_Form_Count;
		$this->style('width: 100%')
			->rows(3);
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$this->id = $windowId . '_' . $this->id;

		if (is_null($this->onkeydown))
		{
			$this->onkeydown = $this->onkeyup = $this->onblur = "FieldCheck('{$windowId}', this)";
		}

		$aAttr = $this->getAttrsString();

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		$aDivAttr = array();

		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		?><span class="caption"><?php echo $this->caption?></span><?php
		?><textarea <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars($this->value)?></textarea><?php

		if ($this->wysiwyg)
		{
			if (!defined('USE_WYSIWYG') || USE_WYSIWYG)
			{
				$aCSS = array();

				if ($this->template_id)
				{
					$oTemplate = Core_Entity::factory('Template', $this->template_id);

					do{
						$aCSS[] = "/templates/template{$oTemplate->id}/style.css?" . Core_Date::sql2timestamp($oTemplate->timestamp);
					} while($oTemplate = $oTemplate->getParent());
				}

				$lng = Core_I18n::instance()->getLng();

				$this->_init = Core_Config::instance()->get('core_wysiwyg');

				// add
				$this->_init['script_url'] = "'/admin/wysiwyg/tiny_mce.js'";

				$this->_init['language'] = '"' . $lng . '"';
				$this->_init['docs_language'] = '"' . $lng . '"';
				$this->_init['elements'] = '"' . $this->id . '"';
				$this->_init['content_css'] = '"' . implode(',', $aCSS) . '"';

				// Array of structures
				$aStructure = $this->_fillStructureList(CURRENT_SITE);

				$tinyMCELinkList = 'var tinyMCELinkList = new Array(';

				$tinyMCELinkListArray = array();

				foreach ($aStructure as $oStructure)
				{
					// Внешняя ссылка есть, если значение внешней ссылки не пустой
					$link = (strlen(trim($oStructure->url)) == 0)
						? $oStructure->getPath()
						: $oStructure->url;

					$tinyMCELinkListArray[] = '["' . addslashes($oStructure->menu_name) . '","' . $link . '"]';
				}

				$tinyMCELinkList .= implode(",", $tinyMCELinkListArray);

				$tinyMCELinkList .= ');';

				unset($tinyMCELinkListArray);

				// Передаем в конфигураци
				$this->_init['external_link_list'] = '"' . addslashes($tinyMCELinkList) . '"';

				if (count($this->_init) > 0)
				{
					$aInit = array();
					foreach ($this->_init as $init_name => $init_value)
					{
						$aInit[] = "{$init_name}: {$init_value}";
					}
					$sInit = implode(", \n", $aInit);
				}
				else
				{
					$sInit = '';
				}

				$Core_Html_Entity_Script = new Core_Html_Entity_Script();
				$Core_Html_Entity_Script
					->type('text/javascript')
					->value("$(function() { setTimeout(function(){ $('#{$windowId} #{$this->id}').tinymce({ {$sInit} }); }, 300); });")
					->execute();
			}
		}
		else
		{
			// Могут быть дочерние элементы элементы
			parent::execute();

			$this->_showFormat();
		}
		?></div><?php
	}

	/**
	 * Fill structure list
	 * @param int $iSiteId site ID
	 * @param int $iParentId parent node ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	protected function _fillStructureList($iSiteId, $iParentId = 0, $iLevel = 0)
	{
		$iSiteId = intval($iSiteId);
		$iParentId = intval($iParentId);
		$iLevel = intval($iLevel);

		$oStructure = Core_Entity::factory('Structure', $iParentId);

		$aReturn = array();

		// Дочерние разделы
		$aChildren = $oStructure->Structures->getBySiteId($iSiteId);

		if (count($aChildren))
		{
			foreach ($aChildren as $oStructure)
			{
				$oStructure->menu_name = str_repeat('  ', $iLevel) . $oStructure->name;
				$aReturn[$oStructure->id] = $oStructure;
				$aReturn += $this->_fillStructureList($iSiteId, $oStructure->id, $iLevel + 1);
			}
		}

		return $aReturn;
	}

}