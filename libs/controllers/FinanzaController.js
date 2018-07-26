module.exports = function ($scope, toastr, entidades) {
  $scope.isShowEntidad = false;
  $scope.entidades = entidades;

  $scope.menus = [{
    isEntidad: true,
    url: 'reporte/solicitudes/entidades',
    name: 'Movimientos Financieros por Instituciones Afiliadas'
  }, {
    isEntidad: false,
    url: 'reporte/solicitudes/sucursales',
    name: 'Ingresos del Laboratorio por Exámenes Realizados - Sucursal'
  }];

  $scope.dateend = new Date();
  $scope.datebegin = new Date();
  $scope.format = 'dd-MMMM-yyyy';
  $scope.popup1 = { opened: false };
  $scope.popup2 = { opened: false };
  $scope.altInputFormats = ['M!/d!/yyyy'];

  $scope.open1 = () => { $scope.popup1.opened = true;  };
  $scope.open2 = () => { $scope.popup2.opened = true;  };

  /* Evento para el submit */
  $scope.onChangeTipo = () => {
    $scope.isShowEntidad = !parseInt($scope.type_reporte);
  }

  $scope.onValidateRango = () => {
    if($scope.datebegin >= $scope.dateend ){
      toastr.error('Lo sentimos pero la fecha de inicio no debe ser mayor o igual a la fecha final', 'Error!')
    }else{
      var { dateend, datebegin } = $scope
      let data = $scope.menus[parseInt($scope.type_reporte)];

      let url = data.url
      let endDate   = `${ dateend.getUTCFullYear() }-${ dateend.getUTCMonth() + 1 }-${ dateend.getUTCDate() }`
      let beginDate = `${ datebegin.getUTCFullYear() }-${ datebegin.getUTCMonth() + 1 }-${ datebegin.getUTCDate() }`
      
      url = `${ url }?b=${beginDate}&f=${endDate}`
      if(data.isEntidad){
        if(!$scope.entidad){
          toastr.error('Debes seleccionar una entidad para poder visualizar el reporte', 'Error');
          return;
        }
        url +=`&entidad=${$scope.entidad}`
      }
      window.open(url, '_blank')
    }
  }
}