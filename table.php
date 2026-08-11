<?php
require_once 'function.php';
require_once 'config.php';
require_once 'botapi.php';
global $pdo;
//-----------------------------------------------------------------
try {

    $tableName = 'user';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            id VARCHAR(500) PRIMARY KEY,
            limit_usertest INT(100) NOT NULL,
            roll_Status BOOL NOT NULL,
            username VARCHAR(500) NOT NULL,
            Processing_value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            Processing_value_one TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            Processing_value_tow TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            Processing_value_four TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            step VARCHAR(500) NOT NULL,
            description_blocking TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
            number VARCHAR(300) NOT NULL,
            Balance INT(255) NOT NULL,
            User_Status VARCHAR(500) NOT NULL,
            pagenumber INT(10) NOT NULL,
            message_count VARCHAR(100) NOT NULL,
            last_message_time VARCHAR(100) NOT NULL,
            agent VARCHAR(100) NOT NULL,
            affiliatescount VARCHAR(100) NOT NULL,
            affiliates VARCHAR(100) NOT NULL,
            namecustom VARCHAR(300) NOT NULL,
            number_username VARCHAR(300) NOT NULL,
            register VARCHAR(100) NOT NULL,
            verify VARCHAR(100) NOT NULL,
            cardpayment VARCHAR(100) NOT NULL,
            codeInvitation VARCHAR(100) NULL,
            pricediscount VARCHAR(100) NULL   DEFAULT '0',
            hide_mini_app_instruction VARCHAR(20) NULL   DEFAULT '0',
            maxbuyagent VARCHAR(100) NULL   DEFAULT '0',
            joinchannel VARCHAR(100) NULL   DEFAULT '0',
            checkstatus VARCHAR(50) NULL   DEFAULT '0',
            bottype TEXT NULL ,
            score INT(255) NULL DEFAULT '0',
            limitchangeloc VARCHAR(50) NULL   DEFAULT '0',
            status_cron VARCHAR(20)  NULL DEFAULT '1',
            expire VARCHAR(100) NULL ,
            token VARCHAR(100) NULL,
            lang VARCHAR(5) NULL  DEFAULT 'fa'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
    } else {
        addFieldToTable($tableName, "lang", "fa", "varchar(5)");
        addFieldToTable($tableName, 'token', null, "VARCHAR(100)");
        addFieldToTable($tableName, 'status_cron', "1", "VARCHAR(20)");
        addFieldToTable($tableName, 'expire', NULL, "VARCHAR(100)");
        addFieldToTable($tableName, 'limitchangeloc', '0', "VARCHAR(50)");
        addFieldToTable($tableName, 'bottype', '0', "TEXT");
        addFieldToTable($tableName, 'score', '0', "INT(255)");
        addFieldToTable($tableName, 'checkstatus', '0', "VARCHAR(50)");
        addFieldToTable($tableName, 'joinchannel', '0', "VARCHAR(100)");
        addFieldToTable($tableName, 'maxbuyagent', '0');
        addFieldToTable($tableName, 'agent', 'f');
        addFieldToTable($tableName, 'verify', '1');
        addFieldToTable($tableName, 'register', 'none');
        addFieldToTable($tableName, 'namecustom', 'none');
        addFieldToTable($tableName, 'number_username', '100');
        addFieldToTable($tableName, 'cardpayment', '1');
        addFieldToTable($tableName, 'affiliatescount', '0');
        addFieldToTable($tableName, 'affiliates', '0');
        addFieldToTable($tableName, 'message_count', '0');
        addFieldToTable($tableName, 'last_message_time', '0');
        addFieldToTable($tableName, 'Processing_value_four', '');
        addFieldToTable($tableName, 'username', 'none');
        addFieldToTable($tableName, 'Processing_value', 'none');
        addFieldToTable($tableName, 'number', 'none');
        addFieldToTable($tableName, 'pagenumber', '');
        addFieldToTable($tableName, 'codeInvitation', null);
        addFieldToTable($tableName, 'pricediscount', "0");
        addFieldToTable($tableName, 'hide_mini_app_instruction', '0', "VARCHAR(20)");
    }
} catch (PDOException $e) {
    file_put_contents('error_log user', $e->getMessage());
}

//-----------------------------------------------------------------
try {

    $tableName = 'help';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name_os varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        Media_os varchar(5000) NOT NULL,
        type_Media_os varchar(500) NOT NULL,
        category TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        Description_os TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
    } else {
        addFieldToTable("help", "category", null, "TEXT");
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//-----------------------------------------------------------------
try {

    $tableName = 'setting';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $DATAAWARD = json_encode(array(
        'one' => "0",
        "tow" => "0",
        "theree" => "0"
    ));
    $limitlist = json_encode(array(
        'free' => 100,
        'all' => 100,
    ));
    $status_cron = json_encode(array(
        'day' => true,
        'volume' => true,
        'remove' => false,
        'remove_volume' => false,
        'test' => false,
        'on_hold' => false,
        'uptime_node' => false,
        'uptime_panel' => false,
    ));
    $keyboardmain = '{"keyboard":[[{"text":"text_sell"},{"text":"text_extend"}],[{"text":"text_usertest"},{"text":"text_wheel_luck"}],[{"text":"text_Purchased_services"},{"text":"accountwallet"}],[{"text":"text_affiliates"},{"text":"text_Tariff_list"}],[{"text":"text_support"},{"text":"text_help"}]]}';
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
        Bot_Status varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        roll_Status varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        get_number varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        iran_number varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        NotUser varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        Channel_Report varchar(600)  NULL,
        limit_usertest_all varchar(600)  NULL,
        affiliatesstatus varchar(600)  NULL,
        affiliatespercentage varchar(600)  NULL,
        removedayc varchar(600)  NULL,
        showcard varchar(200)  NULL,
        numbercount varchar(600)  NULL,
        statusnewuser varchar(600)  NULL,
        statusagentrequest varchar(600)  NULL,
        statuscategory varchar(200)  NULL,
        statusterffh varchar(200)  NULL,
        volumewarn varchar(200)  NULL,
        inlinebtnmain varchar(200)  NULL,
        verifystart varchar(200)  NULL,
        id_support varchar(200)  NULL,
        statusnamecustom varchar(100)  NULL,
        statuscategorygenral varchar(100)  NULL,
        statussupportpv varchar(100)  NULL,
        agentreqprice varchar(100)  NULL,
        bulkbuy varchar(100)  NULL,
        on_hold_day varchar(100)  NULL,
        cronvolumere varchar(100)  NULL,
        verifybucodeuser varchar(100)  NULL,
        scorestatus varchar(100)  NULL,
        Lottery_prize TEXT  NULL,
        wheelـluck varchar(45)  NULL,
        wheelـluck_price varchar(45)  NULL,
        btn_status_extned varchar(45)  NULL,
        daywarn varchar(45)  NULL,
        categoryhelp varchar(45)  NULL,
        linkappstatus varchar(45)  NULL,
        wheelagent varchar(45)  NULL,
        Lotteryagent varchar(45)  NULL,
        statusfirstwheel varchar(45)  NULL,
        statuslimitchangeloc varchar(45)  NULL,
        Debtsettlement varchar(45)  NULL,
        Dice varchar(45) NULL,
        keyboardmain TEXT NOT NULL,
        statusnoteforf varchar(45) NOT NULL,
        statuscopycart varchar(45) NOT NULL,
        timeauto_not_verify varchar(20) NOT NULL,
        status_keyboard_config varchar(20)  NULL,
        cron_status TEXT NOT NULL,
        text_edit JSON NULL,
        limitnumber varchar(200)  NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
        $stmt = $pdo->prepare("INSERT INTO setting (
Bot_Status,roll_Status,get_number,limit_usertest_all,iran_number,NotUser,
affiliatesstatus,affiliatespercentage,removedayc,showcard,statuscategory,
numbercount,statusnewuser,statusagentrequest,volumewarn,inlinebtnmain,
verifystart,statussupportpv,statusnamecustom,statuscategorygenral,
agentreqprice,cronvolumere,bulkbuy,on_hold_day,verifybucodeuser,
scorestatus,Lottery_prize,wheelـluck,wheelـluck_price,daywarn,
categoryhelp,linkappstatus,wheelagent,
Lotteryagent,statusfirstwheel,statuslimitchangeloc,limitnumber,
Debtsettlement,Dice,keyboardmain,statusnoteforf,statuscopycart,
timeauto_not_verify,status_keyboard_config,cron_status
) VALUES (
'botstatuson','rolleon','offAuthenticationphone','1','offAuthenticationiran','offnotuser',
'offaffiliates','0','0','1','offcategory',
'0','onnewuser','onrequestagent','2','offinline',
'offverify','offpvsupport','offnamecustom','offcategorys',
'0','5','onbulk','4','offverify',
'0','$DATAAWARD','0','0','2',
'0','0','0',
'0','1','1','$limitlist',
'0','0','$keyboardmain','0','0',
'1','0','$status_cron'
)");
        $stmt->execute();
    } else {
        addFieldToTable("setting", "cron_status", $status_cron, "TEXT");
        addFieldToTable("setting", "text_edit", "{}", "JSON");
        addFieldToTable("setting", "status_keyboard_config", "1", "varchar(20)");
        addFieldToTable("setting", "statusnoteforf", "1", "varchar(20)");
        addFieldToTable("setting", "timeauto_not_verify", "4", "varchar(20)");
        addFieldToTable("setting", "statuscopycart", "0", "varchar(20)");
        addFieldToTable("setting", "keyboardmain", $keyboardmain, "TEXT");
        addFieldToTable("setting", "Dice", '0', "varchar(45)");
        addFieldToTable("setting", "Debtsettlement", '1', "varchar(45)");
        addFieldToTable("setting", "limitnumber", $limitlist, "varchar(200)");
        addFieldToTable("setting", "statuslimitchangeloc", "0", "varchar(45)");
        addFieldToTable("setting", "statusfirstwheel", "0", "varchar(45)");
        addFieldToTable("setting", "Lotteryagent", "1", "varchar(45)");
        addFieldToTable("setting", "wheelagent", "1", "varchar(45)");
        addFieldToTable("setting", "linkappstatus", "0", "varchar(45)");
        addFieldToTable("setting", "categoryhelp", "0", "varchar(45)");
        addFieldToTable("setting", "daywarn", "2", "varchar(45)");
        addFieldToTable("setting", "btn_status_extned", "0", "varchar(45)");
        addFieldToTable("setting", "wheelـluck_price", "0", "varchar(45)");
        addFieldToTable("setting", "wheelـluck", "0", "varchar(45)");
        addFieldToTable("setting", "Lottery_prize", $DATAAWARD, "TEXT");
        addFieldToTable("setting", "scorestatus", "0", "VARCHAR(100)");
        addFieldToTable("setting", "verifybucodeuser", "offverify", "VARCHAR(100)");
        addFieldToTable("setting", "on_hold_day", "4", "VARCHAR(100)");
        addFieldToTable("setting", "bulkbuy", "onbulk", "VARCHAR(100)");
        addFieldToTable("setting", "statuscategorygenral", "offcategorys", "VARCHAR(100)");
        addFieldToTable("setting", "cronvolumere", "5", "VARCHAR(100)");
        addFieldToTable("setting", "agentreqprice", "0", "VARCHAR(100)");
        addFieldToTable("setting", "statusnamecustom", "offnamecustom", "VARCHAR(100)");
        addFieldToTable("setting", "id_support", "0", "VARCHAR(100)");
        addFieldToTable("setting", "statussupportpv", "offpvsupport", "VARCHAR(100)");
        addFieldToTable("setting", "affiliatespercentage", "0", "VARCHAR(600)");
        addFieldToTable("setting", "inlinebtnmain", "offinline", "VARCHAR(200)");
        addFieldToTable("setting", "volumewarn", "2", "VARCHAR(200)");
        addFieldToTable("setting", "statusagentrequest", "onrequestagent", "VARCHAR(600)");
        addFieldToTable("setting", "statusnewuser", "onnewuser", "VARCHAR(600)");
        addFieldToTable("setting", "numbercount", "0", "VARCHAR(600)");
        addFieldToTable("setting", "statuscategory", "offcategory", "VARCHAR(600)");
        addFieldToTable("setting", "showcard", "1", "VARCHAR(200)");
        addFieldToTable("setting", "removedayc", "1", "VARCHAR(200)");
        addFieldToTable("setting", "affiliatesstatus", "offaffiliates", "VARCHAR(600)");
        addFieldToTable("setting", "NotUser", "offnotuser", "VARCHAR(200)");
        addFieldToTable("setting", "iran_number", "offAuthenticationiran", "VARCHAR(200)");
        addFieldToTable("setting", "get_number", "onAuthenticationphone", "VARCHAR(200)");
        addFieldToTable("setting", "limit_usertest_all", "1", "VARCHAR(200)");
        addFieldToTable("setting", "Channel_Report", "0", "VARCHAR(200)");
        addFieldToTable("setting", "Bot_Status", "botstatuson", "VARCHAR(200)");
        addFieldToTable("setting", "roll_Status", "rolleon", "VARCHAR(200)");
        addFieldToTable("setting", "verifystart", "offverify", "VARCHAR(200)");
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}

//-----------------------------------------------------------------
try {
    $tableName = 'admin';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
        id_admin varchar(500) PRIMARY KEY NOT NULL,
        username varchar(1000) NOT NULL,
        password varchar(1000) NOT NULL,
        rule varchar(500) NOT NULL)");
        $stmt->execute();
        $randomString = bin2hex(random_bytes(5));
        $stmt = $pdo->prepare("INSERT INTO admin (id_admin,rule,username,password) VALUES (:mp1,'administrator','admin',:mp2)");
        $stmt->execute([':mp1' => $adminnumber, ':mp2' => $randomString]);
    } else {
        addFieldToTable("admin", "rule", "administrator", "VARCHAR(200)");
        addFieldToTable("admin", "username", null, "VARCHAR(200)");
        addFieldToTable("admin", "password", null, "VARCHAR(200)");
    }
} catch (Exception $e) {
    file_put_contents('error_log admin', $e->getMessage());
}
//-----------------------------------------------------------------
try {
    $tableName = 'channels';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            remark varchar(200) NOT NULL,
            linkjoin varchar(200) NOT NULL,
            link varchar(200) NOT NULL)");
        $stmt->execute();
    } else {
        addFieldToTable("channels", "remark", null, "VARCHAR(200)");
        addFieldToTable("channels", "linkjoin", null, "VARCHAR(200)");
    }
} catch (Exception $e) {
    file_put_contents('error_log channels', $e->getMessage());
}
$textbotlang = languagechange();

//--------------------------------------------------------------
try {

    $result = $pdo->query("SHOW TABLES LIKE 'marzban_panel'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE marzban_panel (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_panel varchar(200) NULL,
        name_panel varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        status varchar(500) NULL,
        url_panel varchar(2000) NULL,
        username_panel varchar(200) NULL,
        password_panel TEXT NULL,
        agent varchar(200) NULL,
        sublink varchar(500) NULL,
        config varchar(500) NULL,
        MethodUsername varchar(700) NULL,
        TestAccount varchar(100) NULL,
        limit_panel varchar(100) NULL,
        namecustom varchar(100) NULL,
        Methodextend varchar(100) NULL,
        conecton varchar(100) NULL,
        linksubx varchar(1000) NULL,
        inboundid varchar(100) NULL,
        type varchar(100) NULL,
        inboundstatus varchar(100) NULL,
        inbound_deactive varchar(100) NULL,
        time_usertest varchar(100) NULL,
        val_usertest varchar(100)  NULL,
        secret_code varchar(200) NULL,
        priceChangeloc varchar(200) NULL,
        priceextravolume varchar(500) NULL,
        pricecustomvolume varchar(500) NULL,
        pricecustomtime varchar(500) NULL,
        priceextratime varchar(500) NULL,
        mainvolume varchar(500) NULL,
        maxvolume varchar(500) NULL,
        maintime varchar(500) NULL,
        maxtime varchar(500) NULL,
        status_extend varchar(100) NULL,
        datelogin TEXT NULL,
        proxies TEXT NULL,
        inbounds TEXT NULL,
        subvip varchar(60) NULL,
        changeloc varchar(60) NULL,
        on_hold_test varchar(60) NOT NULL,
        version_panel varchar(60) NOT NULL,
        customvolume TEXT NULL,
        hide_user TEXT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table marzban_panel" . implode(' ', $pdo->errorInfo());
        }
    } else {
        $VALUE = json_encode(array(
            'f' => '0',
            'n' => '0',
            'n2' => '0'
        ));
        $valueprice = json_encode(array(
            'f' => "4000",
            'n' => "4000",
            'n2' => "4000"
        ));
        $valuemain = json_encode(array(
            'f' => "1",
            'n' => "1",
            'n2' => "1"
        ));
        $valuemax = json_encode(array(
            'f' => "1000",
            'n' => "1000",
            'n2' => "1000"
        ));
        $valuemax_time = json_encode(array(
            'f' => "365",
            'n' => "365",
            'n2' => "365"
        ));
        addFieldToTable("marzban_panel", "version_panel", "0", "VARCHAR(60)");
        addFieldToTable("marzban_panel", "on_hold_test", "1", "VARCHAR(60)");
        addFieldToTable("marzban_panel", "proxies", null, "TEXT");
        addFieldToTable("marzban_panel", "inbounds", null, "TEXT");
        addFieldToTable("marzban_panel", "customvolume", $VALUE, "TEXT");
        addFieldToTable("marzban_panel", "subvip", "offsubvip", "VARCHAR(60)");
        addFieldToTable("marzban_panel", "changeloc", "offchangeloc", "VARCHAR(60)");
        addFieldToTable("marzban_panel", "hide_user", null, "TEXT");
        addFieldToTable("marzban_panel", "status_extend", "on_extend", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "code_panel", null, "VARCHAR(50)");
        addFieldToTable("marzban_panel", "priceextravolume", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "pricecustomvolume", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "pricecustomtime", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "priceextratime", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "priceChangeloc", "0", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "mainvolume", $valuemain, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "maxvolume", $valuemax, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "maintime", $valuemain, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "maxtime", $valuemax_time, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "MethodUsername", $textbotlang['keyboard']['numericIdRandom'], "VARCHAR(100)");
        addFieldToTable("marzban_panel", "datelogin", null, "TEXT");
        addFieldToTable("marzban_panel", "val_usertest", "100", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "time_usertest", "1", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "secret_code", null, "VARCHAR(200)");
        addFieldToTable("marzban_panel", "inboundstatus", "offinbounddisable", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "inbound_deactive", "0", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "agent", "all", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "inboundid", "1", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "linksubx", null, "VARCHAR(200)");
        addFieldToTable("marzban_panel", "conecton", "offconecton", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "type", "marzban", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "Methodextend", $textbotlang['keyboard']['resetVolumeTime'], "VARCHAR(100)");
        addFieldToTable("marzban_panel", "namecustom", "vpn", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "limit_panel", "unlimted", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "TestAccount", "ONTestAccount", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "status", "active", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "sublink", "onsublink", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "config", "offconfig", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "version_panel", "0", "VARCHAR(60)");
        $max_stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(code_panel, 3) AS UNSIGNED)) as max_num FROM marzban_panel WHERE code_panel LIKE '7e%'");
        $max_stmt->execute();
        $max_row = $max_stmt->fetch(PDO::FETCH_ASSOC);
        $next_num = $max_row['max_num'] ? (int) $max_row['max_num'] + 1 : 15;
        $stmt = $pdo->prepare("SELECT id FROM marzban_panel WHERE code_panel IS NULL OR code_panel = ''");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $code = '7e' . $next_num;
            $stmt = $pdo->prepare("UPDATE marzban_panel SET code_panel = ? WHERE id = ?");
            $stmt->execute([$code, $row['id']]);
            $next_num++;
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}

//-----------------------------------------------------------------
try {

    $result = $pdo->query("SHOW TABLES LIKE 'product'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE product (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_product varchar(200)  NULL,
        name_product varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        price_product varchar(2000) NULL,
        Volume_constraint varchar(2000) NULL,
        Location varchar(200) NULL,
        Service_time varchar(200) NULL,
        agent varchar(100) NULL,
        note TEXT NULL,
        data_limit_reset varchar(200) NULL,
        one_buy_status varchar(20) NOT NULL,
        inbounds TEXT NULL,
        proxies TEXT NULL,
        category varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        hide_panel TEXT  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table product" . implode(' ', $pdo->errorInfo());
        }
    } else {
        addFieldToTable("product", "one_buy_status", "0", "VARCHAR(20)");
        addFieldToTable("product", "Location", null, "VARCHAR(200)");
        addFieldToTable("product", "inbounds", null, "TEXT");
        addFieldToTable("product", "proxies", null, "TEXT");
        addFieldToTable("product", "category", null, "varchar(200)");
        addFieldToTable("product", "note", '', "TEXT");
        addFieldToTable("product", "hide_panel", '{}', "TEXT");
        addFieldToTable("product", "data_limit_reset", "no_reset", "varchar(100)");
        addFieldToTable("product", "agent", "f", "varchar(50)");
        addFieldToTable("product", "code_product", null, "varchar(50)");
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $pdo->query("SHOW TABLES LIKE 'invoice'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE invoice (
        id_invoice varchar(200) PRIMARY KEY,
        id_user varchar(200) NULL,
        username varchar(300) NULL,
        Service_location varchar(300) NULL,
        time_sell VARCHAR(200) NULL,
        name_product varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        price_product varchar(200) NULL,
        Volume varchar(200) NULL,
        Service_time varchar(200) NULL,
        uuid TEXT NULL,
        note varchar(500) NULL,
        user_info TEXT NULL,
        bottype varchar(200) NULL,
        refral varchar(100) NULL,
        time_cron varchar(100) NULL,
        notifctions TEXT NOT NULL,
        Status varchar(200) NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table invoice" . implode(' ', $pdo->errorInfo());
        }
    } else {
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'note'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD note VARCHAR(700)");
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'notifctions'");
        if (($Check_filde)->rowCount() != 1) {
            $data = json_encode(array(
                'volume' => false,
                'time' => false,
            ));
            $result = $pdo->query("ALTER TABLE invoice ADD notifctions TEXT NOT NULL");
            $__q1 = $pdo->prepare("UPDATE invoice SET notifctions = ?");
            $__q1->bindValue(1, $data, PDO::PARAM_STR);
            $__q1->execute();
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'time_cron'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD time_cron VARCHAR(100)");
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'refral'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD refral VARCHAR(100)");
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'bottype'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD bottype VARCHAR(200)");
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'user_info'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD user_info TEXT");
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'time_sell'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD time_sell VARCHAR(200)");
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'uuid'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD uuid TEXT");
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM invoice LIKE 'Status'");
        if (($Check_filde)->rowCount() != 1) {
            $result = $pdo->query("ALTER TABLE invoice ADD Status VARCHAR(100)");
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $pdo->query("SHOW TABLES LIKE 'Payment_report'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE Payment_report (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        id_order varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        time varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        at_updated varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        price varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        dec_not_confirmed TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        Payment_Method varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        payment_Status varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        bottype varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        message_id INT NULL,
        id_invoice varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$result) {
            echo "table Payment_report" . implode(' ', $pdo->errorInfo());
        }
    } else {
        ensureTableUtf8mb4('Payment_report');
        addFieldToTable("Payment_report", "message_id", null, "INT");
        $Check_filde = $pdo->query("SHOW COLUMNS FROM Payment_report LIKE 'Payment_Method'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE Payment_report ADD Payment_Method VARCHAR(200)");
            echo "The Payment_Method field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM Payment_report LIKE 'bottype'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE Payment_report ADD bottype VARCHAR(300)");
            echo "The bottype field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM Payment_report LIKE 'at_updated'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE Payment_report ADD at_updated VARCHAR(200)");
            echo "The at_updated field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM Payment_report LIKE 'id_invoice'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE Payment_report ADD id_invoice VARCHAR(400)");
            $pdo->query("UPDATE Payment_report SET id_invoice = 'none'");
            echo "The id_invoice field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $pdo->query("SHOW TABLES LIKE 'Discount'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE Discount (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code varchar(2000) NULL,
        price varchar(200) NULL,
        limituse varchar(200) NULL,
        limitused varchar(200) NULL)
        ");
        if (!$result) {
            echo "table Discount" . implode(' ', $pdo->errorInfo());
        }
    } else {
        $Check_filde = $pdo->query("SHOW COLUMNS FROM Discount LIKE 'limituse'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE Discount ADD limituse VARCHAR(200)");
            echo "The limituse field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM Discount LIKE 'limitused'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE Discount ADD limitused VARCHAR(200)");
            echo "The limitused field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $pdo->query("SHOW TABLES LIKE 'Giftcodeconsumed'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE  Giftcodeconsumed (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code varchar(2000) NULL,
        id_user varchar(200) NULL)");
        if (!$result) {
            echo "table Giftcodeconsumed" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'PaySetting'");
    $table_exists = ($result->rowCount() > 0);
    $main = 20000;
    $max = 1000000;
    $settings = [
        ['Cartstatus', 'oncard'],
        ['CartDirect', '@cart'],
        ['cardnumber', '603700000000'],
        ['namecard', $textbotlang['db_defaults']['namecardNotSet']],
        ['Cartstatuspv', 'offcardpv'],
        ['apinowpayment', '0'],
        ['nowpaymentstatus', 'offnowpayment'],
        ['digistatus', 'offdigi'],
        ['statusSwapWallet', 'offnSolutions'],
        ['statusaqayepardakht', 'offaqayepardakht'],
        ['merchant_id_aqayepardakht', '0'],
        ['minbalance', '20000'],
        ['maxbalance', '1000000'],
        ['marchent_tronseller', '0'],
        ['walletaddress', '0'],
        ['statustarnado', 'offternado'],
        ['apiternado', '0'],
        ['chashbackcart', '0'],
        ['chashbackstar', '0'],
        ['chashbackperfect', '0'],
        ['chashbackaqaypardokht', '0'],
        ['chashbackiranpay1', '0'],
        ['chashbackiranpay2', '0'],
        ['chashbackplisio', '0'],
        ['chashbackzarinpal', '0'],
        ['checkpaycartfirst', 'offpayverify'],
        ['zarinpalstatus', 'offzarinpal'],
        ['merchant_zarinpal', '0'],
        ['minbalancecart', $main],
        ['maxbalancecart', $max],
        ['minbalancestar', $main],
        ['maxbalancestar', $max],
        ['minbalanceplisio', $main],
        ['maxbalanceplisio', $max],
        ['minbalancedigitaltron', $main],
        ['maxbalancedigitaltron', $max],
        ['minbalanceiranpay1', $main],
        ['maxbalanceiranpay1', $max],
        ['minbalanceiranpay2', $main],
        ['maxbalanceiranpay2', $max],
        ['minbalanceaqayepardakht', $main],
        ['maxbalanceaqayepardakht', $max],
        ['minbalancepaynotverify', $main],
        ['maxbalancepaynotverify', $max],
        ['minbalanceperfect', $main],
        ['maxbalanceperfect', $max],
        ['minbalancezarinpal', $main],
        ['maxbalancezarinpal', $max],
        ['minbalanceiranpay', $main],
        ['maxbalanceiranpay', $max],
        ['minbalancenowpayment', $main],
        ['maxbalancenowpayment', $max],
        ['statusiranpay3', 'oniranpay3'],
        ['apiiranpay', '0'],
        ['chashbackiranpay3', '0'],
        ['helpcart', '2'],
        ['helpaqayepardakht', '2'],
        ['helpstar', '2'],
        ['helpplisio', '2'],
        ['helpiranpay1', '2'],
        ['helpiranpay2', '2'],
        ['helpiranpay3', '2'],
        ['helpperfectmony', '2'],
        ['helpzarinpal', '2'],
        ['helpnowpayment', '2'],
        ['helpofflinearze', '2'],
        ['autoconfirmcart', 'offauto'],
        ['cashbacknowpayment', '0'],
        ['statusstar', '0'],
        ['statusnowpayment', '0'],
        ['Exception_auto_cart', '{}'],
        ['marchent_floypay', '0'],
    ];
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE PaySetting (
        NamePay varchar(500) PRIMARY KEY NOT NULL,
        ValuePay TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table PaySetting" . implode(' ', $pdo->errorInfo());
        }

        foreach ($settings as $setting) {
            $__q2 = $pdo->prepare("INSERT INTO PaySetting (NamePay, ValuePay) VALUES (?, ?)");
            $__q2->bindValue(1, $setting[0], PDO::PARAM_STR);
            $__q2->bindValue(2, $setting[1], PDO::PARAM_STR);
            $__q2->execute();
        }
    } else {
        foreach ($settings as $setting) {
            $__q3 = $pdo->prepare("INSERT IGNORE INTO PaySetting (NamePay, ValuePay) VALUES (?, ?)");
            $__q3->bindValue(1, $setting[0], PDO::PARAM_STR);
            $__q3->bindValue(2, $setting[1], PDO::PARAM_STR);
            $__q3->execute();
        }
    }
    $pdo->exec("DELETE FROM PaySetting WHERE NamePay = 'urlpaymenttron'");
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//----------------------- [ Discount ] --------------------- //
try {
    $result = $pdo->query("SHOW TABLES LIKE 'DiscountSell'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE DiscountSell (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codeDiscount varchar(1000)  NOT NULL,
        price varchar(200)  NOT NULL,
        limitDiscount varchar(500)  NOT NULL,
        agent varchar(500)  NOT NULL,
        usefirst varchar(100)  NOT NULL,
        useuser varchar(100)  NOT NULL,
        code_product varchar(100)  NOT NULL,
        code_panel varchar(100)  NOT NULL,
        time varchar(100)  NOT NULL,
        type varchar(100)  NOT NULL,
        usedDiscount varchar(500) NOT NULL)");
        if (!$result) {
            echo "table DiscountSell" . implode(' ', $pdo->errorInfo());
        }
    } else {
        $Check_filde = $pdo->query("SHOW COLUMNS FROM DiscountSell LIKE 'agent'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE DiscountSell ADD agent VARCHAR(100)");
            echo "The agent discount field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM DiscountSell LIKE 'type'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE DiscountSell ADD type VARCHAR(100)");
            echo "The agent type field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM DiscountSell LIKE 'time'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE DiscountSell ADD time VARCHAR(100)");
            echo "The agent time field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM DiscountSell LIKE 'code_panel'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE DiscountSell ADD code_panel VARCHAR(100)");
            echo "The code_panel discount field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM DiscountSell LIKE 'code_product'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE DiscountSell ADD code_product VARCHAR(100)");
            echo "The code_product discount field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM DiscountSell LIKE 'useuser'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE DiscountSell ADD useuser VARCHAR(100)");
            echo "The useuser discount field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM DiscountSell LIKE 'usefirst'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE DiscountSell ADD usefirst VARCHAR(100)");
            echo "The usefirst discount field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//-----------------------------------------------------------------
try {
    $result = $pdo->query("SHOW TABLES LIKE 'affiliates'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE affiliates (
        description TEXT  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        status_commission varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        Discount varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        price_Discount varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        porsant_one_buy varchar(100),
        id_media varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table affiliates" . implode(' ', $pdo->errorInfo());
        }
        $pdo->query("INSERT INTO affiliates (description,id_media,status_commission,Discount,porsant_one_buy) VALUES ('none','none','oncommission','onDiscountaffiliates','off_buy_porsant')");
    } else {
        $Check_filde = $pdo->query("SHOW COLUMNS FROM affiliates LIKE 'porsant_one_buy'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE affiliates ADD porsant_one_buy VARCHAR(100)");
            $pdo->query("UPDATE affiliates SET porsant_one_buy = 'off_buy_porsant'");
            echo "The Discount field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM affiliates LIKE 'Discount'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE affiliates ADD Discount VARCHAR(100)");
            $pdo->query("UPDATE affiliates SET Discount = 'onDiscountaffiliates'");
            echo "The Discount field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM affiliates LIKE 'price_Discount'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE affiliates ADD price_Discount VARCHAR(100)");
            echo "The price_Discount field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM affiliates LIKE 'status_commission'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE affiliates ADD status_commission VARCHAR(100)");
            $pdo->query("UPDATE affiliates SET status_commission = 'oncommission'");
            echo "The commission field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'shopSetting'");
    $table_exists = ($result->rowCount() > 0);
    $agent_cashback = json_encode(array(
        'n' => 0,
        'n2' => 0
    ));
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE shopSetting (
        Namevalue varchar(500) PRIMARY KEY NOT NULL,
        value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table shopSetting" . implode(' ', $pdo->errorInfo());
        }
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customvolmef','4000')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customvolmen','4000')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customvolmen2','4000')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusextra','offextra')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customtimepricef','4000')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customtimepricen','4000')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customtimepricen2','4000')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusdirectpabuy','ondirectbuy')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('minbalancebuybulk','0')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statustimeextra','ontimeextraa')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusdisorder','offdisorder')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statuschangeservice','onstatus')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusshowprice','offshowprice')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('configshow','onconfig')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('backserviecstatus','on')");
        $pdo->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('chashbackextend','0')");
        $__q4 = $pdo->prepare("INSERT INTO shopSetting (Namevalue,value) VALUES ('chashbackextend_agent',?)");
        $__q4->bindValue(1, $agent_cashback, PDO::PARAM_STR);
        $__q4->execute();
    } else {
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customvolmef','4000')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customvolmen','4000')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customvolmen2','4000')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusextra','offextra')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusdirectpabuy','ondirectbuy')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('minbalancebuybulk','0')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statustimeextra','ontimeextraa')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customtimepricef','4000')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customtimepricen','4000')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customtimepricen2','4000')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusdisorder','offdisorder')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statuschangeservice','onstatus')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusshowprice','offshowprice')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('configshow','onconfig')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('backserviecstatus','on')");
        $pdo->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('chashbackextend','0')");
        $__q5 = $pdo->prepare("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('chashbackextend_agent',?)");
        $__q5->bindValue(1, $agent_cashback, PDO::PARAM_STR);
        $__q5->execute();



    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
//----------------------- [ remove requests ] --------------------- //
try {
    $result = $pdo->query("SHOW TABLES LIKE 'cancel_service'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE cancel_service (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(500)  NOT NULL,
        username varchar(1000)  NOT NULL,
        description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
        status varchar(1000)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table cancel_service" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'service_other'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE service_other (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(500)  NOT NULL,
        username varchar(1000)  NOT NULL,
        value varchar(1000)  NOT NULL,
        time varchar(200)  NOT NULL,
        price varchar(200)  NOT NULL,
        type varchar(1000)  NOT NULL,
        status varchar(200)  NOT NULL,
        output TEXT  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table service_other" . implode(' ', $pdo->errorInfo());
        }
    } else {
        $Check_filde = $pdo->query("SHOW COLUMNS FROM service_other LIKE 'price'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE service_other ADD price VARCHAR(200)");
            echo "The price field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM service_other LIKE 'status'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE service_other ADD status VARCHAR(200)");
            echo "The status field was added ✅";
        }
        $Check_filde = $pdo->query("SHOW COLUMNS FROM service_other LIKE 'output'");
        if (($Check_filde)->rowCount() != 1) {
            $pdo->query("ALTER TABLE service_other ADD output TEXT");
            echo "The output field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'card_number'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE card_number (
        cardnumber varchar(500) PRIMARY KEY,
        namecard  varchar(1000)  NOT NULL)
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table x_ui" . implode(' ', $pdo->errorInfo());
        }
    }
    $columnInfo = $pdo->query("SHOW FULL COLUMNS FROM card_number LIKE 'namecard'");
    if ($columnInfo) {
        $column = $columnInfo->fetch(PDO::FETCH_ASSOC);
        $currentCollation = $column['Collation'] ?? '';
        if (empty($currentCollation) || stripos($currentCollation, 'utf8mb4') === false) {
            $pdo->query("ALTER TABLE card_number CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->query("ALTER TABLE card_number MODIFY cardnumber varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY");
            $pdo->query("ALTER TABLE card_number MODIFY namecard varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        }
        $columnInfo = null;
    }
} catch (Exception $e) {
    file_put_contents('error_log card_number', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'Requestagent'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE Requestagent (
        id varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY,
        username  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        time  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        Description  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        status  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        type  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$result) {
            echo "table Requestagent" . implode(' ', $pdo->errorInfo());
        }
    } else {
        ensureTableUtf8mb4('Requestagent');
    }
} catch (Exception $e) {
    file_put_contents('error_log Requestagent', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'topicid'");
    $table_exists = ($result->rowCount() > 0);
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE topicid (
        report varchar(500) PRIMARY KEY NOT NULL,
        idreport TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table Requestagent" . implode(' ', $pdo->errorInfo());
        }
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','buyreport')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','otherservice')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','paymentreport')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','otherreport')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','reporttest')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','errorreport')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','porsantreport')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','reportnight')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','reportcron')");
        $pdo->query("INSERT INTO topicid (idreport,report) VALUES ('0','backupfile')");
    } else {
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','buyreport')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','otherservice')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','paymentreport')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','otherreport')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','reporttest')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','errorreport')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','porsantreport')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','reportnight')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','reportcron')");
        $pdo->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','backupfile')");




    }
} catch (Exception $e) {
    file_put_contents('error_log topicid', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'manualsell'");
    $table_exists = ($result->rowCount() > 0);
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE manualsell (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codepanel  varchar(100)  NOT NULL,
        codeproduct  varchar(100)  NOT NULL,
        namerecord  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
        username  varchar(500)  NULL,
        contentrecord  TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
        status  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table manualsell" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log manualsell', $e->getMessage());
}
//-----------------------------------------------------------------
try {

    $tableName = 'departman';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idsupport VARCHAR(200) NOT NULL,
            name_departman VARCHAR(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
        $stmt = $pdo->prepare("INSERT INTO departman (idsupport,name_departman) VALUES (?, ?)");
        $stmt->execute([$adminnumber, $textbotlang['db_defaults']['departmanGeneral']]);
    }
} catch (PDOException $e) {
    file_put_contents('error_log departman', $e->getMessage());
}
try {

    $tableName = 'support_message';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Tracking VARCHAR(100) NOT NULL,
            idsupport VARCHAR(100) NOT NULL,
            iduser VARCHAR(100) NOT NULL,
            name_departman VARCHAR(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            result TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            time VARCHAR(200) NOT NULL,
            status ENUM('Answered','Pending','Unseen','Customerresponse','close') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
    } else {
        addFieldToTable("support_message", "result", "0", "TEXT");
    }
} catch (PDOException $e) {
    file_put_contents('error_log suppeor_message', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'wheel_list'");
    $table_exists = ($result->rowCount() > 0);
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE wheel_list (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user  varchar(200)  NOT NULL,
        time  varchar(200)  NOT NULL,
        first_name  varchar(200)  NOT NULL,
        wheel_code  varchar(200)  NOT NULL,
        price  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table wheel_list" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log botsaz', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'botsaz'");
    $table_exists = ($result->rowCount() > 0);
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE botsaz (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user  varchar(200)  NOT NULL,
        bot_token  varchar(200)  NOT NULL,
        admin_ids  TEXT  NOT NULL,
        username  varchar(200)  NOT NULL,
        setting  TEXT  NULL,
        hide_panel  JSON  NOT NULL,
        time  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table botsaz" . implode(' ', $pdo->errorInfo());
        }
    } else {
        addFieldToTable("botsaz", "hide_panel", "{}", "JSON");
    }
} catch (Exception $e) {
    file_put_contents('error_log botsaz', $e->getMessage());
}

try {
    $result = $pdo->query("SHOW TABLES LIKE 'app'");
    $table_exists = ($result->rowCount() > 0);
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE app (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name  varchar(200)   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        link  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table app" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log app', $e->getMessage());
}



try {
    $result = $pdo->query("SHOW TABLES LIKE 'logs_api'");
    $table_exists = ($result->rowCount() > 0);
    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE logs_api (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        header JSON  NULL,
        data JSON  NULL,
        ip  varchar(200)  NOT NULL,
        time  varchar(200)  NOT NULL,
        actions  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table logs_api" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log logs_api', $e->getMessage());
}
//----------------------- [ Category ] --------------------- //
try {
    $result = $pdo->query("SHOW TABLES LIKE 'category'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE category (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        remark varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table category" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}
try {
    $result = $pdo->query("SHOW TABLES LIKE 'reagent_report'");
    $table_exists = ($result->rowCount() > 0);

    if (!$table_exists) {
        $result = $pdo->query("CREATE TABLE reagent_report (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNIQUE  NOT NULL,
        get_gift BOOL   NOT NULL,
        time varchar(50)  NOT NULL,
        reagent varchar(30)  NOT NULL
        )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table affiliates" . implode(' ', $pdo->errorInfo());
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log', $e->getMessage());
}



$balancemain = json_decode(select("PaySetting", "ValuePay", "NamePay", "maxbalance", "select")['ValuePay'], true);
if (!isset($balancemain['f'])) {
    $value = json_encode(array(
        "f" => "1000000",
        "n" => "1000000",
        "n2" => "1000000",
    ));
    $valuemain = json_encode(array(
        "f" => "20000",
        "n" => "20000",
        "n2" => "20000",
    ));
    update("PaySetting", "ValuePay", $value, "NamePay", "maxbalance");
    update("PaySetting", "ValuePay", $valuemain, "NamePay", "minbalance");
}
$pdo->query("ALTER TABLE `invoice` CHANGE `Volume` `Volume` VARCHAR(200)");
$pdo->query("ALTER TABLE `invoice` CHANGE `price_product` `price_product` VARCHAR(200)");
$pdo->query("ALTER TABLE `invoice` CHANGE `name_product` `name_product` VARCHAR(200)");
$pdo->query("ALTER TABLE `invoice` CHANGE `username` `username` VARCHAR(200)");
$pdo->query("ALTER TABLE `invoice` CHANGE `Service_location` `Service_location` VARCHAR(200)");
$pdo->query("ALTER TABLE `invoice` CHANGE `time_sell` `time_sell` VARCHAR(200)");
$pdo->query("ALTER TABLE marzban_panel MODIFY password_panel TEXT COLLATE utf8mb4_bin");
$pdo->query("ALTER TABLE marzban_panel MODIFY name_panel VARCHAR(255) COLLATE utf8mb4_bin");
$pdo->query("ALTER TABLE product MODIFY name_product VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
$pdo->query("ALTER TABLE help MODIFY name_os VARCHAR(500) COLLATE utf8mb4_bin");
try {
    $check = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'ref_code'");
} catch (Exception $e) {
    error_log("[CHECK:$tableName] ❌ " . $e->getMessage());
    exit;
}

if ($check && $check->rowCount() != 0) {
    $pdo->exec("ALTER TABLE `user` DROP `ref_code`");
}
#-----------------[ performance indexes ]-----------------#
$performanceIndexes = [
    ['invoice', 'idx_invoice_id_user', '`id_user`(100)'],
    ['invoice', 'idx_invoice_username', '`username`(150)'],
    ['invoice', 'idx_invoice_status', '`Status`(100)'],
    ['invoice', 'idx_invoice_location', '`Service_location`(150)'],
    ['Payment_report', 'idx_payreport_id_user', '`id_user`(100)'],
    ['Payment_report', 'idx_payreport_id_order', '`id_order`(191)'],
    ['Payment_report', 'idx_payreport_status', '`payment_Status`(100)'],
    ['marzban_panel', 'idx_panel_name', '`name_panel`(150)'],
    ['marzban_panel', 'idx_panel_code', '`code_panel`(100)'],
    ['product', 'idx_product_code', '`code_product`(50)'],
    ['product', 'idx_product_location', '`Location`(100)'],
    ['manualsell', 'idx_manualsell_codepanel', '`codepanel`(100)'],
    ['service_other', 'idx_serviceother_username', '`username`(150)'],
    ['service_other', 'idx_serviceother_type', '`type`(100)'],
    ['user', 'idx_user_affiliates', '`affiliates`'],
];
foreach ($performanceIndexes as $indexDef) {
    list($idxTable, $idxName, $idxCols) = $indexDef;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?");
        $stmt->execute([$idxTable, $idxName]);
        if ((int) $stmt->fetchColumn() == 0) {
            $pdo->exec("ALTER TABLE `$idxTable` ADD INDEX `$idxName` ($idxCols)");
        }
    } catch (Exception $e) {
        error_log("[INDEX:$idxTable.$idxName] " . $e->getMessage());
    }
}
telegram('setwebhook', [
    'url' => "https://$domainhosts/index.php"
]);
