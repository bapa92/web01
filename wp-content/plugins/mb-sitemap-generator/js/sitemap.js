'use strict';

var sitemapGeneratorApp = angular.module('sitemapGeneratorApp', []);
var sitemapGeneratorBlob;

sitemapGeneratorApp.config(['$compileProvider',
	function($compileProvider) {
		$compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|blob):/);
	}
]);

sitemapGeneratorApp.controller('SitemapController', ['$scope', '$http', '$timeout',
	function ($scope, $http, $timeout) {

		$scope.downloadDisabled = true;
		$scope.generateDisabled = false;

		$scope.message = "The generation of the sitemap was not started yet.";
		$scope.messageClass = "alert-info";

		$scope.generateClass = sitemapGeneratorVars.btnPrimaryClass;
		$scope.downloadClass = sitemapGeneratorVars.btnDefaultClass;

		$scope.generate = function(base64URL, identifier) {

			if ($scope.sitemapForm.$valid) {

				$scope.downloadDisabled = true;
				$scope.generateDisabled = true;
				$scope.pageCount = 0;
				$scope.stats = null;

				$scope.message = "The sitemap is being generated. Please wait a moment.";
				$scope.messageClass = "alert-warning";

				$scope.generateClass = sitemapGeneratorVars.btnPrimaryClass;
				$scope.downloadClass = sitemapGeneratorVars.btnDefaultClass;
				
				var poller = function() {
					var proxyURL = sitemapGeneratorVars.proxyURL;
					if (sitemapGeneratorVars.systemName == 'Joomla') {
						proxyURL += '&base64url=' + base64URL + '&identifier=' + identifier;
					}

					$http({
						method: 'GET',
						url: proxyURL,
						headers: {
							'Cache-Control': 'no-store',
						},
					}).
					success(function(data, status, headers, config) {
						if (headers('Content-Type').startsWith('application/xml')) {

							sitemapGeneratorBlob = new Blob([ data ], { type : 'application/xml' });
							$scope.href = (window.URL || window.webkitURL).createObjectURL(sitemapGeneratorBlob);

							$scope.downloadDisabled = false;
							$scope.generateDisabled = false;

							if (headers('X-Limit-Reached') == 1) {

								$scope.message = "The Sitemap Generator reached the URL limit and the generated sitemap probably isn't complete. You may buy a token for the <a href=\"" + sitemapGeneratorVars.professionalURL + "\">Sitemap Generator Professional</a> to crawl up to 50'000 URLs and create a complete sitemap. Additionally to a higher URL limit, the professional version also adds images and videos to your sitemap.";

								$scope.messageClass = "alert-danger";
							}
							else {
								$scope.message = "The generation of the sitemap was successful. The sitemap was saved as sitemap.xml in the " + sitemapGeneratorVars.systemName + " root folder. Please see the stats below.";
								$scope.messageClass = "alert-success";
							}

							if (headers('X-Stats') != null) {
								$scope.stats = JSON.parse(headers('X-Stats'));
							}

							$scope.generateClass = sitemapGeneratorVars.btnDefaultClass;
							$scope.downloadClass = sitemapGeneratorVars.btnPrimaryClass;
						}
						else {
							$scope.pageCount = data.page_count;
							$timeout(poller, 1000);
						}
					}).
					error(function(data, status, headers, config) {

						$scope.generateDisabled = false;

						if (status == 401) { // unauthorized
							$scope.message = "The validation of your token failed. The token is invalid or has expired. Please try it again or contact me if the token should be valid.";
						} else if (status == 500) {
							if (headers('X-Write-Error') == 1) {
								$scope.message = "Could not write sitemap to file. The reason for that could be a permission issue or that not enough space is available.";
							} else if (data != '' && headers('Content-Type').startsWith('application/json')) {
								$scope.message = "The generation of your sitemap failed with the error:<br/><strong>" + JSON.parse(data) + "</strong>.";
							} else {
								$scope.message = "The generation of your sitemap failed. Please try it again.";
							}
						} else if (status == 503) {
							$scope.message = "The backend server is temporarily unavailable. Please try it again later.";
						} else if (status == 504 && headers('X-CURL-Error') == 1) {
							var message = JSON.parse(data);
							if (message == '') {
								$scope.message = "A cURL error occurred. Please contact the developer of the extensions.";
							} else {
								$scope.message = "A cURL error occurred with the error message:<br/><strong>" + message + "</strong>.";
							}
						} else {
							$scope.message = "The generation of your sitemap failed. Please try it again or contact the developer of the extensions.";
						}
						$scope.messageClass = "alert-danger";
					});
				}
				poller();
			}
		}

		$scope.download = function() {
			if (window.navigator.msSaveOrOpenBlob && sitemapGeneratorBlob) { 
				window.navigator.msSaveOrOpenBlob(sitemapGeneratorBlob, 'sitemap.xml');
			}
		}
	}
]);

sitemapGeneratorApp.filter("sanitize", ['$sce', function($sce) {
	return function(htmlCode){
		return $sce.trustAsHtml(htmlCode);
	}
}]);
