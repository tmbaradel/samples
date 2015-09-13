/* jshint ignore:start */
'use strict';

var gruntApp = angular.module('gruntApp');

gruntApp.service('anchorSmoothScroll', function() {

    this.scrollTo = function(eID) {
        var startY = currentYPosition();
        var stopY = elmYPosition(eID);
        var distance = stopY > startY ? stopY - startY : startY - stopY;

        if (distance < 100) {
            scrollTo(0, stopY); return;
        }

        var speed = Math.round(distance / 50);

        if (speed >= 20) {
          speed = 20;
        }

        var step = Math.round(distance / 25);
        var leapY = stopY > startY ? startY + step : startY - step;
        var timer = 0;

        if (stopY > startY) {
            for ( var i=startY; i<stopY; i+=step ) {
                setTimeout("window.scrollTo(0, "+leapY+")", timer * speed);
                leapY += step; if (leapY > stopY) leapY = stopY; timer++;
            } return;
        }

        for ( var i = startY; i > stopY; i -= step ) {
            setTimeout("window.scrollTo(0, "+leapY+")", timer * speed);
            leapY -= step; if (leapY < stopY) leapY = stopY; timer++;
        }

        function currentYPosition() {
            // Firefox, Chrome, Opera, Safari
            if (self.pageYOffset) return self.pageYOffset;
            // Internet Explorer 6 - standards mode
            if (document.documentElement && document.documentElement.scrollTop)
                return document.documentElement.scrollTop;
            // Internet Explorer 6, 7 and 8
            if (document.body.scrollTop) return document.body.scrollTop;
            return 0;
        }

        function elmYPosition(eID) {
            var elm = document.getElementById(eID);
            var y = elm.offsetTop;
            var node = elm;
            while (node.offsetParent && node.offsetParent != document.body) {
                node = node.offsetParent;
                y += node.offsetTop;
            } return y;
        }

    };

});

gruntApp.controller('MainCtrl', function($scope, Api, anchorSmoothScroll) {
    $scope.Video = false;
    $scope.myInterval = 5000;
    $scope.slides = [];
    $scope.loading = false;
    //get carousel images and the load
    Api.images().success(function(data){
        if(data.result){
            for(var i = 0; i < data.result.length; i++) {
                    $scope.slides.push({
                        image: data.result[i]['Filename'],
                        logo: data.result[i]['Logo'],
                        id: i
                        //h1: 'The credit risk landscape',
                        //small: 'Like you\'ve never seen it before',
                        //video: data.result[i]['Video']
                    });
            }
            $scope.loading = true;
        }
    });

    $scope.openToggle = function(id){
        if (id == 0) {
            if (!$scope.Video) {
                $scope.Video = true;
            } else {
                $scope.Video = false;
            }
        }
        else if (id == 1 || id == 2) {
            anchorSmoothScroll.scrollTo('register');
            $scope.Register = true;
        }
        else{
            $scope.Video = false;
        }
    }

}).directive('disableAnimation', function($animate){
    return {
        restrict: 'A',
        link: function($scope, $element, $attrs){
            $element.find('.glyphicon-chevron-left').attr('class','ico-carousel-left');
            $element.find('.glyphicon-chevron-right').attr('class','ico-carousel-right');
            $attrs.$observe('disableAnimation', function(value){
                $animate.enabled(!value, $element);
            });

        }
    }
}).directive('hideImage',function($animate){
    return function($scope, $element, $attrs) {
        $($element).css('opacity','0');
        var bottom_of_object = $($element).parent().position().top + 300 + $($element).outerHeight();
        var bottom_of_window = $(window).scrollTop() + $(window).height();

        if (bottom_of_window > bottom_of_object) {
            $($element).css('-webkit-transition',':.5s linear all');
            $($element).css('transition','.5s linear all');
            $($element).css('opacity','1');
        }

        $(window).scroll(function(i) {
            var bottom_of_object = $($element).parent().position().top - 300  + $($element).outerHeight();
            var bottom_of_window = $(window).scrollTop() + $(window).height();
            if(bottom_of_window > bottom_of_object){
                $($element).css('-webkit-transition',':.5s linear all');
                $($element).css('transition','.5s linear all');
                $($element).css('opacity','1');
            }
        });
    }
});

gruntApp.directive('scrollOnClick', function($location, anchorSmoothScroll) {
  return {
    restrict: 'A',
    link: function(scope, $elm, attrs) {
      var idToScroll = attrs.href;
        $elm.on('click', function() {
        var $target;
        var scr  = idToScroll.substring(1);
        if (scr) {
          $target = $(scr);
        } else {
          $target = $elm;
        }
        $location.hash(scr);
        anchorSmoothScroll.scrollTo(scr);
      });
    }
  }
});

$(document).ready(function(){

    $('input, textarea').placeholder();
    // -----------------------------------------
    // Bootstrap select
    // -----------------------------------------

    $('.selectpicker').selectpicker();


    // -----------------------------------------
    // Clickable element
    // -----------------------------------------

    (function($) {
        $.fn.clickable = function() {
            this.contents().unwrap().parent().css({'cursor':'pointer'}).on(
                'click',
                function() {
                    var href = $(this).find('a').attr('href');
                    window.location = href;
                }
            );
            return this;
        }
        $('.clickable').clickable();
    })(jQuery);


    // -----------------------------------------
    // Placeholder
    // -----------------------------------------



});

/* jshint ignore:end */
