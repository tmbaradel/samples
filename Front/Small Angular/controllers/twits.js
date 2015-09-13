/* jshint ignore:start */
gruntApp.factory('itemService', function( Api) {
    return {
        getAll : function() {
          var items = [];
          Api.twits().success(function(data){
            if(data.result){
              for (var i = 0;i <data.result.length; i++){
                var tmp = [];
                tmp['Text'] = data.result[i]["Text"];
                var days = data.result[i]["Days"]+' days ago';
                if(data.result[i]["Days"] === 0){
                  days = 'today';
                }
                tmp['Days'] = days;
                items.push(tmp);
              }
            }
          });
          return items;
       }
    };
});

gruntApp.controller('ListController', function($scope, itemService) {
    var pagesShown = 1,
        pageSize = 1;


    $scope.items = itemService.getAll();
    $scope.itemsLimit = function() {
        return pageSize * pagesShown;
    };
    $scope.hasMoreItemsToShow = function() {
        return pagesShown < ($scope.items.length / pageSize);
    };
    $scope.showMoreItems = function() {
        pagesShown = pagesShown + 5;
    };

    $scope.showLessItems = function(){
        pagesShown = pagesShown - 5;
    }
});

gruntApp.directive('items', function(){
   return {
    restrict: 'A',
    scope: true,
    controller:function($scope){

    }
   }
});

/* jshint ignore:end */
