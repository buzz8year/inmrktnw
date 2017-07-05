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


			// $img_front = '';

			// $json['img_front'] = $img_front ? $img_front : '';


			// $img_back = '';

			// $json['img_back'] = $img_back ? $img_back : '';


		}




		$callback = $_GET['callback'];


		$jsonResponse = '{"class":"' . $json['class'] . '", "img":"' . $json['img'] . '"}';




		// $this->response->addHeader('Access-Control-Allow-Origin: *');
        //
		// $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        //
		// $this->response->addHeader('Access-Control-Max-Age: 1000');
        //
		// $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        //
		// $this->response->addHeader('Content-Type: application/javascript');



		echo $callback . '(' . $jsonResponse . ')';

    }
