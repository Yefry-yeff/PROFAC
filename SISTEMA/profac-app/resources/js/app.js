require('./bootstrap');// el punto con eslash "./" el archivo que quiero traer viene de la carpeta resources 

// Alpine.js ya no se importa/inicializa manualmente: Livewire v3 lo incluye y lo arranca
// internamente (a través de @livewireScripts). Mantener esta inicialización manual causaba
// un conflicto de doble arranque de Alpine.

// var Turbolinks = require("turbolinks")
// Turbolinks.start()


//windows.Swal, windows me sirve para definir una constante
window.Swal = require('sweetalert2');//aqui estoy importando el archivo sweetalert2 de node modules

//Parsley
// window.parsley = require('parsleyjs');


window.axios = require('axios');
