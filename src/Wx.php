<?php
namespace shangxin\yii_wx;
use yii\base\Component;
class Weeex extends Component {

    public $appid;
    public $secret;
    public $login_url;
    public $refund_url;
    public $ssl_cert_path;
    public $ssl_key_path;
    public $mch_id;
    public $grant_type;

    public $request_params = array();
    public $request_uri;

    public function get_wx_info($code)
    {
        $this->request_params['appid'] = $this->appid;
        $this->request_params['js_code'] = $code;
        $this->request_params['secret'] = $this->secret;
        $this->request_params['grant_type'] = $this->grant_type;

        $this->request_uri = $this->login_url."?".http_build_query($this->request_params);
        $request_res = $this->curl_get_request($this->request_uri);
        if(array_key_exists('errcode',$res)){
            throw new \Exception($res['errmsg']);
        }
        return $res;
    }

    public  function curl_get_request($url){
        //初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 不从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //执行并获取HTML文档内容
        if(($output = curl_exec($ch)) === false){
            throw new \Exception(curl_error($ch));
        }
        //释放curl句柄
        curl_close($ch);
        return json_decode($output,true);
    }


}