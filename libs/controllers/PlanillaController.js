import { filter } from 'lodash'
/*import $ from 'jquery'
global.jQuery = $*/

module.exports.planilla = function($scope, $rootScope, yearPlanilla, Restangular, toastr) {
  $scope.planillas = [];
  $scope.isSelectionYear = null;
  $scope.yearsPlanilla = yearPlanilla.year;

  $scope.permisos = filter($rootScope.recursos, { url: 'planillas' })[0];

  $scope.onChangeYear = () => {
    if($scope.isSelectionYear){
      Restangular.all('planillas').customGET($scope.isSelectionYear).then((res) => {
        $scope.planillas = res;
      });
    }
  }

  $scope.onCreatePlanilla = () => {
    Restangular.all('/planilla').customPOST().then((response) => {
      if(response.status == 201){
        let indexYear = response.data.fecha_creacion.substring(0,4);
        let yearIsShow = filter($scope.yearsPlanilla, { yearPlanilla : indexYear });

        if(!yearIsShow.length){
          $scope.yearsPlanilla.push({ yearPlanilla: indexYear })
        }
        $scope.isSelectionYear = indexYear;
        $scope.onChangeYear();
        toastr.success('Existo', 'La planilla fue generada con exito');
      }else if(response.status == 203){
        toastr.info('Fuera de rango', 'Las fechas para poder generar la planilla esta entre 21 al 31 del mes');
      }else if(response.status == 204){
        toastr.info('Ya registrada', 'La planilla ya esta registrada');
      }else if(response.status == 205){
        toastr.error('Debes tener empleados para poder registrar la planilla', 'Problemas');
      }
    })
  }
}


/* Controllers for permisos */
module.exports.permisos = ($scope, $rootScope, Restangular, $uibModal, toastr, object) => {
  $scope.listEmployeers = object.data;
  $scope.permisos = filter($rootScope.recursos, { url: 'permisos' })[0];

  $scope.deleteEmployeer = ($index) => {
    var listEmployeers = angular.copy($scope.listEmployeers)
    listEmployeers.splice($index, 1)
    $scope.listEmployeers = listEmployeers
  }

  $scope.open = () => {
    var modalInstance = $uibModal.open({
      ariaLabelledBy: 'modal-title',
      ariaDescribedBy: 'modal-body',
      templateUrl: 'public/templates/modals/permisos.html',
      controller : ($scope, $uibModalInstance) => {
        $scope.employeers = [];
        $scope.server = false;

        $scope.onSelectEmployer = (employeer) => {
          $uibModalInstance.close(employeer); };
          $scope.onClose = () => { $uibModalInstance.dismiss('cancel'); }

          $scope.onSearchEmployeers = function (event) {
            Restangular.all('permisos/query').post({
              query: $scope.searchEmployeer.trim()
            }).then((response) => {
              let { data, server } = response;
              $scope.searchEmployeer = ''
              $scope.employeers = data;
              $scope.server = server;
            });
          };
        }
      });

    modalInstance.result.then(function (employeer) {
      employeer = angular.extend(employeer, {
        popupOne : false,
        popupTwo : false,
        begin    : new Date(),
        end      : new Date(),
        type     : '1',
        editing  : true,
        isDescuento : false
      });
      $scope.listEmployeers.unshift(employeer);
    })
  };

  $scope.openComment = (employee) => {
    var modalInstance = $uibModal.open({
      templateUrl: 'public/templates/modals/permisos.comments.html',
      controller : ($scope, Restangular, $uibModalInstance, isComments, toastr) => {
        $scope.isComments = isComments;
        $scope.onClose = () => { $uibModalInstance.dismiss('cancel'); };
        $scope.onComments = function ()  {
          Restangular.all('permisos').customPOST({
            "comments" : $scope.comments, "permiso_id"  : isComments.permiso
          }, 'addComments').then(function(response) {
            if( response.response )
               $uibModalInstance.close(true);
            $uibModalInstance.dismiss('cancel');
          });
        };
      },
      resolve: {
        isComments: ['Restangular', function(Restangular) {
          return Restangular.all('permisos').customPOST({ "permiso_id" : employee.id }, "search");
        }]
      }
    });

    modalInstance.result.then(function(response){
      if(response.response)
        toastr.info("Hemos agregado la observacion con exito", "Exito");
    })
  };

  function disabled(data) {
    var date = data.date, mode = data.mode;
    return mode === 'day' && (date.getDay() === 6);
  }

  $scope.format = 'dd-MMMM-yyyy';
  $scope.altInputFormats = ['M!/d!/yyyy'];

  $scope.dateOptions = {
    dateDisabled: disabled,
    formatYear: 'yy',
    maxDate: new Date(2020, 5, 22),
    minDate: new Date(),
    startingDay: 1
  };

  $scope.popupOneEvent = (employee) => {
    employee.popupOne = true;
  };
  $scope.popupTwoEvent = (employee) => {
    employee.popupTwo = true;
  };

  $scope.PermisoSave = (employee, $index) => {
    if( (employee.type == '2' || employee.type == 3) && employee.end < employee.begin ){
      toastr.error('La fecha final no puede ser menor a la fecha inicial', 'Problemas')
      return;
    }
    employee.begin = new Date(employee.begin - 1);
    employee.end   = new Date(employee.end - 1);

    employee.days = (employee.end - employee.begin) / ( 60 * 60 * 24 * 1000 ) + 1;

    Restangular.all("permisos").post(employee)
      .then((response) => {
        employee.id = response.id;
        employee.editing = false;
        toastr.success(response.message, 'Exito!');
      }, (error) => {
        let { data } = error;
        if(!data.response){
          $scope.deleteEmployeer(0);
          let modal = $uibModal.open({
            templateUrl: 'public/templates/modals/detalle.fechas.pedidas.html',
            controller: function($scope, $uibModalInstance) {
              $scope.data = data.data;
              $scope.cerrar = () => { $uibModalInstance.dismiss('cancel'); }
            }
          })
        }
        toastr.error(data.message, 'Error!');
      });
  }

  /*
  * Modal para la fechas del reporte 
  */
 $scope.openModalDateBeginAndEnd = () => {
  let modal = $uibModal.open({
    templateUrl: 'public/templates/modals/rango-fechas.html',
    controller: ($scope, $uibModalInstance, toastr) => {
      $scope.cerrar = () => $uibModalInstance.close({ result: false })
      // Configuracion para el calendario
      $scope.dateend = new Date();
      $scope.datebegin = new Date();
      $scope.format = 'dd-MMMM-yyyy';
      $scope.popup1 = { opened: false };
      $scope.popup2 = { opened: false };
      $scope.altInputFormats = ['M!/d!/yyyy'];

      $scope.open1 = () => { $scope.popup1.opened = true;  };
      $scope.open2 = () => { $scope.popup2.opened = true;  };

      /* Evento para el submit */
      $scope.onValidateRango = () => {
        if($scope.datebegin >= $scope.dateend ){
          toastr.error('Lo sentimos pero la fecha de inicio no debe ser mayor o igual a la fecha final', 'Error!')
        }else{
          $uibModalInstance.close({ 
            result: true, 
            url: 'reportes/comprobante/permisos',
            begin: $scope.datebegin,
            end  : $scope.dateend
          })
        }
      }
    }
  })

  modal.result.then( response => {
    if(response.result){
      let { begin, end, url} = response
      let endDate   = `${ end.getUTCFullYear() }-${ end.getUTCMonth() + 1 }-${ end.getUTCDate() }`
      let beginDate = `${ begin.getUTCFullYear() }-${ begin.getUTCMonth() + 1 }-${ begin.getUTCDate() }`
      window.open(`${ url }?b=${beginDate}&f=${endDate}`, '_blank')
    }
  })
 }
}