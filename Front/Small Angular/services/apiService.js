/* jshint ignore:start */
angular.module('apiService', [])

    .factory('Api', function($http) {
        'use strict';
        return {
            save : function(registrationData) {
                return $http({
                    method: 'POST',
                    url: this.gethost()+'/rest/json/registration',
                    headers: { 'Content-Type' : 'application/x-www-form-urlencoded' },
                    data: $.param(registrationData)
                  });
            },
            twits : function() {
                return $http.get(this.gethost()+'/rest/json/twits');
            },
            images: function() {
                return $http.get(this.gethost()+'/rest/json/images');
            },
            gethost: function() {
                if(window.location.host == '127.0.0.1:9000'){
                    return 'http://sample.local';
                }
                return '';
            }
          };
      });
/* jshint ignore:end */
