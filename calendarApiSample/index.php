<?php
// ini_set ( 'display_errors' , 1 ); 
// ini_set ( 'display_startup_errors' , 1 ); 
// error_reporting(E_ALL);

static $encodedAccessToken;
$eventos;
$oneMessage;

session_start();
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = hash('sha256', rand());
}


include('funciones.php');
// //Leyendo Eventos

$cookieValues = parseQueryString(@$_COOKIE['graph_auth']);
$encodedAccessToken = rawurlencode(@$cookieValues['access_token']);





isTokenActive($encodedAccessToken);
readMessages($encodedAccessToken);


$responseMessageTable = seeMessageParameters();


// Creando Eventos 
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
  CreateEvents($subjectCrear,$bodyCrear,$isAllDay,$startDateFormat,$endDateFormat,$encodedAccessToken);
}

// Eliminar Evento 
if ($_REQUEST['elimarEvento']) {
  $cookieValues = parseQueryString(@$_COOKIE['graph_auth']);
  //Since cookies are user-supplied content, it must be encoded to avoid header injection
  $encodedAccessToken = rawurlencode(@$cookieValues['access_token']);
  deleteEvents($_REQUEST['elimarEvento'],$encodedAccessToken);
}

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
  updateEvents($_REQUEST['idUpdateEvent'],$updateSubject,$UpdateBody,$isAllDay,$startDateFormat,$endDateFormat,$encodedAccessToken);
}

//disallow other sites from embedding this page
header("X-Frame-Options: SAMEORIGIN");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>MS Graph Calendar Service PHP Sample</title>
  <link rel="stylesheet" type="text/css" href="./css/style.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <script src='https://kit.fontawesome.com/a076d05399.js'></script>
  <meta charset="UTF-8">
</head>

<body>
  <h1>MS Graph CalendarEvents Service PHP Sample</h1>

  <div>
    <div id="meName" class="Name"></div>
    <div id="meImg"></div>
    <button id="auth" onclick="authHandler();">Sign In</button>
    <div id="OneNoteForm">
      <form method="POST" id="postForm">
        <br />
        <input type="hidden" name="csrf_token" value="<?php /* Print the automatically generated session ID for CSRF protection */ echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
        <br />
        <br />
      </form>
    </div>
  </div>

<!-- TABLA DE EVENTOS -->
  <div class="" id="mostrarTabla">
    <button class="btn btn-success" onclick="crearNuevoEvento()">Nuevo Evento</button>
    <table class="table">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Asunto</th>
          <th scope="col">Descripción</th>
          <th scope="col">Fecha</th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
        <?php
        for ($i = 0; $i < count($eventos['value']); $i++) { ?>
          <tr>
            <th scope="row"><?php echo $i + 1 ?></th>
            <td class="truncate"><?php print_r($eventos['value'][$i]['subject']); ?></td>
            <td class="truncate"><?php print_r($eventos['value'][$i]['bodyPreview']); ?></td>
            <td><?php
                $startDate = $eventos['value'][$i]['start']['dateTime'];
                $endDate = $eventos['value'][$i]['end']['dateTime'];
                $date1 = new DateTime("$startDate");
                // Añadimos una hora más ya que está en formato UTC 
                $date1 = $date1->modify('1 hours');
                $date2 = new DateTime("$endDate");
                // Añadimos una hora más ya que está en formato UTC 
                $date2 = $date2->modify('1 hours');
                $a1 = $date1->format('d/m/Y');
                $a2 = $date2->format('d/m/Y');
                $a3 = $date1->format('H:i');
                $a4 = $date2->format('H:i');
                if ($eventos['value'][$i]['isAllDay']) {
                  echo "$a1 - Todo el día";
                } else {
                  if ($a1 == $a2) {
                    echo "$a1, $a3 - $a4";
                  } else {
                    echo "$a1, $a3 - $a2, $a4";
                  }
                }
                ?></td>
            <td><input type="button" class="btn btn-danger" value="Eliminar" onclick="eliminarEvento('<?php echo $eventos['value'][$i]['id']; ?>')"></td>
            <td><input type="button" class="btn btn-primary" value="Modificar" onclick="modificarEvento('<?php echo $eventos['value'][$i]['id']; ?>','<?php echo base64_encode($eventos['value'][$i]['subject']); ?>','<?php echo base64_encode($eventos['value'][$i]['body']['content']) ; ?>','<?php echo $eventos['value'][$i]['isAllDay']; ?>','<?php echo $date1->format('Y-m-d'); ?>','<?php echo $date2->format('Y-m-d'); ?>','<?php echo $a3; ?>','<?php echo $a4; ?>')"></td>
            <td>
              <?php
              for ($j = 0; $j < count($responseMessageTable); $j++) {
                if ($responseMessageTable[$j]['id_evento'] == $eventos['value'][$i]['id']) {
                  $webLinkMessage = readMessageWhitId($responseMessageTable[$j]['id_mensaje'], $encodedAccessToken);
                  if ($webLinkMessage['webLink']) { ?>
                    <a href="<?php echo $webLinkMessage['webLink'] ?>" target="_blank" class="btn btn-secondary btn-lg active" role="button" aria-pressed="true">Link</a>
                  <?php } else { ?>
                    <input type="button" class="btn btn-success" onclick='mostrarCorreoAdjunto("<?php echo utf8_encode($responseMessageTable[$j]["asunto"]) ?>","<?php echo ($responseMessageTable[$j]["descripcion"]) ?>","<?php echo utf8_encode($responseMessageTable[$j]["sender"]) ?>");' value="Ver adjunto">

                  <?php } ?>

              <?php }
              }
              ?>
            </td>

          </tr>

        <?php } ?>

      </tbody>
    </table>
  </div>

  <form action="" method="post" id="formEliminarEvento">
    <input type="hidden" id="elimarEvento" name="elimarEvento" value="">
  </form>
</body>




<!-- MODAL CREANDO EVENTO LONG  -->
<div id="createEventModalLong" class="modal fade">
  <form action="" method="post" name="formDetallesEvento" id="formDetallesEvento">
    <div id="guardadoDatos" style="display: none">

    </div>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4>Detalles</h4>
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span> <span class="sr-only">close</span></button>

        </div>
        <div id="modalBody" class="modal-body">
          <div class="form-group row">
            <div class="col-md-10">
              <input class="form-control" type="text" name="EventName" id="EventName" placeholder="Nombre de evento">
              <input type="hidden" name="creandoLargo" id="creandoLargo" value="S">
            </div>
          </div>

          <div class="form-group form-inline row" style="margin-left: 5px;">
            <label for="">Inicio: </label>
            <div>
              <input type="date" name="startDate" id="startDate">
            </div>
            <div>
              <input type="time" name="startHour" id="startHour">
            </div>
            <div class="col-md-4">
              <label class="radio-inline margen_label" style="margin-top: 5px;">
                <input type="checkbox" onclick="OnChangeCheckbox (this)" name="allDay" id="allDay"> Todo el día
              </label>
            </div>

          </div>
          <div class="form-group form-inline row" style="margin-left: 5px">
            <label for="" style="margin-right: 15px;">Fin: </label>
            <div>
              <input type="date" name="endDate" id="endDate">
            </div>
            <div>
              <input type="time" name="endHour" id="endHour">
            </div>

          </div>




          <div class="form-group">
            <textarea class="form-control" type="text" rows="4" name="modalBodyEvent" id="modalBodyEvent" placeholder="Descripción del Evento"></textarea>
          </div>
          <button type="button" onclick="EnlazarMensaje()">Adjuntar Mensaje</button>
          <div class="ListTableMail" id="tableMessage" style="visibility: hidden; display: none">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Asunto</th>
                  <th scope="col">Descripción</th>
                  <th scope="col">Fecha</th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                // var_dump( print_r($ListMessage['value'][0]['bodyPreview']));
                for ($i = 0; $i < count($ListMessage['value']); $i++) { ?>
                  <tr>
                    <th scope="row"><?php echo $i + 1 ?></th>
                    <td class=""><?php print_r($ListMessage['value'][$i]['sender']['emailAddress']['name']); ?></td>
                    <td class="truncate"><?php print_r($ListMessage['value'][$i]['subject']); ?></td>
                    <td><?php
                        $startDate = $ListMessage['value'][$i]['createdDateTime'];
                        // $endDate = $ListMessage['value'][$i]['end']['dateTime'];                
                        $date1 = new DateTime("$startDate");
                        $a1 = $date1->format('d/m/Y');
                        echo $a1;
                        ?></td>
                    <?php $pru = $ListMessage['value'][$i]['body']['content'];
                    $pru = base64_encode($pru);
                    // var_dump($pru);
                    ?>
                    <td><input type="checkbox" onclick='guardarInfo("<?php print_r($ListMessage["value"][$i]["id"]) ?>","<?php print_r($ListMessage["value"][$i]["sender"]["emailAddress"]["name"]) ?>","<?php print_r(base64_encode($ListMessage["value"][$i]["subject"])) ?>","<?php print_r($ListMessage["value"][$i]["webLink"]) ?>","<?php echo $pru ?>");' value="<?php print_r($ListMessage['value'][$i]['id']); ?>" name="SelectMessage<?php print_r($i); ?>" id="SelectMessage"></td>



                  </tr>

                <?php } ?>

              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
          <button type="button" onclick="subirCrearNuevoEvento()" class="btn btn-primary" id="submitButton">Crear</button>
        </div>
      </div>
    </div>
  </form>

</div>

<!-- MODAL UPDATE EVENTO LONG  -->
<div id="updateEventModal" class="modal fade">
  <form action="" method="post" id="formUpdateEvento">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4>Modificando Evento</h4>
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span> <span class="sr-only">close</span></button>

        </div>
        <div id="modalBody" class="modal-body">
          <div class="form-group row">
            <div class="col-md-10">
              <input type="hidden" name="idUpdateEvent" id="idUpdateEvent">
              <input class="form-control" type="text" name="UpdateEventName" id="UpdateEventName" placeholder="Nombre de evento">
              <input type="hidden" name="updateEvento" id="updateEvento" value="S">
            </div>
          </div>

          <div class="form-group form-inline row" style="margin-left: 5px;">
            <label for="">Inicio: </label>
            <div>
              <input type="date" name="updateStartDate" id="updateStartDate">
            </div>
            <div>
              <input type="time" name="updateStartHour" id="updateStartHour">
            </div>
            <div class="col-md-4">
              <label class="radio-inline margen_label" style="margin-top: 5px;">
                <input type="checkbox" onclick="OnChangeUpdateEventCheckbox (this)" name="updateAllDay" id="updateAllDay"> Todo el día
              </label>
            </div>

          </div>
          <div class="form-group form-inline row" style="margin-left: 5px">
            <label for="" style="margin-right: 15px;">Fin: </label>
            <div>
              <input type="date" name="updateEndDate" id="updateEndDate">
            </div>
            <div>
              <input type="time" name="updateEndHour" id="updateEndHour">
            </div>

          </div>


          <div class="form-group">
            <textarea class="form-control" type="text" rows="4" name="updateModalBodyEvent" id="updateModalBodyEvent" placeholder="Descripción del Evento"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
          <button type="button" onclick="subirModificarEvento()" class="btn btn-primary" id="submitUpdateButton">Modificar</button>
        </div>
      </div>
    </div>
  </form>

</div>


<!-- MODAL VER CORREO  -->
<div id="verCorreoModal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <div id="titleModalMail">
        </div>
      </div>
      <div id="modalBody" class="modal-body">
        <div id="bodyModalMail">

        </div>
      </div>
    </div>
  </div>


</div>



<script src="./lib/app-config.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script>
  window.onload = function() {
    window.document.dispatchEvent(new Event('authStateChanged'));
  }
console.log(buildUrl());
</script>

</html>