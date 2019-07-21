/* 
 * Copyright 2015 Kiala Ntona <kiala@ntoprog.org>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


var app = angular.module('kkb', ['infinite-scroll'], function($httpProvider) {
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    var param = function(obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for(name in obj) {
            value = obj[name];

            if(value instanceof Array) {
                for(i=0; i<value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value instanceof Object) {
                for(subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
});

app.filter('upper', function() {

    var text = function(input) {

        return input.toUpperCase();
    };

    return text;
});

app.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});

app.controller('helloController', function($scope, $http) {
    var obj = {};

    $http.get('http://192.168.202.135/jhabit/page/title/2').success(function(data, status, headers, config) {
        obj.title = data;

        $scope.page = obj;

    }).error(function(data, status, headers, config) {
        alert(status);
    });
}).controller('moduleController', function($scope, $http) {

    $scope.init = function(url) {
        loader.start();
        $http.get(url).success(function(data, status, headers, config) {
            var mo = [];

            [].forEach.call(data, function(item) {

                mo.push(item);
            });

            $scope.modules = mo;
            loader.stop();

        }).error(function(data, status, headers, config) {
            alert(status);
        });
    }
}).controller('scrollController', function($scope, $http) {

    var mo = [], page = 1, global_url, global_value = '';

    $scope.init = function(url, size, value) {
        loader.start();

        if (!size) size = page;

        global_url = url;

        var postData = {id: size};

        if (value) {
            postData.text = value;
        }

        $http.post(url, postData).success(function(data, status, headers, config) {

            [].forEach.call(data, function(item) {

                mo.push(item);
            });

            $scope.items = mo;
            page += data.length;
            loader.stop();

        }).error(function(data, status, headers, config) {
            alert(status);
        });
    };

    $scope.advance = function() {
        $scope.init(global_url, page);
    };
});