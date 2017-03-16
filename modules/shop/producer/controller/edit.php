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
class Shop_Producer_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Groups tree
	 * @var array
	 */
	protected $_aGroupTree = array();
	
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();
		
		switch($modelName)
		{
			case 'shop_producer':
				if (is_null($object->id))
				{
					$object->shop_id = Core_Array::getGet('shop_id');
					$object->shop_producer_dir_id = Core_Array::getGet('producer_dir_id');
				}

				$this
					->addSkipColumn('image_large')
					->addSkipColumn('image_small');

				parent::setObject($object);

				$this->getField('description')->rows(20);

				$oMainTab = $this->getTab('main');

				$oAdditionalTab = $this->getTab('additional');

				$oContactsTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Producer.tab2'))
					->name('Contacts');

				$oBankContactsTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Producer.tab3'))
					->name('Contacts');

				$oSEOTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Producer.tab4'))
					->name('Contacts');

				$this
					->addTabAfter($oContactsTab, $oMainTab)
					->addTabAfter($oBankContactsTab, $oContactsTab)
					->addTabAfter($oSEOTab, $oBankContactsTab);

				$oMainTab
					->move($this->getField('address'), $oContactsTab)
					->move($this->getField('phone'), $oContactsTab)
					->move($this->getField('fax'), $oContactsTab)
					->move($this->getField('site'), $oContactsTab)
					->move($this->getField('email'), $oContactsTab)
					->move($this->getField('tin'), $oBankContactsTab)
					->move($this->getField('kpp'), $oBankContactsTab)
					->move($this->getField('psrn'), $oBankContactsTab)
					->move($this->getField('okpo'), $oBankContactsTab)
					->move($this->getField('okved'), $oBankContactsTab)
					->move($this->getField('bik'), $oBankContactsTab)
					->move($this->getField('current_account'), $oBankContactsTab)
					->move($this->getField('correspondent_account'), $oBankContactsTab)
					->move($this->getField('bank_name'), $oBankContactsTab)
					->move($this->getField('bank_address'), $oBankContactsTab)
					->move($this->getField('seo_title'), $oSEOTab)
					->move($this->getField('seo_description'), $oSEOTab)
					->move($this->getField('seo_keywords'), $oSEOTab);

				$oDescriptionField = $this->getField('description');
				$oDescriptionField->wysiwyg = TRUE;

				$oShop = $this->_object->Shop;

				// Добавляем новое поле типа файл
				$oImageField = Admin_Form_Entity::factory('File');

				$oLargeFilePath = is_file($this->_object->getLargeFilePath())
					? $this->_object->getLargeFileHref()
					: '';

				$oSmallFilePath = is_file($this->_object->getSmallFilePath())
					? $this->_object->getSmallFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oImageField
					->style("width: 400px;")
					->name("image")
					->id("image")
					->largeImage(array(	'max_width' => $oShop->image_large_max_width,
							'max_height' => $oShop->image_large_max_height,
							'path' => $oLargeFilePath,
							'show_params' => TRUE,
							'watermark_position_x' => 0,
							'watermark_position_y' => 0,
							'place_watermark_checkbox_checked' => 0,
							'delete_onclick' =>
							"$.adminLoad({path: '{$sFormPath}', additionalParams:
							'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1',
							action: 'deleteLargeImage', windowId: '{$windowId}'}); return false",
							'caption' => Core::_('Shop_Producer.image_large'),
							'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio
						)
					)
					->smallImage
					(array(	'max_width' => $oShop->image_small_max_width,
							'max_height' => $oShop->image_small_max_height,
							'path' => $oSmallFilePath,
							'create_small_image_from_large_checked' =>
							$this->_object->image_small == '',
							'place_watermark_checkbox_checked' =>
							$oShop->watermark_default_use_small_image,
							'delete_onclick' => "$.adminLoad({path: '{$sFormPath}',
							additionalParams:
							'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1',
							action: 'deleteSmallImage', windowId: '{$windowId}'}); return false",
							'caption' => Core::_('Shop_Producer.image_small'),
							'show_params' => TRUE,
							'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio_small
						)
					);

				$oMainTab->addAfter($oImageField, $oDescriptionField);

				// Удаляем группу товаров
				$oAdditionalTab->delete($this->getField('shop_producer_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');
				$oGroupSelect->caption(Core::_('Shop_Producer_Dir.parent_id'))
					->options(array(' … ') + $this->fillGroupList(Core_Array::getGet('shop_id', 0)))
					->name('shop_producer_dir_id')
					->value($this->_object->shop_producer_dir_id)
					->style('width:300px; float:left')
					->filter(TRUE);

				// Добавляем группу товаров
				$oMainTab->addAfter($oGroupSelect, $this->getField('name'));
				
				$title = $this->_object->id
					? Core::_('Shop_Producer.producer_edit_form_title')
					: Core::_('Shop_Producer.producer_add_form_title');

				$this->title($title);
			break;
			case 'shop_producer_dir':
			
				if (is_null($object->id))
				{
					$object->shop_id = Core_Array::getGet('shop_id');
					$object->parent_id = Core_Array::getGet('producer_dir_id');
				}
				
				parent::setObject($object);
				
				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');
				
				// Удаляем группу товаров
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');
				$oGroupSelect->caption(Core::_('Shop_Producer_Dir.parent_id'))
					->options(array(' … ') + $this->fillGroupList(Core_Array::getGet('shop_id', 0)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->style('width:300px; float:left')
					->filter(TRUE);

				// Добавляем группу товаров
				$oMainTab->addAfter($oGroupSelect, $this->getField('name'));
				
				$title = $this->_object->id
					? Core::_('Shop_Producer_Dir.edit')
					: Core::_('Shop_Producer_Dir.add');

				$this->title($title);
			
			break;
		}

		return $this;
	}
	
	
	/**
	 * Create visual tree of the directories
	 * @param int $shop_id shop ID
	 * @param int $parent_id parent directory ID
	 * @param array $aExclude exclude group IDs array
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillGroupList($shop_id, $parent_id = 0, $aExclude = array(), $iLevel = 0)
	{
		$shop_id = intval($shop_id);
		$parent_id = intval($parent_id);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_producer_dirs')
				->where('shop_id', '=', $shop_id)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				$this->_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset($this->_aGroupTree[$parent_id]))
		{
			$countExclude = count($aExclude);
			foreach ($this->_aGroupTree[$parent_id] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'];
					$aReturn += $this->fillGroupList($shop_id, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && $this->_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$param = array();

		$oShop = $this->_object->Shop;

		$large_image = '';
		$small_image = '';

		$aCore_Config = Core::$mainConfig;

		$create_small_image_from_large = Core_Array::getPost(
		'create_small_image_from_large_small_image');

		$bLargeImageIsCorrect =
			// Поле файла большого изображения существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0;

		if($bLargeImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'],
			$aCore_Config['availableExtension']))
			{
				// Удаление файла большого изображения
				if ($this->_object->image_large)
				{
					$this->_object->deleteLargeImage();
				}

				$file_name = $aFileData['name'];

				$ext = Core_File::getExtension($file_name);

				$large_image = 'shop_producer_image' . $this->_object->id . '.' . $ext;
			}
			else
			{
				$this->addMessage(	Core_Message::get(		Core::_('Core.extension_does_not_allow',
						Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		$aSmallFileData = Core_Array::getFiles('small_image', NULL);
		$bSmallImageIsCorrect =
			// Поле файла малого изображения существует
			!is_null($aSmallFileData)
			&& $aSmallFileData['size'];


		// Задано малое изображение и при этом не задано создание малого изображения
		// из большого или задано создание малого изображения из большого и
		// при этом не задано большое изображение.

		if ($bSmallImageIsCorrect
		|| $create_small_image_from_large
		&& $bLargeImageIsCorrect)
		{
			// Удаление файла малого изображения
			if ($this->_object->image_small)
			{
				$this->_object->deleteSmallImage();
			}

			// Явно указано малое изображение
			if ($bSmallImageIsCorrect
				&& Core_File::isValidExtension($aSmallFileData['name'],
				$aCore_Config['availableExtension']))
			{
				if ($this->_object->image_large != '')
				{
					// Существует ли большое изображение
					$param['large_image_isset'] = true;
					$create_large_image = false;
				}
				else
				{
					$create_large_image = empty($large_image);
				}

				$file_name = $aSmallFileData['name'];

				// Определяем расширение файла
				$ext = Core_File::getExtension($file_name);

				$small_image = 'small_shop_producer_image' . $this->_object->id . '.' . $ext;
			}
			elseif ($create_small_image_from_large && $bLargeImageIsCorrect)
			{
				$small_image = 'small_' . $large_image;
			}
			// Тип загружаемого файла является недопустимым для загрузки файла
			else
			{
				$this->addMessage(	Core_Message::get(		Core::_('Core.extension_does_not_allow',
						Core_File::getExtension($aSmallFileData['name'])),
						'error'
					)
				);
			}
		}

		if ($bLargeImageIsCorrect || $bSmallImageIsCorrect)
		{
			if ($bLargeImageIsCorrect)
			{
				// Путь к файлу-источнику большого изображения;
				$param['large_image_source'] = $aFileData['tmp_name'];
				// Оригинальное имя файла большого изображения
				$param['large_image_name'] = $aFileData['name'];
			}

			if ($bSmallImageIsCorrect)
			{
				// Путь к файлу-источнику малого изображения;
				$param['small_image_source'] = $aSmallFileData['tmp_name'];
				// Оригинальное имя файла малого изображения
				$param['small_image_name'] = $aSmallFileData['name'];
			}

			// Путь к создаваемому файлу большого изображения;
			$param['large_image_target'] = !empty($large_image)
				? $this->_object->getProducerPath() . $large_image
				: '';

			// Путь к создаваемому файлу малого изображения;
			$param['small_image_target'] = !empty($small_image)
				? $this->_object->getProducerPath() . $small_image
				: '' ;

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = !is_null(Core_Array::getPost('create_small_image_from_large_small_image'));

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = Core_Array::getPost('large_max_width_image', 0);

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = Core_Array::getPost('large_max_height_image', 0);

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = Core_Array::getPost('small_max_width_small_image');

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = Core_Array::getPost('small_max_height_small_image');

			// Путь к файлу с "водяным знаком"
			$param['watermark_file_path'] = "";

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = 0;

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = 0;

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['large_image_watermark'] = FALSE;

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['small_image_watermark'] = FALSE;

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('large_preserve_aspect_ratio_image'));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('small_preserve_aspect_ratio_small_image'));

			$this->_object->createDir();

			$result = Core_File::adminUpload($param);

			if ($result['large_image'])
			{
				$this->_object->image_large = $large_image;

				// WARNING !!! Закомментировано до особого указа о добавлении полей для хранения
				// размеров изображений производителя
				//$this->_object->setLargeImageSizes();
			}

			if ($result['small_image'])
			{
				$this->_object->image_small = $small_image;

				//$this->_object->setSmallImageSizes();
			}
		}

		$this->_object->save();
	}
}