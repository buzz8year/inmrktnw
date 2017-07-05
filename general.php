<?php

    if ( function_exists( $_GET['do'] ) ) {

        $_GET['do']();

    }

    function index() {

        echo 'Hello!';

    }

    function load_icons() {

        $json = array();

		if (isset($_GET['img'])) {

            $json['img'] = $_GET['img'] ? $_GET['img'] : '';
			$json['class'] = $_GET['class'] ? $_GET['class'] : '';

		}

		$callback = $_GET['callback'];

		$jsonResponse = '{"class":"' . $json['class'] . '", "img":"' . $json['img'] . '"}';

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		header('Content-Type: application/javascript');


		echo $callback . '(' . $jsonResponse . ')';

    }
