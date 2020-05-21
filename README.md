# DocumentacionGraph
En este documento se especifica los pasos que se han seguido para habilitar el control del calendario de outlook mediante la api de microsoft graph con doble verificación

## Descripción
En este documento se detallan los pasos a seguir para crear y consultar una api de Microsoft Azure. Para este ejemplo se creará una una aplicación que genera eventos directamente en el calendario de Outlook. Contaremos con una aplicación web donde en primera instancia el usuario deberá loguearse mediante el login de Microsoft ( con seguridad en dos pasos) y luego podrá hacer un CRUD entero de los eventos.

## Requisitos
Para llevar acabo este ejemplo se deberá contar con una cuenta de microsoft Azure 

## Registrando la aplicación en Azure
### 1.- Entramos a plataforma de azure 
https://azure.microsoft.com/es-es/

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/0.png" width="100%">

### 2.- Azure Directory
<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/1.png" width="100%">

### 3.- Registro de aplicaciones  y le damos a crear nueva aplicación
<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/2.png" width="100%">

### 4.- Rellenamos los campos

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/3.JPG" width="100%">

***Explicación***

**Nombre** Es el nombre con el que vamos a identificar a nuestra aplicación dentro de Azure.

**Tipo de cuenta compatible**  declaramos que cuentas de microsoft pueden hacer uso de esta api; casos:

- Solo usuarios dentro de nuestra organización (en este caso Fundación Universitaria).
- Todos aquellos usuarios con una cuenta profesional o educativa de Microsoft .
- Todos aquellos usuarios con cuenta de microsoft.

**URI de redirección** En este campo se deberá dar la ruta donde queremos que se nos devuelvan los datos(id_token, access_token, refresh_token) una vez logueado.

### 5.- Autenticación configurando parámetros

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/4.png" width="100%">

***Explicación***

**URI de cierre de sesión**  cuando cerremos las sesión Azure nos devolverá una respuesta a la ruta especificada. Podemos recoger esta respuesta para por ejemplo borrar las cookies como veremos más adelante.

**Tokens de acceso** lo usaremos para realizar acciones con la api.
**Tokens de id** se usa para solicitar información del usuario(nombre, edad …).

### 6.-Permisos de API

En este apartado vamos a configurar qué acciones se van a poder realizar con la aplicación. Para nuestro caso habilitaremos todos los permisos de del calendario y uno llamado 
offline_access. 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/5.png" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/6.JPG" width="100%">

Estos permisos una vez solicitados deberán ser confirmados por un administrador, obteniendo una vista parecida a la siguiente 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/7.JPG" width="100%">

## Login desde la aplicación web
Datos que vamos a necesitar

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/8.png" width="100%">

Para este apartado Microsoft nos ofrece 3 alternativas según las necesidades que tengamos.

- Pidiendo token directamente a la API (Poco seguro)
- Mediante la ruta de login de microsoft
- Usando la librería MSAL

En nuestro caso optaremos por la segunda opción.

### Flujo de código

Obtener autorización

El primer paso sería redigir al usuario a la ruta https://login.microsoftonline.com/common/oauth2/v2.0/authorize aqui el usuario tendra que iniciar sesion con su cuenta y será la propia plataforma quién gestione los permisos referentes al usuario, veamos como hacerlo:

### 1.- Función que crea la URL

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/9.png" width="100%">

***Explicación:***

**baseRequestsUrl** petición url para acceder al login de Microsoft
**CLIENT_ID** dato que escogemos de la plataforma la cual identifica nuestra aplicación dentro de azure (ver imagen superior)
**REDIRECT_URI** esta url es lo que hemos elegido en en el apartado de autenticación para que nos devuelva la respuesta una vez realizado el login.
**SCOPES** definidos los permisos que tenemos en azure podemos tener menos de los que tenemos definidos en Azure, pero nunca más.

Estructura que estamos creando en la uri:

```
// Line breaks for legibility only

https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize?
client_id=6731de76-14a6-49ae-97bc-6eba6914391e
&response_type=code
&redirect_uri=http%3A%2F%2Flocalhost%2Fmyapp%2F
&response_mode=query
&scope=offline_access%20user.read%20mail.read
&state=12345
```

### 2.- Creando la ventana de login de microsoft

JS

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/authHandlerJs.JPG" width="100%">

**3.-Tratando la respuesta**

Es importante entender que una vez que nos hayamos identificado como un usuario válido microsoft enviará la verificación a la url que hayamos facilitado (ver punto 5). Por ello una vez que nos llegue la respuesta vamos a tratarla mediante el método **handlePageReguest()**.

PHP

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/10.png" width="100%">

**1º** 
En este punto en el caso de poseer access_token simplemente no habrá que hacer nada.

**2º** 
En el caso de que no tengamos el access_token pero hayamos recibido respuesta por parte de microsoft azure guardaremos dicha respuesta en una variable (en este caso verifier)

```
**Respuesta obtenida de azure**
GET https://localhost/myapp/?
code=M0ab92efe-b6fd-df08-87dc-2c6500a7f84d
&state=12345
```

**3º Petición del token**
Con el código facilitado deberemos realizar la petición del token y para ello debemos crear la siguiente estructura

// Line breaks for legibility only

POST /{tenant}/oauth2/v2.0/token HTTP/1.1
Host: https://login.microsoftonline.com
Content-Type: application/x-www-form-urlencoded

client_id=6731de76-14a6-49ae-97bc-6eba6914391e
&scope=user.read%20mail.read
&code=OAAABAAAAiL9Kn2Z27UubvWFPbm0gLWQJVzCTE9UkP3pSx1aXxUjq3n8b2JRLk4OxVXr...
&redirect_uri=http%3A%2F%2Flocalhost%2Fmyapp%2F
&grant_type=authorization_code
&client_secret=JqQX2PNo9bpM0uEihUPzyrh    // NOTE: Only required for web apps


En nuestro código será lo siguiente

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/10.JPG" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/11.JPG" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/sendRequestJs.JPG" width="100%">

**Respuesta al token**
Aunque el token de acceso es opaco para su aplicación, la respuesta contiene una lista de los permisos para los que el token de acceso es bueno en el scopeparámetro.

``` json
{
    "token_type": "Bearer",
    "scope": "user.read%20Fmail.read",
    "expires_in": 3600,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ik5HVEZ2ZEstZnl0aEV1Q...",
    "refresh_token": "AwABAAAAvPM1KaPlrEqdFSBzjqfTGAMxZGUTdM0t4B4..."
}
```
**4º** Por último ya solo nos quedaría tratar la respuesta que hemos optenido y crear las cookies con las características que queramos así como guardar el *refresh_token* recibido para futuras actualizaciones del *access_token*.

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/handleTokenResponse.JPG" width="100%">

6º Refresh_token
Es importante aclarar que a la hora de conectarnos con la api de microsoft graph podemos hacerlo logueandonos para que nos dé un nuevo token cada hora ( es el tiempo de vida del access_token) o podemos guardar el refresh_token en nuestra base de datos para solicitar un nuevo access_token sin tener que volver a iniciar sesión durante 90 días(vida útil del refresh token)

Si al llamar a nuestro método **handlePageReguest()**. somo capaces de obtener un refresh_token de nuestra base de datos podemos crear una petición con la siguiente estructura:

<h3>Solicitud</h3>

// Line breaks for legibility only

POST /common/oauth2/v2.0/token HTTP/1.1
Host: https://login.microsoftonline.com
Content-Type: application/x-www-form-urlencoded

client_id=6731de76-14a6-49ae-97bc-6eba6914391e
&scope=user.read%20mail.read
&refresh_token=OAAABAAAAiL9Kn2Z27UubvWFPbm0gLWQJVzCTE9UkP3pSx1aXxUjq...
&redirect_uri=http%3A%2F%2Flocalhost%2Fmyapp%2F
&grant_type=refresh_token
&client_secret=JqQX2PNo9bpM0uEihUPzyrh      // NOTE: Only required for web apps

Para crear la petición en nuestro código el flujo sería el siguiente

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/refreshToken.JPG" width="100%">

1 Primero cargamos nuestro refresh_token que previamente hemos guardado en nuestra base de datos.
2. Creamos la estructura del post 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/requestAccessTokenByRefreshToken.JPG" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/requestAccessToken.JPG" width="100%">

Y enviamos la petición 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/sendRequestJs.JPG" width="100%">

Si todo ha salido bien deberíamos recibir una respuesta con la siguiente estructura:

> A successful token response will look similar to the following.

```
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ik5HVEZ2ZEstZnl0aEV1Q...",
    "token_type": "Bearer",
    "expires_in": 3599,
    "scope": "user.read%20mail.read",
    "refresh_token": "AwABAAAAvPM1KaPlrEqdFSBzjqfTGAMxZGUTdM0t4B4...",
}
```
