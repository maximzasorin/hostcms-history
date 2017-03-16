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
class Shop_Delivery_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		if (is_null($object->id))
		{
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		$this
				->addSkipColumn('image')
				->addSkipColumn('image_height')
				->addSkipColumn('image_width')
				;

		parent::setObject($object);

		// Главная вкладка
		$oMainTab = $this->getTab('main');

		// Магазин, которому принадлежит данный тип доставки
		$oShop = $this->_object->Shop;

		// Добавляем новое поле типа файл
			$oImageField = Admin_Form_Entity::factory('File');

		$oLargeFilePath = is_file($this->_object->getDeliveryFilePath())
			? $this->_object->getDeliveryFileHref()
			: '';

		$sFormPath = $this->_Admin_Form_Controller->getPath();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oImageField
			->style("width: 400px;")
			->name("image")
			->id("image")
			->largeImage(array(
				'max_width' => $oShop->image_large_max_width,
				'max_height' => $oShop->image_large_max_height,
				'path' => $oLargeFilePath,
				'show_params' => TRUE,
				'watermark_position_x' => $oShop->watermark_default_position_x,
				'watermark_position_y' => $oShop->watermark_default_position_y,
				'place_watermark_checkbox_checked' => 0,
				'delete_onclick' =>
				"$.adminLoad({path: '{$sFormPath}', additionalParams:
				'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1',
				action: 'deleteImage', windowId: '{$windowId}'}); return false",
				'caption' => Core::_('Shop_Delivery.image'),
				'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio
			))
			->smallImage
			(
				array(
					'show' => FALSE
				)
			);

		$oMainTab->addAfter(
			$oImageField, $this->getField('description')
		);

		$title = $this->_object->id
					? Core::_('Shop_Delivery.type_of_delivery_edit_form_title')
					: Core::_('Shop_Delivery.type_of_delivery_add_form_title');

		$this->title($title);

		// Создаем вкладку
		$oShopDeliveryTabPaymentSystems = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Delivery.payment_systems'))
					->name('PaymentSystems');

		$this->addTabAfter($oShopDeliveryTabPaymentSystems, $oMainTab);

		// Заполняем вкладку платежных систем

		// Получаем платежные системы, связанные с доставкой
		$aShop_Delivery_Payment_Systems = $this->_object->Shop_Payment_Systems->findAll();

		// Массив идентификаторов платежных систем, связанных с доставкой
		$aDelivery_Payment_Systems = array();

		foreach($aShop_Delivery_Payment_Systems as $oShop_Delivery_Payment_System)
		{
			$aDelivery_Payment_Systems[] = $oShop_Delivery_Payment_System->id;
		}

		// Получаем список платежных систем магазина
		$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->findAll();

		foreach($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$oShop_Payment_System_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->caption($oShop_Payment_System->name)
				->name('shop_payment_system_' . $oShop_Payment_System->id);

			(!$this->_object->id || in_array($oShop_Payment_System->id, $aDelivery_Payment_Systems))
			&& $oShop_Payment_System_Checkbox->value(1);

			$oShopDeliveryTabPaymentSystems->add($oShop_Payment_System_Checkbox);
		}


		$oMainTab->delete($this->getField('type'));
		$oTypeRadio = Admin_Form_Entity::factory('Radiogroup');
		$oTypeRadio->radio(array(
				Core::_('Shop_Delivery.option0'),
				Core::_('Shop_Delivery.option1')
			))
			->divAttr(array('id' => 'import_types'))
			->value($this->_object->type)
			->name('type');
			
		$oMainTab
		->addAfter($oTypeRadio, $this->getField('name'))
		->addAfter(Admin_Form_Entity::factory('Code')
			->html("<script>$(function() {
				$('#{$windowId} #import_types').buttonset();
				if(!{$this->_object->type})
				{
					$('#{$windowId} #code').hide();
				}
				else
				{
					$('#{$windowId} #code').show();
				}
				
				$('#{$windowId} #import_types input:radio').change(
					function()
					{
						if(!($(this).val()%2))
						{
							$('#{$windowId} #code').hide();
						}
						else
						{	
							$('#{$windowId} #code').show();
						}
					}
				);
			});</script>"), $oTypeRadio);
			
		
	$oMainTab->addAfter(
		Admin_Form_Entity::factory('Textarea')
			->caption(Core::_('Shop_Delivery.handler'))
			->name('code')
			->value($this->_object->loadHandlerFile())
			->divAttr(array('id' => 'code')), 
		$oTypeRadio);

		return $this;
	}

	/**
	 * Fill delivery list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	static public function fillDeliveries($iShopId)
	{
		$iShopId = intval($iShopId);

		$oDelivery = Core_Entity::factory('Shop_Delivery');

		$oDelivery->queryBuilder()
			->where('shop_id', '=', $iShopId)
			->orderBy('sorting')
			->orderBy('name');

		$aDeliveries = $oDelivery->findAll();

		$aDeliveryArray = array(' … ');

		foreach($aDeliveries as $oDelivery)
		{
			$aDeliveryArray[$oDelivery->id] = $oDelivery->name;
		}

		return $aDeliveryArray;
	}

	/**
	 * Processing of the form. Apply object fields.
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$oShop = $this->_object->Shop;

		if(Core_Array::getRequest('type') == 1)
		{
			$this->_object->saveHandlerFile(Core_Array::getRequest('code'));
		}

		// Обработка картинок
		$param = array();

		$image = '';

		$aCore_Config = Core::$mainConfig;

		$bImageIsCorrect =
			// Поле файла большого изображения существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0;

		if($bImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'],
			$aCore_Config['availableExtension']))
			{
				// Удаление файла большого изображения
				if ($this->_object->image)
				{
					$this->_object->deleteImage();
				}

				$file_name = $aFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					$image = $file_name;
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($aFileData['name']);

					$image = 'shop_type_of_delivery_image' . $this->_object->id . '.' . ($ext == '' ? '' : $ext);
				}
			}
			else
			{
				$this->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow',
						Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		if ($bImageIsCorrect)
		{
			// Путь к файлу-источнику большого изображения;
			$param['large_image_source'] = $aFileData['tmp_name'];
			// Оригинальное имя файла большого изображения
			$param['large_image_name'] = $aFileData['name'];

			// Путь к создаваемому файлу большого изображения;
			$param['large_image_target'] = !empty($image)
				? $this->_object->getPath() . $image
				: '';

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = FALSE;

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = Core_Array::getPost(
				'large_max_width_image', 0);

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = Core_Array::getPost(
				'large_max_height_image', 0);

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = 0;

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = 0;

			// Путь к файлу с "водяным знаком"
			$param['watermark_file_path'] = $oShop->getWatermarkFilePath();

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = Core_Array::getPost(
				'watermark_position_x_image');

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = Core_Array::getPost(
				'watermark_position_y_image');

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['large_image_watermark'] = !is_null(
				Core_Array::getPost('large_place_watermark_checkbox_image'));

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['small_image_watermark'] = FALSE;

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null(
				Core_Array::getPost('large_preserve_aspect_ratio_image'));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = FALSE;

			$this->_object->createDir();

			$result = Core_File::adminUpload($param);

			if ($result['large_image'])
			{
				$this->_object->image = $image;

				$this->_object->setImageSizes();
			}

			$this->_object->save();
		}

		// Получаем платежные системы, связанные с доставкой
		$aShop_Delivery_Payment_Systems = $this->_object->Shop_Payment_Systems->findAll();

		// Массив идентификаторов платежных систем, связанных с доставкой
		$aDelivery_Payment_Systems = array();

		foreach($aShop_Delivery_Payment_Systems as $oShop_Delivery_Payment_System)
		{
			$aDelivery_Payment_Systems[] = $oShop_Delivery_Payment_System->id;
		}

		$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->findAll();

		foreach($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$iShopPaymentSystemChecked = Core_Array::getPost('shop_payment_system_' . $oShop_Payment_System->id, 0) ? 1 : 0;

			// Платежная система выбрана
			if ($iShopPaymentSystemChecked)
			{
				// Платежная система не связана с доставкой. Добавляем платежную систему доставке
				if (!in_array($oShop_Payment_System->id, $aDelivery_Payment_Systems))
				{
					$this->_object->add($oShop_Payment_System);
				}
			}
			else // Платежная система не выбрана
			{
				// Платежная система связана с доставкой. Удаляем связь платежной системы с доставкой
				if (in_array($oShop_Payment_System->id, $aDelivery_Payment_Systems))
				{
					$oShop_Delivery_Payment_Systems = $this->_object->Shop_Delivery_Payment_Systems->getByShop_payment_system_id($oShop_Payment_System->id);

					!is_null($oShop_Delivery_Payment_Systems) && $oShop_Delivery_Payment_Systems->delete();
				}
			}
		}
	}
}