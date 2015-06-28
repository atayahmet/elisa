#Elisa PHP Template Language

Elisa bir template language kütüphanesidir. Template Language nedir sorusuna kısa cevap olarak görünüm (view) dosyalarından php kodlarını arındırma yöntemi diyebiliriz.

Template dilinde kullanılacak olan php tarafındaki kodların karşılığı olan terimlerin bir kısıtlaması bulunmuyor. Bu tamamen paketi geliştiren developer'ların hayal gücüne bağlı olan bir şey diyebiliriz. 

Ama elbetteki buradaki amaç kullanımı kolay ve hızlıca ortaya bir şeyler çıkarabilmek olmalıdır.

##Kurulum
Elisa composer üzerinden kurulmaktadır. Aşağıdaki json satırını composer dosyanızda **require** alanına ekleyin.

```json
"atayahmet/elisa": "1.0.0.*@dev"
```

sonra komut satırında aşağıdaki komutu çalıştırın:

```php
$ composer update
```

##Yapılandırma

```php
use Elisa\Elisa;

$elisa = new Elisa;

$elisa->setup([
	'storage' => '/storage/path/',
	'cache'	  => false,
	'ext'	  => '.html',
	'master'  => 'master'
]);

```
**Parametre detayları:**

Name     | Type      | Description                                             | Default
-------- | --------- | ------------------------------------------------------- | -------
storage  | string    | Template dosyalarının önbellekte saklanacağı dizin      | /
cache    | boolean   | Önbellekleme                                            | true
ext      | string    | Template dosyalarının uzantıları                        | .html
master   | string    | Ana template dosyasının adı                             | master

##Kullanım
##Setup
İlk olarak master template dosyasını görelim.

**master.html**
```html
<html>
	<head>
		
	</head>

	<body>
	{ @content }
	</body>
</html>

```

Şimdide master dosyasında **@content** alanına gelecek olan dosyamızı görelim.

**home.html**

```html
<h1>Home Page</h1>
<p>This is home page.</p>
```

Gerekli olan **master** ve **content** dosyalarını hazırladıktan sonra bunları tek seferde kullanmayı örneklendirelim.

```php
$elisa->composer('home', true);
```

yada

```php
$home = $elisa->composer('home');
```

Bu işlemlerin ardından yapılandırma aşamasında storage alanına tanımladığınız dizine template dosyalarının php dosyası olarak cache'lendiğini görebilirsiniz.

Composer metodunu çalıştırdıktan sonra sonuç şu şekilde olacaktır:

```html
<html>
	<head>
		
	</head>
	<body>
		<h1>Home Page</h1>
		<p>This is home page.</p>
	</body>
</html>

```

##Metod Referansları

Name        | Description                                             
----------- | ------------------------------------------------------- 
[setup](#setup) | Yapılandırma ayarlarını kaydeder.
storage     | Sadece cache dizinini sınıfa tanımlar.
aliases     | Fonksiyonlara kısa isimler atamanızı sağlar.
ext         | Sadece dosya uzantılarını sınıfa tanımlar.
master      | Sadece master page dosyasını sınıfa tanımlar.
cache       | Sadece cache durumunu sınıfa tanımlar.
composer    | Template dosyalarını derler. (Render)     
view        | Bir php dosyasını olduğu gibi bir değişkene aktarır.
show        | Bir php dosyasını direkt ekrana basar.
with        | Template ve php dosyalarına parametreler göndermenizi sağlar.
each        | Parametreleri tüm template ve php dosyalarına gönderir.
clear       | Php cache dosyalarını temizler.
beforeEvent | İşlem öncesinde (**composer()**, **view()**, **show()**) olayları (event) çalıştırır.
afterEvent  | İşlem sonrasında (**show()**) olayları (event) çalıştırır.