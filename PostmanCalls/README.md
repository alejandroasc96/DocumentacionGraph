# Descripción 
En este apartado se detallan todas las pruebas de las llamadas a la API de Microsoft Graph, para este proyecto.

# Preparación de Entorno

Para poder utilizar dichas pruebas vamos a tener que modificar ciertos valores.

**1.** Descargue e instale [Postman](https://www.postman.com/) (Aseguresé  de instalar  la última versión).


**2.** Haga clic en **Archivo | Importar ...**

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/PostmanViews/0_pulsarImport.png?raw=true)

**3.** Seleccione **Importar desde enlace** .

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/PostmanViews/1_importLink.PNG?raw=true)


**4.** Pegue las siguientes dos URL y haga clic en Importar después de cada una.

PONER LOS ENLACES!!!

Ahora debería ver la colección de **MicrosoftGraph enviroment** en el panel de Colecciones del lado izquierdo.

**5.** Haga clic en el menú desplegable **Sin entorno** en la esquina superior derecha.

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/PostmanViews/5_sinENtorno.png?raw=true)


**6.** Seleccione el **entorno Microsoft Graph** .

**7.** Haga clic en el icono del ojo a la derecha y luego haga clic en **Editar** .

**8.** Ingrese a las variables de entorno (no iniciales ) su Aplicación de identidad de Microsoft: **ClientID** , **ClientSecret** ad **TenantID** .

# Obtener Access_Token 

**1.** Nos digiremos la carpeta **MicrosoftGraph**, función **Get Access_token**, pestaña **Autorization** y pulsaremos en el botón **Get new Access Token**
![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/PostmanViews/0_access_token.png?raw=true)


**2.** Rellenaremos los campos con los valores de nuestra aplicacíon (si tiene dudas vea el siguiente [documento]()) y presionamos en  **Request Token**. 

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/PostmanViews/1_access_token.png?raw=true)


**3.** Le damos a  **Use Token**

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/PostmanViews/2_access_token.png?raw=true)


**4.** Para finalizar solo hace falta asignar el **access token** a nuestra variable **UserAccessToken**

![Image of Postman](https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/PostmanViews/3_access_token.png?raw=true)

>Nota: Es importante resaltar que para asignar el token debemos seleccionarlo tal y como se vé en la imagén.







