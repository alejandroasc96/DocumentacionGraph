<?php
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
define('CLIENTID', 'write here your clientid');
define('CLIENTSECRET', 'write here your CLIENTSECRET');
define('SCOPES', 'write here your SCOPES');


// Make sure this is identical to the REDIRECT_URI_PATH parameter passed in WL.init() call.
define('CALLBACK', 'write here your CALLBACK');

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
  $headers = array('Content-type: application/x-www-form-urlencoded;charset=UTF-8','Prefer: outlook.body-content-type="text"')
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

function JSON2Array($data){
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

// Método para leer el refreshToken de nuestra base de datos, se puede modificar a disposición 
function readRefreshToken()
{
  // read refresh token of the user identified by the site.
  $servername = "localhost";
  $database = "";
  $username = "";
  $password = "";
  
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
    while ($row = $result->fetch_assoc()) {
      $result = $row["refresh_token"];
    }
  } else {
    $result='';
    echo "0 results";
  }
  $conn->close();

  return $result;
}

function saveRefreshToken($refreshToken, $token)
{
  // save the refresh token and associate it with the user identified by your site credential system.
  // guardando refreshToken
  $servername = "";
  $database = "";
  $username = "";
  $password = "";

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

handlePageRequest();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:msgr="http://messenger.live.com/2009/ui-tags">

<head>
  <title>Microsoft Graph OneNote Sample Callback Page</title>
  <script>
    window.onload = function(e) {
      window.close();
      window.opener.document.dispatchEvent(new Event('authStateChanged'));
    }
  </script>
</head>

<body>
</body>

</html>