# PHP HTTPStatusCode library

[![Latest Stable Version](https://poser.pugx.org/josantonius/httpstatuscode/v/stable)](https://packagist.org/packages/josantonius/httpstatuscode) [![Total Downloads](https://poser.pugx.org/josantonius/httpstatuscode/downloads)](https://packagist.org/packages/josantonius/httpstatuscode) [![Latest Unstable Version](https://poser.pugx.org/josantonius/httpstatuscode/v/unstable)](https://packagist.org/packages/josantonius/httpstatuscode) [![License](https://poser.pugx.org/josantonius/httpstatuscode/license)](https://packagist.org/packages/josantonius/httpstatuscode)

[Spanish version](README-ES.md)

Librería PHP para obtener significado de códigos de estado de respuesta HTTP.

---

- [Instalación](#instalación)
- [Requisitos](#requisitos)
- [Cómo empezar y ejemplos](#cómo-empezar-y-ejemplos)
- [Métodos disponibles](#métodos-disponibles)
- [Uso](#uso)
- [Tests](#tests)
- [Manejador de excepciones](#manejador-de-excepciones)
- [Contribuir](#contribuir)
- [Repositorio](#repositorio)
- [Autor](#autor)
- [Licencia](#licencia)

---

### Instalación 

La mejor forma de instalar esta extensión es a través de [composer](http://getcomposer.org/download/).

Para instalar PHP HTTPStatusCode library, simplemente escribe:

    $ composer require Josantonius/HTTPStatusCode

### Requisitos

Esta ĺibrería es soportada por versiones de PHP 7.0 o superiores y es compatible con versiones de HHVM 3.0 o superiores.

Para utilizar esta librería en HHVM (HipHop Virtual Machine) tendrás que activar los tipos escalares. Añade la siguiente ĺínea "hhvm.php7.scalar_types = true" en tu "/etc/hhvm/php.ini".

### Cómo empezar y ejemplos

Para utilizar esta librería, simplemente:

```php
require __DIR__ . '/vendor/autoload.php';

use Josantonius\HTTPStatusCode\HTTPStatusCode;
```
### Métodos disponibles

Métodos disponibles en esta librería:

```php
HTTPStatusCode::load();
HTTPStatusCode::get();
HTTPStatusCode::getAll();
```
### Uso

Ejemplo de uso para esta librería:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Josantonius\HTTPStatusCode\HTTPStatusCode;

### Obtener significado corto

echo "<br>[200] · " . HTTPStatusCode::get(100);
echo "<br>[200] · " . HTTPStatusCode::get(100, 'es');

/*
[100] · Continue
[100] · Continuar
*/

### Obtener significado detallado

echo "<br>[100] · " . HTTPStatusCode::get(100, 'es', 'large');
echo "<br>[100] · " . HTTPStatusCode::get(100, 'en', 'large');

/*
[100] · El navegador puede continuar realizando su petición (se utiliza para indicar que la primera parte de la petición del navegador se ha recibido correctamente).
[100] · The server has received the request headers and the client should proceed to send the request body (in the case of a request for which a body needs to be sent; for example, a POST request). Sending a large request body to a server after a request has been rejected for inappropriate headers would be inefficient. To have a server check the request's headers, a client must send Expect: 100-continue as a header in its initial request and receive a 100 Continue status code in response before sending the body. The response 417 Expectation Failed indicates the request should not be continued.
*/

### Obtener un array con todos los significados y códigos de estado HTTP en inglés

$HTTPStatusCodes = HTTPStatusCode::getAll();

echo '<pre>'; var_dump($HTTPStatusCodes); echo '</pre>';

/*
array(67) {
  ["1xx"]=>
  array(2) {
    ["short"]=>
    string(13) "Informational"
    ["large"]=>
    string(339) "Request received, continuing process. This class of status code indicates a provisional response, consisting only of the Status-Line and optional headers, and is terminated by an empty line. Since HTTP/1.0 did not define any 1xx status codes, servers must not send a 1xx response to an HTTP/1.0 client except under experimental conditions."
  }
  (...)
*/

### Obtener un array con todos los significados y códigos de estado HTTP en español

$HTTPStatusCodes = HTTPStatusCode::getAll('es');

echo '<pre>'; var_dump($HTTPStatusCodes); echo '</pre>';

/*
array(67) {
  ["1xx"]=>
  array(2) {
    ["short"]=>
    string(23) "Respuestas informativas"
    ["large"]=>
    string(875) "Petición recibida, continuando proceso. Esta respuesta significa que el servidor ha recibido los encabezados de la petición, y que el cliente debería proceder a enviar el cuerpo de la misma (en el caso de peticiones para las cuales el cuerpo necesita ser enviado; por ejemplo, una petición Hypertext Transfer Protocol). Si el cuerpo de la petición es largo, es ineficiente enviarlo a un servidor, cuando la petición ha sido ya rechazada, debido a encabezados inapropiados. Para hacer que un servidor cheque si la petición podría ser aceptada basada únicamente en los encabezados de la petición, el cliente debe enviar Expect: 100-continue como un encabezado en su petición inicial (vea Plantilla:Web-RFC: Expect header) y verificar si un código de estado 100 Continue es recibido en respuesta, antes de continuar (o recibir 417 Expectation Failed y no continuar)."
  }
  (...)
*/
```

### Tests 

Para utilizar la clase de [pruebas](tests), simplemente:

```php
<?php
$loader = require __DIR__ . '/vendor/autoload.php';

$loader->addPsr4('Josantonius\\HTTPStatusCode\\Tests\\', __DIR__ . '/vendor/josantonius/httpstatuscode/tests');

use Josantonius\HTTPStatusCode\Tests\HTTPStatusCodeTest;
```
Métodos de prueba disponibles en esta librería:

```php
HTTPStatusCodeTest::testLoad();
HTTPStatusCodeTest::testLoadES();
HTTPStatusCodeTest::testGetEN();
HTTPStatusCodeTest::testGetES();
HTTPStatusCodeTest::testGetLargeEN();
HTTPStatusCodeTest::testGetLargeES();
HTTPStatusCodeTest::testGetShortEN();
HTTPStatusCodeTest::testGetShortES();
HTTPStatusCodeTest::testGetUndefinedEN();
HTTPStatusCodeTest::testGetUndefinedES();
HTTPStatusCodeTest::testGetAllEN();
HTTPStatusCodeTest::testGetAllES();
```

### Manejador de excepciones

Esta librería utiliza [control de excepciones](src/Exception) que puedes personalizar a tu gusto.
### Contribuir
1. Comprobar si hay incidencias abiertas o abrir una nueva para iniciar una discusión en torno a un fallo o función.
1. Bifurca la rama del repositorio en GitHub para iniciar la operación de ajuste.
1. Escribe una o más pruebas para la nueva característica o expón el error.
1. Haz cambios en el código para implementar la característica o reparar el fallo.
1. Envía pull request para fusionar los cambios y que sean publicados.

Esto está pensado para proyectos grandes y de larga duración.

### Repositorio

Los archivos de este repositorio se crearon y subieron automáticamente con [Reposgit Creator](https://github.com/Josantonius/BASH-Reposgit).

### Autor

Mantenido por [Josantonius](https://github.com/Josantonius/).

### Licencia

Este proyecto está licenciado bajo la **licencia MIT**. Consulta el archivo [LICENSE](LICENSE) para más información.