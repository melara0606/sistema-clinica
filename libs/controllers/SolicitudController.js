import { filter, findLastIndex, includes } from 'lodash'

module.exports.paciente = function($scope, $rootScope, paciente, type, toastr, Restangular, $stateParams, $state, $uibModal) {
  $scope.type = type;
  $scope.server = false;
  $scope.promocion = null;
  $scope.paciente = paciente;
  $scope.params = $stateParams;

  // variables y funciones para los examenes
  $scope.examen = null;
  $scope.sumExamTotal = 0.00;
  $scope.catalogo = null;
  $scope.listExamen = [];
  $scope.examenesObject = {};
  $scope.listExamenIds = [];

  function onCheckoutMasterExamenes(arrayOfList){
    arrayOfList.examenes.map(i => {
      i.show = !includes($scope.listExamenIds, i.id)
      return i
    })
    return arrayOfList
  }

  $scope.onChangeExamenes = function() {
    $scope.catalogo = JSON.parse(this.catalogo);
    Restangular.all('categoria_examenes').customGET($scope.catalogo.id + '/examenes').then(function(response) {
      $scope.examenesObject = onCheckoutMasterExamenes(response.data);
    });
  }

  $scope.onDeleteItem = ($index) => {
    let {examen} = $scope.listExamen.splice($index, 1)[0];
    $scope.sumExamTotal -= examen.precio
    
    $scope.listExamenIds.splice($index, 1)
    $scope.examenesObject = onCheckoutMasterExamenes($scope.examenesObject)
  }

  $scope.onValueData = (element, $this) => {
    if(element){
      let isExist = findLastIndex($scope.listExamen, function(item){
        return item.examen.id == element.id
      });

      if(isExist === -1){
        $scope.listExamen.unshift({
          examen : element, catalogo: JSON.parse($this.catalogo)
        });
        element.show = false
        $scope.sumExamTotal += element.precio;
        $scope.listExamenIds.unshift(element.id)
      }else{
        $scope.listExamen.splice(isExist, 1);
        $scope.listExamenIds.splice(isExist, 1)
      }
    }
  }

  // variables y funciones para las promociones
  $scope.promocionObject = {};
  $scope.onChange = function() {
    Restangular.all('promocion').customGET(this.promocion).then(function(response) {
      $scope.promocionObject = response.data;
      $scope.server = true;
    });
  }

  /* Eventos para la creacion de las solicitudes */
  $scope.onClose = function () {
    $scope.promocion = this.promocion = null;
    $scope.promocionObject = null;
  }

  $scope.onCloseExamen = function() {
    $scope.listExamen = [];
  }

  $scope.createPromocion = (item) => {
    Restangular.all('solicitud').customPOST({
      type: '1',
      paciente: $scope.paciente.id,
      promocion : $scope.promocionObject
    }, 'add').then((response) => {
      toastr.success(response.message, 'Exito!');
      let modalSolicitud = $uibModal.open({
        templateUrl : 'public/templates/modals/solicitud.response.html',
        resolve: {
          response : response.data,
          promocion : $scope.promocionObject
        },
        controller: ($scope, response, $uibModalInstance, promocion, Restangular, toastr) => {
          $scope.monto = 0.0;
          $scope.isMonto = false;
          $scope.server = false;
          $scope.isMontoView = true;
          $scope.response = response;
          $scope.promocion = promocion;

          $scope.pagarTotal = function() {
            Restangular.all('solicitud').customPOST({
              id: response.id_solicitud
            }, 'pagoTotal').then((response) => {
              $scope.server = true;
              $scope.isMonto = false;
              toastr.success(response.message, 'Exito!');
            })
          };

          $scope.close = () => {
            $state.go('app.pacientes', {},{ reload: true });
            $uibModalInstance.close({ });
          }

          $scope.addMonto = (value = true) => { $scope.isMonto = value; }

          $scope.addMontoSolicitud = () => {
            let montoDouble = parseFloat($scope.monto)

            if(montoDouble <= 0 || montoDouble > $scope.promocion.precio){
              toastr.error('El monto no puede ser menor o igual a cero ni mayor a la cantida a pagar');
              return;
            };

            Restangular.all('solicitud').customPOST({
              monto : montoDouble,
              id_solicitud: response.id_solicitud
            }, 'monto').then((response) => {
              $scope.server = true;
              $scope.isMonto = false;
              toastr.success(response.message, 'Exito!');
            })
          }
        }
      })
    });
  }

  $scope.solicitudExamen = ($this) => {
    Restangular.all('solicitud').customPOST({
      type        : '2',
      paciente    : $scope.paciente.id,
      listExamen  : $scope.listExamen,
      total       : $scope.sumExamTotal
    }, 'add').then((response) => {
      toastr.success(response.message, 'Exito!');
      let modalSolicitud = $uibModal.open({
        templateUrl : 'public/templates/modals/solicitud.response.html',
        resolve: {
          response : response.data,
          total       : $scope.sumExamTotal,
          promocion : { precio: $scope.sumExamTotal }
        },
        controller: ($scope, response, $uibModalInstance, promocion, Restangular, toastr, total) => {
          $scope.monto = 0.0;
          $scope.isMonto = false;
          $scope.server = false;
          $scope.isMontoView = true;
          $scope.response = response;
          $scope.promocion = promocion;

          $scope.close = () => {
            $state.go('app.pacientes', {},{ reload: true });
            $uibModalInstance.close({ });
          }

          $scope.pagarTotal = function() {
            Restangular.all('solicitud').customPOST({
              id: response.id_solicitud
            }, 'pagoTotal').then((response) => {
              $scope.server = true;
              $scope.isMonto = false;
              toastr.success(response.message, 'Exito!');
            })
          };

          $scope.addMonto = (value = true) => { $scope.isMonto = value; }

          $scope.addMontoSolicitud = () => {
            let montoDouble = parseFloat($scope.monto)

            if(montoDouble <= 0 || montoDouble > $scope.promocion.precio){
              toastr.error('El monto no puede ser menor o igual a cero ni mayor a la cantida a pagar');
              return;
            };

            Restangular.all('solicitud').customPOST({
              monto : montoDouble,
              id_solicitud: response.id_solicitud
            }, 'monto').then((response) => {
              $scope.server = true;
              $scope.isMonto = false;
              toastr.success(response.message, 'Exito!');
            })
          }
        }
      })
    })
  }
}

module.exports.entidad = function($scope, $rootScope, paciente, Restangular,  entidad, toastr, $state, $uibModal){
  $scope.paciente = paciente;
  $scope.listExamen = [];
  $scope.listExamenIds = []
  $scope.sumExamTotal = 0.00;
  $scope.entidad = entidad.data;

  $scope.onValueEntidadData = (element, $this) => {
    if(element){
      let isExist = findLastIndex($scope.listExamen, function(item){
        return item.id == element.id
      });

      if(isExist === -1){
        element.show = false
        $scope.listExamen.unshift(element);
        $scope.sumExamTotal += element.precio;
        $scope.listExamenIds.unshift(element.id)
      }else{
        $scope.listExamenIds.splice(isExist, 1)

        let examen = $scope.listExamen.splice(isExist, 1)[0];
        $scope.sumExamTotal -= examen.precio
      }
    }    
  }

  $scope.create_solicitud = ($this) => {
    Restangular.all('solicitud').customPOST({
      type        : '3',
      descuento   : 1,
      entidad     : $scope.entidad,
      paciente    : $scope.paciente.id,
      listExamen  : $scope.listExamen,
      total       : $scope.sumExamTotal
    }, 'add').then((response) => {
      let modalSolicitud = $uibModal.open({
        templateUrl : 'public/templates/modals/solicitud.response.html',
        resolve: {
          response : response.data,
          total       : $scope.sumExamTotal,
          promocion : { precio: $scope.sumExamTotal }
        },
        controller: ($scope, response, $uibModalInstance, promocion, Restangular, toastr, total) => {
          $scope.promocion = promocion;
          $scope.response = response;
          $scope.isMontoView = false;
          
          $scope.close = () => {
            $state.go('app.pacientes', {},{ reload: true });
            $uibModalInstance.close({ });
          }
        }
      })
      toastr.success(response.message, 'Exito!');
    }, (error) => {
      toastr.error(error.data.message, 'Error!');
    })
  }

  $scope.onClose = () => {
    $scope.listExamen = [];
  }
}
