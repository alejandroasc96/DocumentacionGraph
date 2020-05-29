<?php

$ListMessage;
define('AUTHCOOKIE', 'graph_auth');
define('ERRORCODE', 'error');
define('ERRORDESC', 'error_description');
define('ACCESSTOKEN', 'access_token');
define('ID_TOKEN', 'id_token');
define('CODE', 'code');
define('SCOPE', 'scope');
define('EXPIRESIN', 'expires_in');
define('REFRESHTOKEN', 'refresh_token');

// Update the following values
define('CLIENTID', 'write here');
define('CLIENTSECRET', 'write here');
define('SCOPES', 'write here');


// Make sure this is identical to the REDIRECT_URI_PATH parameter passed in WL.init() call.
define('CALLBACK', 'write here');

define('OAUTHURL', 'https://login.microsoftonline.com/common/oauth2/v2.0/token');

function buildQueryString($array)
{
  $result = '';
  foreach ($array as $k => $v) {
    if ($result == '') $prefix = '';
    else $prefix = '&';

    $result .= $prefix . rawurlencode($k) . '=' . rawurlencode($v);
  }
  return $result;
}

function parseQueryString($query)
{
  $result = array();
  $arr = preg_split('/&/', $query);
  foreach ($arr as $arg) {
    if (strpos($arg, '=') !== false) {
      $kv = preg_split('/=/', $arg);
      $result[rawurldecode($kv[0])] = rawurldecode($kv[1]);
    }
  }
  return $result;
}

function sendRequest(
  $url,
  $method = 'GET',
  $data = array(),
  $headers = array('Content-type: application/x-www-form-urlencoded;charset=UTF-8;')
) {
  $context = stream_context_create(array(
    'http' => array(
      'method' => $method,
      'header' => $headers,
      'content' => buildQueryString($data)
    )
  ));
  $result1 = file_get_contents($url, false, $context);
  return $result1;
}

function JSON2Array($data)
{
  return  (array) json_decode(stripslashes($data));
}

function requestAccessToken($content)
{
  $response = sendRequest(
    OAUTHURL,
    'POST',
    $content
  );
  if ($response !== false) {
    $authToken = json_decode($response);
    if (!empty($authToken) && !empty($authToken->{ACCESSTOKEN})) {
      return $authToken;
    }
  }

  return false;
}

function requestAccessTokenByVerifier($verifier)
{
  return requestAccessToken(array(
    'client_id' => CLIENTID,
    'redirect_uri' => CALLBACK,
    'client_secret' => CLIENTSECRET,
    'code' => $verifier,
    'grant_type' => 'authorization_code',
    'scope' => SCOPES
  ));
}

function requestAccessTokenByRefreshToken($refreshToken)
{
  return requestAccessToken(array(
    'client_id' => CLIENTID,
    'scope' => SCOPES,
    'refresh_token' => $refreshToken,
    'redirect_uri' => CALLBACK,
    'grant_type' => 'refresh_token',
    'client_secret' => CLIENTSECRET
  ));
}

function handlePageRequest()
{
  if (!empty($_GET[ACCESSTOKEN])) {
    // There is a token available already. It should be the token flow. Ignore it.
    return;
  }
  $verifier = $_GET[CODE];
  if (!empty($verifier)) {
    //Creamos estructura del post Y lo enviamos
    $token = requestAccessTokenByVerifier($verifier);
    if ($token !== false) handleTokenResponse($token);
    else {
      handleTokenResponse(null, array(
        ERRORCODE => 'request_failed',
        ERRORDESC => 'Failed to retrieve user access token con el Verifiered.'
      ));
    }

    return;
  }


  $refreshToken = readRefreshToken();
  if (!empty($refreshToken)) {
    $token = requestAccessTokenByRefreshToken($refreshToken);
    if ($token !== false) handleTokenResponse($token);
    else {
      handleTokenResponse(null, array(
        ERRORCODE => 'request_failed',
        ERRORDESC => 'Failed to retrieve user access token.'
      ));
    }

    return;
  }

  $errorCode = $_GET[ERRORCODE];
  $errorDesc = $_GET[ERRORDESC];

  if (!empty($errorCode)) {
    handleTokenResponse(null, array(
      ERRORCODE => $errorCode,
      ERRORDESC => $errorDesc
    ));
  }
}



function readRefreshToken()
{
  // read refresh token of the user identified by the site.
  $servername = "write here";
  $database = "write here";
  $username = "write here";
  $password = "write here";

  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $database);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT refresh_token FROM `usuarios` WHERE id=(
    SELECT max(id) FROM usuarios
    )";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $result = $result->fetch_assoc();
  } else {
    $result = '';
  }
  $conn->close();


  return $result['refresh_token'];
}

function saveRefreshToken($refreshToken, $token)
{
  // save the refresh token and associate it with the user identified by your site credential system.
  // guardando refreshToken
  $servername = "write here";
  $database = "write here";
  $username = "write here";
  $password = "write here";

  $tokenDate = date('Y/m/d H:i:s');
  $refreshTokenDate = date('Y/m/d H:i:s');
  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $database);
  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  $sql = "INSERT INTO usuarios (refresh_token, fecha_refresh_token,token,fecha_token)
  VALUES ('$refreshToken', '$refreshTokenDate','$token','$tokenDate')";


  if ($conn->query($sql) === TRUE) {
    // echo "New record created successfully";
  } else {
    // echo "Error: " . $sql . "<br>" . $conn->error;
  }
  mysqli_close($conn);
}

function saveMessageParameters($subjectMessage, $descriptionMessage, $webLink, $idMessage, $senderMessage, $idEvent)
{
  $servername = "localhost";
  $database = "write here";
  $username = "write here";
  $password = "write here";
  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $database);
  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }
  echo "Connected successfully";

  $sql = "INSERT INTO `Mensajes` (`asunto`,  `descripcion`,`weblink`,`id_mensaje`,`sender`,`id_evento`)
  VALUES ('$subjectMessage', '$descriptionMessage','$webLink','$idMessage','$senderMessage','$idEvent')";


  if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  mysqli_close($conn);
}

function seeMessageParameters()
{
  $servername = "localhost";
  $database = "write here";
  $username = "write here";
  $password = "write here";
  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $database);
  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  $sql = "SELECT * FROM `Mensajes`";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
  $conn->close();
}

function handleTokenResponse($token, $error = null)
{
  $authCookie = $_COOKIE[AUTHCOOKIE];
  $cookieValues = parseQueryString($authCookie);

  if (!empty($token)) {
    $cookieValues[ACCESSTOKEN] = $token->{ACCESSTOKEN};
    $cookieValues[ID_TOKEN] = $token->{ID_TOKEN};
    $cookieValues[SCOPES] = $token->{SCOPE};
    $cookieValues[EXPIRESIN] = $token->{EXPIRESIN};

    if (!empty($token->refresh_token)) {
      saveRefreshToken($token->refresh_token, $token->access_token);
    }
    setrawcookie('session_state', 'Authorized', 0, '/', $_SERVER['SERVER_NAME']);
    setrawcookie('session_active', 'session_active', time() + 1, '/', $_SERVER['SERVER_NAME']);
  }

  if (!empty($error)) {
    $cookieValues[ERRORCODE] = $error[ERRORCODE];
    $cookieValues[ERRORDESC] = $error[ERRORDESC];

    setrawcookie('session_state', 'Unauthorized', 0, '/', $_SERVER['SERVER_NAME']);
  }
  setrawcookie(AUTHCOOKIE, buildQueryString($cookieValues), 0, '/', $_SERVER['SERVER_NAME']);
}

function closeSession()
{
  setrawcookie('session_state', 'Unauthorized', 0, '/', $_SERVER['SERVER_NAME']);
}

// ******************************************************************************************
// BLOQUE LLAMADAS API 
// ******************************************************************************************


// ************* CRUD EVENTS **********************************

function readEvents($encodedAccessToken)
{
  global $eventos;
  $ch = curl_init('https://graph.microsoft.com/v1.0/me/calendar/events?orderby=start/dateTime&$top=100');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $encodedAccessToken, 'Prefer: outlook.body-content-type="text"'));
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

function CreateEvents($subjectCrear,$bodyCrear,$isAllDay,$startDateFormat,$endDateFormat,$encodedAccessToken)
{
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
    header('Location: writte here your web');
  }
}

function deleteEvents($idEvents,$encodedAccessToken)
{
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/me/events/" . $idEvents,
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
    header('Location: writte here your web');
  }
}

function updateEvents($idEvent,$updateSubject,$UpdateBody,$isAllDay,$startDateFormat,$endDateFormat,$encodedAccessToken)
{
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/me/events/" . $idEvent,
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
    header('Location: writte here your web');
  }
}
// ************* END CRUD EVENTS **********************************

// *************** MAILS CALLS **************************************

function readMessageWhitId($messageId,$encodedAccessToken)
{

  $ch = curl_init('https://graph.microsoft.com/v1.0/me/messages/'.$messageId);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $encodedAccessToken));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  $err = curl_error($ch);

  curl_close($ch);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {

      list($header, $body) = explode("\r\n\r\n", $response, 2);

    $response = json_decode($body, true);
    return $response;
  }
}

function readMessages($encodedAccessToken)
{
  global $ListMessage;
  $ch = curl_init('https://graph.microsoft.com/v1.0/users/asantana@fulp.es/mailFolders/Inbox/Messages');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $encodedAccessToken, 'Prefer: outlook.body-content-type="text"'));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  $err = curl_error($ch);

  curl_close($ch);

  if ($err) {
    echo "cURL Error #:" . $err;
    // authHandler();
  } else {
    $view = explode('value', $response);
    $view = $view[1];
    $view = '{"value' . $view;

    $ListMessage = json_decode($view, true);
    // var_dump($ListMessage);
    return $ListMessage;
  }
  // }
}

// *************** END MAILS CALLS **************************************

function isTokenActive($encodedAccessToken)
{
  $isToken = readEvents($encodedAccessToken);
  if (empty($isToken)) {
    handlePageRequest();
    $cookieValues = parseQueryString(@$_COOKIE['graph_auth']);
    $encodedAccessToken = rawurlencode(@$cookieValues['access_token']);
    $bb = readEvents($encodedAccessToken);
    if (empty($bb)) {
      setrawcookie('session_state', 'Unauthorized', 0, '/', $_SERVER['SERVER_NAME']);
      echo "<script src='./lib/app-config.js' type='text/javascript'></script>";
      echo "<script>";
      echo "authHandler();";
      echo "</script>";
    }
  }
}


