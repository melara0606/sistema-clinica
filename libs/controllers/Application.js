import filter from 'lodash/filter';

module.exports = function($scope, $rootScope, Restangular, JQ_CONFIG, $window, $uibModal, $state) {
  $scope.userRegister = [];

  $rootScope.onShowUser = () => {
    $rootScope.showUser = !$rootScope.showUser;
  }

  $scope.modalSucursal = function() {
    let modalSucursal = $uibModal.open({
      templateUrl : 'public/templates/modals/cambiar.sucursal.administrador.html',
      resolve: {
        sucursal: ['Restangular', function(Restangular) {
          return Restangular.all('sucursalChange').customGET();
        }]
      },
      controller: function($scope, sucursal, $uibModalInstance, toastr) {
        $scope.sucursales = sucursal.response;
        $scope.sucursal = sucursal.session;
        $scope.cerrar = () => { $uibModalInstance.close({ }) }

        $scope.onChangeSucursal= ($valid) => {
          if($valid){
            Restangular.all('sucursalChange').customPOST({
              id: $scope.sucursal
            }, '').then((response) => {
              $uibModalInstance.close({ })
              toastr.success(response.response.message, 'Exito!');
              if(response.response.ok){
                toastr.info('Tendremos que actualizar el sistema para poder realizar los cambios', 'Exito!');
                setTimeout(function () {
                  window.location.reload();
                }, 2000);
              }
            });
          }
        }
      }
    })
  }

  $scope.app = {
    name: 'Angulr',
    version: '2.0.1',
    // for chart colors
    color: {
      primary: '#7266ba',
      info:    '#23b7e5',
      success: '#27c24c',
      warning: '#fad733',
      danger:  '#f05050',
      light:   '#e8eff0',
      dark:    '#3a3f51',
      black:   '#1c2b36'
    },
    settings: {
      themeID: 1,
      navbarHeaderColor: 'bg-black',
      navbarCollapseColor: 'bg-white-only',
      asideColor: 'bg-black',
      headerFixed: true,
      asideFixed: false,
      asideFolded: true,
      asideDock: false,
      container: false
    }
  }

  $rootScope.goBack = () => {
    $window.history.back();
  }

  $rootScope.table = null;
  $scope.productsVecimiento = [];

  Restangular.all('user').customGET().then((response) => {
    $scope.userRegister = response;
    $scope.is_paciente = response.is_paciente

    if(response.is_paciente){
      $state.go('app.perfil', {}, { reload: true });
    }else{
      $rootScope.recursos = response.recursos;

      $scope.recursos     = filter(response.recursos, { view: 1 });
      $scope.isShowViewZero = filter(response.recursos, { view: 0 });
      $scope.isShowSucursal = filter($scope.isShowViewZero, { 'url': 'changesucursal' });
      $scope.isShowNotification = filter($scope.isShowViewZero, { 'url': 'fechavencimiento' });

      Restangular.all('fechavecimiento').customGET().then(json => {
        $scope.productsVecimiento = json.productsVecimiento
      })
    }
  });

  $scope.modalVecimiento = () =>{
    let modal = $uibModal.open({
      size: 'lg',
      resolve:{
        productsVecimiento: [function() {
          return $scope.productsVecimiento;
        }]
      },
      templateUrl: 'public/templates/modals/products-vencimiento.html',
      controller: ($scope, productsVecimiento, $uibModalInstance) => {
        $scope.productsVecimiento = productsVecimiento
        $scope.getDate = (date) => new Date(date)
        $scope.cerrar = () => $uibModalInstance.close()
      }
    })
  }
}
