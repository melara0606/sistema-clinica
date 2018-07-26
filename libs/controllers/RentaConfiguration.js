import { range } from 'lodash'

module.exports = function($scope, rentaConfiguration, Restangular, toastr, $state, porcentajes, $uibModal, horarios) {
    $scope.data = rentaConfiguration.data
    $scope.editing = false;
    $scope.itemCurrent = null

    $scope.isISSS = false;
    $scope.isAFP  = false;

    $scope.porcentajes = porcentajes.data;
    $scope.showISSS = () => $scope.isISSS = true;    
    $scope.cancelISSS = () => $scope.isISSS = false;

    $scope.showAFP = () => $scope.isAFP = true;    
    $scope.cancelAFP = () => $scope.isAFP = false;
    
    if($scope.porcentajes.length > 0){
        $scope.porcentajeISSS = parseFloat($scope.porcentajes[3].value_campus)
        $scope.porcentajeAFP  = parseFloat($scope.porcentajes[4].value_campus)
    }
       
    $scope.onUpdateItem = (item) => {
        item.editing = true;
        $scope.editing = true;
        $scope.itemCurrent = angular.copy(item)
    }

    $scope.onCancelItem = (item) => {
        $scope.editing = false
        item.editing = false
    }

    $scope.onCreateItem = (item) => {        
        Restangular.all('renta').customPOST({
            item, itemCurrent: $scope.itemCurrent
        }, 'update').then((response) => {
            $state.reload();
            toastr.success(response.message, 'Exito!');
        }, function(error) {
            toastr.error(error.data, 'Error!')
        });
    }

    /* Modificaciones necesarias */
    $scope.onEditISSS = ($this) => {
        Restangular.all('modificaciones').customPOST({
            value: $this.porcentajeISSS
        },'isss').then((response) => {
            let { value_campus } = response.getData
            $scope.porcentajeISSS = parseFloat(value_campus);
            $scope.isISSS = false;
            toastr.success(response.message, "Exito!")
        }, function(error) {
            toastr.error(error.data, "Error")
        })
    }

    $scope.onEditAFP = ($this) => {
        Restangular.all('modificaciones').customPOST({
            value: $this.porcentajeAFP
        },'afp').then((response) => {
            toastr.success(response.message, "Exito!")
            let { value_campus } = response.getData
            $scope.porcentajeAFP = parseFloat(value_campus);
            $scope.isAFP = false;
        }, function(error){
            toastr.error(error.data, "Error")
        })
    }
    
    /*
    * Configuracion de horarios
    */
    $scope.horarios = horarios.data
    $scope.onActiveSelect = ($item) => {
      Restangular.all('horarios').customPOST({
        id: $item.id
      }, 'status').then( json => {
        $item.status = !$item.status
        toastr.success(json.message, 'Exito!')
      })
    }

    $scope.openFormCalendario = ($isEdit = false, $item = {}, $index = 0) => {      
      let horarInicial = { day: new Date('2018-01-01 06:00:00'), validate: true }
      let horarFinal   = { day: new Date('2018-01-01 06:00:00'), validate: true }

      if($isEdit){
        horarInicial.day = new Date(`2018-01-01 ${$item.hora_entrada}`)
        horarFinal.day = new Date(`2018-01-01 ${$item.hora_salidad}`)
      }

      let open = $uibModal.open({
        templateUrl: "public/templates/modals/agregar.horarios.citas.html",
        resolve: {
          'isEdit'        : $isEdit,
          'horarFinal'    : horarFinal,
          'horarInicial'  : horarInicial,
          'id': $item.id
        },
        controller: ($scope, $uibModalInstance, toastr, Restangular, isEdit, horarInicial, horarFinal, id) => {
          $scope.editing = isEdit
          $scope.horarInicial = horarInicial
          $scope.horarFinal   = horarFinal

          $scope.close = () => $uibModalInstance.close({ result: false })
          $scope.changedHoursInitial = () => verificarHorario($scope.horarInicial)
          $scope.changedHoursEnd     = () => verificarHorario($scope.horarFinal)

          $scope.onSubmitForm = () => {
            if(!($scope.horarInicial.validate && $scope.horarFinal.validate)){
             toastr.error('Has seleccionado horarios fuera de los rangos establecidos'); 
             return false;
            }else if($scope.horarFinal.day.getTime() <= $scope.horarInicial.day.getTime()) {
             toastr.error('La hora final no puede menor o igual a la hora inicial');               
             return false;
            }

            let route = $scope.editing ? 'update' : 'add'
            Restangular.all('horarios').customPOST({
              hours: {
                initial: $scope.horarInicial.day,
                end: $scope.horarFinal.day
              },
              id: id
            }, route).then(json => {
              toastr.success(json.message, 'Exito!')
              $uibModalInstance.close({ result: true, data: json.data })
            }, (error => {
              toastr.error(error.data.message, 'Error!')
            }))
          }

          function verificarHorario($scope) {
            $scope.validate = validateHoursCita($scope.day)
            if(!$scope.validate)
              toastr.error('Lo sentimos pero tu hora esta fuera de los rangos establecidos', 'Error');
          }

          function validateHoursCita (value) {
            return (value.getHours() > 5 && value.getHours() < 16);
          }
        }        
      })

      open.result.then( response => {
        if(response.result){
          if($isEdit){
            $scope.horarios[$index] = response.data
          }else{
            $scope.horarios.push(response.data)            
          }
        }
      })
    }
}