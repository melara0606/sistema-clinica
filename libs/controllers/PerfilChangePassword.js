import { filter } from 'lodash'

module.exports = function($scope, perfil, Restangular, toastr) {
  $scope.perfil = perfil.data
  $scope.perfil.editing = false
  $scope.perfil.changepassword = false
  $scope.isPaciente = perfil.isPaciente

  $scope.onSubmitForm = ($valid) => {
    if($valid){
      if($scope.perfil.changepassword){
        if($scope.isPaciente){
          Restangular.all('changepassword').customPOST({
            data: $scope.perfil
          }, 'personal').then((json) => {
            toastr.success('Hemos actualizado con exito tu contraseña', 'Exito!')
            $scope.perfil.changepassword = false
          }, (error) => toastr.error(error.data.message, 'Error!'))
        }else{
          Restangular.all('changepassword').customPOST({
            data: $scope.perfil
          }, 'empleado').then((json) => {
            toastr.success('Hemos actualizado con exito tu contraseña', 'Exito!')
            $scope.perfil.changepassword = false
          }, (error) => toastr.error(error.data.message, 'Error!'))
        }
      }else{
        if($scope.isPaciente){
          Restangular.all('changeData').customPOST({
            data: $scope.perfil
          }, 'personal').then((json) => {
            toastr.success('Hemos actualizado con exito tu informacion', 'Exito!')
            $scope.perfil.editing = false          
          }, (error) => toastr.error(error.data.message, 'Error!'))
        }else{
          Restangular.all('changeData').customPOST({
            data: $scope.perfil
          }, 'empleado').then((json) => {
            toastr.success('Hemos actualizado con exito tu informacion', 'Exito!')
            $scope.perfil.editing = false          
          }, (error) => toastr.error(error.data.message, 'Error!'))
        }        
      }
    }
  }

  $scope.editItem = function(){
    $scope.perfil.editing = true
  }

  $scope.editPassword = () => {
    $scope.perfil.changepassword = true
  }

  $scope.deleteItem = () => {
    $scope.perfil.editing = false
    $scope.perfil.changepassword = false
  }
}
