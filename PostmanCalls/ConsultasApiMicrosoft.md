# Descripción
A la hora de realizar consultas a la API de Microsoft, podremos agregarles ciertos elementos que nos ayuden a encontrar el recurso que estamos buscando.Estos elementos son conocidos como opciones de consulta de sistema de OData.

## Parámetros

| Nombre   	| Descripción                                                                                                                                                             	| Ejemplo                                  	|
|----------	|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------	|------------------------------------------	|
| $count   	| Recupera el número total de recursos coincidentes.                                                                                                                      	| /me/messages?$top=2&$count=true          	|
| $expand  	| Recupera los recursos relacionados.                                                                                                                                     	| /groups?$expand=members                  	|
| $filter  	| Filtra los resultados (filas).                                                                                                                                          	| /users?$filter=startswith(givenName,'J') 	|
| $format  	| Devuelve los resultados en el formato de medio especificado.                                                                                                            	| /users?$format=json                      	|
| $orderby 	| Ordena los resultados.                                                                                                                                                  	| /users?$orderby=displayName desc         	|
| $search  	| Devuelve los resultados en función de criterios de búsqueda.                                                                                                            	| /me/messages?$search=pizza               	|
| $select  	| Filtra las propiedades (columnas).                                                                                                                                      	| /users?$select=givenName,surname         	|
| $skip    	| Indexa en un conjunto de resultados. También se usa en algunas API para implementar la paginación y se puede usar junto a $top para paginar manualmente los resultados. 	| /me/messages?$skip=11                    	|
| $top     	| Establece el tamaño de la página de resultados.                                                                                                                         	| /users?$top=2                            	|