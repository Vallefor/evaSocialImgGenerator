# Генератор картинок для социальных сетей

## Генератор картинок для социальных сетей
Установка
`
composer require vallefor/eva-social-img-generator
`

Данный класс предназначен для получения красивых картинок при шаринге в социальных сетях. Рассмотрим полный пример использования:

```php

<?php 

    use Eva\Social\imgGenerator as imgGenerator;

	$generator = new imgGenerator();
	$generator->setBigText("Направление деятельности АО «МОСГАЗ»","#ffffff",imgGenerator::position_left_bottom,"auto",'5%')
		->setBigTextShadow("#000000", 75, 1, 2, 2)
		->setBigTextFont("Magistral-Bold")
		->addOverlay($_GET["opacity"]?$_GET["opacity"]:0.5, "#999900")
		->setLogo($_SERVER["DOCUMENT_ROOT"] . "/images/logo.png", imgGenerator::position_left_top, "5%",'auto')
		->fromImg($_SERVER["DOCUMENT_ROOT"] . "/images/background.jpeg")
		->resizeFor("autodetect")
		->show();
}

?>
```

## imgGenerator::setBigText
Используется для установки текста. Имеет следующие параметры:
1. Текст
2. Цвет текста, допускается использование HEX, RGB, RGBA и другие типы цветов, которые поддерживаются Imagick'ом.
3. Позиция текста. Для определение позиции используется константа imgGenerator::position_*, текст можно поставить в любую частьк артинки
4. Размер текста, может быть следующим:
    * auto - размер будет равен 1/10 высоты картинки
    * любое число - для размера шрифта в пикселях
    * 1/10, 1/5, 1/7 и т.д., то есть размер текста будет выстраиваться относительно высоты сгенерированной картинки
5. Отступы - можно указать как целое значение, так и в процентах. Можно задать каждый отступ отдельно, для этого передайте массив: array(top, right, bottom, left)