<?php

// DONT CHANGE THIS
/*==========> INFO 
 * CODE     : BY ZLAXTERT
 * SCRIPT   : EMAIL BOUNCE CHECKER
 * VERSION  : V4
 * TELEGRAM : t.me/zlaxtert
 * BY       : DARKXCODE
 */


//========> REQUIRE

require_once "function/function.php";
require_once "function/gangbang.php";
require_once "function/threesome.php";
require_once "function/settings.php";

//========> BANNER

echo banner();
echo banner2();

//========> GET FILE

enterlist:
echo "$WH [$GR+$WH] Your file ($YL example.txt $WH) $GR>> $BL";
$listname = trim(fgets(STDIN));
if (empty($listname) || !file_exists($listname)) {
    echo PHP_EOL . PHP_EOL . "$WH [$YL!$WH] $RD FILE NOT FOUND$WH [$YL!$WH]$DEF" . PHP_EOL . PHP_EOL;
    goto enterlist;
}
$lists = array_unique(explode("\n", str_replace("\r", "", file_get_contents($listname))));


//=========> THREADS

reqemail:
echo "$WH [$GR+$WH] Threads ($YL Max 15 $WH) ($YL Recommended 5-10 $WH) $GR>> $BL";
$reqemail = trim(fgets(STDIN));
$reqemail = (empty($reqemail) || !is_numeric($reqemail) || $reqemail <= 0) ? 7 : $reqemail;
if ($reqemail > 15) {
    echo PHP_EOL . PHP_EOL . "$WH [$YL!$WH] $RD MAX 15$WH [$YL!$WH]$DEF" . PHP_EOL . PHP_EOL;
    goto reqemail;
}

//=========> COUNT

$live_risk = 0;
$live_deliv = 0;
$die = 0;
$rto = 0;
$unknown = 0;
$limit = 0;
$no = 0;
$total = count($lists);
echo "\n\n$WH [$YL!$WH] TOTAL $GR$total$WH LISTS [$YL!$WH]$DEF\n\n";

//========> LOOPING

$rollingCurl = new \RollingCurl\RollingCurl();

foreach ($lists as $list) {
    // GET SETTINGS
    if (strtolower($mode_proxy) == "off") {
        $Proxies = "";
        $proxy_Auth = $proxy_pwd;
        $type_proxy = $proxy_type;
        $apikey = GetApikey($thisApikey);
        $APIs = GetApiS($thisApi);
    } else {
        $Proxies = GetProxy($proxy_list);
        $proxy_Auth = $proxy_pwd;
        $type_proxy = $proxy_type;
        $apikey = GetApikey($thisApikey);
        $APIs = GetApiS($thisApi);
    }
    // EXPLODE
    $email = multiexplode(array(":", "|", "/", ";", ""), $list)[0];
    $pass = multiexplode(array(":", "|", "/", ";", ""), $list)[1];
    //API
    $api = $APIs . "/validator/bounceV4/?list=$list&proxy=$Proxies&proxyAuth=$proxy_Auth&type_proxy=$type_proxy&apikey=$apikey";
    //CURL
    $rollingCurl->setOptions(array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_MAXREDIRS => 10, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 200))->get($api);

}

//==========> ROLLING CURL

$rollingCurl->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$results) {
    global $listname, $no, $total, $live_deliv, $live_risk, $die, $unknown, $limit, $rto;
    $no++;
    parse_str(parse_url($request->getUrl(), PHP_URL_QUERY), $params);
    $list = $params["list"];
    //RESPONSE
    $x = $request->getResponseText();
    $js = json_decode($x, TRUE);
    $msg = $js['data']['msg'];

    $jam = Jam();



    //============> COLLOR
    $BL = collorLine("BL");
    $RD = collorLine("RD");
    $GR = collorLine("GR");
    $YL = collorLine("YL");
    $MG = collorLine("MG");
    $DEF = collorLine("DEF");
    $CY = collorLine("CY");
    $WH = collorLine("WH");

    //============> RESPONSE

    if (strpos($x, '"status":"success"')) {

        // GET RESPONSE JSON
        
        $email = $js['data']['info']['email'];
        $score = $js['data']['info']['score'];
        $result = $js['data']['info']['result'];
        $reason = $js['data']['info']['reason'];

        if ($score < 1) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 11) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 21) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 31) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 41) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 51) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 61) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 71) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 81) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score < 91) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else if ($score == 100) {

            if ($result == "undeliverable") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "unknown") {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "risky") {
                $live_risk++;
                save_file("result/live_risk.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL RISK$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else if ($result == "deliverable") {
                $live_deliv++;
                save_file("result/live_deliverable.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$GR DELIVERABLE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $WH$result$DEF ] [$YL REASON$DEF: $WH$reason$DEF ] [$YL SCORE$DEF: $WH$score$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            } else {
                $die++;
                save_file("result/die.txt", "$email");
                echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
            }

        } else {
            $die++;
            save_file("result/die.txt", "$email");
            echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $email$DEF | [$YL RESULT$DEF: $MG$result$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
        }


    } else if (strpos($x, '"msg":"Incorrect APIkey!"')) {

        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $list$DEF | [$YL MSG$DEF:$MG Incorrect APIkey!$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;

    } else if (strpos($x, '"status":"die"')) {
        $die++;
        save_file("result/die.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$RD DIE$DEF =>$BL $list$DEF | [$YL MSG$DEF: $MG$msg$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
    } else if (strpos($x, '"status":"unknown"')) {
        $limit++;
        save_file("result/limit.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$CY LIMIT$DEF =>$BL $list$DEF | [$YL MSG$DEF: $MG$msg$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
    } else if ($x == "") {
        $rto++;
        save_file("result/RTO.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$DEF TIMEOUT$DEF =>$BL $list$DEF | [$YL MSG$DEF:$MG REQUEST TIMEOUT!$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
    } else if (strpos($x, 'Request Timeout')) {
        $rto++;
        save_file("result/RTO.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$DEF TIMEOUT$DEF =>$BL $list$DEF | [$YL MSG$DEF:$MG REQUEST TIMEOUT!$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
    } else if (strpos($x, 'Service Unavailable')) {
        $rto++;
        save_file("result/RTO.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$DEF TIMEOUT$DEF =>$BL $list$DEF | [$YL MSG$DEF:$MG REQUEST TIMEOUT!$DEF ] | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
    } else {
        $unknown++;
        save_file("result/unknown.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jam$DEF]$YL UNKNOWN$DEF =>$BL $list$DEF | BY$CY DARKXCODE$DEF (V4)" . PHP_EOL;
    }
})->setSimultaneousLimit((int) $reqemail)->execute();

//============> END

echo PHP_EOL;
echo "================[DONE]================" . PHP_EOL;
echo " DATE             : " . $date . PHP_EOL;
echo " LIVE DELIVERABLE : " . $live_deliv . PHP_EOL;
echo " LIVE RISK        : " . $live_risk . PHP_EOL;
echo " DIE              : " . $die . PHP_EOL;
echo " TIMEOUT          : " . $rto . PHP_EOL;
echo " UNKNOWN          : " . $unknown . PHP_EOL;
echo " TOTAL            : " . $total . PHP_EOL;
echo "======================================" . PHP_EOL;
echo "[+] RATIO VALID     => $GR" . round(RatioCheck($live_deliv, $total)) . "%$DEF" . PHP_EOL;
echo "[+] RATIO LIVE RISK => $YL" . round(RatioCheck($live_risk, $total)) . "%$DEF" . PHP_EOL . PHP_EOL;
echo "[!] NOTE : CHECK AGAIN FILE 'unknown.txt' or 'RTO.txt' [!]" . PHP_EOL;
echo "This file '" . $listname . "'" . PHP_EOL;
echo "File saved in folder 'result/' " . PHP_EOL . PHP_EOL;

// ==========> FUNCTION

function collorLine($col)
{
    $data = array(
        "GR" => "\e[32;1m",
        "RD" => "\e[31;1m",
        "BL" => "\e[34;1m",
        "YL" => "\e[33;1m",
        "CY" => "\e[36;1m",
        "MG" => "\e[35;1m",
        "WH" => "\e[37;1m",
        "DEF" => "\e[0m"
    );
    $collor = $data[$col];
    return $collor;
}
function multiexplode($delimiters, $string)
{
    $one = str_replace($delimiters, $delimiters[0], $string);
    $two = explode($delimiters[0], $one);
    return $two;
}

?>