// TODO: Update the client ID, redirect URI, and scopes for your application
var baseRequestUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
var CLIENT_ID = '2a342bee-bfd2-41d7-96e8-428789dedc52';
var REDIRECT_URI = 'https://scripts.fulp.es/microsoft/onenoteapisamples/callback.php';
var SCOPES = ['openid', 'Calendars.ReadWrite', 'User.Read'];

function EnlazarMensaje(){
  var bottonListMessage = document.getElementById('tableMessage').style.visibility;
  if(bottonListMessage == 'hidden'){
    // id('tableMessage').innerText = 'Cerrar';
    document.getElementById('tableMessage').style.visibility = 'visible';
    document.getElementById('tableMessage').style.display = 'initial';
  }else{
    // id('tableMessage').innerText = 'Adjuntar Mensaje';
    document.getElementById('tableMessage').style.visibility = 'hidden';
    document.getElementById('tableMessage').style.display = 'none';
  }
}


function id(domId) {
  return document.getElementById(domId);
}

function buildUrl() {
  return `${baseRequestUrl}?client_id=${CLIENT_ID}&scope=
  ${SCOPES.join(' ')}&response_type=code&redirect_uri=${REDIRECT_URI}&response_mode=query`;
}

function getCookie(name){
  var cname = name + "=";               
  var dc = document.cookie;             
  if (dc.length > 0) {              
    begin = dc.indexOf(cname);       
    if (begin != -1) {           
      begin += cname.length;       
      end = dc.indexOf(";", begin);
      if (end == -1) end = dc.length;
        return unescape(dc.substring(begin, end));
    } 
  }
  return null;
}

function delCookie (name,path,domain) {
  if (getCookie(name)) {
    document.cookie = name + "=" +
    ((path == null) ? "" : "; path=" + path) +
    ((domain == null) ? "" : "; domain=" + domain) +
    "; expires=Thu, 01-Jan-70 00:00:01 GMT";
  }
}

function authHandler() {
  var sessionState = findSessionState();
  if (sessionState == 'Authorized') {
    document.dispatchEvent(new Event('authStateChanged'));
    window.location.href = 'https://login.microsoftonline.com/common/oauth2/v2.0/logout'
  }
  else openAuthWindow(buildUrl(), 'Authorize OneNote PHP Sample', '700', '500');
}

function openAuthWindow(url, title, w, h) {
  // Fixes dual-screen position                         Most browsers      Firefox
  var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
  var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

  var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
  var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

  var left = ((width / 2) - (w / 2)) + dualScreenLeft;
  var top = ((height / 2) - (h / 2)) + dualScreenTop;
  var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

  // Puts focus on the newWindow
  if (window.focus) {
    newWindow.focus();
  }
}

function findCookie(cookieName, cookieProperty) {
  var cookies = document.cookie.split('; ');
  // console.log("findCookie: "+cookies);
  for (var i = 0; i < cookies.length; i++) {
    var cookie = cookies[i];
    // console.log(cookie);
    if (cookie.startsWith(cookieName) && cookie !=null) {
      if(cookie.indexOf('graph_auth') > -1 && !(cookie.indexOf('null') > -1))
      {
        // console.table(cookie);
        // var accessToken = cookie.split(
      }
      var accessToken = cookie.split(`${cookieProperty}=`)[1].split('&')[0];
      // console.log("dentroFor: "+accessToken);
      return accessToken;
    }
  }
}

function retrieveAccessToken() {
  var d = findCookie('graph_auth', 'access_token');
  // console.log("retrieveAccessToken"+d);
  // return findCookie('graph_auth', 'access_token');
}

function findSessionState() {
  var sessionState = findCookie('session_state', 'session_state');
  return sessionState ? sessionState : 'Unauthorized';
}

function findSessionActive()
{
  var sessionActive = findCookie('session_active', 'session_active');
  return sessionActive ? sessionActive : 'noActiveSession';
}



function initiateXMLHttpRequest(resource, cb) {
  var token = retrieveAccessToken();
  var xhr = new XMLHttpRequest();

  if (token) {
    xhr.open('GET', `https://graph.microsoft.com/v1.0/me/${resource}/$value`);
    xhr.setRequestHeader('Authorization', `Bearer ${token}`);
    xhr.send(null);
  }

  xhr.onreadystatechange = function() {
    var DONE = 4;
    var OK = 200;
    if (xhr.readyState == DONE) {
      if (xhr.status == OK) cb(xhr);
    }
  };
}

function getUserProfilePicture() {
  // Currently not supported for MSA accounts
  initiateXMLHttpRequest('photo', function(xhr) {
    var imgHolder = id('meImg');
    imgHolder.innerHTML = `<img src="${xhr.responseText}" />`;
  });
}

function getUserProfileName() {
  initiateXMLHttpRequest('displayName', function(xhr) {
    var nameHolder = id('meName');
    nameHolder.innerHTML = xhr.responseText;
  });
}

function displayMe() {
  if (id('meImg').innerHTML != '') return;
  getUserProfilePicture();
  getUserProfileName();
}

function clearMe() {
  id('meImg').innerHTML = '';
  id('meName').innerHTML = '';
}

// cambio
function checkState(){
  var sessionState = findSessionState();
  if (sessionState != null) {
    id('auth').innerText = 'Log Out';

    displayMe();
  }
  else {
    id('auth').innerText = 'Sign In';
    clearMe();
  }
}

document.addEventListener('authStateChanged', function() {
  var sessionState = findSessionState();
  var sessionActive = findSessionActive();
  if (sessionState == 'Authorized') {
    id('auth').innerText = 'Log Out';
    document.getElementById('mostrarTabla').style.visibility= 'visible';
    displayMe();
  }
  else {
    id('auth').innerText = 'Sign In';
    document.getElementById('mostrarTabla').style.visibility= 'hidden';
    clearMe();
  }
  if(sessionActive == 'session_active') location.reload();
});

function eliminarEvento(id)
{
  document.getElementById('elimarEvento').value = id;
  document.getElementById('formEliminarEvento').submit();
}

function modificarEvento(id,subject,body,isAllDay,fechaInicio,fechaFin,horaInicio,horaFin)
{
  document.getElementById("idUpdateEvent").value=id;
  document.getElementById("UpdateEventName").value=b64_to_utf8(subject);
  document.getElementById("updateModalBodyEvent").value= b64_to_utf8(body);
  if (isAllDay == 1) {
    document.getElementById('updateAllDay').checked = true;
    document.getElementById('updateStartHour').disabled = true;
    document.getElementById('updateEndHour').disabled = true;
  }else{document.getElementById('updateAllDay').checked = false;}
  document.getElementById('updateStartDate').value = fechaInicio;
  document.getElementById('updateStartHour').value = horaInicio;
  document.getElementById('updateEndDate').value = fechaFin;
  document.getElementById('updateEndHour').value = horaFin;
  $('#updateEventModal').modal('show');
  
  
  // document.getElementById('formModificarEvento').submit();
  
}

function validarModificarEvento()
{
  var inputSubject = document.getElementById('UpdateEventName');
  var inputDescription = document.getElementById('updateModalBodyEvent');
  var inputAllDayCheck = document.getElementById('updateAllDay');
  var inputStartDate = document.getElementById('updateStartDate');
  var inputEndDate = document.getElementById('updateEndDate');
  var inputStartHour = document.getElementById('updateStartHour');
  var inputEndHour = document.getElementById('updateEndHour');
  if(inputSubject.value == ''){
    inputSubject.focus({preventScroll:false});
    alert('Rellene el campo Asunto');
    return false;
  }
  if(inputAllDayCheck.checked)
  {
    if(inputStartDate.value != '' && inputEndDate.value !='')
    {
      if(inputStartDate.value>inputEndDate.value)
      {
        alert('La fecha de inicio tiene que ser antes que la de fin');
        return false;
      }else
      {
        if (inputStartHour.value = '') {
          inputStartHour.focus({preventScroll:false});
          alert('Rellene los campo hora inicio');
          return false;
        }
        if (inputEndHour.value = '') {
          inputEndHour.focus({preventScroll:false});
          alert('Rellene los campo hora fin');
          return false;
        }
      }
    }else{
      inputStartDate.focus({preventScroll:false});
      alert('Rellene los campo fecha');
      return false;
    }
  }else{
    if(inputStartDate.value != '' && inputEndDate.value !='')
    {
      if(inputStartDate.value>inputEndDate.value)
      {
        alert('La fecha de inicio tiene que ser antes que la de fin');
        return false;
      }else
      {
        if (inputStartHour.value == '') {
          inputStartHour.focus({preventScroll:false});
          alert('Rellene los campo hora inicio');
          return false;
        }
        if (inputEndHour.value == '') {
          inputEndHour.focus({preventScroll:false});
          alert('Rellene los campo hora fin');
          return false;
        }
      }
    }else{
      inputStartDate.focus({preventScroll:false});
      alert('Rellene los campo fecha');
      return false;
    }
  }
  return true;
}

function subirModificarEvento(){
  if(confirm("¿Desea modificar el evento?"))
  {
    if(validarModificarEvento()){
      document.getElementById('formUpdateEvento').submit();
    }
  }
}

function crearNuevoEvento()
{
  $('#createEventModalLong').modal('show');

}

function validarCrearNuevoEvento()
{
  var inputSubject = document.getElementById('EventName');
  var inputDescription = document.getElementById('modalBodyEvent');
  var inputAllDayCheck = document.getElementById('allDay');
  var inputStartDate = document.getElementById('startDate');
  var inputEndDate = document.getElementById('endDate');
  var inputStartHour = document.getElementById('startHour');
  var inputEndHour = document.getElementById('endHour');
  if(inputSubject.value == ''){
    inputSubject.focus({preventScroll:false});
    alert('Rellene el campo Asunto');
    return false;
  }
  if(inputAllDayCheck.checked)
  {
    if(inputStartDate.value != '' && inputEndDate.value !='')
    {
      if(inputStartDate.value>inputEndDate.value)
      {
        alert('La fecha de inicio tiene que ser antes que la de fin');
        return false;
      }else
      {
        if (inputStartHour.value = '') {
          inputStartHour.focus({preventScroll:false});
          alert('Rellene los campo hora inicio');
          return false;
        }
        if (inputEndHour.value = '') {
          inputEndHour.focus({preventScroll:false});
          alert('Rellene los campo hora fin');
          return false;
        }
      }
    }else{
      inputStartDate.focus({preventScroll:false});
      alert('Rellene los campo fecha');
      return false;
    }
  }else{
    if(inputStartDate.value != '' && inputEndDate.value !='')
    {
      if(inputStartDate.value>inputEndDate.value)
      {
        alert('La fecha de inicio tiene que ser antes que la de fin');
        return false;
      }else
      {
        if (inputStartHour.value == '') {
          inputStartHour.focus({preventScroll:false});
          alert('Rellene los campo hora inicio');
          return false;
        }
        if (inputEndHour.value == '') {
          inputEndHour.focus({preventScroll:false});
          alert('Rellene los campo hora fin');
          return false;
        }
      }
    }else{
      inputStartDate.focus({preventScroll:false});
      alert('Rellene los campo fecha');
      return false;
    }
  }
  return true;
}

function guardarInfo(idMessage,userName,subjectMessage,webLinkMessage,bodyPreviewMessage){
  var codigoHtml = '<input type="hidden" value="'+idMessage+'" name="idMessage"> <input type="hidden" value="'+userName+'" name="senderMessage"> <input type="hidden" value="'+subjectMessage+'" name="subjectMessage"><input type="hidden" value="'+bodyPreviewMessage+'" name="bodyPreviewMessage"> <input type="hidden" value="'+webLinkMessage+'" name="webLinkMessage">';
  document.getElementById('guardadoDatos').innerHTML = codigoHtml;
}

function b64_to_utf8( str ) {
  return decodeURIComponent(escape(window.atob( str )));
}

function mostrarCorreoAdjunto(mailSubject,bodyMail,senderMail) {
  var mailSubject = b64_to_utf8(mailSubject);
  var bodyMail = b64_to_utf8(bodyMail);
  var codigoHtmlTitleMail = '<h3>'+ mailSubject+'</h3>';
  var codigoHtmlBodyMail = '<h3>'+senderMail +':</h3><p>'+bodyMail +'</p>';
  document.getElementById('titleModalMail').innerHTML =codigoHtmlTitleMail;
  document.getElementById('bodyModalMail').innerHTML =codigoHtmlBodyMail;
  $('#verCorreoModal').modal('show');
  
}

function subirCrearNuevoEvento()
{
  var validando = validarCrearNuevoEvento()
  if(validando)
  {
    document.getElementById('formDetallesEvento').submit();
  }
}

function obtenerFechaActual()
{
  var fecha = new Date(); //Fecha actual
  var mes = fecha.getMonth()+1; //obteniendo mes
  var dia = fecha.getDate(); //obteniendo dia
  var diaMasUno = fecha.getDate()+1; //obteniendo dia
  console.log(diaMasUno);
  var ano = fecha.getFullYear(); //obteniendo año
  if(dia<10)
    dia='0'+dia; //agrega cero si el menor de 10
    if(diaMasUno<10) diaMasUno='0'+diaMasUno; //agrega cero si el menor de 10
  if(mes<10)
    mes='0'+mes //agrega cero si el menor de 10
  document.getElementById('startDate').value=ano+"-"+mes+"-"+dia;
  document.getElementById('endDate').value=ano+"-"+mes+"-"+diaMasUno;
}

function obtenerFechaActualUpdateEvent()
{
  var fecha = new Date(); //Fecha actual
  var mes = fecha.getMonth()+1; //obteniendo mes
  var dia = fecha.getDate(); //obteniendo dia
  var diaMasUno = fecha.getDate()+1; //obteniendo dia
  console.log(diaMasUno);
  var ano = fecha.getFullYear(); //obteniendo año
  if(dia<10)
    dia='0'+dia; //agrega cero si el menor de 10
    if(diaMasUno<10) diaMasUno='0'+diaMasUno; //agrega cero si el menor de 10
  if(mes<10)
    mes='0'+mes //agrega cero si el menor de 10
  document.getElementById('updateStartDate').value=ano+"-"+mes+"-"+dia;
  document.getElementById('updateEndDate').value=ano+"-"+mes+"-"+diaMasUno;
}

function OnChangeCheckbox (checkbox) {
  if (checkbox.checked) {
      document.getElementById('startHour').disabled = true;
      document.getElementById('startHour').value = "00:00:00";
      obtenerFechaActual();
      document.getElementById('endHour').disabled = true;
      document.getElementById('endHour').value = "00:00:00";
  }
  else {
    document.getElementById('startHour').disabled = false;
    document.getElementById('endHour').disabled = false;
  }
}

function OnChangeUpdateEventCheckbox (checkbox) {
  if (checkbox.checked) {
      document.getElementById('updateStartHour').disabled = true;
      document.getElementById('updateStartHour').value = "00:00:00";
      obtenerFechaActualUpdateEvent();
      document.getElementById('updateEndHour').disabled = true;
      document.getElementById('updateEndHour').value = "00:00:00";
  }
  else {
    document.getElementById('updateStartHour').disabled = false;
    document.getElementById('updateEndHour').disabled = false;
  }
}

function removeCookie(cname) {
  setCookie (cname,"",-1);
  
}


// cambio
window.onload = checkState();
