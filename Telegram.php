<?php

class Telegram
{
    public $token;
    public $mysqli;
    public $ocDomain;


    public function __construct($mysqli, $ocDomain)
    {
        // $this->token    = $token;
        $this->mysqli   = $mysqli;
        $this->ocDomain = $ocDomain;
        $this->mysqli->set_charset('utf8');
    }


    public function sendMessage($chat_id, $text, $reply)
    {
        /*$connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, "https://api.telegram.org/{$this->token}/sendMessage?chat_id=$chat_id&text=$text&reply_markup=$reply");
        $res = curl_exec($connection);
        curl_close($connection);*/

        // $res = file_get_contents("https://api.telegram.org/{$this->token}/sendMessage?chat_id=$chat_id&text=$text&parse_mode=HTML&reply_markup=$reply");
        // return json_decode($res);
        return json_encode($text) . json_encode($reply);
    }


    public function makeReply($array)
    {
        array_push(
            $array,
            [[
                "text"          => "Главное меню",
                "callback_data" => json_encode([
                    "function" => "main_menu",
                ]),
            ]]
        );
        return json_encode([
            "inline_keyboard" => $array,
        ]);
    }


    public function makeForceReply()
    {
        return json_encode([
            "force_reply" => true,
        ]);
    }


    private function start_message($chat_id)
    {
        $this->sendMessage(
            $chat_id,
            "Здравствуйте! Мы рады приветствовать вас в нашем магазине! Выберите действие:",
            $this->makeReply([[[
                "text"          => "Искать товары",
                "callback_data" => json_encode([
                    "function" => "search_items",
                ]),
            ]]])
        );

        //#withoffer$this->makeReply([[array("text" => "Искать товары", "callback_data" => json_encode(array("function" => "search_items")))], [array("text" => "Наши акции", "callback_data" => json_encode(array("function" => "show_offer")))]]));
    }


    public function messageHook($message)
    {
        switch ($message->text) {
            case "/start":{
                    $this->sendMessage($message->chat->id,
                        "Здравствуйте! Мы рады приветствовать вас в нашем магазине! Выберите действие:",
                        //#withoffer$this->makeReply([[array("text" => "Искать товары", "callback_data" => json_encode(array("function" => "search_items")))], [array("text" => "Наши акции", "callback_data" => json_encode(array("function" => "show_offer")))]]));
                        $this->makeReply([[[
                            "text"          => "Искать товары",
                            "callback_data" => json_encode([
                                "function" => "search_items",
                            ]),
                        ]]])
                    );

                    return;
                }
        }

        if ($message->reply_to_message->message_id) {

            $message_id = $message->reply_to_message->message_id;
            $user_id    = $message->chat->id;
            $r          = $this->mysqli->query("SELECT * FROM `telegram_messages` WHERE `id` = '{$message_id}' AND `user_id` = {$user_id}");
            $rs         = $r->fetch_object();

            if ($r->num_rows) {
                $session = $this->get_session($rs->session_id);

                if ($session != null) {
                    if ($rs->data == "search_all_names") {

                        $session->step         = 5;
                        $session->min          = 0;
                        $session->max          = 0;
                        $session->man_id       = 0;

                        $session->text_request = $this->mysqli->escape_string($message->text);

                        $this->update_session($session);
                        $this->step($session, $message);
                        return;
                    }
                    switch ($session->step) {
                        case 1:{
                                $session->step = 2;
                                $session->min  = intval($message->text);
                                $this->update_session($session);
                                $this->step($session, $message);
                                break;
                            }
                        case 2:{
                                $session->step = 3;
                                $session->max  = intval($message->text);
                                $this->update_session($session);
                                $this->step($session, $message);
                                break;
                            }
                        case 4:{
                                $session->step         = 5;
                                $session->text_request = $this->mysqli->escape_string($message->text);
                                $this->update_session($session);
                                $this->step($session, $message);
                                break;
                            }
                    }
                }
            }

        } else {
            $this->start_message($message->chat->id);
        }

        return true;
    }

    public function ReplyKeyboardRemove()
    {
        return json_encode([
            "remove_keyboard" => true
        ]);
    }

    public function callbackHook($callback)
    {
        $data = json_decode($callback->data);

        switch ($data->function) {

            case "main_menu":{
                    $this->start_message($callback->message->chat->id);
                    break;
                }

            case "search_items":{
                    $this->sendMessage(
                        $callback->message->chat->id,
                        "Выберите способ поиска товаров:",
                        $this->makeReply([
                            [[
                                "text"          => "По категориям",
                                "callback_data" => json_encode([
                                    "function" => "search_items_by_root_category"
                                ]),
                            ]],
                            [[
                                "text"          => "По названию",
                                "callback_data" => json_encode([
                                    "function" => "search_items_by_name",
                                ]),
                            ]],
                        ])
                    );

                    break;
                }

            case "search_items_by_name":{
                    $message = $this->sendMessage(
                        $callback->message->chat->id,
                        "Введите название товара:",
                        $this->makeForceReply()
                    );

                    $session = $this->new_session($callback->message->chat->id, "*");

                    $this->add_message(
                        $session->id,
                        $callback->message->chat->id,
                        $message->result->message_id,
                        "search_all_names"
                    );
                    break;
                }

            case "search_items_by_root_category":{
                    // $this->messageRootCategories($callback->message->chat->id);
                    $this->messageRootCategories(0);
                    break;
                }

            case "show_category":{
                    $mr = $this->mysqli->query("SELECT * FROM `oc_category` WHERE `parent_id` = {$data->category_id}");

                    if (!$mr->num_rows) {
                        $sa = $this->mysqli->query("SELECT * FROM `telegram_subscribers` WHERE `category_id` = '{$data->category_id}' AND `chat_id` = '{$callback->message->chat->id}' ");

                        if (!$sa->num_rows) {
                            $reply = $this->makeReply([
                                [[
                                    "text" => "Показать все", 
                                    "callback_data" => json_encode([
                                        "function" => "show_all_in_cat", 
                                        "category_id" => $data->category_id
                                    ])
                                ]],
                                [[
                                    "text" => "Уточнить фильтры", 
                                    "callback_data" => json_encode([
                                        "function" => "show_in_cat_by_filter", 
                                        "category_id" => $data->category_id
                                    ])
                                ]],
                                [[
                                    "text" => "Подписаться на уведомления", 
                                    "callback_data" => json_encode([
                                        "function" => "subscribe_category", 
                                        "category_id" => $data->category_id
                                    ])
                                ]],
                            ]);

                        } else {
                            $reply = $this->makeReply([
                                [[
                                    "text" => "Показать все", 
                                    "callback_data" => json_encode([
                                        "function" => "show_all_in_cat", 
                                        "category_id" => $data->category_id
                                    ])
                                ]],
                                [[
                                    "text" => "Уточнить фильтры", 
                                    "callback_data" => json_encode([
                                        "function" => "show_in_cat_by_filter", 
                                        "category_id" => $data->category_id
                                    ])
                                ]],
                                [[
                                    "text" => "Отписаться от уведомлений", 
                                    "callback_data" => json_encode([
                                        "function" => "unsubscribe_category", 
                                        "category_id" => $data->category_id
                                    ])
                                ]],
                            ]);
                        }

                        $this->sendMessage(
                            $callback->message->chat->id,
                            "Выберите:",
                            $reply
                        );

                        break;

                    } else {
                        $this->messageChildCategories($callback->message->chat->id, $data->category_id);
                    }

                    break;
                }

            case "add_man":{
                    $manufacturer_id = $data->manufacturer_id;
                    $session         = $this->get_session($data->session_id);
                    $session->man_id = $manufacturer_id;
                    $session->step++;
                    $this->update_session($session);
                    $this->step($session, $callback);
                    break;
                }

            case "show_in_cat_by_filter":{
                    $this->showCategoryStepByStep($callback);
                    break;
                }

            case "show_all_in_cat":{
                    $this->sendMessage(
                        $callback->message->chat->id,
                        "Выберите:",
                        $this->makeReply([
                            [[
                                "text" => "Топ 10", 
                                "callback_data" => json_encode([
                                    "function" => "show_all_in_cat_top10", 
                                    "category_id" => $data->category_id
                                ])
                            ]], 
                            [[
                                "text" => "Все товары", 
                                "callback_data" => json_encode([
                                    "function" => "show_all_in_cat_all", 
                                    "category_id" => $data->category_id
                                ])
                            ]]
                        ])
                    );

                    break;
                }

            case "show_all_in_cat_top10":{
                    break;
                }

            case "show_all_in_cat_all":{
                    $this->messageAllInCategory($callback->message->chat->id, $data->category_id);
                    break;
                }

            case "show_offer":{
                    $offers = [];

                    $req = $this->mysqli->query("SELECT * FROM `oc_product_special`");
                    //$this->sendMessage($callback->message->chat->id, $req->num_rows, null);

                    while ($row = $req->fetch_assoc()) {

                        $reqs = $this->mysqli->query("SELECT * FROM `oc_product` WHERE `product_id` = '" . $row["product_id"] . "'")->fetch_assoc();
                        $row  = array_merge($row, $reqs);
                        $find = false;

                        for ($i = 0; $i < count($offers); $i++) {
                            if ($offers[$i]["product_id"] == $row["product_id"]) {

                                if ($offers[$i]["priority"] < $row["priority"]) {
                                    $offers[$i]["price"]    = $row["price"];
                                    $offers[$i]["priority"] = $row["priority"];
                                }

                                $find = true;
                            }
                        }

                        if (!$find) {
                            array_push($offers, $row);
                        }

                    }

                    //$this->sendMessage($callback->message->chat->id, json_encode($offers), null);
                    $this->messageProducts(
                        $callback->message->chat->id, 
                        json_decode(json_encode($offers), false), 
                        "*"
                    );

                    break;
                }

            case "subscribe_product":{
                    $time        = mktime();
                    $pr_id       = $data->product_id;
                    $category_id = $data->category_id;
                    $req         = $this->mysqli->query("SELECT * FROM `telegram_subscribers` WHERE `chat_id` = '{$callback->message->chat->id}' AND `product_id` = '{$pr_id}'");

                    if (!$req->num_rows) {
                        $this->mysqli->query("INSERT INTO `telegram_subscribers` (`chat_id`, `product_id`, `category_id`, `time`) VALUES  ('{$callback->message->chat->id}', '$pr_id', '$category_id', '$time')");
                    }

                    $this->sendMessage(
                        $callback->message->chat->id, 
                        "Вы успешно оформили подписку на товар. Мы уведомим вас о всех обновлениях данного продукта", 
                        null
                    );
                    break;
                }

            case "unsubscribe_product":{
                    $pr_id = $data->product_id;
                    $this->mysqli->query("DELETE FROM `telegram_subscribers` WHERE `chat_id` = '{$callback->message->chat->id}' AND `product_id` = '{$pr_id}'");

                    $this->sendMessage(
                        $callback->message->chat->id, 
                        "Подписка отменена!", 
                        null
                    );

                    break;
                }

            case "subscribe_category":{
                    $time        = mktime();
                    $category_id = $data->category_id;
                    $req         = $this->mysqli->query("SELECT * FROM `telegram_subscribers` WHERE `chat_id` = '{$callback->message->chat->id}' AND `category_id` = '{$category_id}'");

                    if (!$req->num_rows) {
                        $this->mysqli->query("INSERT INTO `telegram_subscribers` (`chat_id`, `category_id`, `product_id`, `time`) VALUES  ('{$callback->message->chat->id}', '$category_id', '*', '$time')");
                    }

                    $this->sendMessage(
                        $callback->message->chat->id, 
                        "Вы успешно оформили подписку на категорию. Мы уведомим вас о всех обновлениях!", 
                        null
                    );

                    break;
                }

            case "unsubscribe_category":{
                    $time        = mktime();
                    $category_id = $data->category_id;
                    $req         = $this->mysqli->query("DELETE FROM `telegram_subscribers` WHERE `chat_id` = '{$callback->message->chat->id}' AND `category_id` = '{$category_id}'");

                    $this->sendMessage(
                        $callback->message->chat->id, 
                        "Вы отписались от уведомлений категории.", 
                        null
                    );

                    break;
                }

        }
        return true;
    }

    private function messageAllInCategory($chat_id, $category_id)
    {
        $products = [];

        $r = $this->mysqli->query("SELECT * FROM `oc_product_to_category` WHERE `category_id` = '$category_id'");
        //$this->sendMessage($chat_id, $r->num_rows, null);

        while ($row = $r->fetch_object()) {
            $rp = $this->mysqli->query("SELECT * FROM `oc_product_description` WHERE `product_id` = '{$row->product_id}'");
            array_push($products, $rp->fetch_object());
        }

        $this->messageProducts($chat_id, $products, $category_id);
    }


    private function sendPhoto($chat_id, $url, $caption, $reply)
    {
        $res = file_get_contents("https://api.telegram.org/{$this->token}/sendPhoto?chat_id=$chat_id&photo=$url&caption=$caption&reply_markup=$reply");
        //"https://api.telegram.org/{$this->token}/sendPhoto?chat_id=$chat_id&photo=$url&caption=$caption");
        return $res;
    }


    private function messageProducts($chat_id, $products, $category_id)
    {
        $prt = [];
        //$this->sendMessage($chat_id, json_encode($products), null);

        if (!count($products)) {
            $this->sendMessage(
                $chat_id, 
                "Товаров по заданным условиям не найдено. Начните поиск сначала.", 
                null
            );
            $this->start_message($chat_id);
            return;
        }

        $this->sendMessage(
            $chat_id, 
            "Найдено " . count($products) . " товаров:", 
            null
        );

        for ($i = 0; $i < count($products); $i++) {

            $pr_ob = $this->mysqli->query("SELECT * FROM `oc_product`  WHERE `product_id` = '{$products[$i]->product_id}'")->fetch_object();
            $image = "$this->ocDomain/image/" . $pr_ob->image;

            // $reply = $this->makeReply([[array("text" => "Топ 10", "callback_data" => json_encode(array("function"=>"show_all_in_cat_top10", "category_id" => $data->category_id)))], [array("text" => "Все товары", "callback_data" => json_encode(array("function"=>"show_all_in_cat_all", "category_id" => $data->category_id)))]]);
            //$reply = $this->makeReply([array("text" => "Посмотреть на сайте", "url" => urlencode())]);

            $url = urlencode("$this->ocDomain/index.php?route=product/product&product_id={$products[$i]->product_id}");

            $sa = $this->mysqli->query("SELECT * FROM `telegram_subscribers` WHERE `product_id` = '{$pr_ob->product_id}' AND `chat_id` = '{$chat_id}' ");

            if ($sa->num_rows) {
                $reply = $this->makeReply([
                    [[
                        "text" => "Посмотреть на сайте", 
                        "url" => "$url"
                    ]], 
                    [[
                        "text" => "Отписаться от уведомлений", 
                        "callback_data" => json_encode([
                            "function" => "unsubscribe_product", 
                            "product_id" => $products[$i]->product_id
                        ])
                    ]]
                ]);

            } else {
                $reply = $this->makeReply([
                    [[
                        "text" => "Посмотреть на сайте", 
                        "url" => "$url"
                    ]], 
                    [[
                        "text" => "Подписаться на уведомления о скидках на товар", 
                        "callback_data" => json_encode([
                            "function" => "subscribe_product", 
                            "product_id" => $products[$i]->product_id
                        ])
                    ]]
                ]);
            }

            $response = $this->sendPhoto(
                $chat_id, 
                $image, 
                urlencode($products[$i]->name . "\nЦена: " . intval($pr_ob->price) . " руб."), 
                $reply
            );
            //$this->sendPhoto($chat_id, $image, $products[$i]->name, null);
            //$this->sendMessage($chat_id, $response, null);
        }
    }

    private function messageChildCategories($chat_id, $category_id)
    {
        $categories = [];

        $mr = $this->mysqli->query("SELECT * FROM `oc_category` WHERE `parent_id` = '{$category_id}'");

        while ($mrow = $mr->fetch_object()) {
            $row = $this->mysqli->query("SELECT * FROM `oc_category_description` WHERE `category_id` = '{$mrow->category_id}'")->fetch_object();
            array_push(
                $categories, 
                [[
                    "text" => $row->name, 
                    "callback_data" => json_encode([
                        "function" => "show_category", 
                        "category_id" => $row->category_id
                    ])
                ]]
            );
        }

        //array_push($categories, [array("text" => "Подписаться на уведомления", "callback_data" => json_encode(array("function"=>"subscribe_category", "category_id" => $category_id)))]);
        $this->sendMessage(
            $chat_id, 
            "Выберите 1 из {$mr->num_rows} категорий:", 
            $this->makeReply($categories)
        );
    }


    private function messageRootCategories($chat_id)
    {
        $categories = [];

        $file = './bot_log.txt';
        try {
            $mr = $this->mysqli->query("SELECT * FROM `oc_category` WHERE `parent_id` = 0");
            file_put_contents(
                $file, 
                json_encode($this->mysqli) . PHP_EOL .
                json_encode($mr->fetch_object()) . PHP_EOL . PHP_EOL, 
                FILE_APPEND | LOCK_EX
            );
        } catch (Exception $e) {
            throw new Exception($e);
            // file_put_contents($file, json_encode($e) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        while ($mrow = $mr->fetch_object()) {
        // foreach ($mr as $mrow) {
            print_r($mrow);
            $row = $this->mysqli->query("SELECT * FROM `oc_category_description` WHERE `category_id` = " . $mrow->category_id)->fetch_object();
            array_push(
                $categories,
                [[
                    "text"          => $mrow->category_id,
                    // "text"          => $row['category_id'],
                    // "callback_data" => json_encode([
                    //     "function"    => "show_category",
                    //     "category_id" => $row->category_id,
                    // ]),
                ]]
            );
        }

        $this->sendMessage(
            $chat_id, 
            "Наш товар представлен в следующих {$mr->num_rows} категориях.", 
            $this->makeReply($categories)
        );
    }


    private function showCategoryStepByStep($callback)
    {
        $data = json_decode($callback->data);
        if (isset($data->session_id)) {
            $session = $this->get_session($data->session_id);
            $this->step($session, $callback);
        } else {
            $session = $this->new_session($callback->message->chat->id, $data->category_id);
            $this->step($session, $callback);
        }

    }


    private function step($session, $callback)
    {
        $chatid = $callback->message->chat->id;

        if ($chatid == null) {
            $chatid = $callback->chat->id;
        }

        if ($session != null) {

            if ($session->min == -1) {
                $this->messageRequestMin($chatid, $session->id);
                return;
            }
            if ($session->max == -1) {
                $this->messageRequestMax($chatid, $session->id);
                return;
            }
            if ($session->man_id == -1) {
                $this->messageRequestManufacturer($chatid, $session->id);
                return;
            }
            if ($session->text_request == -1) {
                $this->messageRequestText($chatid, $session->id);
                return;
            }

            $this->searhProductsBySession($chatid, $session);
        }
    }


    private function searhProductsBySession($chat_id, $session)
    {
        $products = [];

        if ($session->category_id == "*") {
            $r = $this->mysqli->query("SELECT DISTINCT `product_id` FROM `oc_product_to_category`");
        } else {
            $r = $this->mysqli->query("SELECT * FROM `oc_product_to_category` WHERE `category_id` = '{$session->category_id}'");
        }

        if (!$r->num_rows) {
            $this->sendMessage(
                $chat_id, 
                "Товаров по заданным условиям не найдено. Начните поиск сначала.", 
                null
            );
            $this->start_message($chat_id);

        } else {

            while ($row = $r->fetch_assoc()) {

                $row_share = $this->mysqli->query("SELECT * FROM `oc_product` WHERE `product_id` = '" . $row['product_id'] . "'")->fetch_assoc();
                $pr_item   = array_merge($row, $row_share);

                $row_share = $this->mysqli->query("SELECT * FROM `oc_product_description` WHERE `product_id` = '" . $row['product_id'] . "'")->fetch_assoc();
                $pr_item   = array_merge($pr_item, $row_share);

                array_push($products, $pr_item);
            }

            for ($i = 0; $i < count($products); $i++) {
                //$this->sendMessage($chat_id, json_encode($products[$i]), null);
                $products[$i]->price = intval($products[$i]->price);

                if ($session->max != -1 && $session->max != 0 && ($products[$i]["price"] > $session->max)) {
                    $products[$i] = null;
                    continue;
                }
                if ($session->min != -1 && $session->min != 0 && ($products[$i]["price"] < $session->min)) {
                    $products[$i] = null;
                    continue;
                }
                if ($session->text_request != "-1" and $session->text_request != "0") {
                    //$this->sendMessage($chat_id, $products[$i]["name"]." ".json_encode(stripos($products[$i]["name"], $session->text_request)), null);
                    //if(!stripos($products[$i]["name"], $session->text_request) && !stripos($products[$i]["description"], $session->text_request) && !stripos($products[$i]["meta_h1"], $session->text_request) && !stripos($products[$i]["meta_keyword"], $session->text_request)){
                    if (!stripos("  " . $products[$i]["name"], $session->text_request)) {
                        //$this->sendMessage($chat_id, "UNSET ".$products[$i]["name"]." ".$session->text_request, null);
                        //$this->sendMessage($chat_id, 'stripos("'.$products[$i]["name"].'", "'.$session->text_request.'")', null);
                        if (isset($products[$i])) {
                            $products[$i] = null;
                            continue;
                        } else {
                            continue;
                        }

                    }
                }
                if ($session->man_id != 0 && $session->man_id != -1) {
                    if ($session->man_id != $products[$i]["manufacturer_id"]) {
                        $products[$i] = null;
                        continue;
                    }
                }

            }

            foreach ($products as $ic => $value) {
                if ($value == null) {
                    unset($products[$ic]);
                }
            }
            $products = array_values($products);

            $this->messageProducts(
                $chat_id, 
                json_decode(json_encode($products), false), 
                $session->category_id
            );
        }
    }


    private function messageRequestManufacturer($chat_id, $session_id)
    {
        $mans = [];

        $r = $this->mysqli->query("SELECT * FROM `oc_manufacturer`");

        while ($row = $r->fetch_object()) {
            array_push(
                $mans, 
                [[
                    "text" => $row->name, 
                    "callback_data" => json_encode([
                        "function" => "add_man", 
                        "manufacturer_id" => $row->manufacturer_id, 
                        "session_id" => $session_id
                    ])
                ]]
            );
        }

        $this->sendMessage(
            $chat_id, 
            "Выберите производителя", 
            $this->makeReply($mans)
        );
    }


    private function messageRequestText($chat_id, $session_id)
    {
        $message = $this->sendMessage(
            $chat_id, 
            "Чтобы искать по названию укажите его, если вы хотите увидеть все товары категории под фильтр отправьте 0", 
            $this->makeForceReply()
        );

        $this->add_message(
            $session_id, 
            $chat_id, 
            $message->result->message_id, 
            null
        );
    }


    private function messageRequestMin($chat_id, $session_id)
    {
        $message = $this->sendMessage(
            $chat_id, 
            "Чтобы задать минимальную стоимость, отправьте ее без цифр и пробелов, если вы не хотите указывать минимальную стоимость отправьте 0", 
            $this->makeForceReply()
        );

        $this->add_message(
            $session_id, 
            $chat_id, 
            $message->result->message_id, 
            null
        );
    }


    private function messageRequestMax($chat_id, $session_id)
    {
        $message = $this->sendMessage(
            $chat_id, 
            "Чтобы задать максимальную стоимость, отправьте ее без цифр и пробелов, если вы не хотите указывать максимальную стоимость отправьте 0", 
            $this->makeForceReply()
        );

        $this->add_message(
            $session_id, 
            $chat_id, 
            $message->result->message_id, 
            null
        );
    }


    private function add_message($session_id, $user_id, $message_id, $message_data)
    {
        $this->mysqli->query("INSERT INTO `telegram_messages` (`id`, `user_id`, `session_id`, `data`) VALUES ('$message_id', '$user_id', '$session_id', '$message_data')");
    }


    private function new_session($user_id, $category_id)
    {
        //$this->mysqli->query("DELETE FROM `telegram_sessions` WHERE `user_id` = '$user_id'");
        $this->mysqli->query("INSERT INTO `telegram_sessions` (`user_id`, `category_id`, `step`) VALUES ('$user_id', '$category_id', '1')");
        return $this->mysqli->query("SELECT * FROM `telegram_sessions` WHERE `user_id` = '$user_id' ORDER BY `id` DESC")->fetch_object();
    }


    private function get_session($session_id)
    {
        return $this->mysqli->query("SELECT * FROM `telegram_sessions` WHERE `id` = '$session_id'")->fetch_object();
    }


    private function update_session($session)
    {
        $this->mysqli->query("UPDATE `telegram_sessions` SET `step` = '{$session->step}', `min` = '{$session->min}', `max` = '{$session->max}', `man_id`  = '{$session->man_id}', `text_request` = '{$session->text_request}' WHERE `id` = '$session->id'");
    }

}
