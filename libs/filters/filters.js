import moment from 'moment'
//require('moment/src/locale/es')(moment)

module.exports.fromGenero = () => {
  return function (genero) {
    return (genero == '1') ? 'Masculino' : 'Femenino'
  }
}

module.exports.fromPresentacion = () => {
  return function (presentacion) {
    return (presentacion == 1) ? 'Caja' : 'Frasco'
  }
}

module.exports.fromContracto = () => {
  return function (typeContracto) {
    return (typeContracto == '1') ? 'Contracto' : 'Fijo'
  }
}

module.exports.fromRecurso = () => {
  return function (status) {
    return (status == '1') ? 'Activado' : 'Desactivado'
  }
}

module.exports.fromEstado = () => {
  return function (typeEstado) {
    return (typeEstado == '1') ? 'Activo' : 'Desactivado'
  }
}

module.exports.fromMesString = () => {
  return function (mesString) {
    var mesString = mesString.substr(0, 2);
    var month = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    return month[parseInt(mesString - 1)];
  }
}

module.exports.fromMoment = () => {
  return function (currentDate) {
    moment.locale('es');
    return moment(currentDate).fromNow();
  }
}

module.exports.fromPermiso = () => {
  return function (permiso) {
    return (permiso == '1') ? 'Permiso' : (permiso == '2') ? 'Incapacidad' : 'Casos Especiales';
  }
}

module.exports.fromDescuento = () => {
  return function (isDescuento) {
    return (isDescuento) ? 'Con Descuento' : 'Sin Descuento';
  }
}

/* Solicitud */
module.exports.fromSolicitudType = () => {
  return function (type) {
    if(type == 1)
      return "Promocion";
    else if(type == 2)
      return "Particular";
    return "Entidad";
  }
}

module.exports.fromSolicitudEstado = () => {
  return function (estado) {
    if( estado == 1 )
      return "Procesada";
    else if ( estado == 2 )
      return "Pendiente";
    else if ( estado == 3 )
      return "Anulada";
    else if(estado == 0 )
      return "Finalizada";
    else if(estado == 4)
      return "Pagada";
  }
}

module.exports.fromPorcentaje = function() {
  return function (porcentaje){
    return ( porcentaje * 100 ) + "%";
  }
}

function generador(value, rangoValor) {
  if(value) {
    let array = rangoValor.split('-');
    let numOne = parseInt(array[0].trim())
    let numTwo = parseInt(array[1].trim())

    if(value < numOne)
      return "Bajo";
    else if(value > numTwo)
      return "Alto"
    else
      return "Normal"
  }
}

module.exports.fromRango = function() {
  return generador
}

module.exports.generador = generador;