<?php
namespace shangxin\yii_wx;
use yii\base\Component;
class Wx extends Component {

    /**
     * @var 微信appid
     */
    public $appid;

    public $secret;
    /**
     * @var 获取用户信息请求地址
     */
    public $user_info_url;
    /**
     * @var 获取access_token地址
     */
    public $access_token_url;
    /**
     * @var 退款地址
     */
    public $refund_url;
    public $ssl_cert_path;
    public $ssl_key_path;
    public $mch_id;
    public $grant_type;

    public $request_params = array();
    public $request_uri;
    public $temp_response;

    /**
     * 获取用户信息
     * @param $code
     * @return $this
     * @throws \Exception
     */
    public function get_wx_info($code)
    {
        $this->get_access_token($code)->reset_request()->get_user_info();
        if(array_key_exists('errcode',$request_res)){
            throw new \Exception($request_res['errmsg']);
        }
        return $this;
    }

    /**
     * 获取微信access_token
     * @param $code
     * @return $this
     * @throws \Exception
     */
    public function get_access_token($code)
    {
        $this->request_params['appid'] = $this->appid;
        $this->request_params['code'] = $code;
        $this->request_params['secret'] = $this->secret;
        $this->request_params['grant_type'] = $this->grant_type;
        $this->request_uri = $this->access_token_url."?".http_build_query($this->request_params);
        $request_res = $this->curl_get_request($this->request_uri);
        if(array_key_exists('errcode',$request_res)){
            throw new \Exception($request_res['errmsg']);
        }
        $this->temp_response = $request_res;
        return $this;
    }

    /**
     * 重置请求信息
     * @return $this
     */
    public function reset_request(){
        $this->request_params = [];
        $this->request_uri = "";
        return $this;
    }

    /**
     * 根据access_token获取用户信息
     * @return mixed
     * @throws \Exception
     */
    public function get_user_info()
    {
        $this->request_params['access_token'] = $this->temp_response['access_token'];
        $this->request_params['openid'] = $this->temp_response['openid'];
        $this->request_uri = $this->user_info_url."?".http_build_query($this->request_params);
        $request_res = $this->curl_get_request($this->request_uri);
        if(array_key_exists('errcode',$request_res)){
            throw new \Exception($request_res['errmsg']);
        }
        return $request_res;
    }


    /**
     * curl---get方式请求
     * @param $url
     * @return mixed
     * @throws \Exception
     */
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