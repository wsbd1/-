<?php


namespace Home\Controller;


use Think\Controller;

class TestController extends Controller
{
    public function login(){
       $this->display();
    }

    /**
     * 使用Get的方式返回：challenge和capthca_id 此方式以实现前后端完全分离的开发模式 专门实现failback
     *
     */
    public function startCaptchaServlet(){
        Vendor('DragValidate.config.config');
        Vendor('DragValidate.lib.geetestlib');
        if($_GET['type'] == 'pc'){
            $GtSdk = new \GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        }elseif ($_GET['type'] == 'mobile') {
            $GtSdk = new \GeetestLib(MOBILE_CAPTCHA_ID, MOBILE_PRIVATE_KEY);
        }
        session_start();
        $user_id = "test";

        $status = $GtSdk->pre_process($user_id);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $user_id;
        echo $GtSdk->get_response_str();
    }

    public function verifyLoginServlet(){
        Vendor('DragValidate.config.config');
        Vendor('DragValidate.lib.geetestlib');
        session_start();
        if($_POST['type'] == 'pc'){
            $GtSdk = new \GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        }elseif ($_POST['type'] == 'mobile') {
            $GtSdk = new \GeetestLib(MOBILE_CAPTCHA_ID, MOBILE_PRIVATE_KEY);
        }
        $user_id = $_SESSION['user_id'];
        if ($_SESSION['gtserver'] == 1) {   //服务器正常
            $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $user_id);
            if ($result) {
                echo '{"status":"success"}';
            } else{
                echo '{"status":"fail"}';
            }
        }else{  //服务器宕机,走failback模式
            if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                echo '{"status":"success"}';
            }else{
                echo '{"status":"fail"}';
            }
        }
    }
}