# DocumentacionGraph
En este documento se especifica los pasos que se han seguido para habilitar el control del calendario de outlook mediante la api de microsoft graph con doble verificación

## Descripción
En este documento se detallan los pasos a seguir para crear y consultar una api de Microsoft Azure. Para este ejemplo se creará una una aplicación que genera eventos directamente en el calendario de Outlook. Contaremos con una aplicación web donde en primera instancia el usuario deberá loguearse mediante el login de Microsoft ( con seguridad en dos pasos) y luego podrá hacer un CRUD entero de los eventos.

## Requisitos
Para llevar acabo este ejemplo se deberá contar con una cuenta de microsoft Azure 

## Registrando la aplicación en Azure
### 1.- Entramos a plataforma de azure 
https://azure.microsoft.com/es-es/

<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/0.png" width="500">

### 2.- Azure Directory
<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/1.png" width="500">

### 3.- Registro de aplicaciones  y le damos a crear nueva aplicación
<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/2.png" width="500">
### 4.- Rellenamos los campos
<img src="https://github.com/alejandroasc96/DocumentacionGraph/blob/master/MicrosoftGraph/3.JPG" width="500">
***Explicación***
*Nombre* Es el nombre con el que vamos a identificar a nuestra aplicación dentro de Azure.
*Tipo de cuenta compatible*  declaramos que cuentas de microsoft pueden hacer uso de esta api; casos:
- Solo usuarios dentro de nuestra organización (en este caso Fundación Universitaria)
- Todos aquellos usuarios con una cuenta profesional o educativa de Microsoft 
- Todos aquellos usuarios con cuenta de microsoft.

