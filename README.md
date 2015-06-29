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
[setup](#setup)         | Yapılandırma ayarlarını kaydeder.
[storage](#storage)     | Sadece cache dizinini sınıfa tanımlar.
[aliases](#aliases)     | Fonksiyonlara kısa isimler atamanızı sağlar.
[ext](#ext)         | Sadece dosya uzantılarını sınıfa tanımlar.
[master](#master)      | Sadece master page dosyasını sınıfa tanımlar.
[cache](#cache)       | Sadece cache durumunu sınıfa tanımlar.
[composer](#composer)    | Template dosyalarını derler. (Render)     
[view](#view)        | Bir php dosyasını olduğu gibi bir değişkene aktarır.
[show](#show)        | Bir php dosyasını direkt ekrana basar.
[with](#with)        | Template ve php dosyalarına parametreler göndermenizi sağlar.
[each](#each)        | Parametreleri tüm template ve php dosyalarına gönderir.
[clear](#clear)       | Php cache dosyalarını temizler.
[beforeEvent](#beforeEvent) | İşlem öncesinde (**composer()**, **view()**, **show()**) olayları (event) çalıştırır.
[afterEvent](#afterEvent)  | İşlem sonrasında (**show()**) olayları (event) çalıştırır.

##Template Yapılandırıcıları

Name        | Description                                             
----------- | ------------------------------------------------------- 
[@content](#@content)     | Sayfamızın gövdesini (body) oluşturan terim.
[@extend()](#@extend())   | Sayfamızı başka template dosyaları ile genişletir.
[@section()](#@section()) |
[@append()](#@append())   |

#Metod Kullanımları

###setup
Paket yapılandırma ayarlarında kullanılan metod.

**Örnek:**
```php
$elisa->setup([
	'storage' => '/storage/path/',
	'cache'	  => false,
	'ext'	  => '.html',
	'master'  => 'master'
]);

```

###storage

**setup** metodundan bağımsız yapılandırma ayarlarından template dosyalarının hangi dizinde cache'leneceği ayarını tanımlar.

**Örnek:**
```php
$elisa->storage('/storage/path/');
```
###ext

**setup** metodundan bağımsız template dosyalarının hangi uzantıda olacağını tanımlar.

**Örnek:**
```php
$elisa->ext('.html');
```
###master

**setup** metodundan bağımsız ana template çatı dosyasını tanımlar.

**Örnek:**
```php
$elisa->master('master_layout');
```

###cache

**setup** metodundan bağımsız render (derlenen) edilen template dosyalarının önbellekte tutulup tutulmayacağını tanımlar.

**Örnek:**
```php
$elisa->cache(true); // default true
```
###aliases

Fonksiyon isimlerine kısa isimler yada farklı isimler vererek kendi fonksiyon terminolojinizi oluşturabilirsiniz.

**Örnek:**
```php
$elisa->aliases(['length' => 'strlen', 'dump' => 'var_dump']);
```

###composer

Template dosyasını önbellekte yok ise derler (render) ve önbelleğe ekler.

**Örnek:**
```php
$elisa->composer('home.index');
``` 

###view

Php dosyasının içeriğini direkt olarak bir değişkene aktarmanızı sağlar.
**Örnek:**

```php
$elisa->view('common.sidebar');
```

###show

Php dosyasının içeriğini direkt olarak ekrana basmanızı sağlar.

**Örnek:**
```php
$elisa->show('tools.slider');
```

###with

Template ve view dosyalarına parametre göndermenizi sağlar.

**Örnek:**
```php
$elisa->with(['name' => 'Ahmet']);

$elisa->composer('home.contet', true);
```

###each

Tüm template ve view dosyalarına her zaman gitmesi istediğiniz parametreleri gönderir.

**Örnek:**
```php
$elisa->each(['name' => 'Ahmet']);
```

Parametreler aşağıdaki her iki view dosyasına gönderilecektir.

**Örnek:**
```php
$elisa->view('common.header');

$elisa->view('common.footer');
```

###clear

Önbellekteki derlenmiş template dosyalarını temizler.

**Örnek:**
```php
$elisa->clear();
```

###beforeEvent

Dosya isimleri ile kayıt edilmiş event'ler dosyalar işleme alınmadan çalıştırılır.

>**Not:** Göndereceğiniz parametreler çalıştırılan event'e gönderilecektir.

**Örnek:**

```php
$elisa->beforeEvent(function($params){
	
	// do something...

});
```

###afterEvent

Dosya isimleri ile kayıt edilmiş event'ler dosyalar işleme alındıktan sonra çalıştırılır.

>**Not:** **afterEvent** sadece **show()** metodu ile çalışmaktadır.

>**Not:** Göndereceğiniz parametreler çalıştırılan event'e gönderilecektir.

**Örnek:**

```php
$elisa->afterEvent(function($params){
	
	// do something...

});
```

#Yapılandırıcılar

###@content

Template içeriğinin gövde kısmını oluşturan bir etikettir. Sadece master template sayfasında kullanılabilir.

**Örnek:**

master.html
```html
<html>
	<head>
		
	</head>

	<body>
	{ @content }
	</body>
</html>
```

Master template ile derlemek istediğiniz gövde (body) template dosyasını şu şekilde kullanabilirisiniz:

```php
$elisa->composer('home.body');
```

###@extend()

Template sayfalarını genişletmek için yardımcı olur. Bu metod ile hem template dosyası hemde normal bir php dosyasını dahil edebilirsiniz.

Ayrıca bu dosyalara parametreler gönderebilirsiniz.

profile.html
```html

<h1>User Profile</h1>

<header>
	{ @extend('profile.header', ['name' => 'Can']) }
</header>

<footer>
	{ @extend('profile.footer') }
</footer>
```

Yukarıda **profile.html** template dosyasına **header** ve **footer** template dosyalarını dahil ettik. Ayrıca **header** dosyasına bir de parametre gönderdik.

###@section()

Belirteceğiniz section alanlarına başka bir template dosyasından içerik gönderebilirsiniz.

**Örnek:**

master.html:

```html
<html>
	
	<head>
		{ @section('header') }

		{ @end }
	</head>

	<body>
		{ @content }
		
		{ @section('footer') }

		{ @end }
	</body>

</html>
```

login.html:

```html
<h1>Login page</h1>

{ @append('header') }
<title>Login page</title>
{ @end }

{ @append('footer') }
<script type="text/javascript">
	function hello()
	{
		alert('Hello World!');
	}
</script>
{ @end }
```

Yukarıda bir master page dosyamız var ve içinde tanımladığımız iki adet section alanları bulunuyor. 

Sonrasında **login.html** adında bir template dosyası oluşturduk ve **append** metodlarıyla master page alanındaki section'lara içerikler gönderdik.

###@append()

Belirtilen section alanlarına içerik gönderir.

>**Not:** Yukarıda detaylı örneği bulabilirsiniz.

**Örnek:**

```html
{ @append('header') }
<title>Login page</title>
{ @end }

{ @append('footer') }
<script type="text/javascript">
	function hello()
	{
		alert('Hello World!');
	}
</script>
{ @end }
```