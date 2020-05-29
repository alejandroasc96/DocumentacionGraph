## Registrando la aplicación en Azure
### 1.- Entramos a plataforma de azure 
https://azure.microsoft.com/es-es/

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/0.png?raw=true)

### 2.- Azure Directory

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/1.png?raw=true)

### 3.- Registro de aplicaciones  y le damos a crear nueva aplicación
<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/2.png?raw=true" width="100%">


### 4.- Rellenamos los campos

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/3.JPG?raw=true" width="100%">


***Explicación***

**Nombre** Es el nombre con el que vamos a identificar a nuestra aplicación dentro de Azure.

**Tipo de cuenta compatible**  declaramos que cuentas de microsoft pueden hacer uso de esta api; casos:

- Solo usuarios dentro de nuestra organización (en este caso Fundación Universitaria).
- Todos aquellos usuarios con una cuenta profesional o educativa de Microsoft .
- Todos aquellos usuarios con cuenta de microsoft.

**URI de redirección** En este campo se deberá dar la ruta donde queremos que se nos devuelvan los datos(id_token, access_token, refresh_token) una vez logueado.

### 5.- Autenticación configurando parámetros

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/4.png?raw=true" width="100%">

***Explicación***

**URI de cierre de sesión**  cuando cerremos las sesión Azure nos devolverá una respuesta a la ruta especificada. Podemos recoger esta respuesta para por ejemplo borrar las cookies como veremos más adelante.

**Tokens de acceso** lo usaremos para realizar acciones con la api.
**Tokens de id** se usa para solicitar información del usuario(nombre, edad …).

### 6.-Permisos de API

En este apartado vamos a configurar qué acciones se van a poder realizar con la aplicación. Para nuestro caso habilitaremos todos los permisos de del calendario y uno llamado 
offline_access. 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/5.png?raw=true" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/6.JPG?raw=true" width="100%">

Estos permisos una vez solicitados deberán ser confirmados por un administrador, obteniendo una vista parecida a la siguiente 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/7.JPG?raw=true" width="100%">