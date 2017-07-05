<?php

    if ( function_exists( $_GET['do'] ) ) {

        $_GET['do']();

    }

    function index() {

        echo 'Hello!';

    }

    function load_icons() {

        $json = array();

        $json['img'] = $_GET['img'] ? $_GET['img'] : '';
        $json['class'] = $_GET['class'] ? $_GET['class'] : '';

		if (isset($_GET['callback'])) {

    		$callback = $_GET['callback'];

    		$jsonResponse = '{"class":"' . $json['class'] . '", "img":"' . $json['img'] . '"}';

            header('Access-Control-Max-Age: 1000');
    		header('Access-Control-Allow-Origin: http://inmrkt.ml/');
    		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

            header('Content-Type: text/javascript; charset=utf8');

    		echo $callback . '(' . json_encode($json) . ')';

        } else {

            header('Content-Type: application/json; charset=utf8');

            echo json_encode($json);

        }

    }
