# Генератор картинок для социальных сетей

## Установка

`composer require vallefor/eva-social-img-generator`


## Пример использования

```php

<?php 

    use Eva\Social\imgGenerator as imgGenerator;
    use Eva\Social\imgTextGenerator as imgTextGenerator;

	$textGenerator = new imgTextGenerator();
	$text=$textGenerator
		->seTextShadow("#000000", 75, 1, 2, 2)
		->setText("Направление деятельности АО «МОСГАЗ»","#ffffff",imgGenerator::position_left_bottom,"auto",'5%')
		->setFont($_SERVER["DOCUMENT_ROOT"]."/upload/fonts/fonts2_7/hinted-PTF55F.ttf");

	$generator = new imgGenerator();
	$generator
		->addText($text)
		->addOverlay(0.5, "#999900")
		->setLogo($_SERVER["DOCUMENT_ROOT"] . "/images/logo.png", imgGenerator::position_left_top, "5%",'auto')
		->fromImg($_SERVER["DOCUMENT_ROOT"] . "/images/background.jpeg")
		->resizeFor("autodetect")
		->show();
}

?>
```

## imgTextGenerator::setText

Используется для установки текста. Имеет следующие параметры:

1. Текст
2. Цвет текста, допускается использование HEX, RGB, RGBA и другие типы цветов, которые поддерживаются Imagick'ом.
3. Позиция текста. Для определение позиции используется константа imgGenerator::position_*, текст можно поставить в любую частьк артинки
4. Размер текста, может быть следующим:
    * auto - размер будет равен 1/10 высоты картинки
    * любое число - для размера шрифта в пикселях
    * 1/10, 1/5, 1/7 и т.д., то есть размер текста будет выстраиваться относительно высоты сгенерированной картинки
5. Отступы - можно указать как целое значение, так и в процентах. Можно задать каждый отступ отдельно, для этого передайте массив: array(top, right, bottom, left)

## imgTextGenerator::setTextShadow

Устанавливает тень под текстом

Параметры:

1. Цвет, допускается использование HEX, RGB, RGBA и другие типы цветов, которые поддерживаются Imagick'ом.
2. Прозрачность - 0-100 (или 0-1)
3. Размер тени
4. Координата X
5. Координата Y

## imgTextGenerator::setFont

Устанавливает шрифт текста. Допускается как указать название шрифта (имейте ввиду, что данный шрифт должен быть установлен в системе), так и путь к шрифтовому файлу.

## imgTextGenerator::setBackground

Устанавливает фон под текстом. Имеет следующие параметры:
1. Цвет, допускается использование HEX, RGB, RGBA и другие типы цветов, которые поддерживаются Imagick'ом.
2. Отступ - можно указать как целое значение, так и в процентах. Можно задать каждый отступ отдельно, для этого передайте массив: array(top, right, bottom, left)

## imgGenerator::addOverlay

Добавляет слой, поверх фоновой картинки (если она есть). Имеет следующие параметры:

1. Прозрачность - 0-100 или 0-1.
2. Цвет, допускается использование HEX, RGB, RGBA и другие типы цветов, которые поддерживаются Imagick'ом.

## imgGenerator::addText

Добавляет текст на картинку. На вход принимает подготовленный экземпляр imgTextGenerator.

## imgGenerator::setLogo

Устанавливает логотип. Имеет следующие параметры:

1. Путь к файлу
2. Позиция логотипа на картинке, для этого используйте константы imgGenerator::position_*
3. Отступ - можно указать как целое значение, так и в процентах. Можно задать каждый отступ отдельно, для этого передайте массив: array(top, right, bottom, left)
4. Размер - в данный момент может принять двух вида:
    * auto - картинка будет занимать не более 25% картинки.
    * false - логотип не будет уменьшен, рекомендуется использовать только если вы картинку генерируете для определенной соц. сети.
    
## imgGenerator::fromImg

За основу генерируемой картинки берется эта картинка.


## imgGenerator::fromColor

За основу картинки берется цвет.

## imgGenerator::resizeFor

Уменьшить картинку для этой соцсети. Допускаются такие строки:

* autodetect - автоопределение исходя из user agent запросившего. Если определить не удалось, то используется vk
* facebook - 1200x630
* vk - 537x240
* twitter - 1024x512
* google_plus - 2120x1192
* ok - однокласники - 780x585

Рекомендуется использовать автоопределение

## imgGenerator::enableCache

В качестве параметра принимает путь к папке, в которую нужно сохранять сгенерированные картинки. Существенно снижает повторную скорость отображения картинки. Но так как, по задумке, к картинке обращаются только роботы социальных сетей - происходить это должно крайне редко.

## imgGenerator::show

Завершающий метод. Генерирует и отображает картинку.

## imgGenerator::getPath

Завершающий метод. Вместо отображения картинки возращает путь к ней (при условии, если был включен кеш.

