# hostcms-framework

Это модули бесплатной редакции системы управления контентом [HostCMS](http://www.hostcms.ru/hostcms/editions/free/) разложенные по версиям, начиная с самой первой, доступной для скачивания с официального сайта, версии 6.1.0.

Для собрания всех версий системы начиная с 6.1.0 был скачан [архив системы](http://www.hostcms.ru/download/6/HostCMS.Free_6.0.zip) с официального сайта, и затем последовательно установлено каждое обновление, для удобной навигации каждому обновлению поставлен в соответствие именованный коммит и тег. Кроме того в описании каждого тега присутствует релиз-ноутс и значение константы `HOSTCMS_UPDATE_NUMBER`, которое можно использовать в коде для проверки наличия той или иной функциональности в текущей версии системы.

Например:
```
if (HOSTCMS_UPDATE_NUMBER >= 153)
{
	// код
}
```

Репозитарий может пригодиться разработчикам общедоступных модулей.

Навигацию по различным версиям системы и исследование кода удобно осуществлять через метки и другие возможности Гитхаба, например:
* [/modules/shop/item/model.php](https://github.com/maximzasorin/hostcms-framework/blame/master/modules/shop/item/model.php) – аннотация модели товара по версиям
* [/modules/shop/item/model.php@6.6.2](https://github.com/maximzasorin/hostcms-framework/blob/6.6.2/modules/shop/item/model.php) – модель товара в версии 6.6.2