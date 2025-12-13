require('./bootstrap');// el punto con eslash "./" el archivo que quiero traer viene de la carpeta resources 


import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// var Turbolinks = require("turbolinks")
// Turbolinks.start()


//windows.Swal, windows me sirve para definir una constante
window.Swal = require('sweetalert2');//aqui estoy importando el archivo sweetalert2 de node modules

//Parsley
// window.parsley = require('parsleyjs');


window.axios = require('axios');
