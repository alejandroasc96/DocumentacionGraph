
## Login desde la aplicación web
Datos que vamos a necesitar

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/8.png?raw=true" width="100%">

Para este apartado Microsoft nos ofrece 3 alternativas según las necesidades que tengamos.

- Pidiendo token directamente a la API (Poco seguro)
- Mediante la ruta de login de microsoft
- Usando la librería MSAL

En nuestro caso optaremos por la segunda opción.

### Flujo de código

Obtener autorización

El primer paso sería redigir al usuario a la ruta https://login.microsoftonline.com/common/oauth2/v2.0/authorize aqui el usuario tendra que iniciar sesion con su cuenta y será la propia plataforma quién gestione los permisos referentes al usuario, veamos como hacerlo:

### 1.- Función que crea la URL

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/9.png?raw=true" width="100%">

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

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/authHandlerJs.JPG?raw=true" width="100%">

**3.-Tratando la respuesta**

Es importante entender que una vez que nos hayamos identificado como un usuario válido microsoft enviará la verificación a la url que hayamos facilitado (ver punto 5). Por ello una vez que nos llegue la respuesta vamos a tratarla mediante el método **handlePageReguest()**.

PHP

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/10.png?raw=true" width="100%">

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

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/10.JPG?raw=true" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/11.JPG?raw=true" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/sendRequestJs.JPG?raw=true" width="100%">

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

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/handleTokenResponse.JPG?raw=true" width="100%">

**6º** Refresh_token
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

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/refreshToken.JPG?raw=true" width="100%">

1 Primero cargamos nuestro refresh_token que previamente hemos guardado en nuestra base de datos.
2. Creamos la estructura del post 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/requestAccessTokenByRefreshToken.JPG?raw=true" width="100%">

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/requestAccessToken.JPG?raw=true" width="100%">

Y enviamos la petición 

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/sendRequestJs.JPG?raw=true" width="100%">

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

## Realizando CRUD 

### URL 

<h5>CREATE</h5>
 "https://graph.microsoft.com/v1.0/me/events" 

<h5>READ</h5>
> https://graph.microsoft.com/v1.0/me/calendar/events?orderby=start/dateTime&$top=100

<h5>UPDATE</h5>
> "https://graph.microsoft.com/v1.0/me/events/[idUpdateEvent]"

<h5>DELETE</h5>
> "https://graph.microsoft.com/v1.0/me/events/[elimarEvento]"

### Nuestro código

<h5>CREATE</h5>

``` php
if ($_REQUEST['creandoLargo']) {
  $subjectCrear = $_REQUEST['EventName'];
  $bodyCrear = (!empty($_REQUEST['modalBodyEvent'])) ? $_REQUEST['modalBodyEvent'] : '';
  if ($_REQUEST['allDay']) {
    $isAllDay = 'true';
    $b = $_REQUEST['startDate'];
    $startDateFormat = $b . 'T00:00:00';
    $bb = $_REQUEST['endDate'];
    $endDateFormat = $bb . 'T00:00:00';
  } else {
    $isAllDay = 'false';
    if (!empty($_REQUEST['startHour'])) {
      $fechaFormat =  $_REQUEST['startDate'];
      $startHourCrear = $_REQUEST['startHour'];
      $startDateFormat = $fechaFormat . 'T' . $startHourCrear;
    }
    if (!empty($_REQUEST['endHour'])) {
      $fechaFormat =  $_REQUEST['endDate'];
      $endtHourCrear = $_REQUEST['endHour'];
      $endDateFormat = $fechaFormat . 'T' . $endtHourCrear;
    }
  }



  $cookieValues = parseQueryString(@$_COOKIE['graph_auth']);
  //Since cookies are user-supplied content, it must be encoded to avoid header injection
  $encodedAccessToken = rawurlencode(@$cookieValues['access_token']);
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/me/events",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "{
            'Subject': '$subjectCrear',
            'Body': {
                'ContentType': 'HTML',
                'Content': '$bodyCrear'
            },
            'isAllDay': '$isAllDay',
            'Start': {
                'DateTime': '$startDateFormat',
                'TimeZone': 'GMT Standard Time'
            },
            'End': {
                'DateTime': '$endDateFormat',
                'TimeZone': 'GMT Standard Time'
            }
            }",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer 
        " . $encodedAccessToken,
      "Content-Type: application/json",
    ),
  ));
  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    $responseArray = json_decode($response, true);

    if ($_REQUEST['idMessage']) {

      saveMessageParameters(
        utf8_decode($_REQUEST['subjectMessage']),
        utf8_decode($_REQUEST['bodyPreviewMessage']),
        $_REQUEST['webLinkMessage'],
        $_REQUEST['idMessage'],
        utf8_decode($_REQUEST['senderMessage']),
        $responseArray['id']
      );
    }
    header('Location: https://scripts.fulp.es/microsoft/onenoteapisamples/index.php');
  }
}
```
<h5>READ</h5>

``` php
function readEvents($encodedAccessToken)
{
  global $eventos;
  var_dump($encodedAccessToken);
  $ch = curl_init('https://graph.microsoft.com/v1.0/me/calendar/events?orderby=start/dateTime&$top=100');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $encodedAccessToken));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  $err = curl_error($ch);

  curl_close($ch);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    $view = explode('value', $response);
    $view = $view[1];
    $view = '{"value' . $view;

    $eventos = json_decode($view, true);
    return $eventos;
  }
}
```

<h5>UPDATE</h5>

``` php
// Update Event 
if ($_REQUEST['idUpdateEvent']) {
  $updateSubject = $_REQUEST['UpdateEventName'];
  $UpdateBody = (!empty($_REQUEST['updateModalBodyEvent'])) ? $_REQUEST['updateModalBodyEvent'] : '';
  if ($_REQUEST['updateAllDay']) {
    $isAllDay = 'true';
    $b = (!empty($_REQUEST['updateStartDate'])) ? $_REQUEST['updateStartDate'] : '';
    $startDateFormat = $b . 'T00:00:00';
    $bb = (!empty($_REQUEST['updateEndDate'])) ? $_REQUEST['updateEndDate'] : '';
    $endDateFormat = $bb . 'T00:00:00';
  } else {
    $isAllDay = 'false';
    if (!empty($_REQUEST['updateStartHour'])) {
      $fechaFormat =  (!empty($_REQUEST['updateStartDate'])) ? $_REQUEST['updateStartDate'] : '';
      $startHourCrear = (!empty($_REQUEST['updateStartHour'])) ? $_REQUEST['updateStartHour'] : '';
      $startDateFormat = $fechaFormat . 'T' . $startHourCrear;
    }
    if (!empty($_REQUEST['updateEndHour'])) {
      $fechaFormat =  (!empty($_REQUEST['updateEndDate'])) ? $_REQUEST['updateEndDate'] : '';
      $endtHourCrear = (!empty($_REQUEST['updateEndHour'])) ? $_REQUEST['updateEndHour'] : '';
      $endDateFormat = $fechaFormat . 'T' . $endtHourCrear;
    }
  }
  $cookieValues = parseQueryString(@$_COOKIE['graph_auth']);
  //Since cookies are user-supplied content, it must be encoded to avoid header injection
  $encodedAccessToken = rawurlencode(@$cookieValues['access_token']);
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/me/events/" . $_REQUEST['idUpdateEvent'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PATCH",
    CURLOPT_POSTFIELDS => "{
      'Subject': '$updateSubject',
      'Body': {
          'ContentType': 'HTML',
          'Content': '$UpdateBody'
      },
      'isAllDay': '$isAllDay',
      'Start': {
          'DateTime': '$startDateFormat',
          'TimeZone': 'GMT Standard Time'
      },
      'End': {
          'DateTime': '$endDateFormat',
          'TimeZone': 'GMT Standard Time'
      }
      }",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer 
            " . $encodedAccessToken,
      "Content-Type: application/json",
    ),
  ));
  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    var_dump($response);
    header('Location: https://scripts.fulp.es/microsoft/onenoteapisamples/index.php');
  }
}
```

<h5>DELETE</h5>

``` php
// Eliminar Evento 
if ($_REQUEST['elimarEvento']) {
  $cookieValues = parseQueryString(@$_COOKIE['graph_auth']);
  //Since cookies are user-supplied content, it must be encoded to avoid header injection
  $encodedAccessToken = rawurlencode(@$cookieValues['access_token']);
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/me/events/" . $_REQUEST['elimarEvento'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "DELETE",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer 
                " . $encodedAccessToken,
      "Content-Type: application/json",
    ),
  ));
  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    var_dump($response);
    header('Location: https://scripts.fulp.es/microsoft/onenoteapisamples/index.php');
  }
}
```