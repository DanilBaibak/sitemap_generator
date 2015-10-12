'use strict';

var siteMapApp = angular.module('sitemap', []);

siteMapApp.controller('MainController', ['$scope', '$rootScope', function ($scope, $rootScope) {
    //create socket
    var socket = new socketService($rootScope);

    //start application
    init();

    /**
     * Initialization of the application
     */
    function init() {
        $scope.isDisable = false;
        $scope.preloader = false;
        $scope.downloadLink = false;
        $scope.errorMessage = false;
        $scope.showStatistic = false;

        $scope.items = [
            {name: 'None', value: 'none'},
            {name: 'Hourly', value: 'hourly'},
            {name: 'Daily', value: 'daily'},
            {name: 'Monthly', value: 'monthly'},
            {name: 'Yearly', value: 'yearly'},
        ];
        //form data
        $scope.settings = {
            siteUrl: "",
            depthScan: "false",
            modifyData: "false",
            priority: "false",
            frequencyUpdate: $scope.items[0]
        };
    }

    /**
     * Send settings for start create XML
     *
     * @returns {boolean}
     */
    $scope.generateSiteMap = function () {
        refreshStatistic();

        $scope.isDisable = true;
        $scope.preloader = true;
        $scope.showStatistic = true;

        //prepare data for send to server
        var data = angular.copy($scope.settings);
        data.frequencyUpdate = $scope.settings.frequencyUpdate.value;
        //send to server
        socket.emit('create sitemap', data);

        return false;
    };

    /**
     * Generation of the site map was finished
     */
    socket.on('generation finished', function (response) {
        console.log(response)
        response = JSON.parse(response);

        //hide preloader
        $scope.isDisable = false;
        $scope.preloader = false;

        //check status of the sitemap generation
        if (response.status) {
            $scope.downloadLink = true;
        } else {
            $scope.errorMessage = true;
            $scope.errorMessageText = response.message;
        }
    });

    /**
     * Show number of the unique links
     */
    socket.on('links number', function (response) {
        $scope.linksNumber = response;
    });

    /**
     * Show memory usage
     */
    socket.on('memory usage', function (response) {
        $scope.memory = response;
    });

    /**
     * Show depth of the searching
     */
    socket.on('links depth', function (response) {
        $scope.linksDepth = response;
    });

    /**
     * Show last link that was found
     */
    socket.on('current link', function (response) {
        $scope.currentLink = response;
    });

    /**
     * Refresh all elements
     */
    function refreshStatistic() {
        $scope.linksNumber = 0;
        $scope.memory = 0;
        $scope.linksDepth = 0;
        $scope.currentLink = '';
        $scope.memory = 0;

        //hide messages
        $scope.downloadLink = false;
        $scope.errorMessage = false;
    }
}]);
