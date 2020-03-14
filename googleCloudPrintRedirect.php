<?php
/*
PHP implementation of Google Cloud Print
Author, Yasir Siddiqui

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

error_reporting(E_ERROR);
$ko_path = './';
require_once "{$ko_path}inc/ko.inc";
require_once "{$ko_path}inc/googleCloudPrint/Config.php";
require_once "{$ko_path}inc/googleCloudPrint/koGoogleCloudPrint.php";

if (isset($_GET['op'])) {
	
	if ($_GET['op']=="getauth") {
		header("Location: ".$urlConfig['authorization_url']."?".http_build_query($redirectConfig)); exit;
	}
	else if ($_GET['op']=="offline") {
		header("Location: ".$urlConfig['authorization_url']."?".http_build_query(array_merge($redirectConfig,$offlineAccessConfig))); exit;
	}
}

session_start();

// Google redirected back with code in query string.
if(isset($_GET['code']) && !empty($_GET['code'])) {
    $code = $_GET['code'];

    $gcp = koGoogleCloudPrint::Instance();
    $gcp->processCode($code);

    $gcp->redirectToUserRequest();
}
